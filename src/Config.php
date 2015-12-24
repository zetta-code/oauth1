<?php

namespace OAuth1;

use InvalidArgumentException;
use OAuth1\Contracts\ConfigInterface;

class Config implements ConfigInterface {
    /**
     * OAuth consumer key.
     *
     * @var string
     */
    protected $consumerKey;

    /**
     * OAuth consumer secret.
     *
     * @var string
     */
    protected $consumerSecret;

    /**
     * OAuth callback url.
     *
     * @var string|null
     */
    protected $callbackUrl;

    /**
     * OAuth request token url.
     *
     * @var string
     */
    protected $requestTokenUrl;

    /**
     * OAuth access token url.
     *
     * @var string
     */
    protected $accessTokenUrl;

    /**
     * Create a new instance of Config.
     *
     * @param string        $consumerKey
     * @param string        $consumerSecret
     * @param string|null   $callbackUrl
     * @param string        $requestTokenUrl
     * @param string        $accessTokenUrl
     */
    public function __construct($consumerKey, $consumerSecret, $callbackUrl, $requestTokenUrl, $accessTokenUrl)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->callbackUrl = $callbackUrl;
        $this->requestTokenUrl = $requestTokenUrl;
        $this->accessTokenUrl = $accessTokenUrl;
    }

    /**
     * Get OAuth consumer key.
     *
     * @return string
     */
    public function consumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * Get OAuth consumer secret.
     *
     * @return string
     */
    public function consumerSecret()
    {
        return $this->consumerSecret;
    }

    /**
     * Get OAuth callback url.
     *
     * @return string|null
     */
    public function callbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * Get OAuth request token url.
     *
     * @return string
     */
    public function requestTokenUrl()
    {
        return $this->requestTokenUrl;
    }

    /**
     * Get OAuth access token url.
     *
     * @return string
     */
    public function accessTokenUrl()
    {
        return $this->accessTokenUrl;
    }

    /**
     * Create an instance from array.
     *
     * @param array $config
     */
    static public function fromArray(array $config)
    {
        $requiredParams = [
            'consumer_key',
            'consumer_secret',
            'request_token_url',
            'access_token_url'
        ];

        foreach ($requiredParams as $param) {
            if (! isset($config[$param])) {
                throw new InvalidArgumentException("Missing OAuth client configuration: $param.");
            }
        }

        $callbackUrl = isset($config['callback_url']) ? $config['callback_url'] : null;

        return new static(
            $config['consumer_key'],
            $config['consumer_secret'],
            $callbackUrl,
            $config['request_token_url'],
            $config['access_token_url']
        );
    }
}