<?php

namespace App\Providers;

use App\Services\SettingsService;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

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
                Config::set('services.google.redirect', route('auth.callback', ['provider' => 'google']));
                Config::set('services.facebook.redirect', route('auth.callback', ['provider' => 'facebook']));
            }
        });

        try {
            Storage::extend('google', function ($app, $config) {
                $options = [];
                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }
                $client = new Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);
                $service = new Drive($client);
                $adapter = new GoogleDriveAdapter($service, $config['folder'] ?? '/', $options);
                $driver = new Filesystem($adapter);
                return new FilesystemAdapter($driver, $adapter);
            });
        } catch (Exception $e) {
            Log::error('Backup Error: ' . $e->getMessage());
        }
    }
}
