<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');

// ===============================
// 🔐 AUTH CHECK
// ===============================
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "error" => "Unauthorized"
    ]);
    exit;
}

// ===============================
// ✅ GET JSON INPUT
// ===============================
$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['room_id']) ||
    !isset($data['date']) ||
    !isset($data['time'])
) {
    echo json_encode([
        "error" => "Missing booking details"
    ]);
    exit;
}

$room_id = (int) $data['room_id'];
$date    = $data['date'];
$time    = $data['time'];

// ===============================
// ✅ GET USER FROM SESSION
// ===============================
$user_id = $_SESSION['user_id'];
$name    = $_SESSION['user_name'];
$email   = $_SESSION['user_email'];

// ===============================
// ✅ FETCH ROOM PRICE FROM DB
// ===============================
$stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room) {
    echo json_encode([
        "error" => "Room not found"
    ]);
    exit;
}

$amount = (int) $room['price'] * 100; // convert to paise

// ===============================
// ❗ PREVENT DOUBLE BOOKING
// ===============================
$check = $conn->prepare("
    SELECT id FROM orders 
    WHERE room_id = ? 
    AND booking_date = ? 
    AND booking_time = ? 
    AND status = 'confirmed'
");

$check->bind_param("iss", $room_id, $date, $time);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "error" => "Room already booked for this slot"
    ]);
    exit;
}

// ===============================
// ✅ CREATE RAZORPAY ORDER
// ===============================
$api = new Api($razorpay['key_id'], $razorpay['secret']);

$receipt = "order_" . time();

try {
    $order = $api->order->create([
        'receipt'  => $receipt,
        'amount'   => $amount,
        'currency' => 'INR'
    ]);
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
    exit;
}

$order_id = $order['id'];

// ===============================
// ✅ STORE BOOKING IN SESSION
// ===============================
$_SESSION['booking'] = [
    'user_id' => $user_id,
    'room_id' => $room_id,
    'date'    => $date,
    'time'    => $time,
    'amount'  => $amount,
    'razorpay_order_id' => $order_id,
    'receipt' => $receipt
];

// ===============================
// ✅ INSERT ORDER (CREATED)
// ===============================
$stmt = $conn->prepare("
    INSERT INTO orders 
    (user_id, user_name, email, room_id, booking_date, booking_time, amount, razorpay_order_id, receipt, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'created')
");

$stmt->bind_param(
    "issississ",
    $user_id,
    $name,
    $email,
    $room_id,
    $date,
    $time,
    $amount,
    $order_id,
    $receipt
);

if (!$stmt->execute()) {
    echo json_encode([
        "error" => $stmt->error
    ]);
    exit;
}

// ===============================
// ✅ SUCCESS RESPONSE
// ===============================
echo json_encode([
    "order_id" => $order_id,
    "amount"   => $amount,
    "name"     => $name,
    "email"    => $email
]);