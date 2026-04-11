<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');

// ===============================
// ✅ START SESSION
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// 🔐 AUTH CHECK
// ===============================
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// ===============================
// ✅ GET JSON INPUT
// ===============================
$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['room_id']) ||
    empty($data['check_in']) ||
    empty($data['check_out']) ||
    empty($data['time'])
) {
    echo json_encode(["error" => "Missing booking details"]);
    exit;
}

$room_id   = (int) $data['room_id'];
$check_in  = $data['check_in'];
$check_out = $data['check_out'];
$time      = $data['time'];

// ===============================
// 🔍 VALIDATE DATES
// ===============================
$today = date("Y-m-d");

if ($check_in < $today) {
    echo json_encode(["error" => "Check-in cannot be in the past"]);
    exit;
}

if ($check_out <= $check_in) {
    echo json_encode(["error" => "Invalid check-out date"]);
    exit;
}

// ===============================
// ✅ USER FROM SESSION
// ===============================
$user_id = $_SESSION['user_id'];
$name    = $_SESSION['user_name'];
$email   = $_SESSION['user_email'];

// ===============================
// ✅ FETCH ROOM PRICE
// ===============================
$stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room) {
    echo json_encode(["error" => "Room not found"]);
    exit;
}

// ===============================
// 💰 CALCULATE TOTAL AMOUNT
// ===============================
$price_per_night = (int) $room['price'];

$nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);

$total_amount = $price_per_night * $nights;
$amount_paise = $total_amount * 100;

// ===============================
// ❗ OVERLAPPING BOOKING CHECK
// ===============================
$check = $conn->prepare("
    SELECT id FROM orders 
    WHERE room_id = ? 
    AND status = 'confirmed'
    AND (
        booking_date < ? 
        AND check_out > ?
    )
");

$check->bind_param("iss", $room_id, $check_out, $check_in);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(["error" => "Room already booked for selected dates"]);
    exit;
}

// ===============================
// ✅ INIT RAZORPAY
// ===============================
$api = new Api($razorpay['key_id'], $razorpay['secret']);

$receipt = "order_" . time();

try {
    $order = $api->order->create([
        'receipt'  => $receipt,
        'amount'   => $amount_paise,
        'currency' => 'INR'
    ]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

$order_id = $order['id'];

// ===============================
// ✅ STORE SESSION
// ===============================
$_SESSION['booking'] = [
    'user_id' => $user_id,
    'room_id' => $room_id,
    'check_in' => $check_in,
    'check_out' => $check_out,
    'time' => $time,
    'amount' => $total_amount,
    'razorpay_order_id' => $order_id
];

// ===============================
// ✅ INSERT ORDER (PENDING)
// ===============================
$stmt = $conn->prepare("
    INSERT INTO orders 
    (user_id, user_name, email, room_id, booking_date, check_out, booking_time, amount, razorpay_order_id, receipt, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

$stmt->bind_param(
    "ississsiss",
    $user_id,
    $name,
    $email,
    $room_id,
    $check_in,
    $check_out,
    $time,
    $total_amount,
    $order_id,
    $receipt
);

if (!$stmt->execute()) {
    echo json_encode(["error" => $stmt->error]);
    exit;
}

// ===============================
// ✅ RESPONSE
// ===============================
echo json_encode([
    "order_id" => $order_id,
    "amount"   => $amount_paise,
    "name"     => $name,
    "email"    => $email
]);