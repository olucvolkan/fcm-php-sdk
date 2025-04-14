<?php

namespace Firebase\CloudMessaging\RequestResponse\ResponseParser;

use Firebase\CloudMessaging\RequestResponse\Exception\InvalidResponseException;

class FCMResponseParser implements ResponseParserInterface
{
    /**
     * Parse FCM response
     * 
     * @param string $response
     * @return array|null
     */
    public function parse($response)
    {
        if (empty($response)) {
            return null;
        }
        
        $parsedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidResponseException('Failed to parse FCM response: ' . json_last_error_msg());
        }
        
        return $parsedResponse;
    }
}
