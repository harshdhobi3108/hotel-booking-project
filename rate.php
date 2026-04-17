<?php
require_once("includes/config.php");

header('Content-Type: application/json');

/* ================= SESSION ================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= AUTH ================= */

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status"  => "error",
        "message" => "Please login first."
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status"  => "error",
        "message" => "Invalid request."
    ]);
    exit;
}

$order_id = isset($data['order_id']) ? (int) $data['order_id'] : 0;
$rating   = isset($data['rating']) ? (int) $data['rating'] : 0;

/* ================= VALIDATION ================= */

if ($order_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode([
        "status"  => "error",
        "message" => "Please select a valid rating."
    ]);
    exit;
}

try {

    /* ================= CHECK ORDER OWNERSHIP ================= */

    $check = $conn->prepare("
        SELECT id, rating, booking_status
        FROM orders
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");

    $check->bind_param("ii", $order_id, $user_id);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Booking not found.");
    }

    $order = $result->fetch_assoc();

    /* ================= RULES ================= */

    if ($order['booking_status'] === 'cancelled') {
        throw new Exception("Cancelled booking cannot be rated.");
    }

    if (!empty($order['rating']) && $order['rating'] > 0) {
        throw new Exception("You already rated this stay.");
    }

    /* ================= SAVE RATING ================= */

    $stmt = $conn->prepare("
        UPDATE orders
        SET rating = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ii", $rating, $order_id);

    if (!$stmt->execute()) {
        throw new Exception("Unable to save rating.");
    }

    /* ================= SUCCESS ================= */

    echo json_encode([
        "status"  => "success",
        "rating"  => $rating,
        "message" => "Thanks for rating your stay ⭐"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}
?>