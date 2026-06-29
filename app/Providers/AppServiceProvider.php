<?php

namespace App\Providers;

use App\Models\NotificationMvp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            $user = request()->user();
            $notifications = collect();
            $unreadCount = 0;

            if ($user) {
                $baseQuery = NotificationMvp::query()
                    ->where('operador_id', (int) ($user->operador_id ?: 1))
                    ->whereHas('users', fn ($query) => $query->where('users.id', $user->id));

                $notifications = (clone $baseQuery)
                    ->with(['users' => fn ($query) => $query->where('users.id', $user->id)])
                    ->latest('id')
                    ->limit(8)
                    ->get();

                $unreadCount = (clone $baseQuery)
                    ->whereHas('users', fn ($query) => $query->where('users.id', $user->id)->whereNull('notification_users.read_at'))
                    ->count();
            }

            $view->with([
                'panelNotifications' => $notifications,
                'panelUnreadCount' => $unreadCount,
            ]);
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api-write', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(60)->by('api-write:'.$key);
        });

        RateLimiter::for('app-write', function (Request $request) {
            return Limit::perMinute(30)->by('app-write:'.$request->ip());
        });
    }
}
