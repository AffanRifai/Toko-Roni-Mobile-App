<?php

namespace App\Providers;

use App\Events\RealtimeNotificationCreated;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
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
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        DatabaseNotification::created(function (DatabaseNotification $notification): void {
            if ($notification->notifiable_type !== User::class) {
                return;
            }

            if (empty($notification->notifiable_id)) {
                return;
            }

            try {
                event(new RealtimeNotificationCreated($notification));
            } catch (\Throwable $e) {
                Log::warning('Realtime notification broadcast failed: ' . $e->getMessage());
            }
        });
    }
}
