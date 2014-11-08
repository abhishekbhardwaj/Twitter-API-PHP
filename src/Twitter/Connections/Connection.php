<?php namespace Twitter\Connections;

use Twitter\Config\Config;
use Twitter\Config\Credentials;
use Twitter\Config\AppCredentials;
use Twitter\Config\UserCredentials;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Message\RequestInterface;

class Connection {

    /**
     * Twitter Credentials - Connection type.
     *
     * @var AppCredentials|UserCredentials
     */
    private $credentials;

    /**
     * Guzzle Client to be used during the connection.
     *
     * @var GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * A connection. Contains common methods between AppConnection & UserConnection!
     *
     * @param Credentials $credentials Twitter Credentials
     */
    public function __construct(Credentials $credentials)
    {
        $this->credentials = $credentials;
        $this->guzzleClient = $this->createGuzzleClient(Config::get('base_url'), Config::get('api_version'));
    }

    /**
     * Creates a new Guzzle client with Twitter API's base URL and API Version.
     *
     * @param string $baseUrl Base Twitter API URL
     *
     * @return GuzzleHttp\Client A new Guzzle Client
     */
    private function createGuzzleClient($baseUrl)
    {
        //create and return the Guzzle client
        return new Client(array(
            'base_url' = $baseUrl
        ));
    }

    /**
     * Set Headers for a Request specified by $headers.
     *
     * @param RequestInterface $request a Guzzle Request
     * @param array            $headers headers to set (should be an assoc array).
     */
    private function setGuzzleHeaders(RequestInterface $request, array $headers)
    {
        //iterate over the headers array and set each item
        foreach ($headers as $key => $value) {
            //Sets Header
            $request->setHeader($key, $value);
        }

        //return the request
        return $request;
    }

    /**
     * Prepend Twitter's API Version to an endpoint.
     *
     * @param  string $endpoint Twitter endpoint.
     * @param  string $version  Twitter API version.
     *
     * @return string           Twitter endpoint with the API version prepended.
     */
    private function prependVersionToEndpoint($endpoint, $version)
    {
        return ($version . '/' . $endpoint);
    }

    /**
     * Constructs an options array that is sent with the request.
     *
     * @return array options for the request
     */
    private function constructRequestOptions()
    {
        //empty array
        $options = array();

        //if this is an App connection, use Bearer Token.
        if($this->credentials instanceof AppCredentials)
        {
            //add Bearer Token to the header
            $headers = array(
                'Authorization' => 'Bearer ' . $this->credentials->getBearerToken()
            );

            //Add query parameters to options.
            $options['query'] => $params;

            //Add headers to the request.
            $options['headers'] => $headers;
        }
        else
        {
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
        }

        //return constructed options
        return $options;
    }

    /**
     * Make a GET request to the endpoint. Also appends query params to the URL.
     *
     * @param  string $endpoint The endpoint to send a GET request to.
     * @param  array $params   associative array for the query parameters.
     *
     * @link   http://guzzle.readthedocs.org/en/latest/quickstart.html#using-responses ResponseInterface details.
     *
     * @return GuzzleHttp\Message\ResponseInterface API Response. Contains a lot of information.
     */
    public function get($endpoint, $params)
    {
        //prepend Twitter's API version to the endpoint
        $endpoint = $this->prependVersionToEndpoint($endpoint, Config::get('api_version'));

        //contruct an options array to configure the request
        $options = $this->constructRequestOptions();

        //make the GET request to the endpoint with the constructed options.
        $response = $this->guzzleClient->get($endpoint, $options);

        //return response
        return $response;
    }

    /**
     * Make a POST request to the endpoint. Also appends query params to the URL.
     *
     * @param  string $endpoint The endpoint to send a POST request to.
     * @param  array $params   associative array for the query parameters.
     *
     * @link   http://guzzle.readthedocs.org/en/latest/quickstart.html#using-responses ResponseInterface details.
     *
     * @return GuzzleHttp\Message\ResponseInterface API Response. Contains a lot of information.
     */
    public function get($endpoint, $params)
    {
        //prepend Twitter's API version to the endpoint
        $endpoint = $this->prependVersionToEndpoint($endpoint, Config::get('api_version'));

        //contruct an options array to configure the request
        $options = $this->constructRequestOptions();

        //make the GET request to the endpoint with the constructed options.
        $response = $this->guzzleClient->post($endpoint, $options);

        //return response
        return $response;
    }

}
