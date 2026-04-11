<?php
require_once("includes/config.php");

header('Content-Type: application/json');

// ✅ SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 AUTH
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// ✅ GET DATA
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['order_id']) || empty($data['rating'])) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$order_id = (int)$data['order_id'];
$rating   = (int)$data['rating'];
$user_id  = $_SESSION['user_id'];

// ✅ VALIDATION
if ($rating < 1 || $rating > 5) {
    echo json_encode(["status" => "error", "message" => "Invalid rating"]);
    exit;
}

try {

    // 🔍 CHECK ORDER BELONGS TO USER
    $check = $conn->prepare("
        SELECT id FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $check->bind_param("ii", $order_id, $user_id);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        throw new Exception("Invalid order");
    }

    // ✅ UPDATE RATING
    $stmt = $conn->prepare("
        UPDATE orders SET rating = ? WHERE id = ?
    ");
    $stmt->bind_param("ii", $rating, $order_id);

    if (!$stmt->execute()) {
        throw new Exception("DB update failed");
    }

    echo json_encode([
        "status" => "success",
        "rating" => $rating
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}