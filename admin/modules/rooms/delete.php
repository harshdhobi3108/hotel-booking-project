<?php
require_once '../../includes/db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM rooms WHERE id=$id");

header("Location: list.php");