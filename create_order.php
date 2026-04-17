<?php
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/config.php');

use Razorpay\Api\Api;

/*
|--------------------------------------------------------------------------
| HotelLux - create_order.php (Updated Production Version)
|--------------------------------------------------------------------------
| Improvements:
| ✅ Better validation
| ✅ Prevent duplicate pending bookings
| ✅ Stronger overlap check
| ✅ Secure transaction flow
| ✅ Cleaner responses
| ✅ Ready for verify_payment.php email flow
|--------------------------------------------------------------------------
*/

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= AUTH ================= */

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "error" => "Unauthorized"
    ]);
    exit;
}

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

$room_id   = (int) ($data['room_id'] ?? 0);
$check_in  = trim($data['check_in'] ?? '');
$check_out = trim($data['check_out'] ?? '');
$time      = trim($data['time'] ?? '');

if (!$room_id || !$check_in || !$check_out || !$time) {
    echo json_encode([
        "error" => "Missing booking details"
    ]);
    exit;
}

/* ================= DATE VALIDATION ================= */

$today = date("Y-m-d");

if ($check_in < $today) {
    echo json_encode([
        "error" => "Check-in cannot be in the past"
    ]);
    exit;
}

if ($check_out <= $check_in) {
    echo json_encode([
        "error" => "Invalid check-out date"
    ]);
    exit;
}

/* ================= USER SESSION ================= */

$user_id = (int) $_SESSION['user_id'];
$name    = $_SESSION['user_name'];
$email   = $_SESSION['user_email'];

/* ================= ROOM FETCH ================= */

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

$price_per_night = (float) $room['price'];

$nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
$nights = max(1, (int) $nights);

$total_amount = $price_per_night * $nights;
$amount_paise = (int) round($total_amount * 100);

/* ================= CLEAN OLD PENDING BOOKINGS ================= */

$cleanup = $conn->prepare("
    UPDATE orders
    SET status = 'failed'
    WHERE status = 'pending'
    AND payment_id IS NULL
    AND created_at < NOW() - INTERVAL 10 MINUTE
");

$cleanup->execute();

/* ================= PREVENT SAME USER DUPLICATE PENDING ================= */

$pending = $conn->prepare("
    SELECT id
    FROM orders
    WHERE user_id = ?
    AND room_id = ?
    AND status = 'pending'
    AND created_at > NOW() - INTERVAL 10 MINUTE
    LIMIT 1
");

$pending->bind_param("ii", $user_id, $room_id);
$pending->execute();

if ($pending->get_result()->num_rows > 0) {
    echo json_encode([
        "error" => "Payment already initiated. Please complete previous payment."
    ]);
    exit;
}

/* ================= ROOM OVERLAP CHECK ================= */

$check = $conn->prepare("
    SELECT id
    FROM orders
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
    echo json_encode([
        "error" => "Room already booked for selected dates"
    ]);
    exit;
}

/* ================= TRANSACTION ================= */

$conn->begin_transaction();

try {

    /* ================= RAZORPAY ORDER ================= */

    $api = new Api($razorpay['key_id'], $razorpay['secret']);

    $receipt = "HLX_" . time() . "_" . $user_id;

    $order = $api->order->create([
        'receipt'  => $receipt,
        'amount'   => $amount_paise,
        'currency' => 'INR'
    ]);

    $order_id = $order['id'];

    /* ================= SESSION STORE ================= */

    $_SESSION['booking'] = [
        'user_id'            => $user_id,
        'room_id'            => $room_id,
        'room_name'          => $room['name'],
        'check_in'           => $check_in,
        'check_out'          => $check_out,
        'time'               => $time,
        'amount'             => $total_amount,
        'razorpay_order_id'  => $order_id
    ];

    /* ================= INSERT ORDER ================= */

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
            razorpay_order_id,
            receipt,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $insert->bind_param(
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

    if (!$insert->execute()) {
        throw new Exception("Unable to create booking");
    }

    $conn->commit();

    echo json_encode([
        "success"  => true,
        "order_id" => $order_id,
        "amount"   => $amount_paise,
        "name"     => $name,
        "email"    => $email,
        "room"     => $room['name']
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}