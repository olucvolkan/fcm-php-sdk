<?php

namespace Firebase\CloudMessaging\Laravel;

use Firebase\CloudMessaging\Client\FCMClient;
use Firebase\CloudMessaging\Laravel\Channels\FCMChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class FCMServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('fcm', function ($app) {
            $config = $app['config']['services.fcm'] ?? ['key' => null];
            $serverKey = $config['key'] ?? env('FCM_SERVER_KEY');
            
            return new FCMClient($serverKey);
        });
        
        $this->app->alias('fcm', FCMClient::class);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register notification channel
        $this->registerNotificationChannel();
    }

    /**
     * Register the FCM notification channel.
     *
     * @return void
     */
    protected function registerNotificationChannel()
    {
        // Only register if Notification class exists (Laravel 5.5+)
        if (class_exists(Notification::class)) {
            Notification::extend('fcm', function ($app) {
                return new FCMChannel($app['fcm']);
            });
        }
    }
}
