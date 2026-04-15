<?php
session_start();

if (isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

$error = "";
if (isset($_GET['error'])) {
    $error = "Something went wrong!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Hotel Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- GOOGLE FONT -->
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
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
      text-align: center;
      color: white;
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(25px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .auth-card h2 {
      font-weight: 600;
      margin-bottom: 8px;
    }

    .subtitle {
      font-size: 14px;
      color: #ddd;
      margin-bottom: 25px;
    }

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
      font-size: 14px;
    }

    .input-group label {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      font-size: 13px;
      color: #ccc;
      pointer-events: none;
      transition: 0.3s;
    }

    .input-group input:focus + label,
    .input-group input:valid + label {
      top: -8px;
      font-size: 11px;
      color: #c77dff;
    }

    .login-btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, #7b2cbf, #9d4edd);
      color: white;
      font-weight: 500;
      cursor: pointer;
      transition: 0.3s;
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(157, 78, 221, 0.4);
    }

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
      font-weight: 500;
      transition: 0.3s;
    }

    .google-btn img {
      width: 18px;
    }

    .google-btn:hover {
      transform: scale(1.02);
    }

    .divider {
      margin: 20px 0;
      font-size: 12px;
      color: #ccc;
      display: flex;
      align-items: center;
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .divider span {
      margin: 0 10px;
    }

    .links {
      margin-top: 15px;
      font-size: 13px;
    }

    .links a {
      color: #e0aaff;
      text-decoration: none;
      font-weight: 500;
    }

    .links a:hover {
      text-decoration: underline;
    }

    .error {
      color: #ff6b6b;
      font-size: 13px;
      margin-bottom: 10px;
    }

    /* ================= RESPONSIVE FIX ================= */

    /* Tablets */
    @media (max-width: 768px) {
      .auth-card {
        width: 90%;
        padding: 30px 20px;
      }

      .auth-card h2 {
        font-size: 20px;
      }

      .subtitle {
        font-size: 13px;
      }

      .login-btn,
      .google-btn {
        padding: 11px;
        font-size: 14px;
      }
    }

    /* Mobile */
    @media (max-width: 480px) {
      body {
        padding: 15px;
        align-items: flex-start; /* 👈 important for small screens */
      }

      .auth-card {
        width: 100%;
        padding: 25px 15px;
        border-radius: 14px;
        margin-top: 20px;
      }

      .auth-card h2 {
        font-size: 18px;
      }

      .subtitle {
        font-size: 12px;
      }

      .input-group input {
        padding: 10px;
        font-size: 14px;
      }

      .login-btn {
        padding: 10px;
        font-size: 14px;
      }

      .google-btn {
        padding: 10px;
        font-size: 13px;
      }

      .links {
        font-size: 12px;
      }
    }

    /* ================= FINAL PERFECT CENTER FIX ================= */

/* Fix full height stretching */
body {
  min-height: 100dvh;
  height: auto;
}

/* Make container control spacing properly */
.auth-container {
  min-height: 100dvh;
}

/* Mobile optimization */
@media (max-width: 480px) {

  .auth-container {
    align-items: flex-start; /* move slightly down */
    padding-top: 30px;
    padding-bottom: 30px;
  }

  .auth-card {
    margin: 0 auto;
  }
}
  </style>

</head>
<body>

<div class="auth-container">
  <div class="auth-card">

    <h2>HotelLux</h2>
    <p class="subtitle">Join us and start booking hotel rooms</p>

    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="/hotel-booking/auth/register_process.php" method="POST">

      <div class="input-group">
        <input type="text" name="name" required>
        <label>Full Name</label>
      </div>

      <div class="input-group">
        <input type="email" name="email" required>
        <label>Email</label>
      </div>

      <div class="input-group">
        <input type="password" name="password" required>
        <label>Password</label>
      </div>

      <div class="input-group">
        <input type="password" name="confirm_password" required>
        <label>Confirm Password</label>
      </div>

      <button type="submit" class="login-btn">Sign Up</button>
    </form>

    <div class="divider"><span>OR</span></div>

    <a href="google_auth.php" class="google-btn">
      <img src="https://cdn-icons-png.flaticon.com/512/281/281764.png">
      Continue with Google
    </a>

    <div class="links">
      <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

  </div>
</div>

</body>
</html>