<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

/* ===== FILTER ===== */
$statusFilter = $_GET['status'] ?? '';

$where = "";
if (!empty($statusFilter)) {
    $allowed = ['confirmed', 'cancelled'];
    if (in_array($statusFilter, $allowed)) {
        $where = "WHERE o.booking_status = '$statusFilter'";
    }
}

/* ===== STATS ===== */
$total = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$active = $conn->query("SELECT COUNT(*) as c FROM orders WHERE booking_status='confirmed'")->fetch_assoc()['c'];
$cancelled = $conn->query("SELECT COUNT(*) as c FROM orders WHERE booking_status='cancelled'")->fetch_assoc()['c'];
$revenue = $conn->query("SELECT SUM(amount) as s FROM orders WHERE booking_status='confirmed'")->fetch_assoc()['s'] ?? 0;

/* ===== QUERY ===== */
$query = "
    SELECT 
        o.id,
        o.user_name,
        o.email,
        r.name AS room_name,
        o.booking_date,
        o.check_out,
        o.amount,
        o.payment_method,
        o.booking_status,
        o.cancelled_at
    FROM orders o
    JOIN rooms r ON o.room_id = r.id
    $where
    ORDER BY o.id DESC
";

$result = $conn->query($query);

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<?php include("../../includes/header.php"); ?>

<style>

/* ===== TOP BAR ===== */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* ===== FILTER ===== */
.filter {
    background: #fff;
    padding: 6px 10px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.filter select {
    border: none;
    outline: none;
}

/* ===== STATS ===== */
.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-box {
    background: linear-gradient(135deg, #ffffff, #f1f5f9);
    padding: 18px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    font-size: 14px;
    transition: all 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-3px);
}

.stat-box strong {
    font-size: 22px;
    display: block;
    margin-top: 6px;
    color: #111827;
}

/* COLORS */
.stat-box:nth-child(1) { border-left: 5px solid #6366f1; }
.stat-box:nth-child(2) { border-left: 5px solid #22c55e; }
.stat-box:nth-child(3) { border-left: 5px solid #ef4444; }
.stat-box:nth-child(4) { border-left: 5px solid #f59e0b; }

/* ===== CARD ===== */
.card {
    background: white;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* ===== TABLE ===== */
.table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
}

.table th {
    text-align: left;
    padding: 14px;
    background: #f9fafb;
    font-size: 14px;
}

.table td {
    padding: 14px;
    border-top: 1px solid #eee;
}

.table tr:hover {
    background: #f1f5f9;
    transition: 0.2s;
}

/* ===== HIERARCHY ===== */
.table td:nth-child(4) {
    font-weight: 600;
}

.table td:nth-child(7) {
    font-weight: 700;
    color: #111827;
}

/* ===== CANCELLED ROW ===== */
.row-cancelled {
    opacity: 0.6;
    background: #fff5f5;
}

/* ===== BADGES ===== */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

/* PAYMENT */
.badge.payment-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.badge.payment-pending {
    background: #fef3c7;
    color: #92400e;
}

.badge.payment-failed {
    background: #fee2e2;
    color: #991b1b;
}

/* BOOKING */
.badge.booking-confirmed {
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 600;
}

.badge.booking-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

/* ===== BUTTON ===== */
.btn-cancel {
    background: linear-gradient(135deg, #ff4d4f, #d9363e);
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px rgba(255, 77, 79, 0.3);
}

/* ALIGN */
.table td:last-child {
    text-align: center;
}

/* ================== ✅ RESPONSIVE ONLY ADDED ================== */

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

@media (max-width: 768px) {

    .top-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .stats {
        grid-template-columns: 1fr 1fr !important;
    }

    .table {
        min-width: 900px;
    }

    .btn-cancel {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .stats {
        grid-template-columns: 1fr !important;
    }
}

</style>

<!-- ===== HEADER ===== -->
<div class="top-bar">
    <h2>Bookings</h2>

    <div class="filter">
        <form method="GET">
            <select name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="confirmed" <?= $statusFilter=='confirmed'?'selected':'' ?>>Active</option>
                <option value="cancelled" <?= $statusFilter=='cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
        </form>
    </div>
</div>

<!-- ===== STATS -->
<div class="stats">
    <div class="stat-box">Total<br><strong><?= $total ?></strong></div>
    <div class="stat-box">Active<br><strong><?= $active ?></strong></div>
    <div class="stat-box">Cancelled<br><strong><?= $cancelled ?></strong></div>
    <div class="stat-box">Revenue<br><strong>₹<?= number_format($revenue) ?></strong></div>
</div>

<!-- ===== TABLE ===== -->
<div class="card">

    <!-- ✅ WRAPPER ONLY ADDED -->
    <div class="table-wrapper">

<table class="table">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Email</th>
    <th>Room</th>
    <th>Check In</th>
    <th>Check Out</th>
    <th>Amount</th>
    <th>Payment</th>
    <th>Booking</th>
    <th>Cancelled At</th>
    <th>Action</th>
</tr>

<?php if ($result->num_rows === 0): ?>
<tr>
    <td colspan="11" style="text-align:center; padding: 30px; color:#888;">
        No bookings found
    </td>
</tr>
<?php endif; ?>

<?php while($row = $result->fetch_assoc()): ?>
<tr class="<?= $row['booking_status'] == 'cancelled' ? 'row-cancelled' : '' ?>">

    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['user_name']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= htmlspecialchars($row['room_name']) ?></td>
    <td><?= $row['booking_date'] ?></td>
    <td><?= $row['check_out'] ?></td>
    <td>₹<?= number_format($row['amount']) ?></td>

    <td>
        <?php if ($row['payment_method'] == 'confirmed'): ?>
            <span class="badge payment-confirmed">Paid</span>
        <?php elseif ($row['payment_method'] == 'pending'): ?>
            <span class="badge payment-pending">Pending</span>
        <?php else: ?>
            <span class="badge payment-failed">Failed</span>
        <?php endif; ?>
    </td>

    <td>
        <?php if ($row['booking_status'] == 'confirmed'): ?>
            <span class="badge booking-confirmed">Active</span>
        <?php else: ?>
            <span class="badge booking-cancelled">Cancelled</span>
        <?php endif; ?>
    </td>

    <td>
        <?= $row['cancelled_at'] 
            ? date('d M Y, h:i A', strtotime($row['cancelled_at'])) 
            : '-' ?>
    </td>

    <td>
        <?php if ($row['booking_status'] == 'confirmed'): ?>
            <form method="POST" action="update_status.php" onsubmit="return confirm('Cancel this booking?');">
                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn-cancel">Cancel</button>
            </form>
        <?php else: ?>
            <span style="color:#999;">—</span>
        <?php endif; ?>
    </td>

</tr>
<?php endwhile; ?>

</table>

    </div>

</div>

<?php include("../../includes/footer.php"); ?>