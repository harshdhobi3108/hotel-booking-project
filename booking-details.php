<?php
require_once("includes/config.php");

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$order_id = intval($_GET['id']);

$query = "
SELECT o.*, r.name as room_name, r.price
FROM orders o
JOIN rooms r ON o.room_id = r.id
WHERE o.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$booking = $result->fetch_assoc();
?>

<h2>Booking Details</h2>

<p><strong>Room:</strong> <?php echo $booking['room_name']; ?></p>
<p><strong>Price:</strong> ₹<?php echo $booking['price']; ?></p>
<p><strong>Status:</strong> <?php echo $booking['status']; ?></p>
<p><strong>Date:</strong> <?php echo date("d M Y", strtotime($booking['created_at'])); ?></p>