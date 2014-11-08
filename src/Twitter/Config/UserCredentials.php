<?php namespace Twitter\Config;

class UserCredentials extends Credentials {

    /**
     * Twitter Access Token.
     *
     * @var string
     */
    private $accessToken;

    /**
     * Twitter Access Token Secret.
     *
     * @var string
     */
    private $accessTokenSecret;

    /**
     * Twitter Application Callback URL.
     *
     * @var string
     */
    private $callbackUrl;

    /**
     * Constructs a Credentials object for Users (making requests on behalf of a user).
     *
     * @param string $consumerKey    Twitter Application Consumer Key
     * @param string $consumerSecret Twitter Application Consumer Secret
     * @param string|null $callbackUrl Twitter Application Callback URL
     * @param string|null $accessToken Twitter Access Token
     * @param string|null $accessTokenSecret Twitter Access Token Secret
     */
    public function __construct($consumerKey, $consumerSecret, $callbackUrl = null, $accessToken = null, $accessTokenSecret = null)
    {
        parent::__construct($consumerKey, $consumerSecret);

        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * Gets the Twitter Access Token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Sets the Twitter Access Token.
     *
     * @param string $accessToken the access token
     *
     * @return self
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Gets the Twitter Access Token Secret.
     *
     * @return string
     */
    public function getAccessTokenSecret()
    {
        return $this->accessTokenSecret;
    }

    /**
     * Sets the Twitter Access Token Secret.
     *
     * @param string $accessTokenSecret the access token secret
     *
     * @return self
     */
    public function setAccessTokenSecret($accessTokenSecret)
    {
        $this->accessTokenSecret = $accessTokenSecret;

        return $this;
    }

    /**
     * Gets the Twitter Application Callback URL.
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * Sets the Twitter Application Callback URL.
     *
     * @param string $callbackUrl the callback url
     *
     * @return self
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

}
