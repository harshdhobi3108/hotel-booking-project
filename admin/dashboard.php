<?php
require_once("includes/auth_check.php");
require_once("includes/db.php");

/* USERS */
$users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;

/* ROOMS */
$rooms = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc()['total'] ?? 0;

/* BOOKINGS */
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'] ?? 0;

$activeBookings = $conn->query("
    SELECT COUNT(*) as total FROM orders 
    WHERE booking_status='confirmed'
")->fetch_assoc()['total'] ?? 0;

$cancelledBookings = $conn->query("
    SELECT COUNT(*) as total FROM orders 
    WHERE booking_status='cancelled'
")->fetch_assoc()['total'] ?? 0;

/* REVENUE */
$revenue = $conn->query("
    SELECT SUM(amount) as total 
    FROM orders 
    WHERE booking_status='confirmed'
")->fetch_assoc()['total'] ?? 0;

/* RECENT BOOKINGS */
$recent = $conn->query("
    SELECT o.*, r.name AS room_name
    FROM orders o
    JOIN rooms r ON o.room_id = r.id
    ORDER BY o.id DESC LIMIT 5
");
?>

<?php include("includes/header.php"); ?>

<style>

/* ===== MAIN ===== */
.dashboard {
    margin-top: 20px;
}

/* ===== STATS ===== */
.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff, #eef2ff);
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-6px);
}

.stat-card h3 {
    font-size: 14px;
    color: #666;
}

.stat-card h2 {
    font-size: 26px;
    margin-top: 8px;
    font-weight: bold;
}

/* COLORS */
.users { border-left: 5px solid #3a86ff; }
.rooms { border-left: 5px solid #22c55e; }
.total { border-left: 5px solid #6366f1; }
.active { border-left: 5px solid #10b981; }
.cancelled { border-left: 5px solid #ef4444; }
.revenue { 
    border-left: 5px solid #f59e0b;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
}

/* ===== CARD ===== */
.card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* ===== HEADER ROW ===== */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* ===== VIEW BUTTON ===== */
.view-btn {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: white;
    padding: 6px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    transition: 0.2s;
}

.view-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px rgba(99,102,241,0.3);
}

/* ===== TABLE ===== */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.table th {
    text-align: left;
    padding: 12px;
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.table td {
    padding: 12px;
    border-top: 1px solid #eee;
}

.table tr {
    transition: 0.2s;
}

.table tr:hover {
    background: #eef2ff;
    cursor: pointer;
}

/* ===== BADGES ===== */
.badge {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge.active {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1d4ed8;
}

.badge.cancelled {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {

    .stats {
        grid-template-columns: 1fr !important;
    }

    .dashboard {
        width: 100% !important;
    }

    .card {
        width: 100% !important;
    }

    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

}

</style>

<div class="dashboard">

    <!-- ===== STATS ===== -->
    <div class="stats">

        <div class="stat-card users">
            <h3>Total Users</h3>
            <h2><?= $users ?></h2>
        </div>

        <div class="stat-card rooms">
            <h3>Total Rooms</h3>
            <h2><?= $rooms ?></h2>
        </div>

        <div class="stat-card total">
            <h3>Total Bookings</h3>
            <h2><?= $totalBookings ?></h2>
        </div>

        <div class="stat-card active">
            <h3>Active Bookings</h3>
            <h2><?= $activeBookings ?></h2>
        </div>

        <div class="stat-card cancelled">
            <h3>Cancelled Bookings</h3>
            <h2><?= $cancelledBookings ?></h2>
        </div>

        <div class="stat-card revenue">
            <h3>Total Revenue</h3>
            <h2>₹<?= number_format($revenue) ?></h2>
        </div>

    </div>

    <!-- ===== RECENT BOOKINGS ===== -->
    <div class="card">

        <div class="card-header">
            <h3>Recent Bookings</h3>
            <a href="modules/bookings/list.php" class="view-btn">View All</a>
        </div>

        <table class="table">
            <tr>
                <th>User</th>
                <th>Room</th>
                <th>Check In</th>
                <th>Status</th>
            </tr>

            <?php if($recent->num_rows > 0): ?>
                <?php while($row = $recent->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['room_name']) ?></td>
                    <td><?= date("Y-m-d", strtotime($row['booking_date'])) ?></td>
                    <td>
                        <?php if($row['booking_status'] == 'confirmed'): ?>
                            <span class="badge active">Active</span>
                        <?php else: ?>
                            <span class="badge cancelled">Cancelled</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:20px;">No bookings found</td>
                </tr>
            <?php endif; ?>

        </table>

    </div>

</div>

<?php include("includes/footer.php"); ?>