<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>

    <!-- Auto redirect after 2 seconds -->
    <meta http-equiv="refresh" content="2;url=/hotel-booking/admin/login.php">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .logout-box {
            background: rgba(255,255,255,0.05);
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .logout-box h2 {
            margin-bottom: 10px;
        }

        .logout-box p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .loader {
            margin: 20px auto;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255,255,255,0.2);
            border-top: 4px solid #ef4444;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-login {
            margin-top: 15px;
            display: inline-block;
            padding: 10px 18px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

<div class="logout-box">
    <h2>Logging Out...</h2>
    <div class="loader"></div>
    <p>You are being securely logged out.</p>

    <a href="/hotel-booking/admin/login.php" class="btn-login">
        Go to Login
    </a>
</div>

</body>
</html>