<?php
session_start();

// If already logged in → redirect
if (isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

// Error message
$error = "";
if (isset($_GET['error'])) {
    $error = "Invalid email or password!";
}

// Success message (from register)
$success = "";
if (isset($_GET['success'])) {
    $success = "Registration Successful! Please login.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Hotel Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #240046, #5a189a, #9d4edd);
}

.auth-container {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.auth-card {
  width: 400px;
  padding: 40px 30px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  text-align: center;
  color: white;
}

.auth-card h2 {
  font-weight: 600;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 14px;
  color: #ddd;
  margin-bottom: 20px;
}

/* SUCCESS */
.success {
  background: rgba(46, 204, 113, 0.15);
  color: #2ecc71;
  padding: 10px;
  border-radius: 10px;
  margin-bottom: 15px;
  font-size: 13px;
}

/* ERROR */
.error {
  color: #ff6b6b;
  font-size: 13px;
  margin-bottom: 10px;
}

/* INPUT */
.input-group {
  position: relative;
  margin-bottom: 20px;
}

.input-group input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.3);
  background: transparent;
  color: white;
  outline: none;
}

.input-group label {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  font-size: 13px;
  color: #ccc;
  transition: 0.3s;
}

.input-group input:focus + label,
.input-group input:valid + label {
  top: -8px;
  font-size: 11px;
  color: #c77dff;
}

/* BUTTON */
.login-btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #7b2cbf, #9d4edd);
  color: white;
  cursor: pointer;
}

.login-btn:hover {
  transform: translateY(-2px);
}

/* GOOGLE */
.google-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px;
  border-radius: 10px;
  text-decoration: none;
  background: white;
  color: #333;
  margin-top: 15px;
}

.google-btn img {
  width: 18px;
}

.divider {
  margin: 20px 0;
  font-size: 12px;
  color: #ccc;
}

.links {
  margin-top: 15px;
  font-size: 13px;
}

.links a {
  color: #e0aaff;
  text-decoration: none;
}
  </style>
</head>

<body>

<div class="auth-container">
  <div class="auth-card">

    <h2>HotelLux</h2>
    <p class="subtitle">Login to continue</p>

    <!-- SUCCESS MESSAGE -->
    <?php if ($success): ?>
      <div class="success" id="successMsg"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- ERROR MESSAGE -->
    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form action="/hotel-booking/auth/login_process.php" method="POST">

      <div class="input-group">
        <input type="email" name="email" required>
        <label>Email</label>
      </div>

      <div class="input-group">
        <input type="password" name="password" required>
        <label>Password</label>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="divider">OR</div>

    <a href="google_auth.php" class="google-btn">
      <img src="https://cdn-icons-png.flaticon.com/512/281/281764.png">
      Continue with Google
    </a>

    <div class="links">
      <p>Don’t have an account? <a href="register.php">Sign up</a></p>
      <p><a href="#">Forgot password?</a></p>
    </div>

  </div>
</div>

<!-- AUTO HIDE SUCCESS -->
<script>
setTimeout(() => {
  const msg = document.getElementById('successMsg');
  if(msg) msg.style.display = 'none';
}, 3000);
</script>

</body>
</html>