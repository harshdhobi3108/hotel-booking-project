<?php
require_once("includes/auth_check.php");
require_once("includes/db.php");

/* USERS */
$users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

/* ROOMS */
$rooms = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc()['total'];

/* BOOKINGS (FROM ORDERS) */
$bookings = $conn->query("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE status = 'confirmed'
")->fetch_assoc()['total'];

/* REVENUE (FROM PAYMENTS) */
$revenueData = $conn->query("
    SELECT SUM(amount) as total 
    FROM payments 
    WHERE status = 'success'
")->fetch_assoc();

$revenue = $revenueData['total'] ? $revenueData['total'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI';
            background: #f4f6f9;
        }

        .container {
            padding: 20px;
        }

        h1 {
            margin-bottom: 25px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.15);
        }

        .card h3 {
            margin: 0;
            font-size: 16px;
            color: #777;
        }

        .card h2 {
            margin-top: 10px;
            font-size: 26px;
        }

        .users { border-left: 5px solid #3498db; }
        .rooms { border-left: 5px solid #2ecc71; }
        .bookings { border-left: 5px solid #f39c12; }
        .revenue { border-left: 5px solid #e74c3c; }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Welcome, <?= $_SESSION['admin_name'] ?> 👋</h1>

    <div class="cards">

        <div class="card users">
            <h3>Users</h3>
            <h2><?= $users ?></h2>
        </div>

        <div class="card rooms">
            <h3>Rooms</h3>
            <h2><?= $rooms ?></h2>
        </div>

        <div class="card bookings">
            <h3>Bookings</h3>
            <h2><?= $bookings ?></h2>
        </div>

        <div class="card revenue">
            <h3>Revenue</h3>
            <h2>₹<?= number_format($revenue) ?></h2>
        </div>

    </div>

</div>

</body>
</html>