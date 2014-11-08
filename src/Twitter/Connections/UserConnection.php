<?php namespace Twitter\Connections;

use Twitter\Config\UserCredentials;

class AppConnection extends Connection {

    /**
     * A user connection to Twitter.
     *
     * @param UserCredentials $credentials Twitter API credentials
     */
    public function __construct(UserCredentials $credentials)
    {
        parent::__construct($credentials);
    }

    /**
     * Constructs an options array that is sent with the request.
     *
     * Uses Oauth tokens since this is a UserConnection.
     *
     * @return array options for the request
     */
    protected function constructRequestOptions($params)
    {
        //empty array
        $options = array();

        //this is a User connection, use Oauth1 tokens.
        $oauth = new Oauth1(array(
            'consumer_key'    => $this->credentials->getConsumerKey(),
            'consumer_secret' => $this->credentials->getConsumerSecret(),
            'token'           => $this->credentials->getAccessToken(),
            'token_secret'    => $this->credentials->getAccessTokenSecret()
        ));

        //attach oauth
        $this->guzzleClient->getEmitter()->attach($oauth);

        //Add query parameters to options.
        $options['query'] => $params;

        //Set the "auth" request option to "oauth" to sign using oauth.
        $options['auth'] => 'oauth';

        //return constructed options
        return $options;
    }

}
