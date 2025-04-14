<?php

namespace Firebase\CloudMessaging\RequestResponse\Exception;

/**
 * Exception thrown when a required API query is missing
 */
class MissingApiQueryException extends FCMException
{
    protected $message = 'API query is required but not provided';
}
