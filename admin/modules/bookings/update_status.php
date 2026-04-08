<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

$id = $_GET['id'];
$status = $_GET['status'];

$stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

header("Location: list.php");