<?php
require_once("includes/config.php");

$data = json_decode(file_get_contents("php://input"), true);

$room_id   = (int)$data['room_id'];
$check_in  = $data['check_in'];
$check_out = $data['check_out'];

// ================= VALIDATION =================
if (!$room_id || !$check_in || !$check_out) {
    echo json_encode(["available" => false]);
    exit;
}

// ================= CHECK ONLY CONFIRMED BOOKINGS =================
$stmt = $conn->prepare("
    SELECT id FROM orders 
    WHERE room_id = ?
    AND status = 'confirmed'
    AND payment_id IS NOT NULL
    AND (
        booking_date < ? 
        AND check_out > ?
    )
    LIMIT 1
");

$stmt->bind_param("iss", $room_id, $check_out, $check_in);
$stmt->execute();
$result = $stmt->get_result();

// ================= RESPONSE =================
echo json_encode([
    "available" => $result->num_rows === 0
]);