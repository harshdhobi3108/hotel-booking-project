<?php
require_once(__DIR__ . "/../includes/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // ✅ Password match check
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit;
    }

    // ✅ CHECK IF EMAIL EXISTS
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    // ===============================
    // 🔥 CASE 1: EMAIL EXISTS
    // ===============================
    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        // 🚨 If Google user → convert to password user
        if ($user['login_type'] === 'google') {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare("
                UPDATE users 
                SET password = ?, login_type = 'both'
                WHERE id = ?
            ");
            $update->bind_param("si", $hashed_password, $user['id']);

            if ($update->execute()) {
                echo "<script>alert('Account updated! You can now login with password.'); window.location='login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Update failed'); window.history.back();</script>";
                exit;
            }

        } else {
            // ❌ Normal user already exists
            echo "<script>alert('Email already registered'); window.history.back();</script>";
            exit;
        }
    }

    // ===============================
    // ✅ CASE 2: NEW USER
    // ===============================
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, login_type)
        VALUES (?, ?, ?, 'normal')
    ");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: login.php?success=1");
        exit;
    } else {
        echo "<script>alert('Something went wrong'); window.history.back();</script>";
    }
}
?>