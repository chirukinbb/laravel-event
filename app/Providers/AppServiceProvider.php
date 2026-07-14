<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(RouteMatched::class, function () {
            if (!$this->app->runningInConsole()) {
                Config::set('services.google.redirect', route('api.auth.callback', ['provider' => 'google']));
                Config::set('services.facebook.redirect', route('api.auth.callback', ['provider' => 'facebook']));
            }
        });
    }
}
