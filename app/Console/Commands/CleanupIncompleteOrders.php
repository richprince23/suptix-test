<?php

namespace App\Console\Commands;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupIncompleteOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:cleanup-incomplete
                            {--timeout=15 : Timeout in minutes for incomplete orders}
                            {--dry-run : Show what would be cleaned up without actually doing it}';

    /**
     * The console command description.
     */
    protected $description = 'Delete incomplete orders, pending payments, and their associated tickets after specified timeout';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timeoutMinutes = (int) $this->option('timeout');
        $isDryRun = $this->option('dry-run');

        // Find incomplete orders older than the timeout
        $cutoffTime = now()->subMinutes($timeoutMinutes);

        $incompleteOrders = Order::where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->with(['tickets', 'payments', 'event', 'organizer'])
            ->get();

        // Find pending payments older than the timeout (including orphaned ones)
        $pendingPayments = Payment::whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', $cutoffTime)
            ->with(['order', 'organizer'])
            ->get();

        if ($incompleteOrders->isEmpty() && $pendingPayments->isEmpty()) {
            $this->info('No incomplete orders or pending payments found to clean up.');

            return Command::SUCCESS;
        }

        $this->info("Found {$incompleteOrders->count()} incomplete orders and {$pendingPayments->count()} pending payments older than {$timeoutMinutes} minutes.");

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');

            if ($incompleteOrders->isNotEmpty()) {
                $this->info("\nIncomplete Orders:");
                $this->table(
                    ['Order #', 'Event', 'Organizer', 'Created', 'Tickets', 'Payments', 'Amount'],
                    $incompleteOrders->map(fn ($order) => [
                        $order->order_number,
                        $order->event->title ?? 'N/A',
                        $order->organizer->name ?? 'N/A',
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->tickets->count(),
                        $order->payments->count(),
                        'GHS '.number_format((float) $order->total_amount, 2),
                    ])
                );
            }

            if ($pendingPayments->isNotEmpty()) {
                $this->info("\nPending Payments:");
                $this->table(
                    ['Payment ID', 'Order #', 'Organizer', 'Amount', 'Status', 'Created'],
                    $pendingPayments->map(fn ($payment) => [
                        $payment->id,
                        $payment->order->order_number ?? 'N/A',
                        $payment->organizer->name ?? 'N/A',
                        'GHS '.number_format((float) $payment->amount, 2),
                        $payment->status,
                        $payment->created_at->format('Y-m-d H:i:s'),
                    ])
                );
            }

            return Command::SUCCESS;
        }

        $deletedOrdersCount = 0;
        $deletedTicketsCount = 0;
        $deletedPaymentsCount = 0;
        $deletedStandalonePaymentsCount = 0;

        DB::transaction(function () use ($incompleteOrders, $pendingPayments, &$deletedOrdersCount, &$deletedTicketsCount, &$deletedPaymentsCount, &$deletedStandalonePaymentsCount) {
            foreach ($incompleteOrders as $order) {
                try {
                    // Count related records before deletion
                    $ticketCount = $order->tickets()->count();
                    $paymentCount = $order->payments()->count();

                    // Log the cleanup before deletion
                    Log::info('Order deletion cleanup', [
                        'order_number' => $order->order_number,
                        'event_id' => $order->event_id,
                        'organizer_id' => $order->organizer_id,
                        'tickets_count' => $ticketCount,
                        'payments_count' => $paymentCount,
                        'timeout_minutes' => $this->option('timeout'),
                        'created_at' => $order->created_at,
                        'total_amount' => $order->total_amount,
                    ]);

                    // Restore ticket quantities and delete associated tickets
                    if ($ticketCount > 0) {
                        // Group tickets by ticket type to restore quantities
                        $ticketsByType = $order->tickets()
                            ->select('ticket_type_id', DB::raw('COUNT(*) as ticket_count'))
                            ->groupBy('ticket_type_id')
                            ->get();

                        // Restore quantities for each ticket type
                        foreach ($ticketsByType as $ticketGroup) {
                            $ticketType = TicketType::find($ticketGroup->ticket_type_id);
                            if ($ticketType) {
                                $oldQuantity = $ticketType->quantity;
                                $restoreCount = (int) $ticketGroup->ticket_count;
                                $ticketType->increment('quantity', $restoreCount);

                                Log::info('Ticket quantity restored', [
                                    'ticket_type_id' => $ticketType->id,
                                    'ticket_type_name' => $ticketType->name,
                                    'restored_count' => $restoreCount,
                                    'old_quantity' => $oldQuantity,
                                    'new_quantity' => $oldQuantity + $restoreCount,
                                    'order_number' => $order->order_number,
                                ]);
                            }
                        }

                        // Delete the tickets
                        $order->tickets()->delete();
                        $deletedTicketsCount += $ticketCount;
                    }

                    // Delete associated payments
                    if ($paymentCount > 0) {
                        $order->payments()->delete();
                        $deletedPaymentsCount += $paymentCount;
                    }

                    // Give back the discount use this unpaid order consumed
                    if ($order->discount_id) {
                        Discount::where('id', $order->discount_id)
                            ->where('used_count', '>', 0)
                            ->decrement('used_count');
                    }

                    // Delete the order itself
                    $orderNumber = $order->order_number;
                    $order->delete();

                    $deletedOrdersCount++;

                    $this->line("✓ Deleted order {$orderNumber} ({$ticketCount} tickets, {$paymentCount} payments)");

                } catch (\Exception $e) {
                    $this->error("Failed to delete order {$order->order_number}: ".$e->getMessage());
                    Log::error('Order deletion failed', [
                        'order_number' => $order->order_number,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Clean up standalone pending payments
            foreach ($pendingPayments as $payment) {
                try {
                    // Skip payments that belong to orders we just processed
                    if ($payment->order && $incompleteOrders->contains('id', $payment->order_id)) {
                        continue;
                    }

                    Log::info('Standalone payment deletion', [
                        'payment_id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'organizer_id' => $payment->organizer_id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'timeout_minutes' => $this->option('timeout'),
                        'created_at' => $payment->created_at,
                    ]);

                    $paymentId = $payment->id;
                    $payment->delete();
                    $deletedStandalonePaymentsCount++;

                    $this->line("✓ Deleted standalone payment #{$paymentId}");

                } catch (\Exception $e) {
                    $this->error("Failed to delete payment #{$payment->id}: ".$e->getMessage());
                    Log::error('Standalone payment deletion failed', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        });

        $this->info('✅ Cleanup completed!');
        $this->info('📊 Summary:');
        $this->info("   • Orders deleted: {$deletedOrdersCount}");
        $this->info("   • Tickets deleted: {$deletedTicketsCount}");
        $this->info("   • Order payments deleted: {$deletedPaymentsCount}");
        $this->info("   • Standalone payments deleted: {$deletedStandalonePaymentsCount}");

        if ($deletedOrdersCount > 0 || $deletedStandalonePaymentsCount > 0) {
            Log::info('Cleanup deletion summary', [
                'orders_deleted' => $deletedOrdersCount,
                'tickets_deleted' => $deletedTicketsCount,
                'order_payments_deleted' => $deletedPaymentsCount,
                'standalone_payments_deleted' => $deletedStandalonePaymentsCount,
                'timeout_minutes' => $timeoutMinutes,
            ]);
        }

        return Command::SUCCESS;
    }
}
