<?php
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$_SESSION['user'] = [
  "name" => $data['name'],
  "email" => $data['email']
];

echo json_encode(["status" => "success"]);