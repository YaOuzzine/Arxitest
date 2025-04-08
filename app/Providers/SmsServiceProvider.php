<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('sms.client', function ($app) {
            return new Client(
                config('services.twilio.sid'),
                config('services.twilio.auth_token')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
