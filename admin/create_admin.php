<?php
require_once("../includes/config.php");

$name = "Admin";
$email = "admin@gmail.com";
$password = password_hash("admin123", PASSWORD_BCRYPT);

$conn->query("DELETE FROM admins WHERE email='$email'");

$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);
$stmt->execute();

echo "Admin Created Successfully";
?>