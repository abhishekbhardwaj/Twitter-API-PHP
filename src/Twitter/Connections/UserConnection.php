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
     * @param array $params URL query parameters
     * @param GuzzleHttp\Client $client a client to attach Oauth1 plugin to (can be null).
     *
     * @return array options for the request
     */
    protected function constructRequestOptions($params, $client = null)
    {
        //empty options array
        $options = array();

        //this is a User connection, use Oauth1 tokens.
        $oauth = new Oauth1(array(
            'consumer_key'    => $this->credentials->getConsumerKey(),
            'consumer_secret' => $this->credentials->getConsumerSecret(),
            'token'           => $this->credentials->getAccessToken(),
            'token_secret'    => $this->credentials->getAccessTokenSecret()
        ));

        //attach oauth to Guzzle client
        if(is_null($client))
        {
            //use the instance client
            $this->guzzleClient->getEmitter()->attach($oauth);
        }
        else
        {
            //to the parameter specified in the client
            $client->getEmitter()->attach($oauth);
        }

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

    /**
     * Base64 encode the Media located at $mediaPath.
     *
     * @param  string $mediaPath media where it's located
     * @return string
     */
    private function base64EncodeMedia($mediaPath)
    {
        //get media type (extension)
        $type = pathinfo($path, PATHINFO_EXTENSION);

        //get media data
        $data = file_get_contents($mediaPath);

        //encode the filedata with base64 - and then concatenate to make the encoded string.
        $encodedData = 'data:image/' . $type . ';base64,' . base64_encode($data);

        //return the encoded data
        return $encodedData;
    }

    /**
     * Upload media to Twitter and return a comma separated string containing their
     * media ID's to send with a status.
     *
     * @param  array $filepaths should be a maximum of 4
     * @return string
     */
    public function uploadMedia($filepaths)
    {

        //comma separated list of media id's uploaded
        $mediaIds = "";

        //create a new Guzzle client
        $client = $this->createGuzzleClient(Config::get('base_upload_url'));

        //prepend Twitter's API version to the endpoint
        $endpoint = $this->prependVersionToEndpoint("media/upload.json", Config::get('api_version'));

        //to keep track of the number of media files uploaded
        $i = 0;

        //iterate over each filepath
        foreach ($filepaths as $filepath)
        {

            /**
             * If current media count is equal to max media items Twitter allows,
             * break out of the loop since we don't want to add anymore.
             */
            if( $i == (Config::get('max_media_ids') - 1) )
            {
                break;
            }

            //contruct an options array to configure the request
            $options = $this->constructRequestOptions(array(
                'media_data' => $this->base64EncodeMedia($filepath);
            ));

            //make the GET request to the endpoint with the constructed options.
            $response = $this->guzzleClient->post($endpoint, $options);

            //concatenate media id to the return string.
            if($i == 0)
            {
                //if this is the first filename, don't start with a comma
                $mediaIds .= $response->json()['media_id_string'];
            }
            else
            {
                //if this is not the first filename, start with a comma
                $mediaIds .= ',' . $response->json()['media_id_string'];
            }

            //increment the media uploaded counter.
            $i++;

        }

        //return all media ID's
        return $mediaIds;

    }


}
