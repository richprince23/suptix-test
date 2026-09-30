<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on system analysis
        $permissions = [
            // Event Management
            'view_events',
            'create_events',
            'update_events',
            'delete_events',
            'manage_ticket_types',

            // Venue Management
            'view_venues',
            'create_venues',
            'update_venues',
            'delete_venues',

            // checking management
            'view_checkins',
            'create_checkins',
            'update_checkins',
            'delete_checkins',

            // Sales & Orders
            'view_orders',
            'create_orders',
            'update_orders',
            'delete_orders',
            'process_refunds',

            // Ticket Management
            'view_tickets',
            'create_tickets',
            'update_tickets',
            'delete_tickets',
            'validate_tickets',
            'check_in_attendees',

            // Financial Management
            'view_payments',
            'create_payments',
            'update_payments',
            'delete_payments',
            'view_financial_reports',

            // Payment Info & Paystack Management
            'view_payment_info',
            'create_payment_info',
            'update_payment_info',
            'delete_payment_info',
            'manage_paystack_subaccounts',
            'sync_payment_balances',
            'configure_payment_settings',

            // Marketing & Promotions
            'view_discounts',
            'create_discounts',
            'update_discounts',
            'delete_discounts',

            // Booking Management
            'view_reservations',
            'create_reservations',
            'update_reservations',
            'delete_reservations',
            'manage_seating',

            // Customer Feedback
            'view_reviews',
            'moderate_reviews',
            'respond_to_reviews',
            'delete_reviews',

            // User & Team Management
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'manage_user_roles',

            // Organization Management
            'view_organizer_settings',
            'update_organizer_settings',
            'manage_organizer_profile',

            // Dashboard & Analytics
            'view_dashboard',
            'view_analytics',
            'view_sales_reports',
            'export_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with specific permission sets

        // 1. ORGANIZER ADMIN ROLE
        // Full access to organizer's tenant - can manage everything within their organization
        $organizerAdmin = Role::firstOrCreate(['name' => 'Organizer Admin']);

        // Remove existing permissions and reassign
        $organizerAdmin->syncPermissions([]);
        $organizerAdmin->givePermissionTo([
            // Event Management - Full access
            'view_events',
            'create_events',
            'update_events',
            'delete_events',
            'manage_ticket_types',

            // Venue Management - Full access
            'view_venues',
            'create_venues',
            'update_venues',
            'delete_venues',

            // Checkin management
            'view_checkins',
            'create_checkins',
            'update_checkins',
            'delete_checkins',

            // Sales & Orders - Full access
            'view_orders',
            'create_orders',
            'update_orders',
            'delete_orders',
            'process_refunds',

            // Ticket Management - Full access
            'view_tickets',
            'create_tickets',
            'update_tickets',
            'delete_tickets',
            'validate_tickets',
            'check_in_attendees',

            // Financial Management - Full access
            'view_payments',
            'create_payments',
            'update_payments',
            'delete_payments',
            'view_financial_reports',

            // Payment Info & Paystack Management - Full access
            'view_payment_info',
            'create_payment_info',
            'update_payment_info',
            'delete_payment_info',
            'manage_paystack_subaccounts',
            'sync_payment_balances',
            'configure_payment_settings',

            // Marketing & Promotions - Full access
            'view_discounts',
            'create_discounts',
            'update_discounts',
            'delete_discounts',

            // Booking Management - Full access
            'view_reservations',
            'create_reservations',
            'update_reservations',
            'delete_reservations',
            'manage_seating',

            // Customer Feedback - Full access
            'view_reviews',
            'moderate_reviews',
            'respond_to_reviews',
            'delete_reviews',

            // User & Team Management - Full access
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'manage_user_roles',

            // Organization Management - Full access
            'view_organizer_settings',
            'update_organizer_settings',
            'manage_organizer_profile',

            // Dashboard & Analytics - Full access
            'view_dashboard',
            'view_analytics',
            'view_sales_reports',
            'export_reports',
        ]);

        // 2. EVENT MANAGER ROLE
        // Focused on event creation and management with limited financial access
        $eventManager = Role::firstOrCreate(['name' => 'Event Manager']);
        $eventManager->syncPermissions([]);
        $eventManager->givePermissionTo([
            // Event Management - Full access
            'view_events',
            'create_events',
            'update_events',
            'manage_ticket_types',

            // Venue Management - Full access
            'view_venues',
            'create_venues',
            'update_venues',

            // Checkin management
            'view_checkins',
            'create_checkins',
            'update_checkins',
            'delete_checkins',

            // Sales & Orders - View only, no deletion
            'view_orders',
            'create_orders',
            'update_orders',

            // Ticket Management - Full access except deletion
            'view_tickets',
            'create_tickets',
            'update_tickets',
            'validate_tickets',
            'check_in_attendees',

            // Financial Management - View only
            'view_payments',

            // Payment Info & Paystack Management - View only
            'view_payment_info',
            'sync_payment_balances',

            // Marketing & Promotions - Full access
            'view_discounts',
            'create_discounts',
            'update_discounts',
            'delete_discounts',

            // Booking Management - Full access
            'view_reservations',
            'create_reservations',
            'update_reservations',
            'manage_seating',

            // Customer Feedback - View and respond only
            'view_reviews',
            'respond_to_reviews',

            // Limited user management
            'view_users',

            // Dashboard access
            'view_dashboard',
            'view_analytics',
        ]);

        // 3. CUSTOMER SERVICE ROLE
        // Focused on customer support, order management, and basic operations
        $customerService = Role::firstOrCreate(['name' => 'Customer Service']);
        $customerService->syncPermissions([]);
        $customerService->givePermissionTo([
            // Event Management - View only
            'view_events',

            // Venue Management - View only
            'view_venues',

            // Checkin management
            'view_checkins',
            'create_checkins',
            'update_checkins',
            // 'delete_checkins',

            // Sales & Orders - Full access for customer support
            'view_orders',
            'create_orders',
            'update_orders',
            'process_refunds',

            // Ticket Management - Support focused
            'view_tickets',
            'create_tickets',
            'update_tickets',
            'validate_tickets',
            'check_in_attendees',

            // Financial Management - View payments only
            'view_payments',

            // Payment Info & Paystack Management - Basic view only
            'view_payment_info',

            // Marketing & Promotions - View only
            'view_discounts',

            // Booking Management - Full access for customer support
            'view_reservations',
            'create_reservations',
            'update_reservations',
            'delete_reservations',

            // Customer Feedback - Full moderation access
            'view_reviews',
            'moderate_reviews',
            'respond_to_reviews',

            // Basic user viewing
            'view_users',

            // Basic dashboard access
            'view_dashboard',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles:');
        $this->command->info('1. Organizer Admin - Full access to all organizer features including payment management');
        $this->command->info('2. Event Manager - Event creation and management with payment viewing');
        $this->command->info('3. Customer Service - Customer support, order management, and basic payment viewing');
        $this->command->info('');
        $this->command->info('New payment permissions added:');
        $this->command->info('- view_payment_info, create_payment_info, update_payment_info, delete_payment_info');
        $this->command->info('- manage_paystack_subaccounts, sync_payment_balances, configure_payment_settings');
    }
}
