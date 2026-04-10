<?php
require 'vendor/autoload.php';
require __DIR__ . '/includes/config.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

// ✅ START SESSION (FIXED)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 AUTH CHECK
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// ✅ GET DATA
$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['razorpay_order_id']) ||
    empty($data['razorpay_payment_id']) ||
    empty($data['razorpay_signature'])
) {
    echo json_encode(["status" => "error", "message" => "Missing payment data"]);
    exit;
}

// ✅ SESSION CHECK
if (!isset($_SESSION['booking'])) {
    echo json_encode(["status" => "error", "message" => "Session expired"]);
    exit;
}

$booking = $_SESSION['booking'];

// 🔥 ORDER MATCH CHECK
if ($booking['razorpay_order_id'] !== $data['razorpay_order_id']) {
    echo json_encode(["status" => "error", "message" => "Order mismatch"]);
    exit;
}

// ✅ INIT API
$api = new Api($razorpay['key_id'], $razorpay['secret']);

try {

    // 🔐 SIGNATURE VERIFY
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id'   => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature'  => $data['razorpay_signature']
    ]);

    // 🔥 EXTRA SECURITY (VERY IMPORTANT)
    $payment = $api->payment->fetch($data['razorpay_payment_id']);

    if ($payment->status !== 'captured') {
        throw new Exception("Payment not captured");
    }

    // ✅ EXTRACT
    $user_id = $booking['user_id'];
    $room_id = $booking['room_id'];
    $date    = $booking['date'];
    $time    = $booking['time'];
    $amount  = $booking['amount'];

    // 🔍 USER VALIDATION (FIX YOUR OLD BUG)
    $userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $userCheck->bind_param("i", $user_id);
    $userCheck->execute();

    if ($userCheck->get_result()->num_rows === 0) {
        throw new Exception("User not found");
    }

    // ❗ DOUBLE BOOK CHECK
    $check = $conn->prepare("
        SELECT id FROM orders 
        WHERE room_id = ? AND booking_date = ? AND booking_time = ? AND status = 'confirmed'
    ");
    $check->bind_param("iss", $room_id, $date, $time);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        throw new Exception("Room already booked");
    }

    // 🔐 TRANSACTION
    $conn->begin_transaction();

    // 1️⃣ PAYMENT INSERT
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (user_id, room_id, amount, payment_id, order_id, status)
        VALUES (?, ?, ?, ?, ?, 'success')
    ");
    $stmt->bind_param("iiiss", $user_id, $room_id, $amount, $data['razorpay_payment_id'], $data['razorpay_order_id']);

    if (!$stmt->execute()) {
        throw new Exception("Payment insert failed");
    }

    // 2️⃣ ORDER UPDATE
    $stmt2 = $conn->prepare("
        UPDATE orders 
        SET status = 'confirmed', payment_id = ?
        WHERE razorpay_order_id = ?
    ");
    $stmt2->bind_param("ss", $data['razorpay_payment_id'], $data['razorpay_order_id']);

    if (!$stmt2->execute()) {
        throw new Exception("Order update failed");
    }

    $conn->commit();

    unset($_SESSION['booking']);

    echo json_encode(["status" => "success"]);

} catch (SignatureVerificationError $e) {

    $conn->rollback();

    $stmt = $conn->prepare("
        UPDATE orders SET status = 'failed' WHERE razorpay_order_id = ?
    ");
    $stmt->bind_param("s", $data['razorpay_order_id']);
    $stmt->execute();

    echo json_encode(["status" => "error", "message" => "Invalid signature"]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}