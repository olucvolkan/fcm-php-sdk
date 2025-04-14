<?php

namespace Firebase\CloudMessaging\RequestResponse;

interface ApiResponseInterface
{
    /**
     * Get the HTTP status code of the response
     * 
     * @return int
     */
    public function getStatusCode(): int;
    
    /**
     * Get any error message from the request
     * 
     * @return string
     */
    public function getErrorMessage(): string;
    
    /**
     * Check if the request was successful
     * 
     * @return bool
     */
    public function isSuccess(): bool;
    
    /**
     * Get the raw response string
     * 
     * @return string
     */
    public function getRawResponse(): string;
    
    /**
     * Get the parsed response
     * 
     * @return array|null
     */
    public function getResult(): ?array;
}
