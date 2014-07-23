<?php
//require_once '../config.php';
require_once 'config.php';
require_once 'Google_Client.php';
require_once 'contrib/Google_Oauth2Service.php';

$client = new Google_Client();

$oauth2 = new Google_Oauth2Service($client);

$authUrl = $client->createAuthUrl();

header("Location:".$authUrl);
?>