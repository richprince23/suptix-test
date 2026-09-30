<?php

use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\EventController;
use App\Http\Controllers\Api\V2\TicketController;
use App\Http\Controllers\Api\V2\OrderController;
use App\Http\Controllers\Api\V2\OrganizerController;
use App\Http\Controllers\Api\V2\UserController;
use Illuminate\Support\Facades\Route;

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::delete('account', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');
});

// Public routes
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::get('/{event}/tickets', [EventController::class, 'getTickets']);
});

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::get('orders', [UserController::class, 'orders']);
        Route::get('tickets', [UserController::class, 'tickets']);
    });

    // Order management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/', [OrderController::class, 'store']);
    });

    // Ticket management
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{ticket}', [TicketController::class, 'show']);
        Route::post('/scan', [TicketController::class, 'scan']);
        Route::post('/verify', [TicketController::class, 'verify']);
        Route::put('/{ticket}/check-in', [TicketController::class, 'checkIn']);
    });

    // Organizer routes (for organizer app features)
    Route::prefix('organizer')->group(function () {
        Route::get('profile', [OrganizerController::class, 'profile']);
        Route::get('events', [OrganizerController::class, 'events']);
        Route::get('events/{event}', [OrganizerController::class, 'event']);
        Route::get('events/{event}/statistics', [OrganizerController::class, 'eventStatistics']);
        Route::get('events/{event}/tickets', [OrganizerController::class, 'eventTickets']);
        Route::get('events/{event}/orders', [OrganizerController::class, 'eventOrders']);
        Route::put('events/{event}/tickets/{ticket}/check-in', [OrganizerController::class, 'checkInTicket']);
    });
});