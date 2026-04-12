<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

/* ===== FILTER ===== */
$statusFilter = $_GET['status'] ?? '';

$where = "";
if (!empty($statusFilter)) {
    $allowed = ['pending', 'confirmed', 'failed'];
    if (in_array($statusFilter, $allowed)) {
        $where = "WHERE o.status = '$statusFilter'";
    }
}

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
        o.status
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
.filter select {
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

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
    background: #f7f9fc;
}

/* ===== STATUS ===== */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.badge.confirmed {
    background: #d1fae5;
    color: #065f46;
}

.badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.badge.failed {
    background: #fee2e2;
    color: #991b1b;
}

/* ===== BUTTON ===== */
.btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    color: white;
}

.btn-cancel {
    background: #dc3545;
}

</style>

<!-- ===== HEADER ===== -->
<div class="top-bar">
    <h2>Bookings</h2>

    <div class="filter">
        <form method="GET">
            <select name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="confirmed" <?= $statusFilter=='confirmed'?'selected':'' ?>>Confirmed</option>
                <option value="pending" <?= $statusFilter=='pending'?'selected':'' ?>>Pending</option>
                <option value="failed" <?= $statusFilter=='failed'?'selected':'' ?>>Failed</option>
            </select>
        </form>
    </div>
</div>

<!-- ===== TABLE ===== -->
<div class="card">

<table class="table">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Email</th>
    <th>Room</th>
    <th>Check In</th>
    <th>Check Out</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>

    <td><?= $row['id'] ?></td>

    <td><?= htmlspecialchars($row['user_name']) ?></td>

    <td><?= htmlspecialchars($row['email']) ?></td>

    <td><?= htmlspecialchars($row['room_name']) ?></td>

    <td><?= $row['booking_date'] ?></td>

    <td><?= $row['check_out'] ?></td>

    <td>₹<?= number_format($row['amount']) ?></td>

    <!-- STATUS -->
    <td>
        <?php if ($row['status'] == 'confirmed'): ?>
            <span class="badge confirmed">Confirmed</span>
        <?php elseif ($row['status'] == 'pending'): ?>
            <span class="badge pending">Pending Payment</span>
        <?php else: ?>
            <span class="badge failed">Failed</span>
        <?php endif; ?>
    </td>

    <!-- ACTION -->
    <td>
        <?php if ($row['status'] == 'confirmed'): ?>
            <a href="cancel.php?id=<?= $row['id'] ?>"
               class="btn btn-cancel"
               onclick="return confirm('Cancel this booking?')">
               Cancel
            </a>
        <?php else: ?>
            <span style="color:#999;">—</span>
        <?php endif; ?>
    </td>

</tr>
<?php endwhile; ?>

</table>

</div>

<?php include("../../includes/footer.php"); ?>