<?php
error_reporting(E_ALL);
require_once 'vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setAuthConfigFile('../key.json');
$client->setRedirectUri('https://dnb4.me/calendar/oauth2callback.php');
$client->addScope('https://www.googleapis.com/auth/calendar');

if (! isset($_GET['code'])) {
  $_SESSION['loginprompt']['in'] = 1;
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/calendar/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}