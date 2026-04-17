<?php
require_once("includes/config.php");
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

/*
|--------------------------------------------------------------------------
| HotelLux - invoice.php (Updated Professional Version)
|--------------------------------------------------------------------------
| Features:
| ✅ Reusable generateInvoice($order_id)
| ✅ Auto save PDF for email attachment
| ✅ Manual browser download support
| ✅ Secure booking fetch
| ✅ Clean structure
|--------------------------------------------------------------------------
*/

/* ================= SESSION ================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= GENERATE FUNCTION ================= */

function generateInvoice($conn, $order_id, $download = false)
{
    $order_id = (int) $order_id;

    /* ================= FETCH ORDER ================= */

    $query = "
        SELECT o.*, r.name AS room_name
        FROM orders o
        JOIN rooms r ON o.room_id = r.id
        WHERE o.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        return false;
    }

    /* ================= FETCH PAYMENT ================= */

    $payQuery = "
        SELECT *
        FROM payments
        WHERE order_id = ?
        LIMIT 1
    ";

    $stmt2 = $conn->prepare($payQuery);
    $stmt2->bind_param("s", $order['razorpay_order_id']);
    $stmt2->execute();

    $payment = $stmt2->get_result()->fetch_assoc();

    /* ================= CALCULATIONS ================= */

    $amount = (float) $order['amount'];

    $gst  = round($amount * 0.18, 2);
    $base = round($amount - $gst, 2);

    $checkin  = new DateTime($order['booking_date']);
    $checkout = new DateTime($order['check_out']);

    $nights = max(1, $checkin->diff($checkout)->days);

    /* ================= HTML ================= */

    $html = '
    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            color:#333;
            padding:25px;
            font-size:13px;
        }

        .header{
            border-bottom:2px solid #eee;
            padding-bottom:15px;
        }

        .brand{
            font-size:28px;
            font-weight:bold;
            color:#7b2cbf;
        }

        .tagline{
            color:#777;
            font-size:12px;
        }

        .invoice-box{
            margin-top:20px;
        }

        .meta{
            margin-top:10px;
            line-height:1.8;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        th{
            background:#f4f4f4;
            padding:10px;
            text-align:left;
        }

        td{
            padding:10px;
            border-bottom:1px solid #eee;
        }

        .totals{
            margin-top:20px;
            width:300px;
            float:right;
        }

        .totals td{
            border:none;
            padding:6px;
        }

        .grand{
            font-weight:bold;
            font-size:16px;
        }

        .footer{
            clear:both;
            margin-top:60px;
            text-align:center;
            color:#777;
            font-size:12px;
        }

        .paid{
            display:inline-block;
            padding:5px 10px;
            background:#d1fae5;
            color:#065f46;
            border-radius:6px;
            font-size:11px;
            font-weight:bold;
        }
    </style>

    <div class="header">
        <div class="brand">HotelLux</div>
        <div class="tagline">Luxury stays. Affordable comfort.</div>
    </div>

    <div class="invoice-box">

        <h2>Invoice #' . $order['id'] . '</h2>

        <div class="meta">
            Date: ' . date("d M Y", strtotime($order['created_at'])) . '<br>
            <span class="paid">PAID</span>
        </div>

        <br>

        <strong>Billed To:</strong><br>
        ' . htmlspecialchars($order['user_name']) . '<br>
        ' . htmlspecialchars($order['email']) . '

        <br><br>

        <strong>Payment Details:</strong><br>
        Receipt: ' . htmlspecialchars($order['receipt']) . '<br>
        Razorpay Order: ' . htmlspecialchars($order['razorpay_order_id']) . '<br>
        Payment ID: ' . htmlspecialchars($payment['payment_id'] ?? 'N/A') . '

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
                    <td>' . htmlspecialchars($order['room_name']) . '</td>
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
                <td>₹' . number_format($base, 2) . '</td>
            </tr>

            <tr>
                <td>GST (18%)</td>
                <td>₹' . number_format($gst, 2) . '</td>
            </tr>

            <tr class="grand">
                <td>Total Paid</td>
                <td>₹' . number_format($amount, 2) . '</td>
            </tr>
        </table>

        <div class="footer">
            Thank you for choosing <strong>HotelLux</strong><br>
            support@hotellux.com | +91 9876543210<br>
            Digitally generated invoice.
        </div>

    </div>
    ';

    /* ================= DOMPDF ================= */

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    /* ================= FOLDER ================= */

    $folder = __DIR__ . '/uploads/invoices/';

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $filePath = $folder . "HotelLux_Invoice_" . $order_id . ".pdf";

    file_put_contents($filePath, $dompdf->output());

    /* ================= DOWNLOAD MODE ================= */

    if ($download) {
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=HotelLux_Invoice_$order_id.pdf");
        readfile($filePath);
        exit;
    }

    return $filePath;
}

/* ================= MANUAL DOWNLOAD ================= */

if (isset($_GET['id'])) {

    $order_id = (int) $_GET['id'];

    $file = generateInvoice($conn, $order_id, true);

    if (!$file) {
        die("Invoice not found");
    }
}
?>