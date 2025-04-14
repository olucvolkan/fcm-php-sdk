<?php

namespace Firebase\CloudMessaging\RequestResponse;

interface ApiRequestInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return ApiQueryInterface
     */
    public function getQuery();

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
