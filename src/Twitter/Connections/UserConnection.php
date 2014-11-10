<?php namespace Twitter\Connections;

use Twitter\Config\Config;
use Twitter\Config\UserCredentials;
use Twitter\Connections\Exceptions\MediaUploadLimitException;
use Twitter\Connections\Exceptions\ClientException as CE;
use Twitter\Connections\Exceptions\ServerException as SE;

use GuzzleHttp\Post\PostFile;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

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
        $type = pathinfo($mediaPath, PATHINFO_EXTENSION);

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
     * @param  GuzzleHttp\Client $client Optional. To inject your own instance of Guzzle. The base_url of the injected client should be set to Config::get('base_upload_url').
     *
     * @return string|false If a ServerException occurs, return false.
     */
    public function uploadMedia($filepaths, $client = null)
    {

        //maximum number of media files that a user can upload
        $maxMediaIds = Config::get('max_media_ids');

        //if number of media files supplied is larger than $maxMediaIds, throw exception.
        if(count($filepaths) > $maxMediaIds)
        {
            throw new MediaUploadLimitException("You cannot upload more than ${maxMediaIds} media files in a tweet!");
        }

        //array list of media id's uploaded
        $mediaIds = array();

        //create a new Guzzle client, if the user hasn't injected anything!
        if(is_null($client))
        {
            $client = $this->createGuzzleClient(Config::get('base_upload_url'));
        }

        //prepend Twitter's API version to the endpoint
        $endpoint = $this->prependVersionToEndpoint("media/upload.json", Config::get('api_version'));

        //iterate over each filepath
        foreach ($filepaths as $filepath)
        {

            //contruct an options array to configure the request
            $options = $this->constructRequestOptions(array(), $client);

            //add body options to the POST request
            $options['body'] = array (
                'media' => new PostFile('media', fopen($filepath, 'r'))
            );

            try
            {
                //make the POST request to the endpoint with the constructed options.
                $response = $client->post($endpoint, $options);
            }
            catch(ClientException $ex)
            {
                //custom ClientException
                throw new CE("Oops! You made some error.", $ex);
            }
            catch(ServerException $ex)
            {
                //custom ServerException
                throw new SE("Oops! Twitter's servers are under load. Try again, later!", $ex);
            }

            //add media_id to array
            array_push($mediaIds, $response->json()['media_id_string']);

        }

        //return all media ID's as a string (comma seperated)
        return (implode(",", $mediaIds));

    }


}
