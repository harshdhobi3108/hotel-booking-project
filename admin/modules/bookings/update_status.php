<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $order_id = intval($_POST['order_id']);

    $query = "UPDATE orders 
              SET booking_status = 'cancelled',
                  cancelled_at = NOW()
              WHERE id = $order_id";

    if ($conn->query($query)) {
        header("Location: list.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}