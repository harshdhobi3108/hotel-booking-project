<?php

require_once __DIR__ . '/../includes/config.php';

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();

$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost/hotel-booking/auth/google_callback.php');

$client->addScope("email");
$client->addScope("profile");

header('Location: ' . $client->createAuthUrl());
exit;