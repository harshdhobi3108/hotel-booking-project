<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/invoice.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/*
|--------------------------------------------------------------------------
| HotelLux - verify_payment.php (Final Updated Version)
|--------------------------------------------------------------------------
| Features:
| ✅ Razorpay payment verification
| ✅ Prevent double booking
| ✅ Save payment record
| ✅ Confirm booking
| ✅ Auto generate invoice PDF
| ✅ Send booking confirmation email + invoice attachment
| ✅ Clean rollback on error
|--------------------------------------------------------------------------
*/

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

/* ================= SESSION ================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= AUTH ================= */

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['razorpay_order_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Order ID missing"
    ]);
    exit;
}

/* ================= PAYMENT FAILED / CLOSED ================= */

if (empty($data['razorpay_payment_id'])) {

    $stmt = $conn->prepare("
        UPDATE orders
        SET status = 'failed'
        WHERE razorpay_order_id = ?
    ");

    $stmt->bind_param("s", $data['razorpay_order_id']);
    $stmt->execute();

    unset($_SESSION['booking']);

    echo json_encode([
        "status" => "failed",
        "message" => "Payment failed or cancelled"
    ]);
    exit;
}

/* ================= SESSION BOOKING ================= */

if (!isset($_SESSION['booking'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Session expired"
    ]);
    exit;
}

$booking = $_SESSION['booking'];

if ($booking['razorpay_order_id'] !== $data['razorpay_order_id']) {
    echo json_encode([
        "status" => "error",
        "message" => "Order mismatch"
    ]);
    exit;
}

/* ================= RAZORPAY ================= */

$api = new Api($razorpay['key_id'], $razorpay['secret']);

try {

    $conn->begin_transaction();

    /* ================= SIGNATURE VERIFY ================= */

    if (empty($data['razorpay_signature'])) {
        throw new Exception("Signature missing");
    }

    $api->utility->verifyPaymentSignature([
        'razorpay_order_id'   => $data['razorpay_order_id'],
        'razorpay_payment_id' => $data['razorpay_payment_id'],
        'razorpay_signature'  => $data['razorpay_signature']
    ]);

    /* ================= PAYMENT FETCH ================= */

    $payment = $api->payment->fetch($data['razorpay_payment_id']);

    if ($payment->status !== 'captured') {
        throw new Exception("Payment not captured");
    }

    /* ================= BOOKING DATA ================= */

    $user_id   = $booking['user_id'];
    $room_id   = $booking['room_id'];
    $check_in  = $booking['check_in'];
    $check_out = $booking['check_out'];
    $amount    = $booking['amount'];

    if ($check_out <= $check_in) {
        throw new Exception("Invalid booking dates");
    }

    if ($payment->amount != ($amount * 100)) {
        throw new Exception("Amount mismatch");
    }

    /* ================= DOUBLE BOOKING CHECK ================= */

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
        throw new Exception("Room already booked for selected dates");
    }

    /* ================= SAVE PAYMENT ================= */

    $payInsert = $conn->prepare("
        INSERT INTO payments
        (user_id, room_id, amount, payment_id, order_id, status)
        VALUES (?, ?, ?, ?, ?, 'success')
    ");

    $payInsert->bind_param(
        "iiiss",
        $user_id,
        $room_id,
        $amount,
        $data['razorpay_payment_id'],
        $data['razorpay_order_id']
    );

    if (!$payInsert->execute()) {
        throw new Exception("Payment insert failed");
    }

    /* ================= CONFIRM ORDER ================= */

    $confirm = $conn->prepare("
        UPDATE orders
        SET status = 'confirmed',
            payment_id = ?
        WHERE razorpay_order_id = ?
    ");

    $confirm->bind_param(
        "ss",
        $data['razorpay_payment_id'],
        $data['razorpay_order_id']
    );

    if (!$confirm->execute()) {
        throw new Exception("Order update failed");
    }

    /* ================= FETCH ORDER ================= */

    $details = $conn->prepare("
        SELECT o.*, r.name AS room_name
        FROM orders o
        JOIN rooms r ON o.room_id = r.id
        WHERE o.razorpay_order_id = ?
        LIMIT 1
    ");

    $details->bind_param("s", $data['razorpay_order_id']);
    $details->execute();

    $order = $details->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found");
    }

    /* ================= INVOICE GENERATE ================= */

    $invoicePath = generateInvoice($conn, $order['id'], false);

    /* ================= EMAIL SEND ================= */

    $to = $order['email'];
    $guestName = $order['user_name'];

    $subject = "Booking Confirmed - HotelLux";

    $body = '
    <div style="font-family:Arial,sans-serif;background:#f4f6fb;padding:30px;">
        <div style="max-width:650px;margin:auto;background:#ffffff;border-radius:14px;overflow:hidden;">

            <div style="background:linear-gradient(135deg,#7b2cbf,#9d4edd);padding:28px;text-align:center;color:#fff;">
                <h1 style="margin:0;">HotelLux</h1>
                <p style="margin:8px 0 0;">Your Booking is Confirmed</p>
            </div>

            <div style="padding:30px;color:#222;">

                <p>Hello <strong>' . htmlspecialchars($guestName) . '</strong>,</p>

                <p>Your booking has been successfully confirmed. We look forward to hosting you.</p>

                <table style="width:100%;border-collapse:collapse;margin-top:20px;">
                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Booking ID</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">#' . $order['id'] . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Room</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">' . htmlspecialchars($order['room_name']) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Check-in</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">' . htmlspecialchars($order['booking_date']) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Check-out</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">' . htmlspecialchars($order['check_out']) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Amount Paid</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;color:#16a34a;font-weight:bold;">₹' . number_format($amount, 2) . '</td>
                    </tr>
                </table>

                <p style="margin-top:20px;">
                    Your invoice is attached with this email.
                </p>

                <p style="margin-top:15px;">
                    Thank you for choosing HotelLux.
                </p>

            </div>

            <div style="padding:18px;background:#f8f8f8;text-align:center;font-size:13px;color:#666;">
                © 2026 HotelLux. All rights reserved.
            </div>

        </div>
    </div>';

    sendHotelMail($to, $subject, $body, $invoicePath);

    /* ================= COMMIT ================= */

    $conn->commit();

    unset($_SESSION['booking']);

    echo json_encode([
        "status" => "success"
    ]);

} catch (SignatureVerificationError $e) {

    $conn->rollback();

    $stmt = $conn->prepare("
        UPDATE orders
        SET status = 'failed'
        WHERE razorpay_order_id = ?
    ");

    $stmt->bind_param("s", $data['razorpay_order_id']);
    $stmt->execute();

    unset($_SESSION['booking']);

    echo json_encode([
        "status" => "error",
        "message" => "Invalid signature"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    $stmt = $conn->prepare("
        UPDATE orders
        SET status = 'failed'
        WHERE razorpay_order_id = ?
    ");

    $stmt->bind_param("s", $data['razorpay_order_id']);
    $stmt->execute();

    unset($_SESSION['booking']);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>