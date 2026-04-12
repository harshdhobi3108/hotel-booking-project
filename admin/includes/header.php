<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel - HotelLux</title>

<style>
    
/* ================= RESET ================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f9;
    display: flex;
}

/* ================= SIDEBAR ================= */
.sidebar {
    width: 240px;
    height: 100vh;
    background: linear-gradient(180deg, #10002b, #3c096c);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px;
}

.sidebar h2 {
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #ccc;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.3s;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.sidebar a.active {
    background: linear-gradient(135deg, #7b2cbf, #9d4edd);
    color: white;
}

/* ================= MAIN (FIXED) ================= */
.main {
    margin-left: 240px;
    padding: 30px;
    width: calc(100% - 240px);
    min-height: 100vh;

    display: flex;
    flex-direction: column;
}

/* 🚀 IMPORTANT: FULL WIDTH CONTENT */
.main > * {
    width: 100%;
}

/* ================= CARD ================= */
.card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* ================= TABLE ================= */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th {
    text-align: left;
    padding: 12px;
    background: #f9fafb;
}

.table td {
    padding: 12px;
    border-top: 1px solid #eee;
}

.table tr:hover {
    background: #f7f9fc;
}

/* ================= BADGES ================= */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.badge.success {
    background: #d4edda;
    color: #155724;
}

.badge.danger {
    background: #f8d7da;
    color: #721c24;
}

/* ================= BUTTONS ================= */
.btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    color: white;
}

.btn-edit { background: #3498db; }
.btn-delete { background: #e74c3c; }
.btn-add { background: #2ecc71; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>HotelLux</h2>

    <a href="/hotel-booking/admin/dashboard.php">🏠 Dashboard</a>
    <a href="/hotel-booking/admin/modules/rooms/list.php">🏨 Rooms</a>
    <a href="/hotel-booking/admin/modules/bookings/list.php">📅 Bookings</a>
    <a href="/hotel-booking/admin/modules/users/list.php">👤 Users</a>
</div>

<!-- MAIN CONTENT START -->
<div class="main">