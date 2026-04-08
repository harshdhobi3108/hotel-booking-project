<?php
require 'vendor/autoload.php';
require __DIR__ . '/includes/config.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// DEBUG (optional)
file_put_contents("debug.txt", print_r($data, true));

// Validate required fields
if (
    !isset($data['razorpay_order_id']) ||
    !isset($data['razorpay_payment_id']) ||
    !isset($data['razorpay_signature'])
) {
    echo "Missing payment data";
    exit;
}

// Razorpay init
$api = new Api($razorpay['key_id'], $razorpay['secret']);

try {

    // 🔐 VERIFY PAYMENT
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id' => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature' => $data['razorpay_signature']
    ]);

    // ✅ GET EXTRA DATA FROM FRONTEND
    $room   = $data['room'] ?? 'Unknown Room';
    $amount = $data['amount'] ?? 0;

    $payment_id = $data['razorpay_payment_id'];
    $order_id   = $data['razorpay_order_id'];
    $status     = "success";

    // Dummy user (replace later)
    $user_id = 1;

    // ✅ INSERT INTO DB
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (user_id, room_name, amount, payment_id, order_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("DB Error: " . $conn->error);
    }

    $stmt->bind_param("isisss", $user_id, $room, $amount, $payment_id, $order_id, $status);
    $stmt->execute();

    echo "Payment Successful ✅";

} catch (SignatureVerificationError $e) {

    echo "ERROR: " . $e->getMessage();

}