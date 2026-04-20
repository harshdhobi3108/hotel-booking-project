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

    $payInsert->bind_param("iid", $order_id, $user_id, $amount);

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

    $body = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Booking Confirmed</title>
</head>

<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 15px;background:#f4f5f7;">
<tr>
<td align="center">

<table width="680" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 35px rgba(0,0,0,0.08);">

<tr>
<td style="padding:38px 30px;background:linear-gradient(135deg,#6a11cb,#9b3fe4);text-align:center;color:#ffffff;">
<h1 style="margin:0;font-size:38px;font-weight:700;">HotelLux</h1>
<p style="margin:12px 0 0;font-size:16px;opacity:0.95;">Your Booking is Confirmed</p>
</td>
</tr>

<tr>
<td style="padding:35px 30px;color:#222;">

<p style="font-size:22px;margin:0 0 20px;font-weight:700;">Hello '.$order['user_name'].'</p>

<p style="font-size:15px;line-height:1.8;color:#555;margin:0 0 25px;">
Thank you for choosing HotelLux. We are delighted to confirm your reservation.
Your attached invoice is included with this email.
</p>

<table width="100%" cellpadding="14" cellspacing="0" style="border-collapse:collapse;border:1px solid #eeeeee;border-radius:10px;overflow:hidden;">

<tr style="background:#fafafa;">
<td style="font-weight:700;">Booking ID</td>
<td>#'.$order['id'].'</td>
</tr>

<tr>
<td style="font-weight:700;">Room</td>
<td>'.$order['room_name'].'</td>
</tr>

<tr style="background:#fafafa;">
<td style="font-weight:700;">Check-in</td>
<td>'.$order['booking_date'].'</td>
</tr>

<tr>
<td style="font-weight:700;">Check-out</td>
<td>'.$order['check_out'].'</td>
</tr>

<tr style="background:#fafafa;">
<td style="font-weight:700;">Amount Paid</td>
<td style="font-weight:700;color:#16a34a;">₹'.number_format($amount,2).'</td>
</tr>

</table>

<p style="margin:28px 0 0;font-size:14px;color:#666;line-height:1.7;">
We look forward to welcoming you and making your stay memorable.
</p>

</td>
</tr>

<tr>
<td style="background:#fafafa;padding:22px;text-align:center;color:#888;font-size:13px;">
© 2026 HotelLux. All Rights Reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>';

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