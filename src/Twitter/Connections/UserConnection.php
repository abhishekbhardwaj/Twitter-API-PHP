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

        //if query parameters not supplied, continue.
        if(!is_null($options['query']))
        {
            //Add query parameters to options.
            $options['query'] => $params;
        }

        //Set the "auth" request option to "oauth" to sign using oauth.
        $options['auth'] => 'oauth';

        //return constructed options
        return $options;
    }

    /**
     * Get a request token and configures a Twitter authorization URL that the
     * user will be redirected to for authentication.
     *
     * Then returns the prepared Twitter authorization URL.
     *
     * @return string authentication URL
     */
    public function getRedirectUrlForAuth()
    {
        //obtain request token for the authorization popup.
        $requestTokenResponse = $this->get(
            Config::get('oauth_request_token'),
            null
        );

        //Parse the response from Twitter
        $oauthToken = array();
        parse_str($requestTokenResponse->getBody(), $oauthToken);

        //build the query parameters
        $params = http_build_query(array(
            'oauth_token' => $oauthToken['oauth_token']
        ));

        //return the redirect URL the user should be redirected to.
        return (Config::get('oauth_authenticate') . '?' . $params);
    }

}
