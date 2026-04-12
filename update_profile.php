<?php
require_once("includes/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /hotel-booking/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name    = trim($_POST['name']);

// ================= DEFAULT PROFILE PIC =================
$profile_pic = $_SESSION['user_picture'] ?? null;

// ================= IMAGE UPLOAD =================
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {

    // 🔥 NEW PROFILE FOLDER
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/hotel-booking/uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = $_FILES['profile_pic']['name'];
    $tmpName      = $_FILES['profile_pic']['tmp_name'];
    $fileSize     = $_FILES['profile_pic']['size'];

    // ================= VALIDATION =================
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Only JPG, JPEG, PNG, WEBP allowed.");
    }

    if ($fileSize > 5 * 1024 * 1024) {
        die("File size must be less than 5MB.");
    }

    // ================= CLEAN NAME =================
    $cleanName = strtolower($name);
    $cleanName = preg_replace('/[^a-z0-9]/', '-', $cleanName);
    $cleanName = preg_replace('/-+/', '-', $cleanName);

    if (empty($cleanName)) {
        $cleanName = "user" . $user_id;
    }

    // ================= FINAL FILE NAME =================
    $imageName = $cleanName . '-' . $user_id . '.' . $ext;
    $destination = $uploadDir . $imageName;

    // ================= MOVE FILE =================
    if (!move_uploaded_file($tmpName, $destination)) {
        die("Failed to upload image.");
    }

    // ================= DELETE OLD IMAGE (SAFE) =================
    if (!empty($_SESSION['user_picture']) && strpos($_SESSION['user_picture'], '/uploads/profile/') !== false) {
        $oldPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['user_picture'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // ================= SAVE PATH =================
    $profile_pic = "/hotel-booking/uploads/profile/" . $imageName;
}

// ================= UPDATE =================
$query = "UPDATE users SET name=?, profile_pic=? WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $name, $profile_pic, $user_id);
$stmt->execute();

// ================= UPDATE SESSION =================
$_SESSION['user_name']    = $name;
$_SESSION['user_picture'] = $profile_pic;

header("Location: profile.php");
exit();
?>