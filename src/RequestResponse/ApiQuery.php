<?php

namespace Firebase\CloudMessaging\RequestResponse;

class ApiQuery implements ApiQueryInterface
{
    /**
     * @var array
     */
    private $queryParams;

    /**
     * ApiQuery constructor.
     * @param array $queryParams
     */
    public function __construct(array $queryParams = [])
    {
        $this->queryParams = $queryParams;
    }

    /**
     * Add query parameter
     * 
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addParam($key, $value)
    {
        $this->queryParams[$key] = $value;
        return $this;
    }

    /**
     * Get all query parameters
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->queryParams;
    }

    /**
     * Get a specific query parameter
     * 
     * @param string $key
     * @return string|null
     */
    public function getParam($key)
    {
        return isset($this->queryParams[$key]) ? $this->queryParams[$key] : null;
    }

    /**
     * Build the query string from parameters
     * 
     * @return string
     */
    public function getQueryString()
    {
        return http_build_query($this->queryParams);
    }
}
