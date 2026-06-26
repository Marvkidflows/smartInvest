<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS on production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register Brevo API mail transport
        Mail::extend('brevo', function (array $config) {
            $factory = new BrevoApiTransportFactory();

            return $factory->create(new Dsn(
                'brevo+api',
                'default',
                $config['key'] ?? env('BREVO_API_KEY')
            ));
        });
    }
}