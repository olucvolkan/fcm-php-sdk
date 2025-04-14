<?php

namespace Firebase\CloudMessaging\RequestResponse;

use Firebase\CloudMessaging\Models\Priority;

class Message
{
    /**
     * @var array
     */
    protected $notification = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string|null
     */
    protected $token = null;

    /**
     * @var array|null
     */
    protected $tokens = null;

    /**
     * @var string|null
     */
    protected $topic = null;

    /**
     * @var string
     */
    protected $priority = Priority::NORMAL;

    /**
     * @var int|null
     */
    protected $timeToLive = null;

    /**
     * @var string|null
     */
    protected $collapseKey = null;

    /**
     * @var bool
     */
    protected $contentAvailable = false;

    /**
     * @var bool
     */
    protected $mutableContent = false;

    /**
     * Set notification parameters
     *
     * @param array $params
     * @return $this
     */
    public function setNotification(array $params): self
    {
        $this->notification = $params;
        return $this;
    }

    /**
     * Set data payload
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set target device token
     *
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        $this->tokens = null;
        $this->topic = null;
        return $this;
    }

    /**
     * Set multiple target tokens (up to 1000)
     *
     * @param array $tokens
     * @return $this
     */
    public function setTokens(array $tokens): self
    {
        $this->tokens = $tokens;
        $this->token = null;
        $this->topic = null;
        return $this;
    }

    /**
     * Set target topic
     *
     * @param string $topic
     * @return $this
     */
    public function setTopic(string $topic): self
    {
        $this->topic = $topic;
        $this->token = null;
        $this->tokens = null;
        return $this;
    }

    /**
     * Set message priority
     *
     * @param string $priority
     * @return $this
     */
    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Set time to live in seconds
     *
     * @param int $seconds
     * @return $this
     */
    public function setTimeToLive(int $seconds): self
    {
        $this->timeToLive = $seconds;
        return $this;
    }

    /**
     * Set collapse key for message grouping
     *
     * @param string $key
     * @return $this
     */
    public function setCollapseKey(string $key): self
    {
        $this->collapseKey = $key;
        return $this;
    }

    /**
     * Enable content-available flag for iOS
     *
     * @param bool $contentAvailable
     * @return $this
     */
    public function setContentAvailable(bool $contentAvailable = true): self
    {
        $this->contentAvailable = $contentAvailable;
        return $this;
    }

    /**
     * Enable mutable-content flag for iOS
     *
     * @param bool $mutableContent
     * @return $this
     */
    public function setMutableContent(bool $mutableContent = true): self
    {
        $this->mutableContent = $mutableContent;
        return $this;
    }

    /**
     * Build FCM payload based on the configured options
     *
     * @return array
     */
    public function buildPayload(): array
    {
        $payload = [];

        // Add notification if set
        if (!empty($this->notification)) {
            $payload['notification'] = $this->notification;
        }

        // Add data if set
        if (!empty($this->data)) {
            $payload['data'] = $this->data;
        }

        // Set target (token, tokens or topic)
        if ($this->token !== null) {
            $payload['to'] = $this->token;
        } elseif ($this->tokens !== null) {
            $payload['registration_ids'] = $this->tokens;
        } elseif ($this->topic !== null) {
            $payload['to'] = '/topics/' . $this->topic;
        }

        // Add optional parameters
        if ($this->priority) {
            $payload['priority'] = $this->priority;
        }

        if ($this->timeToLive !== null) {
            $payload['time_to_live'] = $this->timeToLive;
        }

        if ($this->collapseKey) {
            $payload['collapse_key'] = $this->collapseKey;
        }

        if ($this->contentAvailable) {
            $payload['content_available'] = true;
        }

        if ($this->mutableContent) {
            $payload['mutable_content'] = true;
        }

        return $payload;
    }
}
