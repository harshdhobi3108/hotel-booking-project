<?php
require_once("../../includes/auth_check.php");
require_once("../../includes/db.php");

$id = $_GET['id'];

$conn->query("DELETE FROM users WHERE id=$id");

header("Location: list.php");