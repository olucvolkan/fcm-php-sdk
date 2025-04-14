<?php

namespace Firebase\CloudMessaging\Laravel\Channels;

use Firebase\CloudMessaging\Client\FCMClient;
use Firebase\CloudMessaging\RequestResponse\Exception\FCMException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FCMChannel
{
    /**
     * The FCM client instance.
     *
     * @var \Firebase\CloudMessaging\Client\FCMClient
     */
    protected $fcm;

    /**
     * Create a new FCM channel instance.
     *
     * @param \Firebase\CloudMessaging\Client\FCMClient $fcm
     */
    public function __construct(FCMClient $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            if (method_exists($notification, 'toArray')) {
                $message = $this->buildMessageFromArray($notifiable, $notification);
            } else {
                return null;
            }
        } else {
            $message = $notification->toFcm($notifiable);
        }
        
        if (empty($message)) {
            return null;
        }
        
        try {
            $response = $this->fcm->send($message);
            
            if (!$response->isSuccess()) {
                if (function_exists('Log::error')) {
                    Log::error('FCM notification failed: ' . $response->getErrorMessage());
                }
            }
            
            return $response;
        } catch (FCMException $e) {
            if (function_exists('Log::error')) {
                Log::error('FCM error: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Build a message from the notification's toArray method.
     * 
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Firebase\CloudMessaging\RequestResponse\Message|null
     */
    protected function buildMessageFromArray($notifiable, Notification $notification)
    {
        $data = $notification->toArray($notifiable);
        
        if (empty($data)) {
            return null;
        }

        // Get FCM token from notifiable entity
        $token = null;
        
        // Try to get token from routeNotificationForFcm method
        if (method_exists($notifiable, 'routeNotificationForFcm')) {
            $token = $notifiable->routeNotificationForFcm($notification);
        } 
        // If not available, try to get from fcmToken property or method
        elseif (isset($notifiable->fcmToken)) {
            $token = $notifiable->fcmToken;
        } elseif (method_exists($notifiable, 'fcmToken')) {
            $token = $notifiable->fcmToken();
        } elseif (method_exists($notifiable, 'getFcmToken')) {
            $token = $notifiable->getFcmToken();
        }
        
        if (empty($token)) {
            return null;
        }
        
        // Create new message
        $message = new \Firebase\CloudMessaging\RequestResponse\Message();
        
        // Set notification if title and body present
        if (isset($data['title']) && isset($data['body'])) {
            $notification = [
                'title' => $data['title'],
                'body' => $data['body'],
                'sound' => 'default',
            ];
            
            // Add optional notification params
            foreach (['badge', 'click_action', 'sound', 'icon', 'color', 'tag'] as $param) {
                if (isset($data[$param])) {
                    $notification[$param] = $data[$param];
                }
            }
            
            $message->setNotification($notification);
            
            // Remove these keys from the data payload
            unset($data['title'], $data['body']);
            foreach (['badge', 'click_action', 'sound', 'icon', 'color', 'tag'] as $param) {
                unset($data[$param]);
            }
        }
        
        // Set remaining data as message data
        if (!empty($data)) {
            $message->setData($data);
        }
        
        // Set token
        $message->setToken($token);
        
        return $message;
    }
}
