<?php
require_once("includes/config.php");

session_start();

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];

// ================= IMAGE UPLOAD =================
$profile_pic = $_SESSION['user_picture'];

if (!empty($_FILES['profile_pic']['name'])) {

    $targetDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFile = $targetDir . $fileName;

    move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile);

    $profile_pic = "/hotel-booking/" . $targetFile;
}

// ================= UPDATE =================
$query = "UPDATE users SET name=?, profile_pic=? WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $name, $profile_pic, $user_id);
$stmt->execute();

// UPDATE SESSION
$_SESSION['user_name'] = $name;
$_SESSION['user_picture'] = $profile_pic;

header("Location: profile.php");