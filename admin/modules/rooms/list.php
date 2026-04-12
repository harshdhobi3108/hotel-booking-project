<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

// Fetch all rooms
$result = $conn->query("SELECT * FROM rooms");
?>

<?php include(__DIR__ . "/../../includes/header.php"); ?>

<style>

/* ===== TOP BAR ===== */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

/* ===== TITLE ===== */
.page-title {
    font-size: 22px;
    font-weight: 600;
}

/* ===== BUTTONS ===== */
.btn {
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    color: white;
    display: inline-block;
    transition: 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-add { background: #2ecc71; }
.btn-edit { background: #3498db; }
.btn-delete { background: #e74c3c; }

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
    font-size: 14px;
}

/* Hover */
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

.badge.available {
    background: #d1fae5;
    color: #065f46;
}

.badge.booked {
    background: #fee2e2;
    color: #991b1b;
}

</style>

<!-- ===== HEADER ===== -->
<div class="top-bar">
    <div class="page-title">Manage Rooms</div>
    <a href="add.php" class="btn btn-add">+ Add Room</a>
</div>

<!-- ===== TABLE ===== -->
<div class="card">

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($row = $result->fetch_assoc()) { 

            // 🔥 CHECK IF ROOM IS CURRENTLY BOOKED (TODAY BASED)
            $room_id = $row['id'];
            $today = date('Y-m-d');

            $checkQuery = "
                SELECT id FROM orders 
                WHERE room_id = '$room_id'
                AND booking_date <= '$today'
                AND check_out >= '$today'
                LIMIT 1
            ";

            $checkResult = $conn->query($checkQuery);

            $isBooked = $checkResult->num_rows > 0;

            $status = $isBooked ? 'booked' : 'available';
        ?>

        <tr>
            <td><?= $row['id'] ?></td>

            <td><?= htmlspecialchars($row['name']) ?></td>

            <td>₹<?= number_format($row['price']) ?></td>

            <td>
                <span class="badge <?= $status ?>">
                    <?= ucfirst($status) ?>
                </span>
            </td>

            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">
                    Edit
                </a>

                <a href="delete.php?id=<?= $row['id'] ?>"
                   class="btn btn-delete"
                   onclick="return confirm('Delete this room?')">
                   Delete
                </a>
            </td>
        </tr>

        <?php } ?>

    </table>

</div>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>