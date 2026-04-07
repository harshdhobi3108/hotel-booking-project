<?php

$host = "localhost";
$user = "root";        // default XAMPP username
$pass = "";            // default password is empty
$db   = "hotel_booking";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}