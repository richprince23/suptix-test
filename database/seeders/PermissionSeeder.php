<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Event Management
            ['name' => 'view_events', 'description' => 'View events list and details'],
            ['name' => 'create_events', 'description' => 'Create new events'],
            ['name' => 'update_events', 'description' => 'Edit existing events'],
            ['name' => 'delete_events', 'description' => 'Delete events'],
            ['name' => 'manage_ticket_types', 'description' => 'Create and manage ticket types for events'],

            // Venue Management
            ['name' => 'view_venues', 'description' => 'View venues list and details'],
            ['name' => 'create_venues', 'description' => 'Add new venues'],
            ['name' => 'update_venues', 'description' => 'Edit venue information'],
            ['name' => 'delete_venues', 'description' => 'Remove venues'],

            // Checkin management
            ['name' => 'view_checkins', 'description' => 'View checkins list and details'],
            ['name' => 'create_checkins', 'description' => 'Add new checkins'],
            ['name' => 'update_checkins', 'description' => 'Edit checkins information'],
            ['name' => 'delete_checkins', 'description' => 'Remove checkins'],

            // Sales & Orders
            ['name' => 'view_orders', 'description' => 'View orders and sales data'],
            ['name' => 'create_orders', 'description' => 'Create new orders'],
            ['name' => 'update_orders', 'description' => 'Modify existing orders'],
            ['name' => 'delete_orders', 'description' => 'Cancel/delete orders'],
            ['name' => 'process_refunds', 'description' => 'Process customer refunds'],

            // Ticket Management
            ['name' => 'view_tickets', 'description' => 'View ticket information'],
            ['name' => 'create_tickets', 'description' => 'Generate new tickets'],
            ['name' => 'update_tickets', 'description' => 'Modify ticket details'],
            ['name' => 'delete_tickets', 'description' => 'Cancel/delete tickets'],
            ['name' => 'validate_tickets', 'description' => 'Validate tickets at events'],
            ['name' => 'check_in_attendees', 'description' => 'Check-in attendees at events'],

            // Financial Management
            ['name' => 'view_payments', 'description' => 'View payment transactions'],
            ['name' => 'create_payments', 'description' => 'Process payments'],
            ['name' => 'update_payments', 'description' => 'Modify payment records'],
            ['name' => 'delete_payments', 'description' => 'Remove payment records'],
            ['name' => 'view_financial_reports', 'description' => 'Access financial reports and analytics'],

            // Payment Info & Paystack Management
            ['name' => 'view_payment_info', 'description' => 'View payment settings and Paystack accounts'],
            ['name' => 'create_payment_info', 'description' => 'Set up new payment accounts'],
            ['name' => 'update_payment_info', 'description' => 'Modify payment settings'],
            ['name' => 'delete_payment_info', 'description' => 'Remove payment accounts'],
            ['name' => 'manage_paystack_subaccounts', 'description' => 'Create and manage Paystack subaccounts'],
            ['name' => 'sync_payment_balances', 'description' => 'Sync account balances from Paystack'],
            ['name' => 'configure_payment_settings', 'description' => 'Configure payment processing settings'],

            // Marketing & Promotions
            ['name' => 'view_discounts', 'description' => 'View discount codes and promotions'],
            ['name' => 'create_discounts', 'description' => 'Create new discount codes'],
            ['name' => 'update_discounts', 'description' => 'Modify existing discounts'],
            ['name' => 'delete_discounts', 'description' => 'Remove discount codes'],

            // Customer Feedback
            ['name' => 'view_reviews', 'description' => 'View customer reviews and ratings'],
            ['name' => 'moderate_reviews', 'description' => 'Approve/reject reviews'],
            ['name' => 'respond_to_reviews', 'description' => 'Reply to customer reviews'],
            ['name' => 'delete_reviews', 'description' => 'Remove inappropriate reviews'],

            // User & Team Management
            ['name' => 'view_users', 'description' => 'View team members and user accounts'],
            ['name' => 'create_users', 'description' => 'Add new team members'],
            ['name' => 'update_users', 'description' => 'Edit user information'],
            ['name' => 'delete_users', 'description' => 'Remove user accounts'],
            ['name' => 'manage_user_roles', 'description' => 'Assign roles to team members'],

            // Dashboard & Analytics
            ['name' => 'view_dashboard', 'description' => 'Access main dashboard'],
            ['name' => 'view_analytics', 'description' => 'View event and sales analytics'],
            ['name' => 'view_sales_reports', 'description' => 'Access detailed sales reports'],
            ['name' => 'export_reports', 'description' => 'Export data and reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['description' => $permission['description']]
            );
        }
    }
}
