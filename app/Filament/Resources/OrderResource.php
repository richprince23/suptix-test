<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\TicketsRelationManager;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Mail\TicketEmail;
use App\Models\Order;
use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_orders') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_orders') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_orders') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_orders') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id)->whereNotIn('status', ['failed', 'cancelled', 'pending', 'processing', 'refunded']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Information')->schema([
                    TextInput::make('order_number')
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('event_id')
                        ->relationship('event', 'title', function (Builder $query) {
                            return $query->where('organizer_id', Filament::getTenant()->id);
                        })
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(2),

                Section::make('Buyer Information')->schema([
                    TextInput::make('buyer_email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('buyer_phone')
                        ->tel()
                        ->maxLength(16),
                ])->columns(2),

                Section::make('Payment Information')->schema([
                    TextInput::make('total_amount')
                        ->numeric()
                        ->prefix('GHS')
                        ->required(),
                    Select::make('payment_method')
                        ->options([
                            'paystack' => 'Paystack',
                            'momo' => 'Mobile Money',
                            'bank_transfer' => 'Bank Transfer',
                            'cash' => 'Cash',
                        ])
                        ->required(),
                    TextInput::make('payment_reference')
                        ->maxLength(255),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'processing' => 'Processing',
                            'cancelled' => 'Cancelled',
                            'refunded' => 'Refunded',
                        ])
                        ->default('pending')
                        ->required(),
                    DateTimePicker::make('purchased_at')
                        ->nullable(),
                ])->columns(2),

                Section::make('Admin Information')->schema([
                    Textarea::make('admin_notes')
                        ->label('Admin Notes')
                        ->maxLength(1000)
                        ->rows(3)
                        ->placeholder('Optional notes about this order (will be auto-filled if left empty)')
                        ->columnSpanFull()
                        ->helperText('Describe any special circumstances or customer requests for this order.'),
                ])->visible(fn () => auth()->user()?->can('create_orders')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('buyer_email')
                    ->description(fn ($record) => $record->buyer_phone ?? '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_tickets')
                    ->label('Tickets')
                    ->getStateUsing(fn ($record) => $record->tickets()->count()),
                TextColumn::make('total_amount')
                    ->money('GHS')
                    ->sortable()
                    ->summarize(Sum::make()->money('GHS')),
                TextColumn::make('payment_method')
                    ->badge()
                    ->colors([
                        'primary' => 'credit_card',
                        'secondary' => 'debit_card',
                        'success' => 'paypal',
                        'warning' => 'bank_transfer',
                        'danger' => 'cash',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'processing',
                        'danger' => ['cancelled', 'refunded'],
                    ]),
                TextColumn::make('purchased_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdByUser.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('System'),
                IconColumn::make('is_admin_order')
                    ->label('Admin Order')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('admin_notes')
                    ->label('Admin Notes')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                    ]),
                SelectFilter::make('event')
                    ->relationship('event', 'title', function (Builder $query) {
                        return $query->where('organizer_id', Filament::getTenant()->id);
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Orders'),
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('changeEmail')
                        ->label('Change Email')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->form([
                            TextInput::make('new_email')
                                ->label('New Email Address')
                                ->email()
                                ->required()
                                ->helperText('This will revoke all existing tickets and generate new ones with the new email.'),
                        ])
                        ->action(function (array $data, Order $record): void {
                            self::changeOrderEmail($record, $data['new_email']);
                        })
                        ->requiresConfirmation()
                        ->modalDescription('This action will revoke all existing tickets and generate new ones with the updated email address.')
                        ->modalHeading('Change Order Email')
                        ->visible(fn (Order $record) => $record->status === 'confirmed'),

                    Action::make('resendTickets')
                        ->label('Resend Tickets')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function (Order $record): void {
                            self::resendTicketEmails($record);
                        })
                        ->requiresConfirmation()
                        ->modalDescription('This will resend ticket emails to the buyer.')
                        ->visible(fn (Order $record) => $record->status === 'confirmed'),

                ])->tooltip('More'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RecentOrdersWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('status', 'confirmed')->count();
    }

    /**
     * Change order email and regenerate tickets
     */
    protected static function changeOrderEmail(Order $order, string $newEmail): void
    {
        DB::transaction(function () use ($order, $newEmail) {
            $oldEmail = $order->buyer_email;

            // Step 1: Mark all existing tickets as cancelled/revoked
            $order->tickets()->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => "Cancelled due to email change from {$oldEmail} to {$newEmail}",
            ]);

            // Step 2: Update order email
            $order->update(['buyer_email' => $newEmail]);

            // Step 3: Create new tickets with same details but new email
            $cancelledTickets = $order->tickets()->where('status', 'cancelled')->get();

            foreach ($cancelledTickets as $oldTicket) {
                // Generate new ticket number
                $newTicketNumber = 'TKT-'.strtoupper(uniqid());

                // Create new ticket
                $newTicket = Ticket::create([
                    'ticket_number' => $newTicketNumber,
                    'event_id' => $oldTicket->event_id,
                    'order_id' => $oldTicket->order_id,
                    'organizer_id' => $oldTicket->organizer_id,
                    'ticket_type_id' => $oldTicket->ticket_type_id,
                    'attendee_name' => $oldTicket->attendee_name,
                    'attendee_email' => $newEmail, // New email here
                    'price' => $oldTicket->price,
                    'status' => 'active',
                ]);

                // Send ticket email for new ticket
                try {
                    Mail::to($newEmail)->sendNow(new TicketEmail($newTicket, $order));
                } catch (\Exception $e) {
                    \Log::error('Failed to send ticket email', [
                        'ticket_id' => $newTicket->id,
                        'email' => $newEmail,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Step 4: Log the change
            \Log::info('Order email changed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
                'cancelled_tickets' => $cancelledTickets->count(),
                'new_tickets' => $cancelledTickets->count(),
            ]);
        });

        Notification::make()
            ->title('Email Changed Successfully')
            ->body("Order email updated to {$newEmail}. Old tickets revoked and new tickets sent.")
            ->success()
            ->send();
    }

    /**
     * Resend ticket emails to buyer
     */
    protected static function resendTicketEmails(Order $order): void
    {
        $activeTickets = $order->tickets()->where('status', 'active')->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($activeTickets as $ticket) {
            try {
                Mail::to($order->buyer_email)->sendNow(new TicketEmail($ticket, $order));
                $sentCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to resend ticket email', [
                    'ticket_id' => $ticket->id,
                    'email' => $order->buyer_email,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        if ($sentCount > 0) {
            Notification::make()
                ->title('Tickets Resent')
                ->body("{$sentCount} ticket(s) resent successfully".($failedCount > 0 ? ", {$failedCount} failed" : ''))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('No Tickets to Resend')
                ->body('No active tickets found for this order')
                ->warning()
                ->send();
        }
    }
}
