<?php
require_once 'config/db.php';

$name = "Test User";
$email = "test@gmail.com";

$stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $email);

if ($stmt->execute()) {
    echo "✅ User inserted successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}
?>