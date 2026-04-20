<?php
require_once("includes/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_email'])) {
    header("Location: auth/login.php");
    exit();
}

/* ================= VALID REQUEST ================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Request");
}

$order_id = (int) $_GET['id'];

/* ================= GET BOOKING ================= */
$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
    LIMIT 1
");

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
    die("Booking already cancelled");
}

/* ================= DATE CHECK ================= */
$checkin_datetime = $order['booking_date'] . ' ' . $order['booking_time'];

$checkin_time = strtotime($checkin_datetime);
$current_time = time();

$hours_before_checkin = ($checkin_time - $current_time) / 3600;

if ($hours_before_checkin <= 0) {
    die("Cannot cancel after check-in time");
}

/* ================= REFUND CALCULATION ================= */
$price = (float)$order['amount'];

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

/* ================= START TRANSACTION ================= */
$conn->begin_transaction();

try {

    /* ================= UPDATE BOOKING ================= */
    $update = $conn->prepare("
        UPDATE orders
        SET booking_status = 'cancelled',
            cancelled_at = NOW(),
            refund_amount = ?
        WHERE id = ?
    ");

    $update->bind_param("di", $refund, $order_id);

    if (!$update->execute()) {
        throw new Exception("Cancel update failed");
    }

    /* ================= SEND EMAIL ================= */
    require_once("mailer.php");

    $customerName = !empty($order['user_name'])
        ? $order['user_name']
        : 'Guest';

    $subject = "Booking Cancelled - HotelLux";

    $message = '
    <div style="font-family:Arial;padding:25px;background:#f8f9fc;">
        <div style="max-width:650px;margin:auto;background:#fff;border-radius:14px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#6a11cb,#8e2de2);padding:25px;text-align:center;color:#fff;">
                <h1 style="margin:0;">HotelLux</h1>
                <p>Booking Cancelled Successfully</p>
            </div>

            <div style="padding:25px;">
                <p>Hello <strong>' . $customerName . '</strong>,</p>
                <p>Your booking has been cancelled successfully.</p>

                <table style="width:100%;border-collapse:collapse;margin-top:20px;">
                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Booking ID</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">#' . $order_id . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;"><strong>Original Amount</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;">₹' . number_format($price, 2) . '</td>
                    </tr>

                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #eee;color:green;"><strong>Refund Amount</strong></td>
                        <td style="padding:10px;border-bottom:1px solid #eee;color:green;">₹' . number_format($refund, 2) . '</td>
                    </tr>
                </table>

                <p style="margin-top:20px;color:#666;">
                    Refund will be processed within 5-7 working days.
                </p>
            </div>

            <div style="background:#f4f4f4;padding:15px;text-align:center;color:#666;">
                © 2026 HotelLux
            </div>
        </div>
    </div>
    ';

    sendHotelMail($order['email'], $subject, $message);

    /* ================= COMMIT ================= */
    $conn->commit();

    header("Location: profile.php?msg=cancel_success");
    exit();

} catch (Exception $e) {

    $conn->rollback();

    die("Something went wrong: " . $e->getMessage());
}
?>