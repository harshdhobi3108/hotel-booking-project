<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');

$api = new Api($razorpay['key_id'], $razorpay['secret']);

// ✅ GET DATA FROM FRONTEND
if (!isset($_GET['amount']) || !isset($_GET['room_id'])) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

$amount = (int) $_GET['amount'];   // already in paise
$room_id = (int) $_GET['room_id'];

// Dummy user (replace later with session)
$user_id = 1;

// Create receipt
$receipt = "order_" . time();

// ===============================
// 1️⃣ CREATE RAZORPAY ORDER
// ===============================
$order = $api->order->create([
    'receipt'  => $receipt,
    'amount'   => $amount,
    'currency' => 'INR'
]);

$order_id = $order['id'];

// ===============================
// 2️⃣ STORE IN DATABASE
// ===============================
$stmt = $conn->prepare("
    INSERT INTO orders 
    (user_id, room_id, amount, razorpay_order_id, receipt, status)
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die(json_encode([
        "error" => "Prepare failed: " . $conn->error
    ]));
}

$status = "created";

$stmt->bind_param("iiisss", $user_id, $room_id, $amount, $order_id, $receipt, $status);

if (!$stmt->execute()) {
    die(json_encode([
        "error" => "Execute failed: " . $stmt->error
    ]));
}

// ===============================
// 3️⃣ RESPONSE
// ===============================
echo json_encode([
    'order_id' => $order_id,
    'amount'   => $amount
]);