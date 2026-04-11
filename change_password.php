<?php
require_once("includes/config.php");

session_start();

$user_id = $_SESSION['user_id'];
$old = $_POST['old_password'];
$new = $_POST['new_password'];

// FETCH CURRENT PASSWORD
$query = "SELECT password FROM users WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!password_verify($old, $result['password'])) {
    die("Wrong old password");
}

// HASH NEW PASSWORD
$newHash = password_hash($new, PASSWORD_DEFAULT);

// UPDATE
$update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$update->bind_param("si", $newHash, $user_id);
$update->execute();

header("Location: profile.php");