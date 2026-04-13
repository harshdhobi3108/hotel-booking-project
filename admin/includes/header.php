<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - HotelLux</title>

<style>

/* ================= RESET ================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* ❗ FIX: REMOVE FLEX FROM BODY */
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f9;
}

/* ================= SIDEBAR ================= */
.sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(180deg, #10002b, #3c096c);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px;
    transition: 0.3s;
    z-index: 1000;
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

/* ================= MAIN ================= */
.main {
    margin-left: 250px;
    width: calc(100% - 250px);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ================= TOPBAR ================= */
.topbar {
    height: 60px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 999;
}

.menu-toggle {
    font-size: 22px;
    cursor: pointer;
    display: none;
}

.admin-info {
    font-size: 14px;
    font-weight: 500;
}

/* ================= CONTENT ================= */
.content {
    padding: 20px;
    width: 100%;
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {

    .sidebar {
        left: -260px;
    }

    .sidebar.active {
        left: 0;
    }

    .main {
        margin-left: 0;
        width: 100%;
    }

    .menu-toggle {
        display: block;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<?php include(__DIR__ . "/sidebar.php"); ?>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <span class="menu-toggle" onclick="toggleSidebar()">☰</span>

        <div class="admin-info">
            👤 <?= htmlspecialchars($_SESSION['admin_name']) ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>