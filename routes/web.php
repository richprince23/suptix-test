<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\SeoService;

// Public routes
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Support pages
Route::get('privacy-policy', function () {
    SeoService::setPrivacyPolicySEO();
    return Inertia::render('PrivacyPolicy', [
        'seo' => SeoService::privacyPolicy(),
    ]);
})->name('privacy-policy');

Route::get('terms-of-service', function () {
    SeoService::setTermsOfServiceSEO();
    return Inertia::render('TermsOfService', [
        'seo' => SeoService::termsOfService(),
    ]);
})->name('terms-of-service');

Route::get('refund-policy', function () {
    SeoService::setRefundPolicySEO();
    return Inertia::render('RefundPolicy', [
        'seo' => SeoService::refundPolicy(),
    ]);
})->name('refund-policy');

Route::get('help-center', function () {
    SeoService::setHelpCenterSEO();
    return Inertia::render('HelpCenter', [
        'seo' => SeoService::helpCenter(),
    ]);
})->name('help-center');

Route::get('contact-us', [App\Http\Controllers\ContactController::class, 'index'])->name('contact-us');

// Newsletter routes
Route::get('newsletter/unsubscribe/{token}', [App\Http\Controllers\NewsletterController::class, 'unsubscribePage'])->name('newsletter.unsubscribe');
Route::post('newsletter/unsubscribe/{token}', [App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.confirm');
Route::post('api/contact', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

Route::get('request-demo', [App\Http\Controllers\DemoController::class, 'index'])->name('request-demo');
Route::post('api/demo-request', [App\Http\Controllers\DemoController::class, 'store'])->name('demo.store');

// SEO routes
Route::get('sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('robots.txt', [App\Http\Controllers\RobotsController::class, 'index'])->name('robots');

// Event routes
Route::get('events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('calendar', [App\Http\Controllers\EventController::class, 'calendar'])->name('calendar');
Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'show'])->name('events.show');

// Organizer routes
Route::get('organizers/{organizer:slug}', [App\Http\Controllers\OrganizerController::class, 'show'])->name('organizers.show');
Route::post('organizers/{organizer:slug}/subscribe', [App\Http\Controllers\OrganizerController::class, 'subscribe'])->name('organizers.subscribe');
Route::delete('organizers/{organizer:slug}/unsubscribe', [App\Http\Controllers\OrganizerController::class, 'unsubscribe'])->name('organizers.unsubscribe');

// Backward compatibility route for old ID-based URLs
Route::get('events/id/{id}', [App\Http\Controllers\EventController::class, 'showById'])->name('events.show.id')->where('id', '[0-9]+');

// Legacy route for backwards compatibility
Route::get('events-page', [App\Http\Controllers\EventController::class, 'index'])->name('events.page');

// Order search routes
Route::get('order-search', [App\Http\Controllers\OrderController::class, 'searchPage'])->name('orders.search.page');
Route::post('api/orders/search', [App\Http\Controllers\OrderController::class, 'search'])->name('orders.search');
Route::post('api/orders/share-tickets', [App\Http\Controllers\OrderController::class, 'shareTickets'])->name('orders.share-tickets');
Route::post('api/orders/send-confirmation', [App\Http\Controllers\OrderController::class, 'sendOrderConfirmation'])->name('orders.send-confirmation');
Route::post('api/orders/send-all-emails', [App\Http\Controllers\OrderController::class, 'sendAllEmails'])->name('orders.send-all-emails');
Route::get('orders/{orderNumber}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');

// Discount validation routes
Route::post('api/discounts/validate', [App\Http\Controllers\DiscountController::class, 'validateDiscount'])->name('discounts.validate');

// Ticket verification routes (for QR code scanning)
Route::get('verify-ticket/{ticketNumber}', [App\Http\Controllers\TicketController::class, 'verify'])->name('tickets.verify');
Route::patch('api/tickets/{ticket}/mark-used', [App\Http\Controllers\TicketController::class, 'markAsUsed'])->name('tickets.mark-used');
Route::post('api/tickets/scan-qr', [App\Http\Controllers\TicketController::class, 'scanQr'])->name('tickets.scan-qr');

// Attendee Check-in route (authenticated)
Route::get('attendee-checkin', [App\Http\Controllers\CheckInController::class, 'index'])
    ->middleware('auth')
    ->name('attendee-checkin');

// Ticket details route (for email links)
Route::get('ticket/{ticketNumber}', [App\Http\Controllers\TicketController::class, 'details'])->name('tickets.details');
Route::get('api/tickets/{ticket}/download-pdf', [App\Http\Controllers\TicketController::class, 'downloadPdf'])->name('tickets.download-pdf');

// Payment routes
Route::post('api/payment/initialize', [App\Http\Controllers\PaymentController::class, 'initialize'])->name('payment.initialize');
Route::get('payment/callback', [App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
Route::get('payment/success/{order}', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
Route::get('payment/failed', [App\Http\Controllers\PaymentController::class, 'failed'])->name('payment.failed');
Route::post('api/payment/verify', [App\Http\Controllers\PaymentController::class, 'verify'])->name('payment.verify');

// Paystack webhook route (public, no CSRF protection needed)
Route::post('paystack/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('paystack.webhook');

// RSVP credit purchase routes (organizer must be authenticated via Filament)
Route::middleware(['auth'])->group(function () {
    Route::post('events/{event}/credits/purchase', [App\Http\Controllers\CreditPurchaseController::class, 'store'])->name('credits.purchase');
    Route::get('events/{event}/credits/verify', [App\Http\Controllers\CreditPurchaseController::class, 'verify'])->name('credits.verify');
});

// Paystack API routes (authenticated)
Route::middleware(['auth'])->prefix('api/paystack')->name('paystack.')->group(function () {
    Route::get('banks', [App\Http\Controllers\PaystackController::class, 'getBanks'])->name('banks');
    Route::post('verify-account', [App\Http\Controllers\PaystackController::class, 'verifyAccount'])->name('verify-account');
    Route::post('payment-info/{paymentInfo}/sync-balance', [App\Http\Controllers\PaystackController::class, 'syncBalance'])->name('sync-balance');
});

// Authenticated routes
// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::get('/signup', function () {
    return Inertia::render('OrganizerSignup', [
        'seo' => [
            'title' => 'Become an Event Organizer',
            'description' => 'Join thousands of successful event organizers. Create, promote, and manage events with our powerful platform.',
            'keywords' => 'event organizer, create events, event management, sign up, register',
        ],
    ]);
})->name('signup');

Route::get('/register', function () {
    return redirect()->route('filament.admin.auth.register');
})->name('register');

// Payment Info API routes (for internal dashboard use)
Route::post('api/verify-account', [App\Http\Controllers\PaystackController::class, 'verifyAccount'])->name('verify-account');

// Include other route files
// require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/ticket-purchase.php';
