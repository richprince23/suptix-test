<?php

use App\Http\Controllers\DiscountController;
use App\Http\Controllers\TicketPurchaseController;
use Illuminate\Support\Facades\Route;

// Public ticket purchase routes (no auth required)
Route::prefix('events/{event}')->name('events.')->group(function () {
    // Show event details and ticket selection page
    Route::get('tickets', [TicketPurchaseController::class, 'show'])->name('tickets.show');

    // Get available tickets for an event (API endpoint)
    Route::get('tickets/available', [TicketPurchaseController::class, 'getAvailableTickets'])->name('tickets.available');

    // Show buyer form page
    Route::post('tickets/buyer-form', [TicketPurchaseController::class, 'showBuyerForm'])->name('tickets.buyer-form');
    Route::get('tickets/buyer-form', function (App\Models\Event $event) {
        return redirect()->route('events.show', $event);
    });

    // Process ticket purchase
    Route::post('tickets/purchase', [TicketPurchaseController::class, 'purchase'])->name('tickets.purchase');

    // Validate discount code
    Route::post('discount/validate', [DiscountController::class, 'validateDiscount'])->name('discount.validate');
});

// Order confirmation and management routes
Route::prefix('orders')->name('orders.')->group(function () {
    // Show order confirmation page
    Route::get('{orderNumber}/confirmation', [TicketPurchaseController::class, 'confirmation'])->name('confirmation');

    // Download tickets (future implementation)
    Route::get('{orderNumber}/download', [TicketPurchaseController::class, 'downloadTickets'])->name('download');

});

// Alternative shorter routes for direct access (backward compatibility)
Route::get('tickets/{event}', [TicketPurchaseController::class, 'show'])->name('tickets.show.alt');
Route::get('confirmation/{orderNumber}', [TicketPurchaseController::class, 'confirmation'])->name('ticket.confirmation');
