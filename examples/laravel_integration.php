<?php

/**
 * This file demonstrates how to integrate the FCM PHP SDK with Laravel
 * 
 * Installation:
 * 1. Install the package through composer
 *    composer require volk/php-firebase-cloud-messaging
 * 
 * 2. Create a service provider (app/Providers/FCMServiceProvider.php)
 * 3. Create a facade (app/Facades/FCM.php)
 * 4. Add the service provider to config/app.php
 * 5. Create a notification class that uses the SDK
 */

/**
 * Example Service Provider (app/Providers/FCMServiceProvider.php)
 */

namespace App\Providers;

use Firebase\CloudMessaging\Client\FCMClient;
use Illuminate\Support\ServiceProvider;

class FCMServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('fcm', function ($app) {
            return new FCMClient(config('services.fcm.key'));
        });
    }
}

/**
 * Example Facade (app/Facades/FCM.php)
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FCM extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fcm';
    }
}

/**
 * Configuration in config/services.php
 */
// Add this to your config/services.php
return [
    // other services...
    
    'fcm' => [
        'key' => env('FCM_SERVER_KEY'),
    ],
];

/**
 * Example Notification Class (app/Notifications/PushNotification.php)
 */

namespace App\Notifications;

use App\Facades\FCM;
use Firebase\CloudMessaging\RequestResponse\Message;
use Firebase\CloudMessaging\Models\Priority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;

    public function __construct(string $title, string $body, array $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable)
    {
        // Get device token from the notifiable entity
        $token = $notifiable->fcm_token;
        
        if (empty($token)) {
            return null;
        }

        $message = new Message();
        $message
            ->setNotification([
                'title' => $this->title,
                'body' => $this->body,
                'sound' => 'default',
            ])
            ->setData($this->data)
            ->setToken($token)
            ->setPriority(Priority::HIGH);
            
        return $message;
    }
}

/**
 * Example of sending a notification
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $user->notify(new PushNotification(
            'Hello from Laravel!',
            'This is a push notification sent from Laravel using the FCM SDK',
            ['key1' => 'value1', 'key2' => 'value2']
        ));
        
        return response()->json(['message' => 'Notification sent']);
    }
}

/**
 * Custom Notification Channel (app/Channels/FCMChannel.php)
 */

namespace App\Channels;

use App\Facades\FCM;
use Firebase\CloudMessaging\RequestResponse\Exception\FCMException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FCMChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }
        
        $message = $notification->toFcm($notifiable);
        
        if (empty($message)) {
            return;
        }
        
        try {
            $response = FCM::send($message);
            
            if (!$response->isSuccess()) {
                Log::error('FCM notification failed: ' . $response->getErrorMessage());
            }
            
            return $response;
        } catch (FCMException $e) {
            Log::error('FCM error: ' . $e->getMessage());
            throw $e;
        }
    }
}

/**
 * Register the custom channel in EventServiceProvider.php
 */

namespace App\Providers;

use App\Channels\FCMChannel;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Support\Facades\Notification;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();
        
        // Register the FCM channel
        Notification::extend('fcm', function () {
            return new FCMChannel();
        });
    }
}

/**
 * Make User model notifiable with FCM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;
    
    // Add this column to your users table
    protected $fillable = [
        'name', 'email', 'password', 'fcm_token'
    ];
}
