<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/invoice.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

/* ================= SESSION ================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['razorpay_order_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

/* ================= PAYMENT CLOSED ================= */

if (empty($data['razorpay_payment_id'])) {

    if (isset($_SESSION['booking']['order_id'])) {

        $fail = $conn->prepare("
            UPDATE orders
            SET booking_status='cancelled'
            WHERE id=?
        ");

        $fail->bind_param("i", $_SESSION['booking']['order_id']);
        $fail->execute();
    }

    unset($_SESSION['booking']);

    echo json_encode([
        "status" => "failed",
        "message" => "Payment cancelled"
    ]);
    exit;
}

if (!isset($_SESSION['booking'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Session expired"
    ]);
    exit;
}

$booking = $_SESSION['booking'];

$api = new Api($razorpay['key_id'], $razorpay['secret']);

try {

    $conn->begin_transaction();

    /* ================= VERIFY SIGNATURE ================= */

    $api->utility->verifyPaymentSignature([
        'razorpay_order_id'   => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature'  => $data['razorpay_signature']
    ]);

    /* ================= PAYMENT FETCH ================= */

    $payment = $api->payment->fetch($data['razorpay_payment_id']);

    if ($payment->status !== 'captured' && $payment->status !== 'authorized') {
        throw new Exception("Payment not captured");
    }

    /* ================= BOOKING DATA ================= */

    $order_id  = (int)$booking['order_id'];
    $user_id   = (int)$booking['user_id'];
    $room_id   = (int)$booking['room_id'];
    $check_in  = $booking['check_in'];
    $check_out = $booking['check_out'];
    $amount    = (float)$booking['amount'];

    /* ================= CONFIRM ORDER ================= */

    $confirm = $conn->prepare("
        UPDATE orders
        SET booking_status='confirmed',
            payment_method='razorpay'
        WHERE id=?
    ");

    $confirm->bind_param("i", $order_id);

    if (!$confirm->execute()) {
        throw new Exception("Order update failed");
    }

    /* ================= ROOM STATUS BOOKED ================= */

    $roomUpdate = $conn->prepare("
        UPDATE rooms
        SET status='booked'
        WHERE id=?
    ");

    $roomUpdate->bind_param("i", $room_id);

    if (!$roomUpdate->execute()) {
        throw new Exception("Room update failed");
    }

    /* ================= REMOVE OLD PENDING DUPLICATES ================= */

    $clean = $conn->prepare("
        DELETE FROM orders
        WHERE user_id=?
        AND room_id=?
        AND booking_status='pending'
        AND id <> ?
    ");

    $clean->bind_param("iii", $user_id, $room_id, $order_id);
    $clean->execute();

    /* ================= SAVE PAYMENT ================= */

    $payInsert = $conn->prepare("
        INSERT INTO payments
        (order_id,user_id,amount,payment_method,payment_status)
        VALUES (?, ?, ?, 'razorpay', 'paid')
    ");

    $payInsert->bind_param(
        "iid",
        $order_id,
        $user_id,
        $amount
    );

    if (!$payInsert->execute()) {
        throw new Exception("Payment insert failed");
    }

    /* ================= FETCH ORDER DETAILS ================= */

    $details = $conn->prepare("
        SELECT o.*, r.name AS room_name
        FROM orders o
        JOIN rooms r ON o.room_id = r.id
        WHERE o.id=?
        LIMIT 1
    ");

    $details->bind_param("i", $order_id);
    $details->execute();

    $order = $details->get_result()->fetch_assoc();

    /* ================= INVOICE ================= */

    $invoicePath = generateInvoice($conn, $order_id, false);

    /* ================= COMMIT ================= */

    $conn->commit();

    /* ================= EMAIL ================= */

    $subject = "Booking Confirmed - HotelLux";

    $body = "
    <h2>Hello {$order['user_name']}</h2>
    <p>Your booking has been confirmed successfully.</p>
    <p><strong>Booking ID:</strong> #{$order['id']}</p>
    <p><strong>Room:</strong> {$order['room_name']}</p>
    <p><strong>Check-in:</strong> {$order['booking_date']}</p>
    <p><strong>Check-out:</strong> {$order['check_out']}</p>
    <p><strong>Amount Paid:</strong> ₹" . number_format($amount,2) . "</p>
    ";

    sendHotelMail($order['email'], $subject, $body, $invoicePath);

    unset($_SESSION['booking']);

    echo json_encode([
        "success" => true,
        "status" => "success",
        "message" => "Booking confirmed"
    ]);

} catch (SignatureVerificationError $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Payment signature invalid"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>