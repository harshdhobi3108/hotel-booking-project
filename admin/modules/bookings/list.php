<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

/* ===== FILTER ===== */
$statusFilter = $_GET['status'] ?? '';

$where = "";
if ($statusFilter) {
    $where = "WHERE o.status = '$statusFilter'";
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
    background: #d4edda;
    color: #155724;
}

.badge.cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* ===== BUTTONS ===== */
.btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    color: white;
    margin-right: 5px;
}

.btn-confirm { background: #28a745; }
.btn-cancel { background: #dc3545; }

/* ===== RESPONSIVE ===== */
@media(max-width: 768px) {
    .table th, .table td {
        font-size: 12px;
        padding: 10px;
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
                <option value="confirmed" <?= $statusFilter=='confirmed'?'selected':'' ?>>Confirmed</option>
                <option value="cancelled" <?= $statusFilter=='cancelled'?'selected':'' ?>>Cancelled</option>
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

            <td>
                <span class="badge <?= $row['status'] ?>">
                    <?= ucfirst($row['status']) ?>
                </span>
            </td>

            <td>
                <?php if ($row['status'] !== 'confirmed'): ?>
                    <a href="update_status.php?id=<?= $row['id'] ?>&status=confirmed"
                       class="btn btn-confirm">
                       Confirm
                    </a>
                <?php endif; ?>

                <?php if ($row['status'] !== 'cancelled'): ?>
                    <a href="update_status.php?id=<?= $row['id'] ?>&status=cancelled"
                       class="btn btn-cancel">
                       Cancel
                    </a>
                <?php endif; ?>
            </td>

        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include("../../includes/footer.php"); ?>