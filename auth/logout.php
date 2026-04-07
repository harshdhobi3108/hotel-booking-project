<?php
session_start();
session_destroy();

// Correct absolute path
header("Location: /hotel-booking/index.php");
exit();