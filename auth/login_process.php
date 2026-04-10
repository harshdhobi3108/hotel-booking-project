<?php
session_start();
require_once(__DIR__ . "/../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ GET USER
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // 🚨 GOOGLE USER BLOCK (no password login)
        if ($user['login_type'] === 'google') {
            header("Location: login.php?error=google_user");
            exit;
        }

        // ✅ PASSWORD VERIFY
        if (!empty($user['password']) && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];

            // 🔥 PROFILE IMAGE FIX
            $_SESSION['user_picture'] = !empty($user['profile_pic'])
                ? $user['profile_pic']
                : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']);

            header("Location: ../index.php");
            exit;

        } else {
            header("Location: login.php?error=invalid_password");
            exit;
        }

    } else {
        header("Location: login.php?error=user_not_found");
        exit;
    }
}
?>