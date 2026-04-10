<?php

// ================= AUTOLOAD =================
require_once __DIR__ . '/../vendor/autoload.php';

// ================= LOAD ENV =================
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// ================= SESSION =================
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // keep empty for localhost
        'secure' => false, // true only if HTTPS
        'httponly' => true,
        'samesite' => 'Lax' // 🔥 IMPORTANT
    ]);

    session_start();
}

// ================= DATABASE CONFIG =================
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'hotel_booking');

// ================= DATABASE CONNECTION =================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional: Set charset (important for production)
$conn->set_charset("utf8mb4");

// ================= RAZORPAY CONFIG =================
$razorpay = [
    'key_id' => $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_SahxQ39qIdVeKw',
    'secret' => $_ENV['RAZORPAY_KEY_SECRET'] ?? '14m1GYPyen19tGXGff4mcluH'
];

// ================= VALIDATION =================
if (empty($razorpay['key_id']) || empty($razorpay['secret'])) {
    die("Razorpay keys are missing in .env file");
}