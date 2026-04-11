<?php
require_once("includes/config.php");
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$order_id = intval($_GET['id']);

// ================= FETCH ORDER =================
$query = "
SELECT o.*, r.name as room_name
FROM orders o
JOIN rooms r ON o.room_id = r.id
WHERE o.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Invoice not found");
}

// ================= FETCH PAYMENT =================
$payQuery = "SELECT * FROM payments WHERE order_id = ?";
$stmt2 = $conn->prepare($payQuery);
$stmt2->bind_param("s", $order['razorpay_order_id']);
$stmt2->execute();
$payment = $stmt2->get_result()->fetch_assoc();

// ================= CALCULATIONS =================
$amount = $order['amount'];
$gst = round($amount * 0.18);
$base = $amount - $gst;

$checkin = new DateTime($order['booking_date']);
$checkout = new DateTime($order['check_out']);
$nights = $checkin->diff($checkout)->days;

// ================= HTML =================
$html = '
<style>
body {
  font-family: DejaVu Sans, sans-serif;
  color: #333;
  padding: 25px;
}

/* HEADER */
.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 2px solid #eee;
  padding-bottom: 15px;
}

.brand {
  font-size: 26px;
  font-weight: bold;
  color: #7b2cbf;
}

.tagline {
  font-size: 12px;
  color: #777;
  margin-top: 4px;
}

.invoice-meta {
  text-align: right;
}

.invoice-meta h2 {
  margin: 0;
  font-size: 18px;
}

.invoice-meta p {
  margin: 3px 0;
  font-size: 12px;
  color: #555;
}

.paid {
  display: inline-block;
  margin-top: 5px;
  background: #d1fae5;
  color: #065f46;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: bold;
}

/* COMPANY */
.company {
  margin-top: 10px;
  font-size: 12px;
  color: #666;
}

/* SECTION */
.section {
  margin-top: 20px;
}

/* FLEX */
.flex {
  display: flex;
  justify-content: space-between;
}

/* BOX */
.box {
  border: 1px solid #eee;
  padding: 14px;
  border-radius: 10px;
  width: 48%;
}

/* TABLE */
.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.table th {
  background: #f3f4f6;
  padding: 10px;
  text-align: left;
}

.table td {
  padding: 10px;
  border-bottom: 1px solid #eee;
}

/* TOTAL */
.total-box {
  margin-top: 20px;
  width: 320px;
  float: right;
}

.total-box td {
  padding: 6px;
}

.total {
  font-weight: bold;
  font-size: 16px;
}

/* FOOTER */
.footer {
  margin-top: 60px;
  text-align: center;
  font-size: 12px;
  color: #777;
}
</style>

<!-- HEADER -->
<div class="header">

  <div>
    <div class="brand">HotelLux</div>
    <div class="tagline">Luxury stays. Affordable comfort.</div>
  </div>

  <div class="invoice-meta">
    <h2>INVOICE</h2>
    <p><strong>#'.$order['id'].'</strong></p>
    <p>'.date("d M Y", strtotime($order['created_at'])).'</p>
    <span class="paid">PAID</span>
  </div>

</div>

<!-- COMPANY -->
<div class="company">
  HotelLux Pvt Ltd, Ahmedabad, Gujarat, India<br>
  GSTIN: 24ABCDE1234F1Z5<br>
  support@hotellux.com
</div>

<!-- CUSTOMER + INFO -->
<div class="section flex">

  <div class="box">
    <strong>Billed To:</strong><br>
    '.$order['user_name'].'<br>
    '.$order['email'].'
  </div>

  <div class="box">
    <strong>Receipt:</strong> '.$order['receipt'].'<br>
    <strong>Razorpay Order:</strong> '.$order['razorpay_order_id'].'<br>
    <strong>Payment ID:</strong> '.($payment['payment_id'] ?? 'N/A').'
  </div>

</div>

<!-- TABLE -->
<div class="section">
  <table class="table">
    <thead>
      <tr>
        <th>Room</th>
        <th>Check-In</th>
        <th>Check-Out</th>
        <th>Nights</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>'.$order['room_name'].'</td>
        <td>'.$order['booking_date'].'</td>
        <td>'.$order['check_out'].'</td>
        <td>'.$nights.'</td>
        <td>₹'.$amount.'</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- TOTAL -->
<div class="total-box">
  <table>
    <tr>
      <td>Base Amount</td>
      <td>₹'.$base.'</td>
    </tr>
    <tr>
      <td>GST (18%)</td>
      <td>₹'.$gst.'</td>
    </tr>
    <tr class="total">
      <td>Total Paid</td>
      <td>₹'.$amount.'</td>
    </tr>
  </table>
</div>

<div style="clear: both;"></div>

<!-- FOOTER -->
<div class="footer">
  Thank you for choosing <strong>HotelLux</strong> ❤️<br>
  For support: support@hotellux.com | +91 9876543210<br>
  This is a digitally generated invoice.
</div>
';

// ================= PDF =================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("HotelLux_Invoice_$order_id.pdf", ["Attachment" => true]);