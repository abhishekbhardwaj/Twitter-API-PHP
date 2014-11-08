<?php

require __DIR__ . '/../vendor/autoload.php';

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
