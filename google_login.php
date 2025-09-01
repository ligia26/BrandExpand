<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setClientId('883831739384-4bd22usqjbaug151hkblo3frv81sluhe.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('https://automation.datainnovation.io/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
?>
