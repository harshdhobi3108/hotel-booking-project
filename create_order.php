<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');

$api = new Api($razorpay['key_id'], $razorpay['secret']);

// Get amount from frontend (₹)
$amount_rupees = $_GET['amount'] ;

// Convert to paise
$amount = $amount_rupees * 1;

// Dummy user (replace later)
$user_id = 1;
$room_name = "Room Booking";

// Create receipt
$receipt = "order_" . time();

// Create Razorpay order
$order = $api->order->create([
    'receipt'  => $receipt,
    'amount'   => $amount,
    'currency' => 'INR'
]);

$order_id = $order['id'];

// ✅ INSERT INTO DB (STRICT CHECK)
$stmt = $conn->prepare("
    INSERT INTO orders (user_id, room_name, amount, razorpay_order_id, receipt, status)
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die(json_encode([
        "error" => "Prepare failed: " . $conn->error
    ]));
}

$status = "created";

$stmt->bind_param("isisss", $user_id, $room_name, $amount, $order_id, $receipt, $status);

if (!$stmt->execute()) {
    die(json_encode([
        "error" => "Execute failed: " . $stmt->error
    ]));
}

// ✅ SUCCESS RESPONSE
echo json_encode([
    'order_id' => $order_id,
    'amount'   => $amount
]);