<?php namespace Twitter\Connections;

use Twitter\Config\Config;
use Twitter\Config\UserCredentials;

use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * getRedirectUrlForAuth() and getAccessToken() are used for user authentication workflow (Oauth 1.0).
 *
 * Steps:
 *
 * - Get a request Token from Twitter and construct an Authentication URL for the user.
 * - Once the user is redirected back to the app, use 'oauth_token' and 'oauth_verifier' to get access tokens from Twitter.
 */
class UserConnection extends Connection {

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
        if(!is_null($params))
        {
            //Add query parameters to options.
            $options['query'] = $params;
        }

        //Set the "auth" request option to "oauth" to sign using oauth.
        $options['auth'] = 'oauth';

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
        //Oauth1 plugin to get access tokens!
        $oauth = new Oauth1(array(
            'consumer_key'    => $this->credentials->getConsumerKey(),
            'consumer_secret' => $this->credentials->getConsumerSecret(),
            'callback'        => $this->credentials->getCallbackUrl()
        ));

        $this->guzzleClient->getEmitter()->attach($oauth);

        //obtain request token for the authorization popup.
        $requestTokenResponse = $this->guzzleClient->post(
            Config::get('oauth_request_token'),
            array(
                'auth' => 'oauth'
            )
        );

        //Parse the response from Twitter
        $oauthToken = array();
        parse_str($requestTokenResponse->getBody(), $oauthToken);

        //build the query parameters
        $params = http_build_query(array(
            'oauth_token' => $oauthToken['oauth_token']
        ));

        //return the redirect URL the user should be redirected to.
        return (Config::get('base_url') . Config::get('oauth_authenticate') . '?' . $params);
    }

    /**
     * Get Access tokens from the user in exchange of oauth_token and oauth_verifier and return
     * them.
     *
     * @param  string $oauthToken
     * @param  string $oauthVerifier
     * @return array contains 'oauth_token', 'oauth_token_secret', 'user_id' and 'screen_name'.
     */
    public function getAccessToken($oauthToken, $oauthVerifier)
    {
        //Oauth1 plugin to get access tokens!
        $oauth = new Oauth1(array(
            'consumer_key'    => $this->credentials->getConsumerKey(),
            'consumer_secret' => $this->credentials->getConsumerSecret(),
            'token'           => $oauthToken,
            'verifier'        => $oauthVerifier
        ));

        //attach oauth to request
        $this->guzzleClient->getEmitter()->attach($oauth);

        //POST to 'oauth/access_token' - get access tokens
        $accessTokenResponse = $this->guzzleClient->post(
            Config::get('oauth_access_token'),
            array(
                'auth' => 'oauth'
            )
        );

        //handle response
        $response = array();
        parse_str($accessTokenResponse->getBody(), $response);

        //set access tokens
        $this->credentials
            ->setAccessToken($response['oauth_token'])
            ->setAccessTokenSecret($response['oauth_token_secret']);

        return $response; //contains 'oauth_token', 'oauth_token_secret', 'user_id' and 'screen_name'
    }

}
