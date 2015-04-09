<?php
error_reporting(E_ALL);

session_start();

$_SESSION = array();

$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);


session_destroy();

$redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/calendar/';
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
?>