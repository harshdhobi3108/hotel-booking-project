<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= AUTH =================
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// ================= INPUT =================
$data = json_decode(file_get_contents("php://input"), true);

$room_id   = (int) ($data['room_id'] ?? 0);
$check_in  = $data['check_in'] ?? null;
$check_out = $data['check_out'] ?? null;
$time      = $data['time'] ?? null;

if (!$room_id || !$check_in || !$check_out || !$time) {
    echo json_encode(["error" => "Missing booking details"]);
    exit;
}

// ================= DATE VALIDATION =================
$today = date("Y-m-d");

if ($check_in < $today) {
    echo json_encode(["error" => "Check-in cannot be in the past"]);
    exit;
}

if ($check_out <= $check_in) {
    echo json_encode(["error" => "Invalid check-out date"]);
    exit;
}

// ================= USER =================
$user_id = $_SESSION['user_id'];
$name    = $_SESSION['user_name'];
$email   = $_SESSION['user_email'];

// ================= ROOM =================
$stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    echo json_encode(["error" => "Room not found"]);
    exit;
}

// ================= PRICE =================
$price_per_night = (int) $room['price'];

$nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);

// FIX: minimum 1 night
$nights = max(1, $nights);

$total_amount = $price_per_night * $nights;
$amount_paise = $total_amount * 100;

// ================= 🔥 CLEANUP =================
$cleanup = $conn->prepare("
    UPDATE orders 
    SET status = 'failed' 
    WHERE status = 'pending' 
    AND payment_id IS NULL
    AND created_at < NOW() - INTERVAL 10 MINUTE
");
$cleanup->execute();

// ================= 🔥 OVERLAP CHECK =================
$check = $conn->prepare("
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

$check->bind_param("iss", $room_id, $check_out, $check_in);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(["error" => "Room already booked for selected dates"]);
    exit;
}

// ================= TRANSACTION =================
$conn->begin_transaction();

try {

    // ================= RAZORPAY =================
    $api = new Api($razorpay['key_id'], $razorpay['secret']);

    $receipt = "order_" . time();

    $order = $api->order->create([
        'receipt'  => $receipt,
        'amount'   => $amount_paise,
        'currency' => 'INR'
    ]);

    $order_id = $order['id'];

    // ================= SESSION =================
    $_SESSION['booking'] = [
        'user_id' => $user_id,
        'room_id' => $room_id,
        'check_in' => $check_in,
        'check_out' => $check_out,
        'time' => $time,
        'amount' => $total_amount,
        'razorpay_order_id' => $order_id
    ];

    // ================= INSERT ORDER =================
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
        throw new Exception($stmt->error);
    }

    $conn->commit();

    echo json_encode([
        "order_id" => $order_id,
        "amount"   => $amount_paise,
        "name"     => $name,
        "email"    => $email
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}