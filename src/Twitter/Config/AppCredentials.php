<?php namespace Twitter\Config;

class AppCredentials extends Credentials {

    /**
     * Twitter Bearer Token (For Application Use).
     *
     * @var string
     */
    private $bearerToken;

    /**
     * Constructs a Credentials object for applications (when making calls on Application's behalf).
     *
     * @link https://dev.twitter.com/oauth/application-only Application-Only Authentication
     *
     * @param string $consumerKey    Twitter Application Consumer Key
     * @param string $consumerSecret Twitter Application Consumer Secret
     */
    public function __construct($consumerKey, $consumerSecret, $bearerToken = null)
    {
        parent::__construct($consumerKey, $consumerSecret);

        $this->bearerToken = $bearerToken;
    }

    /**
     * Gets the Twitter Bearer Token (For Application Use).
     *
     * @return string
     */
    public function getBearerToken()
    {
        return $this->bearerToken;
    }

    /**
     * Sets the Twitter Bearer Token (For Application Use).
     *
     * @param string $bearerToken the bearer token
     *
     * @return self
     */
    public function setBearerToken($bearerToken)
    {
        $this->bearerToken = $bearerToken;

        return $this;
    }

}
