<?php

use App\Filament\Widgets\EventsOverviewWidget;
use App\Filament\Widgets\OrganizerBalanceWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\SalesOverviewWidget;
use App\Filament\Widgets\TicketsSoldChartWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use App\Models\Organizer;
use App\Models\User;

use Spatie\Permission\Models\Role;



beforeEach(function () {
    $this->seed();
    
    // Create roles
    Role::firstOrCreate(['name' => 'Organizer Admin']);
    Role::firstOrCreate(['name' => 'Event Manager']);
    Role::firstOrCreate(['name' => 'Customer Service']);
});

test('organizer admin can view all widgets', function () {
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer',
    ]);
    $organizer->users()->attach($user->id);
    
    $user->assignRole('Organizer Admin');
    $this->actingAs($user);
    
    // Organizer Admin should be able to view all widgets
    expect(EventsOverviewWidget::canView())->toBeTrue();
    expect(SalesOverviewWidget::canView())->toBeTrue();
    expect(OrganizerBalanceWidget::canView())->toBeTrue();
    expect(RecentOrdersWidget::canView())->toBeTrue();
    expect(TicketsSoldChartWidget::canView())->toBeTrue();
    expect(UpcomingEventsWidget::canView())->toBeTrue();
    expect(QuickActionsWidget::canView())->toBeTrue();
});

test('event manager has limited widget access', function () {
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer',
    ]);
    $organizer->users()->attach($user->id);
    
    $user->assignRole('Event Manager');
    $this->actingAs($user);
    
    // Event Manager should have limited access
    expect(EventsOverviewWidget::canView())->toBeTrue();
    expect(SalesOverviewWidget::canView())->toBeTrue(); // Has view_payments permission
    expect(OrganizerBalanceWidget::canView())->toBeFalse(); // Needs BOTH view_financial_reports AND view_payment_info
    expect(RecentOrdersWidget::canView())->toBeTrue(); // Has view_orders and view_payments
    expect(TicketsSoldChartWidget::canView())->toBeTrue();
    expect(UpcomingEventsWidget::canView())->toBeTrue();
    expect(QuickActionsWidget::canView())->toBeTrue();
});

test('customer service has very limited widget access', function () {
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer',
    ]);
    $organizer->users()->attach($user->id);
    
    $user->assignRole('Customer Service');
    $this->actingAs($user);
    
    // Customer Service should have very limited access
    expect(EventsOverviewWidget::canView())->toBeTrue();
    expect(SalesOverviewWidget::canView())->toBeTrue(); // Has view_payments permission
    expect(OrganizerBalanceWidget::canView())->toBeFalse(); // No view_payment_info permission
    expect(RecentOrdersWidget::canView())->toBeTrue(); // Has view_orders and view_payments
    expect(TicketsSoldChartWidget::canView())->toBeFalse(); // No view_analytics permission
    expect(UpcomingEventsWidget::canView())->toBeTrue();
    expect(QuickActionsWidget::canView())->toBeFalse(); // No creation permissions beyond tickets
});

test('users without roles cannot view sensitive widgets', function () {
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer',
    ]);
    $organizer->users()->attach($user->id);
    
    // User without any roles
    $this->actingAs($user);
    
    // Should not be able to view any widgets
    expect(EventsOverviewWidget::canView())->toBeFalse();
    expect(SalesOverviewWidget::canView())->toBeFalse();
    expect(OrganizerBalanceWidget::canView())->toBeFalse();
    expect(RecentOrdersWidget::canView())->toBeFalse();
    expect(TicketsSoldChartWidget::canView())->toBeFalse();
    expect(UpcomingEventsWidget::canView())->toBeFalse();
    expect(QuickActionsWidget::canView())->toBeFalse();
});

test('system admin cannot view organizer widgets', function () {
    $user = User::factory()->create(['is_system_admin' => true]);
    $this->actingAs($user);
    
    // System admin should not see organizer widgets
    expect(EventsOverviewWidget::canView())->toBeFalse();
    expect(SalesOverviewWidget::canView())->toBeFalse();
    expect(OrganizerBalanceWidget::canView())->toBeFalse();
    expect(RecentOrdersWidget::canView())->toBeFalse();
    expect(TicketsSoldChartWidget::canView())->toBeFalse();
    expect(UpcomingEventsWidget::canView())->toBeFalse();
    expect(QuickActionsWidget::canView())->toBeFalse();
});