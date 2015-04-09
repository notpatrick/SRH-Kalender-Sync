<?php
error_reporting(E_ALL);
require_once 'vendor/autoload.php';
session_start();

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/calendar/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} else {
  $redirect_uri = 'https://dnb4.me/calendar/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

