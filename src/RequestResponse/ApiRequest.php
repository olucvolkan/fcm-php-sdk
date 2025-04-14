<?php

namespace Firebase\CloudMessaging\RequestResponse;

use Firebase\CloudMessaging\RequestResponse\Exception\MissingApiQueryException;
use Firebase\CloudMessaging\RequestResponse\ResponseParser\ResponseParserInterface;

class ApiRequest implements ApiRequestInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var ApiQueryInterface
     */
    private $query;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var string|null
     */
    private $postFields = null;

    /**
     * ApiRequest constructor.
     * 
     * @param string $url
     * @param ApiQueryInterface|null $query
     * @param ResponseParserInterface|null $responseParser
     */
    public function __construct($url, ApiQueryInterface $query = null, ResponseParserInterface $responseParser = null)
    {
        $this->url = $url;
        $this->query = $query;
        $this->responseParser = $responseParser;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return ApiQueryInterface
     * @throws MissingApiQueryException
     */
    public function getQuery(): ApiQueryInterface
    {
        if (empty($this->query)) {
            throw new MissingApiQueryException();
        }

        return $this->query;
    }

    /**
     * @param ApiQueryInterface $query
     */
    public function setQuery(ApiQueryInterface $query)
    {
        $this->query = $query;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param string $postFields
     */
    public function setPostFields(string $postFields)
    {
        $this->postFields = $postFields;
    }

    /**
     * @param string $format
     *
     * @return ApiResponse
     * @throws \InvalidArgumentException
     */
    public function getRequest($format = 'json')
    {
        $url = $this->getUrl();
        
        if ($this->query) {
            $queryString = $this->getQuery()->getQueryString();
            if (!empty($queryString)) {
                $url .= '?' . $queryString;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
        
        if ($this->postFields !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postFields);
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        switch ($format) {
            case 'json':
                return new ApiResponse($result, $this->responseParser, $httpCode, $error);
            default:
                throw new \InvalidArgumentException('Supported formats are json');
        }
    }
}
