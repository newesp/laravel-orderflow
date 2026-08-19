<?php

namespace App\Providers;

use App\Events\OrderStatusChanged;
use App\Listeners\LogOrderStatusChanged;
use App\Models\AdminSessionUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
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

        // Custom UserProvider for Session-backed non-persistent AdminSessionUser
        Auth::provider('admin_session', function ($app, array $config) {
            return new class implements UserProvider {
                public function retrieveById($identifier)
                {
                    $data = session('admin_user');
                    if (is_array($data) && ($data['id'] ?? '') === (string) $identifier) {
                        return AdminSessionUser::fromArray($data);
                    }
                    if (is_array($data) && !empty($data['id'])) {
                        return AdminSessionUser::fromArray($data);
                    }
                    return null;
                }

                public function retrieveByToken($identifier, $token)
                {
                    return null;
                }

                public function updateRememberToken(Authenticatable $user, $token)
                {
                    // Non-persistent session, no-op
                }

                public function retrieveByCredentials(array $credentials)
                {
                    $data = session('admin_user');
                    if (is_array($data)) {
                        return AdminSessionUser::fromArray($data);
                    }
                    return null;
                }

                public function validateCredentials(Authenticatable $user, array $credentials)
                {
                    return true;
                }

                public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
                {
                    return false;
                }
            };
        });

        Event::listen(
            OrderStatusChanged::class,
            LogOrderStatusChanged::class
        );
    }
}
