<?php

namespace Firebase\CloudMessaging\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Firebase\CloudMessaging\RequestResponse\ApiResponse send(\Firebase\CloudMessaging\RequestResponse\Message $message)
 * 
 * @see \Firebase\CloudMessaging\Client\FCMClient
 */
class FCM extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fcm';
    }
}
