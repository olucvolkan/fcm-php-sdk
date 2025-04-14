<?php

namespace Firebase\CloudMessaging\RequestResponse\ResponseParser;

interface ResponseParserInterface
{
    /**
     * Parse a raw response string into a structured format
     * 
     * @param string $response
     * @return array|null
     */
    public function parse($response);
}
