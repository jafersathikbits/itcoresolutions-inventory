<?php

namespace App\Providers;

use App\Models\AppNotification;
use Illuminate\Pagination\Paginator;
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
        Paginator::useTailwind();

        // Data the app layout needs on every page (unread badge + role label in the top bar).
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $view->with([
                'unreadNotificationsCount' => $user
                    ? AppNotification::where('user_id', $user->id)->whereNull('read_at')->count()
                    : 0,
                'currentRoleName' => $user?->getRoleNames()->first() ?? 'No role',
            ]);
        });
    }
}
