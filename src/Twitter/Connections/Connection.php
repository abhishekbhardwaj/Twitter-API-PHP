<?php namespace Twitter\Connections;

use Twitter\Config\Config;
use Twitter\Config\Credentials;
use Twitter\Config\AppCredentials;
use Twitter\Config\UserCredentials;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Message\RequestInterface;

class Connection {

    private $credentials;

    private $guzzleClient;

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

    public function get($endpoint, $params)
    {
        $options = array();
        $endpoint = $this->prependVersionToEndpoint($endpoint, Config::get('api_version'));

        if($this->credentials instanceof AppCredentials)
        {
            $headers = array(
                'Authorization' => 'Bearer ' . $this->credentials->getBearerToken()
            );

            $options['query'] => $params;
            $options['headers'] => $headers;
        }
        else
        {
            $oauth = new Oauth1(array(
                'consumer_key'    => $this->credentials->getConsumerKey(),
                'consumer_secret' => $this->credentials->getConsumerSecret(),
                'token'           => $this->credentials->getAccessToken(),
                'token_secret'    => $this->credentials->getAccessTokenSecret()
            ));

            $this->guzzleClient->getEmitter()->attach($oauth);

            $options['query'] => $params;
            $options['auth'] => 'oauth';
        }

        $response = $this->guzzleClient->get($endpoint, $options);

        return $response;
    }

    public function post($endpoint, $params)
    {
        $options = array();
        $endpoint = $this->prependVersionToEndpoint($endpoint, Config::get('api_version'));

        if($this->credentials instanceof AppCredentials)
        {
            $headers = array(
                'Authorization' => 'Bearer ' . $this->credentials->getBearerToken()
            );

            $options['query'] => $params;
            $options['headers'] => $headers;
        }
        else
        {
            $oauth = new Oauth1(array(
                'consumer_key'    => $this->credentials->getConsumerKey(),
                'consumer_secret' => $this->credentials->getConsumerSecret(),
                'token'           => $this->credentials->getAccessToken(),
                'token_secret'    => $this->credentials->getAccessTokenSecret()
            ));

            $this->guzzleClient->getEmitter()->attach($oauth);

            $options['query'] => $params;
            $options['auth'] => 'oauth';
        }

        $response = $this->guzzleClient->post($endpoint, $options);

        return $response;
    }

}
