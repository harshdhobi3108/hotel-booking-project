<?php
require 'vendor/autoload.php';
require 'includes/config.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// DB connection
$conn = new mysqli("localhost", "root", "", "hotel_booking");

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

// DEBUG (optional)
file_put_contents("debug.txt", print_r($data, true));

if (
    !isset($data['razorpay_order_id']) ||
    !isset($data['razorpay_payment_id']) ||
    !isset($data['razorpay_signature'])
) {
    echo "Missing payment data";
    exit;
}

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

try {

    // ✅ VERIFY PAYMENT
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id' => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature' => $data['razorpay_signature']
    ]);

    // 📦 DATA
    $payment_id = $data['razorpay_payment_id'];
    $order_id   = $data['razorpay_order_id'];
    $status     = "success";

    // dummy (replace later)
    $user_id = 1;
    $room = "Room Booking";
    $amount = 0;

    // ✅ INSERT INTO DB
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (user_id, room_name, amount, payment_id, order_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("isisss", $user_id, $room, $amount, $payment_id, $order_id, $status);
    $stmt->execute();

    echo "Payment Saved Successfully ✅";

} catch (SignatureVerificationError $e) {

    echo "ERROR: " . $e->getMessage();

}