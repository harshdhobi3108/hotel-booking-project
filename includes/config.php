<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database config
$host = "localhost";
$user = "root";
$password = "";
$database = "hotel_booking";

// Connect DB
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}