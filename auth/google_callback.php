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

    // ===============================
    // ✅ PREPARE LOCAL IMAGE STORAGE
    // ===============================
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hotel-booking/uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Clean name
    $cleanName = strtolower($name);
    $cleanName = preg_replace('/[^a-z0-9]/', '-', $cleanName);
    $cleanName = preg_replace('/-+/', '-', $cleanName);

    // ===============================
    // ✅ CHECK USER IN DB
    // ===============================
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // ===============================
    // 📥 DOWNLOAD GOOGLE IMAGE
    // ===============================
    $googleImage = !empty($userInfo->picture)
        ? str_replace('=s96-c', '=s400-c', $userInfo->picture)
        : null;

    $profile_pic = null;

    if ($result->num_rows > 0) {

        // ✅ EXISTING USER
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Generate filename
        $fileName = $cleanName . '-' . $user_id . '.jpg';
        $filePath = $uploadDir . $fileName;

        // Download image
        if ($googleImage) {
            $imageData = @file_get_contents($googleImage);
            if ($imageData !== false) {
                file_put_contents($filePath, $imageData);
                $profile_pic = "/hotel-booking/uploads/profile/" . $fileName;
            } else {
                $profile_pic = $user['profile_pic']; // fallback
            }
        }

        // Update DB
        $update = $conn->prepare("UPDATE users SET profile_pic = ?, login_type = 'both' WHERE id = ?");
        $update->bind_param("si", $profile_pic, $user_id);
        $update->execute();

    } else {

        // ✅ NEW USER
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, login_type)
            VALUES (?, ?, 'google')
        ");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();

        $user_id = $stmt->insert_id;

        // Generate filename
        $fileName = $cleanName . '-' . $user_id . '.jpg';
        $filePath = $uploadDir . $fileName;

        // Download image
        if ($googleImage) {
            $imageData = @file_get_contents($googleImage);
            if ($imageData !== false) {
                file_put_contents($filePath, $imageData);
                $profile_pic = "/hotel-booking/uploads/profile/" . $fileName;
            }
        }

        // Update profile_pic
        $update = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $update->bind_param("si", $profile_pic, $user_id);
        $update->execute();
    }

    // ===============================
    // ✅ SESSION SET
    // ===============================
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_pic;

    header("Location: /hotel-booking/");
    exit;
}