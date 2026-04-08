<?php
require 'vendor/autoload.php';
require __DIR__ . '/includes/config.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Debug log (optional)
file_put_contents("debug.txt", print_r($data, true));

// ✅ Validate required fields
if (
    !isset($data['razorpay_order_id']) ||
    !isset($data['razorpay_payment_id']) ||
    !isset($data['razorpay_signature']) ||
    !isset($data['room_id'])
) {
    echo "Missing payment data";
    exit;
}

// Razorpay init
$api = new Api($razorpay['key_id'], $razorpay['secret']);

try {

    // 🔐 VERIFY PAYMENT SIGNATURE
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id' => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature' => $data['razorpay_signature']
    ]);

    // ✅ GET DATA
    $room_id = (int) $data['room_id'];
    $amount  = (int) ($data['amount'] ?? 0);

    $payment_id = $data['razorpay_payment_id'];
    $order_id   = $data['razorpay_order_id'];
    $status     = "success";

    // TODO: Replace with session later
    $user_id = 1;

    // ===============================
    // 1️⃣ INSERT PAYMENT
    // ===============================
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (user_id, room_id, amount, payment_id, order_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("DB Error: " . $conn->error);
    }

    $stmt->bind_param("iiisss", $user_id, $room_id, $amount, $payment_id, $order_id, $status);
    $stmt->execute();

    // ===============================
    // 2️⃣ UPDATE ROOM STATUS
    // ===============================
    $stmt2 = $conn->prepare("
        UPDATE rooms 
        SET status = 'booked' 
        WHERE id = ?
    ");

    $stmt2->bind_param("i", $room_id);
    $stmt2->execute();

    // ===============================
    // 3️⃣ UPDATE ORDER STATUS
    // ===============================
    $stmt3 = $conn->prepare("
        UPDATE orders 
        SET status = 'confirmed' 
        WHERE razorpay_order_id = ?
    ");

    $stmt3->bind_param("s", $order_id);
    $stmt3->execute();

    echo "Payment Successful & Room Booked ✅";

} catch (SignatureVerificationError $e) {

    echo "Payment Verification Failed ❌";
}