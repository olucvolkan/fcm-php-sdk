<?php

namespace Firebase\CloudMessaging\Client;

use Firebase\CloudMessaging\RequestResponse\ApiQuery;
use Firebase\CloudMessaging\RequestResponse\ApiRequest;
use Firebase\CloudMessaging\RequestResponse\ApiResponse;
use Firebase\CloudMessaging\RequestResponse\Message;
use Firebase\CloudMessaging\RequestResponse\ResponseParser\FCMResponseParser;

class FCMClient
{
    /**
     * @var string
     */
    protected $serverKey;

    /**
     * @var string
     */
    protected $url = 'https://fcm.googleapis.com/fcm/send';

    /**
     * FCMClient constructor.
     * @param string $serverKey Your Firebase Server Key
     */
    public function __construct(string $serverKey)
    {
        $this->serverKey = $serverKey;
    }

    /**
     * Send a FCM message
     * 
     * @param Message $message
     * @return ApiResponse
     */
    public function send(Message $message): ApiResponse
    {
        $query = new ApiQuery([]);
        $payload = $message->buildPayload();
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ];
        
        $apiResponseParser = new FCMResponseParser();
        $apiRequest = new ApiRequest($this->url, $query, $apiResponseParser);
        $apiRequest->setHeaders($headers);
        $apiRequest->setPostFields(json_encode($payload));
        
        return $apiRequest->getRequest('json');
    }
}
