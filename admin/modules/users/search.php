<?php
require_once("../../includes/db.php");

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

$stmt = $conn->prepare("
    SELECT * FROM users 
    WHERE name LIKE ? OR email LIKE ?
    ORDER BY id DESC
");

$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);