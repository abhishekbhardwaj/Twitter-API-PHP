<?php namespace Twitter\Connections;

use Twitter\Config\AppCredentials;

class AppConnection extends Connection {

    /**
     * An application connection to Twitter.
     *
     * @param AppCredentials $credentials Twitter API credentials
     */
    public function __construct(AppCredentials $credentials)
    {
        parent::__construct($credentials);
    }

    /**
     * Creates and returns an encoded bearer token credentials to be used for making calls to Twitter.
     *
     * Currently:
     *     - URL encode consumer key and consumer secret
     *     - Create bearer token credentials
     *     - Base64 encode the resulting value and return.
     *
     * @link https://dev.twitter.com/docs/auth/application-only-auth Bearer Token Description
     *
     * @return string base64 encoded bearer token.
     */
    private function createBearerCredentials()
    {
        //URL encode the consumer key and consumer secret
        $consumerKey = rawurlencode($this->credentials->consumerKey);
        $consumerSecret = rawurlencode($this->credentials->consumerSecret);

        //create bearer token credentials by concatenating the consumer key and consumer secret, seperated by a colon.
        $bearerTokenCredentials = $consumerKey . ':' . $consumerSecret;

        //base64 encode the bearer token credentials
        return base64_encode($bearerTokenCredentials);
    }

    /**
     * Calls Twitter and gets a bearer token. This bearer token is valid until it gets invalidated.
     *
     * @link https://dev.twitter.com/docs/auth/application-only-auth Bearer Token Description
     *
     * @return $this AppConnection
     */
    public function createBearerToken()
    {
        //get bearer token credentials - to be used for getting the bearer token from Twitter.
        $bearerCredentials = $this->createBearerCredentials();

        //Required Headers
        $headers = array(
            'Authorization' => 'Basic ' . $bearerCredentials,
            'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
        );

        //Required Body
        $body = 'grant_type=client_credentials';

        //Send a Post request to `oauth2/token` and convert the resulting JSON to assoc-array.
        $data = $this->guzzleClient->post(
                Config::get('oauth2_token'),
                array(
                    'headers' => $headers,
                    'body' => $body
                )
            )->json();

        //Set the bearer token in the AppCredentials object
        $this->credentials->setBearerToken($data['access_token']);

        //Return the current object
        return $this;
    }

    /**
     * Constructs an options array that is sent with the request.
     *
     * Uses Bearer Token since this is an AppConnection.
     *
     * @return array options for the request
     */
    protected function constructRequestOptions($params)
    {
        //empty array
        $options = array();

        //add Bearer Token to the header
        $headers = array(
            'Authorization' => 'Bearer ' . $this->credentials->getBearerToken()
        );

        //Add query parameters to options.
        $options['query'] => $params;

        //Add headers to the request.
        $options['headers'] => $headers;

        //return constructed options
        return $options;
    }
}
