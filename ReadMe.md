### Simple PHP Wrapper for Twitter API v1.1

Twitter-API-PHP is a simple PHP wrapper library for Twitter v1.1 calls. It has been designed for making the Twitter API as simple to use as possible for developers (on the server or on the client).

It supports:

- [Application-Only Authentication]{https://dev.twitter.com/oauth/application-only} (Oauth 2.0).
- [Single-User OAuth]{https://dev.twitter.com/oauth/overview/single-user} (Oauth 1.0a).
- [3-legged Authorization]{https://dev.twitter.com/oauth/3-legged} (Oauth 1.0a).


#### Installation

{To be filled up later!}

#### Dependencies

```
"php": ">=5.4.0",
"guzzlehttp/guzzle": "~5.0",
"guzzlehttp/oauth-subscriber": "0.1.*@dev"
```

#### Basic Usage

Some examples are located in the `examples/` directory:

- appExample.php: Example of Application-Only Authentication

```php
use Twitter\Client;
use Twitter\Config\AppCredentials;

//Twitter API keys
$consumerKey = '{INSERT_CONSUMER_KEY_HERE}';
$consumerSecret = '{INSERT_CONSUMER_SECRET_HERE}';

//Create a new app credentials object and inject the API keys
$credentials = new AppCredentials($consumerKey, $consumerSecret);

//create a Twitter Client.
$client = new Client($credentials);

//Setup a connection with Twitter as an application.
$app = $client->connect();

//Get a bearer token.
$app->createBearerToken();

// echo "Bearer Token: " . $app->getCredentials()->getBearerToken();

//Get a user's timeline. Response is an instance of GuzzleHttp\Message\ResponseInterface
$response = $app->get('users/show.json', array('screen_name' => 'abhishekwebin'));

//print the JSON reply.
echo $response->getBody();
```

- userExample.php: Example of 3-legged Authorization

#### API Reference


#### Important Links

1. All Twitter API Endpoints can be found [here]{https://dev.twitter.com/rest/public}.
2. All Twitter Authentication related stuff can be found [here]{https://dev.twitter.com/oauth}.
