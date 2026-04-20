<?php
require_once("includes/config.php");
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/*
|--------------------------------------------------------------------------
| HotelLux - Final invoice.php
|--------------------------------------------------------------------------
| Compatible With Current Schema
| orders:
| id,user_id,user_name,email,room_id,booking_date,check_out,
| booking_time,amount,payment_method,booking_status,cancelled_at,created_at
|
| payments:
| id,order_id,user_id,amount,payment_method,payment_status,created_at
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   GENERATE INVOICE FUNCTION
========================================================= */

function generateInvoice($conn, $order_id, $download = false)
{
    $order_id = (int)$order_id;

    /* ================= FETCH ORDER ================= */

    $sql = "
        SELECT o.*, r.name AS room_name
        FROM orders o
        INNER JOIN rooms r ON o.room_id = r.id
        WHERE o.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        return false;
    }

    /* ================= FETCH PAYMENT ================= */

    $paySql = "
        SELECT *
        FROM payments
        WHERE order_id = ?
        ORDER BY id DESC
        LIMIT 1
    ";

    $stmt2 = $conn->prepare($paySql);

    if (!$stmt2) {
        return false;
    }

    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();

    $payment = $stmt2->get_result()->fetch_assoc();

    /* ================= CALCULATIONS ================= */

    $amount = (float)$order['amount'];

    $gst   = round($amount * 0.18, 2);
    $base  = round($amount - $gst, 2);

    $checkIn  = new DateTime($order['booking_date']);
    $checkOut = new DateTime($order['check_out']);

    $nights = max(1, $checkIn->diff($checkOut)->days);

    $invoiceDate = !empty($order['created_at'])
        ? date("d M Y", strtotime($order['created_at']))
        : date("d M Y");

    $paidDate = !empty($payment['created_at'])
        ? date("d M Y", strtotime($payment['created_at']))
        : $invoiceDate;

    $guestName = htmlspecialchars($order['user_name']);
    $guestMail = htmlspecialchars($order['email']);
    $roomName  = htmlspecialchars($order['room_name']);

    $payMethod = htmlspecialchars($payment['payment_method'] ?? $order['payment_method'] ?? 'Online');
    $payStatus = htmlspecialchars($payment['payment_status'] ?? 'Paid');

    /* ================= HTML ================= */

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body{
                font-family: DejaVu Sans, sans-serif;
                color:#222;
                font-size:13px;
                margin:0;
                padding:28px;
            }

            .header{
                border-bottom:2px solid #ececec;
                padding-bottom:18px;
                margin-bottom:20px;
            }

            .brand{
                font-size:30px;
                font-weight:bold;
                color:#7b2cbf;
            }

            .tagline{
                color:#777;
                font-size:12px;
                margin-top:4px;
            }

            .row{
                width:100%;
                margin-bottom:15px;
            }

            .left{
                width:60%;
                float:left;
            }

            .right{
                width:40%;
                float:right;
                text-align:right;
            }

            .clear{
                clear:both;
            }

            .badge{
                display:inline-block;
                background:#d1fae5;
                color:#065f46;
                padding:6px 10px;
                border-radius:5px;
                font-size:11px;
                font-weight:bold;
            }

            h2{
                margin:18px 0 8px;
                font-size:22px;
            }

            table{
                width:100%;
                border-collapse:collapse;
                margin-top:18px;
            }

            th{
                background:#f4f4f4;
                text-align:left;
                padding:10px;
                border:1px solid #ececec;
            }

            td{
                padding:10px;
                border:1px solid #ececec;
            }

            .totals{
                width:320px;
                margin-top:20px;
                margin-left:auto;
            }

            .totals td{
                border:none;
                padding:6px 0;
            }

            .grand{
                font-weight:bold;
                font-size:16px;
                border-top:1px solid #ccc;
                padding-top:8px;
            }

            .footer{
                margin-top:55px;
                text-align:center;
                font-size:12px;
                color:#777;
                line-height:1.8;
            }
        </style>
    </head>
    <body>

        <div class="header">
            <div class="left">
                <div class="brand">HotelLux</div>
                <div class="tagline">Luxury stays. Affordable comfort.</div>
            </div>

            <div class="right">
                <div><strong>Invoice #' . $order['id'] . '</strong></div>
                <div style="margin-top:6px;">' . $invoiceDate . '</div>
                <div style="margin-top:8px;"><span class="badge">PAID</span></div>
            </div>

            <div class="clear"></div>
        </div>

        <div class="row">
            <div class="left">
                <strong>Billed To</strong><br>
                ' . $guestName . '<br>
                ' . $guestMail . '
            </div>

            <div class="right">
                <strong>Payment Details</strong><br>
                Method: ' . $payMethod . '<br>
                Status: ' . $payStatus . '<br>
                Paid On: ' . $paidDate . '
            </div>

            <div class="clear"></div>
        </div>

        <h2>Booking Summary</h2>

        <table>
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
                    <td>' . $roomName . '</td>
                    <td>' . $order['booking_date'] . '</td>
                    <td>' . $order['check_out'] . '</td>
                    <td>' . $nights . '</td>
                    <td>₹' . number_format($amount, 2) . '</td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Base Amount</td>
                <td align="right">₹' . number_format($base, 2) . '</td>
            </tr>

            <tr>
                <td>GST (18%)</td>
                <td align="right">₹' . number_format($gst, 2) . '</td>
            </tr>

            <tr class="grand">
                <td>Total Paid</td>
                <td align="right">₹' . number_format($amount, 2) . '</td>
            </tr>
        </table>

        <div class="footer">
            Thank you for choosing <strong>HotelLux</strong><br>
            support@hotellux.com | +91 9876543210<br>
            This is a digitally generated invoice.
        </div>

    </body>
    </html>
    ';

    /* ================= DOMPDF ================= */

    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    /* ================= SAVE FILE ================= */

    $folder = __DIR__ . '/uploads/invoices/';

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $filePath = $folder . 'HotelLux_Invoice_' . $order_id . '.pdf';

    file_put_contents($filePath, $dompdf->output());

    /* ================= DOWNLOAD ================= */

    if ($download) {
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=HotelLux_Invoice_$order_id.pdf");
        readfile($filePath);
        exit;
    }

    return $filePath;
}

/* =========================================================
   MANUAL DOWNLOAD MODE
========================================================= */

if (isset($_GET['id'])) {

    $order_id = (int)$_GET['id'];

    $file = generateInvoice($conn, $order_id, true);

    if (!$file) {
        die("Invoice not found");
    }
}
?>