<?php

namespace App\Providers;

use App\Channels\SmsChannel;
use App\Models\PaymentInfo;
use App\Observers\PaymentInfoObserver;
use App\Services\HubtelSmsService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Hubtel SMS Service as singleton
        $this->app->singleton(HubtelSmsService::class, function ($app) {
            return new HubtelSmsService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom SMS notification channel
        Notification::extend('sms', function ($app) {
            return new SmsChannel($app->make(HubtelSmsService::class));
        });

        // Register model observers
        PaymentInfo::observe(PaymentInfoObserver::class);
    }
}
