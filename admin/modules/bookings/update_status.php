<?php
require_once("../../includes/db.php");

$id = intval($_GET['id']);
$status = $_GET['status'];

$conn->query("
    UPDATE orders 
    SET status='$status' 
    WHERE id=$id
");

header("Location: list.php");
exit;