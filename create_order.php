<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= SESSION SUPPORT (ALL LOGIN TYPES) ================= */
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$name    = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['user'] ?? '';
$email   = $_SESSION['user_email'] ?? $_SESSION['email'] ?? '';

/* ================= DEBUG SESSION ================= */
if (!$user_id || empty($email)) {
    echo json_encode([
        "error"   => "Session expired",
        "session" => $_SESSION
    ]);
    exit;
}

/* ================= INPUT ================= */
$data = json_decode(file_get_contents("php://input"), true);

$room_id   = (int)($data['room_id'] ?? 0);
$check_in  = $data['check_in'] ?? '';
$check_out = $data['check_out'] ?? '';
$time      = $data['time'] ?? '';

if (!$room_id || !$check_in || !$check_out || !$time) {
    echo json_encode([
        "error" => "Missing fields"
    ]);
    exit;
}

/* ================= ROOM DETAILS ================= */
$stmt = $conn->prepare("
    SELECT id, name, price
    FROM rooms
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $room_id);
$stmt->execute();

$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    echo json_encode([
        "error" => "Room not found"
    ]);
    exit;
}

/* ================= PRICE CALCULATION ================= */
$nights = max(
    1,
    (strtotime($check_out) - strtotime($check_in)) / 86400
);

$total_amount = $room['price'] * $nights;
$amount_paise = $total_amount * 100;

/* ================= AVAILABILITY CHECK ================= */
$check = $conn->prepare("
    SELECT id
    FROM orders
    WHERE room_id = ?
    AND booking_status = 'confirmed'
    AND (
        booking_date < ?
        AND check_out > ?
    )
    LIMIT 1
");

$check->bind_param("iss", $room_id, $check_out, $check_in);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "error" => "Room already booked"
    ]);
    exit;
}

/* ================= RAZORPAY ORDER ================= */
$api = new Api(
    $razorpay['key_id'],
    $razorpay['secret']
);

$receipt = "HLX_" . time() . "_" . $user_id;

$order = $api->order->create([
    'receipt'  => $receipt,
    'amount'   => $amount_paise,
    'currency' => 'INR'
]);

$razor_order_id = $order['id'];

/* ================= INSERT PENDING BOOKING ================= */
$insert = $conn->prepare("
    INSERT INTO orders
    (
        user_id,
        user_name,
        email,
        room_id,
        booking_date,
        check_out,
        booking_time,
        amount,
        payment_method,
        booking_status
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, ?, 'razorpay', 'pending'
    )
");

$insert->bind_param(
    "ississsd",
    $user_id,
    $name,
    $email,
    $room_id,
    $check_in,
    $check_out,
    $time,
    $total_amount
);

if (!$insert->execute()) {
    echo json_encode([
        "error" => "Booking insert failed"
    ]);
    exit;
}

/* ================= SAVE BOOKING SESSION (IMPORTANT FIX) ================= */
$_SESSION['booking'] = [
    'order_id'         => $insert->insert_id,
    'razorpay_order_id'=> $razor_order_id,
    'user_id'          => $user_id,
    'room_id'          => $room_id,
    'check_in'         => $check_in,
    'check_out'        => $check_out,
    'time'             => $time,
    'amount'           => $total_amount,
    'email'            => $email,
    'name'             => $name
];

/* ================= SUCCESS RESPONSE ================= */
echo json_encode([
    "success"  => true,
    "order_id" => $razor_order_id,
    "amount"   => $amount_paise
]);
?>