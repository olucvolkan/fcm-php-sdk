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
        string $rawResponse, 
        ResponseParserInterface $responseParser = null, 
        int $statusCode = 200,
        string $errorMessage = ''
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
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get any error message from the request
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Check if the request was successful
     * 
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300 && empty($this->errorMessage);
    }

    /**
     * Get the raw response string
     * 
     * @return string
     */
    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    /**
     * Get the parsed response
     * 
     * @return array|null
     */
    public function getResult(): ?array
    {
        return $this->parsedResponse;
    }
}
