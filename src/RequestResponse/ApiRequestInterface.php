<?php

namespace Firebase\CloudMessaging\RequestResponse;

interface ApiRequestInterface
{
    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return ApiQueryInterface
     */
    public function getQuery(): ApiQueryInterface;

    /**
     * @param ApiQueryInterface $query
     */
    public function setQuery(ApiQueryInterface $query);
    
    /**
     * @param string $format
     * @return ApiResponse
     */
    public function getRequest($format = 'json');
}
