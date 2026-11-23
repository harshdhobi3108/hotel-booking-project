<?php

// ================= DATABASE CONFIG =================
$host = "sql203.infinityfree.com";
$user = "if0_43513445";
$pass = "kYAouxrb9dky5j";
$db   = "if0_43513445_hotellux";

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
