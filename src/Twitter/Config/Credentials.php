<?php namespace Twitter\Config;

class Credentials {

    /**
     * Twitter Application Consumer Key.
     *
     * @var string
     */
    private $consumerKey;

    /**
     * Twitter Application Consumer Secret.
     *
     * @var string
     */
    private $consumerSecret;

    /**
     * Construct a Credentials object.
     *
     * @param string $consumerKey    Twitter Application Consumer Key
     * @param string $consumerSecret Twitter Application Consumer Secret
     */
    public function __construct($consumerKey, $consumerSecret)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
    }

    /**
     * Gets the Twitter Application Consumer Key.
     *
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * Gets the Twitter Application Consumer Secret.
     *
     * @return string
     */
    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }

}
