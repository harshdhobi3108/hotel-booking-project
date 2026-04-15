<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>

    <style>
        /* ===== PREMIUM PRIVACY PAGE ===== */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f3ff, #efe7ff);
        }

        /* Container */
        .policy-container {
            max-width: 1000px;
            margin: 80px auto;
            padding: 50px 40px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(90, 24, 154, 0.15);
        }

        /* Header */
        .policy-title {
            font-size: 34px;
            font-weight: 700;
            color: #3c096c;
            margin-bottom: 8px;
        }

        .policy-subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 15px;
        }

        /* Section Card */
        .policy-section {
            background: #ffffff;
            border-radius: 14px;
            padding: 22px 24px;
            margin-bottom: 20px;
            border-left: 4px solid #7b2cbf;
            box-shadow: 0 8px 25px rgba(90, 24, 154, 0.08);
            transition: all 0.3s ease;
        }

        /* Hover effect */
        .policy-section:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(90, 24, 154, 0.15);
        }

        /* Section Title */
        .policy-section h3 {
            color: #5a189a;
            margin-bottom: 8px;
            font-size: 18px;
        }

        /* Text */
        .policy-section p {
            color: #444;
            line-height: 1.7;
            font-size: 14px;
        }

        /* ===== MOBILE ===== */

        @media (max-width: 768px) {

            .policy-container {
                margin: 60px 15px;
                padding: 30px 20px;
                border-radius: 16px;
            }

            .policy-title {
                font-size: 26px;
            }

            .policy-subtitle {
                font-size: 14px;
                margin-bottom: 25px;
            }

            .policy-section {
                padding: 18px;
            }

            .policy-section h3 {
                font-size: 16px;
            }

            .policy-section p {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<div class="policy-container">

    <h1 class="policy-title">Privacy Policy</h1>
    <p class="policy-subtitle">We respect your privacy and are committed to protecting your data.</p>

    <div class="policy-section">
        <h3>Information Collection</h3>
        <p>
            We collect personal details such as your name, email, and booking information to provide a seamless hotel booking experience.
        </p>
    </div>

    <div class="policy-section">
        <h3>Usage of Data</h3>
        <p>
            Your data is used to process bookings, improve services, and provide customer support. We never sell your data.
        </p>
    </div>

    <div class="policy-section">
        <h3>Data Security</h3>
        <p>
            We implement secure technologies and encryption methods to protect your personal information from unauthorized access.
        </p>
    </div>

    <div class="policy-section">
        <h3>User Rights</h3>
        <p>
            You have the right to access, update, or delete your personal data at any time through your account settings.
        </p>
    </div>

</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>