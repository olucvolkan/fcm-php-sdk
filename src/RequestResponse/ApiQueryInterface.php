<?php

namespace Firebase\CloudMessaging\RequestResponse;

interface ApiQueryInterface
{
    /**
     * Add a parameter to the query
     * 
     * @param string $key
     * @param string $value
     * @return self
     */
    public function addParam(string $key, string $value): self;

    /**
     * Get all query parameters
     * 
     * @return array
     */
    public function getParams(): array;

    /**
     * Get a specific parameter by key
     * 
     * @param string $key
     * @return string|null
     */
    public function getParam(string $key): ?string;

    /**
     * Get the formatted query string
     * 
     * @return string
     */
    public function getQueryString(): string;
}
