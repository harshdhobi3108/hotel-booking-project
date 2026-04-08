<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();

$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost/hotel-booking/auth/google_callback.php');

if (isset($_GET['code'])) {

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $google_oauth = new Google_Service_Oauth2($client);
    $userInfo = $google_oauth->userinfo->get();

    $_SESSION['user_name'] = $userInfo->name;
    $_SESSION['user_email'] = $userInfo->email;

    $_SESSION['user_picture'] = !empty($userInfo->picture)
        ? str_replace('=s96-c', '=s200-c', $userInfo->picture)
        : 'https://ui-avatars.com/api/?name=' . urlencode($userInfo->name);

    header("Location: /hotel-booking/");
    exit;
}