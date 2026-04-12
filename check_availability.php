<?php
require_once("includes/config.php");

$data = json_decode(file_get_contents("php://input"), true);

$room_id   = $data['room_id'];
$check_in  = $data['check_in'];
$check_out = $data['check_out'];

// ================= CHECK DOUBLE BOOKING =================
$query = "
    SELECT * FROM orders 
    WHERE room_id = '$room_id'
    AND status != 'cancelled'
    AND NOT (
        check_out <= '$check_in' 
        OR booking_date >= '$check_out'
    )
";

$result = $conn->query($query);

echo json_encode([
    "available" => $result->num_rows === 0
]);