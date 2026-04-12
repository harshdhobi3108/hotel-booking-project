<?php
session_start();
require_once("includes/db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {

        $stmt = $conn->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {

                // SESSION SET
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name']  = $admin['name'];

                header("Location: /hotel-booking/admin/dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Admin not found!";
        }

    } else {
        $error = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | HotelLux</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- LINK CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <h2>Admin Login</h2>

        <?php if (!empty($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST" autocomplete="off">

            <div class="form-group">
                <label>Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="Enter admin email"
                    required
                >
            </div>

            <div class="form-group">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Enter password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">Login</button>

        </form>

        <div class="login-footer">
            HotelLux Admin Panel
        </div>

    </div>
</div>

</body>
</html>