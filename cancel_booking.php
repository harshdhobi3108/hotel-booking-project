<?php
require_once("includes/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email'])) {
    header("Location: auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$order_id = intval($_GET['id']);

/* ================= GET BOOKING ================= */

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$order = $result->fetch_assoc();

/* ================= SECURITY CHECK ================= */

if ($order['email'] !== $_SESSION['user_email']) {
    die("Unauthorized access");
}

/* ================= ALREADY CANCELLED ================= */

if ($order['booking_status'] === 'cancelled') {
    die("Already Cancelled");
}

/* ================= DATE LOGIC ================= */

$checkin_datetime = $order['booking_date'] . ' ' . $order['booking_time'];

$checkin = strtotime($checkin_datetime);
$now = time();

$hours_before_checkin = ($checkin - $now) / 3600;

/* ================= PRICE ================= */

$price = (float) $order['amount'];

/* ================= VALIDATION ================= */

if ($hours_before_checkin <= 0) {
    die("Cannot cancel after check-in date");
}

/* ================= REFUND LOGIC ================= */

if ($hours_before_checkin > 48) {
    $refund = $price;
} elseif ($hours_before_checkin > 24) {
    $refund = $price * 0.75;
} elseif ($hours_before_checkin > 12) {
    $refund = $price * 0.50;
} else {
    $refund = $price * 0.25;
}

$refund = round($refund, 2);

/* ================= TRANSACTION ================= */

$conn->begin_transaction();

try {

    $update = $conn->prepare("
        UPDATE orders
        SET booking_status = 'cancelled',
            cancelled_at = NOW(),
            refund_amount = ?
        WHERE id = ?
    ");

    $update->bind_param("di", $refund, $order_id);
    $update->execute();

    /* ================= EMAIL ================= */

    $toEmail = $order['email'];

    // FIXED NAME ISSUE
    $customerName = !empty($order['user_name'])
        ? $order['user_name']
        : (!empty($order['full_name']) ? $order['full_name'] : 'Guest');

    $subject = "Booking Cancelled - HotelLux";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: HotelLux <noreply@hotellux.com>\r\n";

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Booking Cancelled</title>
    </head>

    <body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,sans-serif;">

        <div style="max-width:650px;margin:30px auto;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);">

            <div style="background:linear-gradient(135deg,#7b2cbf,#5a189a);padding:28px;text-align:center;color:#fff;">
                <h1 style="margin:0;font-size:28px;">HotelLux</h1>
                <p style="margin:8px 0 0;">Booking Cancellation Confirmation</p>
            </div>

            <div style="padding:30px;color:#222;">

                <p style="font-size:16px;">
                    Hello <strong>' . htmlspecialchars($customerName) . '</strong>,
                </p>

                <p style="font-size:15px;line-height:1.7;">
                    Your booking has been successfully cancelled.
                </p>

                <table style="width:100%;border-collapse:collapse;margin-top:18px;">

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Booking ID</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">#' . $order_id . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Check-in Date</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">' . htmlspecialchars($order['booking_date']) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Check-in Time</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">' . htmlspecialchars($order['booking_time']) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Original Amount</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">₹' . number_format($price, 2) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;color:#16a34a;font-weight:bold;"><strong>Refund Amount</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;color:#16a34a;font-weight:bold;">₹' . number_format($refund, 2) . '</td>
                    </tr>

                </table>

                <p style="margin-top:22px;font-size:14px;line-height:1.7;color:#555;">
                    Refund will be processed based on your payment method within 5-7 working days.
                </p>

                <p style="margin-top:18px;font-size:14px;color:#555;">
                    Need help? Contact our support team anytime.
                </p>

            </div>

            <div style="background:#f8f8f8;padding:18px;text-align:center;font-size:13px;color:#666;">
                © 2026 HotelLux. All rights reserved.
            </div>

        </div>

    </body>
    </html>
    ';

    require_once 'mailer.php';
    sendHotelMail($toEmail, $subject, $message);

    $conn->commit();

    header("Location: profile.php?msg=cancel_success");
    exit();

} catch (Exception $e) {

    $conn->rollback();
    die("Something went wrong");
}
?>