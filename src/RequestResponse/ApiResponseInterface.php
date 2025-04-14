<?php

namespace Firebase\CloudMessaging\RequestResponse;

interface ApiResponseInterface
{
    /**
     * Get the HTTP status code of the response
     * 
     * @return int
     */
    public function getStatusCode();
    
    /**
     * Get any error message from the request
     * 
     * @return string
     */
    public function getErrorMessage();
    
    /**
     * Check if the request was successful
     * 
     * @return bool
     */
    public function isSuccess();
    
    /**
     * Get the raw response string
     * 
     * @return string
     */
    public function getRawResponse();
    
    /**
     * Get the parsed response
     * 
     * @return array|null
     */
    public function getResult();
}
