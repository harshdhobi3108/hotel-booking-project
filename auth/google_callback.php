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

if (!isset($_GET['code'])) {
    die("Authorization code missing.");
}

try {

    /* STEP 1: GET TOKEN */
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!is_array($token) || isset($token['error']) || empty($token['access_token'])) {
        echo "<pre>";
        print_r($token);
        exit;
    }

    /* STEP 2: SET TOKEN */
    $client->setAccessToken($token);

    /* STEP 3: USER INFO */
    $google_oauth = new Google_Service_Oauth2($client);
    $userInfo = $google_oauth->userinfo->get();

    $name  = trim($userInfo->name);
    $email = trim($userInfo->email);

    if (empty($email)) {
        die("Google email not received.");
    }

    /* IMAGE FOLDER */
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hotel-booking/uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $cleanName = strtolower($name);
    $cleanName = preg_replace('/[^a-z0-9]/', '-', $cleanName);
    $cleanName = preg_replace('/-+/', '-', $cleanName);

    /* CHECK USER */
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    $googleImage = !empty($userInfo->picture)
        ? str_replace('=s96-c', '=s400-c', $userInfo->picture)
        : null;

    $profile_pic = null;

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        $fileName = $cleanName . '-' . $user_id . '.jpg';
        $filePath = $uploadDir . $fileName;

        if ($googleImage) {
            $img = @file_get_contents($googleImage);
            if ($img !== false) {
                file_put_contents($filePath, $img);
                $profile_pic = "/hotel-booking/uploads/profile/" . $fileName;
            } else {
                $profile_pic = $user['profile_pic'];
            }
        }

        $update = $conn->prepare("UPDATE users SET profile_pic=?, login_type='both' WHERE id=?");
        $update->bind_param("si", $profile_pic, $user_id);
        $update->execute();

    } else {

        $insert = $conn->prepare("
            INSERT INTO users (name,email,login_type)
            VALUES (?, ?, 'google')
        ");
        $insert->bind_param("ss", $name, $email);
        $insert->execute();

        $user_id = $insert->insert_id;

        $fileName = $cleanName . '-' . $user_id . '.jpg';
        $filePath = $uploadDir . $fileName;

        if ($googleImage) {
            $img = @file_get_contents($googleImage);
            if ($img !== false) {
                file_put_contents($filePath, $img);
                $profile_pic = "/hotel-booking/uploads/profile/" . $fileName;
            }
        }

        $update = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
        $update->bind_param("si", $profile_pic, $user_id);
        $update->execute();
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_pic;

    header("Location: /hotel-booking/");
    exit;

} catch (Exception $e) {
    die("Google Login Failed: " . $e->getMessage());
}