<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Lightweight connectivity probe (used by the scanner app to confirm reachability)
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Login
Route::post('/login', [AuthController::class, 'login']);

// logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth:sanctum']);

// Account deletion endpoints
Route::get('/account/deletion-info', [AuthController::class, 'getAccountDeletionInfo'])
    ->middleware('auth:sanctum');
Route::delete('/account', [AuthController::class, 'deleteAccount'])
    ->middleware('auth:sanctum');

// Ticket verification endpoints (public for initial verification)
Route::post('/tickets/scan-qr', [TicketController::class, 'scanQr']); // For QR code scanning (mobile apps)

// Authenticated ticket verification endpoints (for check-in purposes)
Route::middleware(['auth:sanctum,web'])->group(function () {
    Route::post('/tickets/verify-by-number', [TicketController::class, 'verifyByNumber']); // For ticket number verification
    Route::get('/orders/{orderNumber}', [OrderController::class, 'getOrderByNumber']); // Order lookup
});


// Mark ticket as used (requires authentication)
Route::put('/tickets/{ticket}/mark-used', [TicketController::class, 'markAsUsed'])
    ->middleware(['auth:sanctum,web']);

// Mobile App API routes (with authentication)
Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {
    // Ticket verification for mobile apps
    Route::post('/tickets/verify-by-number', [TicketController::class, 'verifyByNumber']);
    Route::post('/tickets/scan-qr', [TicketController::class, 'scanQr']);

    // Order lookup for mobile apps
    Route::get('/orders/{orderNumber}', [OrderController::class, 'getOrderByNumber']);

});

// Organizer-specific routes
Route::prefix('organizers/{organizer}/events')->middleware('auth:sanctum')->group(function () {
    // Get organizer's events
    Route::get('/', [EventController::class, 'getEvents']);

    // Get a single event (read-only; events are created/edited on the web app)
    Route::get('/{event}', [EventController::class, 'getEvent']);

    // Get all tickets for an event
    Route::get('/{event}/tickets', [TicketController::class, 'getEventTickets']);

    // Verify ticket by number or QR for specific event
    Route::post('/{event}/tickets/verify', [TicketController::class, 'verifyTicket']);

    // Mark ticket as used for specific event
    Route::put('/{event}/tickets/{ticket}', [TicketController::class, 'markTicketAsUsedForEvent']);

    // Get ticket entry history
    Route::get('/{event}/tickets/entries', [TicketController::class, 'getTicketEntryHistory']);

    // Get event statistics
    Route::get('/{event}/statistics', [TicketController::class, 'getEventStatistics']);

    // Get ticket scanning statistics
    Route::get('/{event}/tickets/scan-statistics', [TicketController::class, 'getTicketScanStatistics']);
});
