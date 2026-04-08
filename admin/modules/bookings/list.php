<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

$result = $conn->query("
    SELECT b.id, u.name, r.room_name, b.status 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
");
?>

<h2>Bookings</h2>

<table>
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Room</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['room_name'] ?></td>
    <td><?= $row['status'] ?></td>
    <td>
        <a href="update_status.php?id=<?= $row['id'] ?>&status=confirmed">Confirm</a>
        <a href="update_status.php?id=<?= $row['id'] ?>&status=cancelled">Cancel</a>
    </td>
</tr>
<?php endwhile; ?>
</table>