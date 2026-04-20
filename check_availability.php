<?php
require_once("includes/config.php");

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data) {
        echo json_encode([
            "available" => false,
            "message" => "Invalid JSON request"
        ]);
        exit;
    }

    $room_id   = isset($data['room_id']) ? (int)$data['room_id'] : 0;
    $check_in  = $data['check_in'] ?? '';
    $check_out = $data['check_out'] ?? '';

    if (!$room_id || !$check_in || !$check_out) {
        echo json_encode([
            "available" => false,
            "message" => "Missing fields"
        ]);
        exit;
    }

    $sql = "
        SELECT id FROM orders
        WHERE room_id = ?
        AND booking_status = 'confirmed'
        AND (
            booking_date < ?
            AND check_out > ?
        )
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "available" => false,
            "message" => "Prepare failed"
        ]);
        exit;
    }

    $stmt->bind_param("iss", $room_id, $check_out, $check_in);
    $stmt->execute();

    $result = $stmt->get_result();

    echo json_encode([
        "available" => ($result->num_rows === 0)
    ]);
    exit;

} catch (Throwable $e) {

    echo json_encode([
        "available" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}
?>