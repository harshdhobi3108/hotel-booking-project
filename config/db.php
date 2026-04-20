<?php

// ================= DATABASE CONFIG =================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "hotel_booking_new";

// ================= CREATE CONNECTION =================
$conn = new mysqli($host, $user, $pass, $db);

// ================= ERROR HANDLING =================
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// ================= OPTIONAL (IMPORTANT) =================
// Set charset to avoid encoding issues
$conn->set_charset("utf8mb4");

// ================= OPTIONAL DEBUG MODE =================
// Uncomment below during development
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>