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

// ================= GET BOOKING =================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$order = $result->fetch_assoc();

// ================= SECURITY CHECK =================
if ($order['email'] !== $_SESSION['user_email']) {
    die("Unauthorized access");
}

// ================= CHECK IF ALREADY CANCELLED =================
if ($order['booking_status'] === 'cancelled') {
    die("Already Cancelled");
}

// ================= DATE LOGIC =================
// Combine booking_date + booking_time (IMPORTANT FIX)
$checkin_datetime = $order['booking_date'] . ' ' . $order['booking_time'];
$checkin = strtotime($checkin_datetime);
$now = time();

$hours_before_checkin = ($checkin - $now) / 3600;

// ================= PRICE =================
$price = floatval($order['amount']);

// ================= VALIDATION =================
if ($hours_before_checkin <= 0) {
    die("Cannot cancel after check-in date");
}

// ================= REFUND LOGIC =================
if ($hours_before_checkin > 48) {
    $refund = $price; // 100%
} elseif ($hours_before_checkin > 24) {
    $refund = $price * 0.75;
} elseif ($hours_before_checkin > 12) {
    $refund = $price * 0.5;
} else {
    $refund = $price * 0.25;
}

// Round refund properly
$refund = round($refund, 2);

// ================= TRANSACTION =================
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

    $conn->commit();

    header("Location: profile.php?msg=cancel_success");
    exit();

} catch (Exception $e) {

    $conn->rollback();
    die("Something went wrong");
}