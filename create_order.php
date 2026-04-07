<?php
require 'vendor/autoload.php';
require 'includes/config.php';

use Razorpay\Api\Api;

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

$amount = $_GET['amount'] ?? 10000;

$order = $api->order->create([
    'receipt' => 'order_' . rand(),
    'amount' => $amount,
    'currency' => 'INR'
]);

echo json_encode([
    'order_id' => $order['id'],
    'amount' => $amount
]);