<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Auto redirect -->
    <meta http-equiv="refresh" content="2;url=/hotel-booking/admin/login.php">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e293b, #0f172a);

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
            padding: 20px;

            color: white;
        }

        .logout-box {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 18px;
            text-align: center;

            width: 100%;
            max-width: 380px;

            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .logout-box h2 {
            margin-bottom: 10px;
            font-size: 22px;
            font-weight: 600;
        }

        .logout-box p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .loader {
            margin: 20px auto;
            width: 42px;
            height: 42px;
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
            margin-top: 18px;
            display: inline-block;
            padding: 12px 18px;
            width: 100%;

            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            text-decoration: none;
            border-radius: 10px;

            font-size: 14px;
            font-weight: 500;

            transition: 0.3s;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
        }

        /* ===== MOBILE PERFECT ===== */
        @media (max-width: 480px) {

            .logout-box {
                padding: 25px;
                border-radius: 14px;
            }

            .logout-box h2 {
                font-size: 20px;
            }

            .loader {
                width: 36px;
                height: 36px;
            }
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