<?php

require __DIR__ . '/../vendor/autoload.php';

use Twitter\Client;
use Twitter\Config\AppCredentials;

$consumerKey = '{INSERT_CONSUMER_KEY_HERE}';
$consumerSecret = '{INSERT_CONSUMER_SECRET_HERE}';

$credentials = new AppCredentials($consumerKey, $consumerSecret);

$client = new Client($credentials);

//get application
$appConnect = $client->connect();
$appConnect->createBearerToken();

// echo "Bearer Token: " . $appConnect->getCredentials()->getBearerToken();

//Get user's JSON
$response = $appConnect->get('users/show.json', array('screen_name' => 'abhishekwebin'));

//echo JSON
echo $response->getBody();
