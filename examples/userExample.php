<?php

#################################################################
# To TEST, all the sections below: i.e., #1, #2 and #3 are login workflow
# and need to run one after the other.
#
# Steps:
#
# - Start off by filling the consumerKey and consumerSecret variables
#   you get from Twitter.
#
# - Run Section #1, go to the Redirect URL produced and login.
#
# - The URL you get redirected to contains 'oauth_token' and 'oauth_verifier'
#   as query parameters. Fill the 'oauthToken' and 'oauthVerifier'
#   variables with these values.
#
# - Run Section #2, the returned accessTokens contains oauth_token and
#   oauth_token_secret. These are the access tokens.
#   Fill these values in the 'accessToken' and 'accessTokenSecret'
#   variables.
#
# - Run Section #3, and it'll post a new tweet at the account you
#   just authenticated.
#################################################################
require __DIR__ . '/../vendor/autoload.php';

use Twitter\Client;
use Twitter\Config\UserCredentials;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

//Twitter API keys
$consumerKey = '{INSERT_CONSUMER_KEY_HERE}';
$consumerSecret = '{INSERT_CONSUMER_SECRET_HERE}';
$callbackUrl = '{INSERT_CALLBACK_URL_HERE}';

//Create a new app credentials object and inject the API keys
$credentials = new UserCredentials($consumerKey, $consumerSecret, $callbackUrl);

//create a Twitter Client.
$client = new Client($credentials);

//Setup a connection with Twitter as a user application.
$app = $client->connect();

##################################################################
# 1. Get Request Token from Twitter and construct a auth URL.
##################################################################

$redirectUrl = $app->getRedirectUrlForAuth();
echo "Auth URL: " . $redirectUrl;

##################################################################
# 2. After going to the URL and logging in, get the oauth_token
#    and oauth_verifier and fill the details below.
#
#    Then, get access tokens from Twitter.
##################################################################

//oauthToken and oauthVerifier as specified in the callback URL params after you login to the redirect URL above.
$oauthToken = '{OAUTH_TOKEN}';
$oauthVerifier = '{OAUTH_VERIFIER}';

//get access tokens
$accessTokens = $app->getAccessToken($oauthToken, $oauthVerifier);
var_dump($accessTokens); //returns access_token and access_token_secret

##################################################################
# 3. Set Access Tokens and post a new status.
##################################################################

$accessToken = '{ACCESS_TOKEN}';
$accessTokenSecret = '{ACCESS_TOKEN_SECRET}';

//set access tokens
$app->getCredentials()->setAccessToken($accessToken)->setAccessTokenSecret($accessTokenSecret);

try
{
    //list of pictures to upload. Input array items shouldn't be more than 4.
    //this uploads the pictures to Twitter and gets their media ID's which will then be attached to a tweet.
    $media = $app->uploadMedia(array(
        '{{FULL PATH TO PICTURE}}',
        '{{FULL PATH TO PICTURE}}',
        '{{FULL PATH TO PICTURE}}',
        '{{FULL PATH TO PICTURE}}'
    ));
}
catch(ClientException $ex)
{
    //HTTP 400* was returned. Inspect!
    echo "You made an error!";
}
catch(ServerException $ex)
{
    //HTTP 500* was returned. Inspect!
    echo "Oops! Twitter's servers are under load. Try again, later!";
}

//post a new status
$response = $app->post('statuses/update.json', array(
    'status' => 'Test status!',
    'media_ids' => $media
));

var_dump($response->json());
