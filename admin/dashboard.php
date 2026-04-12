<?php
require_once("includes/auth_check.php");
require_once("includes/db.php");

/* USERS */
$usersData = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();
$users = intval($usersData['total'] ?? 0);

/* ROOMS */
$roomsData = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc();
$rooms = intval($roomsData['total'] ?? 0);

/* BOOKINGS */
$bookingsData = $conn->query("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE status = 'confirmed'
")->fetch_assoc();
$bookings = intval($bookingsData['total'] ?? 0);

/* REVENUE */
$revenueData = $conn->query("
    SELECT SUM(amount) as total 
    FROM payments 
    WHERE status = 'success'
")->fetch_assoc();
$revenue = intval($revenueData['total'] ?? 0);

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

/* ===== MAIN DASHBOARD LAYOUT ===== */
.dashboard-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-top: 20px;
}

/* LEFT SIDE */
.dashboard-left h1 {
    margin-bottom: 20px;
}

/* RIGHT SIDE */
.dashboard-right {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* ===== STATS GRID ===== */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

/* ===== STAT CARDS ===== */
.stat-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    position: relative;
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card h3 {
    font-size: 14px;
    color: #777;
}

.stat-card h2 {
    font-size: 28px;
    margin-top: 8px;
    font-weight: bold;
    color: #111;
}

/* LEFT BORDER COLORS */
.stat-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 5px;
    height: 100%;
    border-radius: 16px 0 0 16px;
}

.users::before { background: #3a86ff; }
.rooms::before { background: #2ecc71; }
.bookings::before { background: #f39c12; }
.revenue::before { background: #e74c3c; }

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
    color: #333;
}

.table td {
    padding: 12px;
    border-top: 1px solid #eee;
    color: #444;
}

.table tr:hover {
    background: #f7f9fc;
}

/* ===== STATUS BADGE ===== */
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

/* ===== RESPONSIVE ===== */
@media(max-width: 992px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

</style>

<div class="dashboard-layout">

    <!-- LEFT SIDE -->
    <div class="dashboard-left">
        
        <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?> 👋</h1>

        <div class="dashboard-grid">

            <div class="stat-card users">
                <h3>Total Users</h3>
                <h2><?= $users ?></h2>
            </div>

            <div class="stat-card rooms">
                <h3>Total Rooms</h3>
                <h2><?= $rooms ?></h2>
            </div>

            <div class="stat-card bookings">
                <h3>Total Bookings</h3>
                <h2><?= $bookings ?></h2>
            </div>

            <div class="stat-card revenue">
                <h3>Total Revenue</h3>
                <h2>₹<?= number_format($revenue) ?></h2>
            </div>

        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="dashboard-right">

        <h3>Recent Bookings</h3>

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
                        <span class="badge <?= $row['status'] == 'confirmed' ? 'success' : 'danger' ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No bookings found</td>
                </tr>
            <?php endif; ?>

        </table>

    </div>

</div>

<?php include("includes/footer.php"); ?>