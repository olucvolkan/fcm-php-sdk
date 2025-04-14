<?php

/**
 * This file demonstrates how to integrate the FCM PHP SDK with Laravel 5.5
 * 
 * Installation:
 * 1. Install the package through composer
 *    composer require volk/php-firebase-cloud-messaging
 * 
 * 2. The ServiceProvider will be auto-discovered in Laravel 5.5+
 */

/**
 * Step 1: Configure FCM in your services.php config file
 */

// Add this to your config/services.php
return [
    // other services...
    
    'fcm' => [
        'key' => env('FCM_SERVER_KEY'),
    ],
];

/**
 * Step 2: Add the FCM server key to your .env file
 */

// Add this to your .env file
// FCM_SERVER_KEY=your_firebase_server_key_here

/**
 * Example Notification Class (app/Notifications/PushNotification.php)
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Firebase\CloudMessaging\RequestResponse\Message;
use Firebase\CloudMessaging\Models\Priority;

class PushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;

    public function __construct($title, $body, array $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['fcm'];
    }

    /**
     * Get the fcm representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Message|null
     */
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
                'badge' => '1'
            ])
            ->setData($this->data)
            ->setToken($token)
            ->setPriority(Priority::HIGH);
            
        return $message;
    }
}

/**
 * Example of using the Notification
 */

namespace App\Http\Controllers;

use App\User;
use App\Notifications\PushNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Send a push notification to a user
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function sendNotification(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $user->notify(new PushNotification(
            'Hello from Laravel 5.5!',
            'This is a test notification from Laravel 5.5',
            ['key1' => 'value1', 'key2' => 'value2']
        ));
        
        return response()->json(['message' => 'Notification sent successfully']);
    }
}

/**
 * Example of using FCM directly with Facade
 */

namespace App\Http\Controllers;

use Firebase\CloudMessaging\Laravel\Facades\FCM;
use Firebase\CloudMessaging\RequestResponse\Message;
use Firebase\CloudMessaging\Models\Priority;
use Illuminate\Http\Request;

class DirectFCMController extends Controller
{
    /**
     * Send a notification directly using the FCM facade
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendPushNotification(Request $request)
    {
        $token = $request->input('device_token');
        
        if (empty($token)) {
            return response()->json(['error' => 'Device token is required'], 400);
        }
        
        $message = new Message();
        $message
            ->setNotification([
                'title' => 'Direct Notification',
                'body' => 'This notification was sent directly using the FCM facade',
                'sound' => 'default'
            ])
            ->setToken($token)
            ->setPriority(Priority::HIGH);
            
        $response = FCM::send($message);
        
        if ($response->isSuccess()) {
            return response()->json([
                'message' => 'Notification sent successfully',
                'result' => $response->getResult()
            ]);
        } else {
            return response()->json([
                'error' => 'Failed to send notification',
                'message' => $response->getErrorMessage()
            ], 500);
        }
    }
}

/**
 * Adding FCM Token to User
 */

// Migration example for adding fcm_token to users table
// Create a migration with: php artisan make:migration add_fcm_token_to_users_table

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFcmTokenToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
}

// Update User model
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'fcm_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}

/**
 * Example API endpoint for updating FCM token
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{
    /**
     * Update device token
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);
        
        // Get the authenticated user
        $user = auth()->user();
        
        // Update FCM token
        $user->update([
            'fcm_token' => $request->token
        ]);
        
        return response()->json([
            'message' => 'Token updated successfully'
        ]);
    }
}

// In your routes/api.php file
Route::middleware('auth:api')->post('/device/token', 'Api\DeviceController@updateToken');
