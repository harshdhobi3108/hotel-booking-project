<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$client = new Google_Client();

$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost/hotel-booking/auth/google_callback.php');

if (isset($_GET['code'])) {

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $google_oauth = new Google_Service_Oauth2($client);
    $userInfo = $google_oauth->userinfo->get();

    $name  = $userInfo->name;
    $email = $userInfo->email;

    $picture = !empty($userInfo->picture)
        ? str_replace('=s96-c', '=s200-c', $userInfo->picture)
        : 'https://ui-avatars.com/api/?name=' . urlencode($name);

    // ===============================
    // ✅ CHECK USER IN DB
    // ===============================
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        // ✅ EXISTING USER
        $user = $result->fetch_assoc();

        // 🔥 Update profile picture (keeps Google photo fresh)
        $update = $conn->prepare("UPDATE users SET profile_pic = ?, login_type = 'both' WHERE id = ?");
        $update->bind_param("si", $picture, $user['id']);
        $update->execute();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_picture'] = $picture;

    } else {

        // ✅ NEW USER
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, login_type, profile_pic)
            VALUES (?, ?, 'google', ?)
        ");
        $stmt->bind_param("sss", $name, $email, $picture);
        $stmt->execute();

        $user_id = $stmt->insert_id;

        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_picture'] = $picture;
    }

    header("Location: /hotel-booking/");
    exit;
}