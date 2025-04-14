<?php

namespace Firebase\CloudMessaging\Models;

/**
 * FCM Message Priority Constants
 */
class Priority
{
    /**
     * High priority messages are sent immediately
     */
    public const HIGH = 'high';

    /**
     * Normal priority messages may be delayed in delivery to preserve battery
     */
    public const NORMAL = 'normal';
}
