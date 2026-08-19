<?php

namespace App\Providers;

use App\Auth\AdminSessionUserProvider;
use App\Events\OrderStatusChanged;
use App\Listeners\LogOrderStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (config('app.env') === 'production' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }

        // Register custom non-persistent session-backed Admin UserProvider
        Auth::provider('admin_session', function ($app, array $config) {
            return new AdminSessionUserProvider();
        });

        Event::listen(
            OrderStatusChanged::class,
            LogOrderStatusChanged::class
        );
    }
}
