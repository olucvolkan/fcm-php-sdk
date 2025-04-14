<?php

namespace Firebase\CloudMessaging\RequestResponse;

use Firebase\CloudMessaging\RequestResponse\ResponseParser\ResponseParserInterface;

class ApiResponse implements ApiResponseInterface
{
    /**
     * @var string
     */
    private $rawResponse;

    /**
     * @var array|null
     */
    private $parsedResponse;

    /**
     * @var ResponseParserInterface|null
     */
    private $responseParser;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * ApiResponse constructor.
     * 
     * @param string $rawResponse
     * @param ResponseParserInterface|null $responseParser
     * @param int $statusCode
     * @param string $errorMessage
     */
    public function __construct(
        $rawResponse, 
        ResponseParserInterface $responseParser = null, 
        $statusCode = 200,
        $errorMessage = ''
    ) {
        $this->rawResponse = $rawResponse;
        $this->responseParser = $responseParser;
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
        
        if ($this->responseParser !== null) {
            $this->parsedResponse = $this->responseParser->parse($rawResponse);
        }
    }

    /**
     * Get the HTTP status code of the response
     * 
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get any error message from the request
     * 
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Check if the request was successful
     * 
     * @return bool
     */
    public function isSuccess()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300 && empty($this->errorMessage);
    }

    /**
     * Get the raw response string
     * 
     * @return string
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * Get the parsed response
     * 
     * @return array|null
     */
    public function getResult()
    {
        return $this->parsedResponse;
    }
}
