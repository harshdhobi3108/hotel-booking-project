<?php
require 'vendor/autoload.php';
require __DIR__ . '/includes/config.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

// ===============================
// ✅ SESSION
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// 🔐 AUTH CHECK
// ===============================
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// ===============================
// ✅ GET DATA
// ===============================
$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['razorpay_order_id']) ||
    empty($data['razorpay_payment_id']) ||
    empty($data['razorpay_signature'])
) {
    echo json_encode(["status" => "error", "message" => "Missing payment data"]);
    exit;
}

// ===============================
// ✅ SESSION BOOKING CHECK
// ===============================
if (!isset($_SESSION['booking'])) {
    echo json_encode(["status" => "error", "message" => "Session expired"]);
    exit;
}

$booking = $_SESSION['booking'];

if ($booking['razorpay_order_id'] !== $data['razorpay_order_id']) {
    echo json_encode(["status" => "error", "message" => "Order mismatch"]);
    exit;
}

// ===============================
// ✅ INIT RAZORPAY
// ===============================
$api = new Api($razorpay['key_id'], $razorpay['secret']);

try {

    // ===============================
    // 🔐 VERIFY SIGNATURE
    // ===============================
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id'   => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature'  => $data['razorpay_signature']
    ]);

    // ===============================
    // 🔥 FETCH PAYMENT
    // ===============================
    $payment = $api->payment->fetch($data['razorpay_payment_id']);

    if ($payment->status !== 'captured') {
        throw new Exception("Payment not captured");
    }

    // ===============================
    // ✅ EXTRACT DATA
    // ===============================
    $user_id   = $booking['user_id'];
    $room_id   = $booking['room_id'];
    $check_in  = $booking['check_in'];
    $check_out = $booking['check_out'];
    $time      = $booking['time'];
    $amount    = $booking['amount'];

    // ===============================
    // 🔥 VALIDATE DATES
    // ===============================
    if ($check_out <= $check_in) {
        throw new Exception("Invalid booking dates");
    }

    // ===============================
    // 🔥 AMOUNT VALIDATION
    // ===============================
    if ($payment->amount != ($amount * 100)) {
        throw new Exception("Amount mismatch");
    }

    // ===============================
    // 🔥 FINAL DOUBLE BOOKING CHECK (STRONG LOGIC)
    // ===============================
    $check = $conn->prepare("
        SELECT id FROM orders 
        WHERE room_id = ?
        AND status = 'confirmed'
        AND NOT (
            check_out <= ?
            OR booking_date >= ?
        )
    ");

    $check->bind_param("iss", $room_id, $check_in, $check_out);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("Room already booked for selected dates");
    }

    // ===============================
    // 🔐 START TRANSACTION
    // ===============================
    $conn->begin_transaction();

    // ===============================
    // ✅ INSERT PAYMENT
    // ===============================
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (user_id, room_id, amount, payment_id, order_id, status)
        VALUES (?, ?, ?, ?, ?, 'success')
    ");

    $stmt->bind_param(
        "iiiss",
        $user_id,
        $room_id,
        $amount,
        $data['razorpay_payment_id'],
        $data['razorpay_order_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Payment insert failed");
    }

    // ===============================
    // ✅ UPDATE ORDER (CONFIRM BOOKING)
    // ===============================
    $stmt2 = $conn->prepare("
        UPDATE orders 
        SET status = 'confirmed',
            payment_id = ?
        WHERE razorpay_order_id = ?
    ");

    $stmt2->bind_param(
        "ss",
        $data['razorpay_payment_id'],
        $data['razorpay_order_id']
    );

    if (!$stmt2->execute()) {
        throw new Exception("Order update failed");
    }

    // ===============================
    // ✅ COMMIT
    // ===============================
    $conn->commit();

    unset($_SESSION['booking']);

    echo json_encode(["status" => "success"]);

} catch (SignatureVerificationError $e) {

    $conn->rollback();

    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = 'failed' 
        WHERE razorpay_order_id = ?
    ");

    $stmt->bind_param("s", $data['razorpay_order_id']);
    $stmt->execute();

    echo json_encode([
        "status" => "error",
        "message" => "Invalid signature"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}