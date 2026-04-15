<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions</title>

    <style>
        /* ===== PREMIUM TERMS PAGE ===== */
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
            display: flex;
            gap: 15px;
            background: #ffffff;
            border-radius: 14px;
            padding: 22px 24px;
            margin-bottom: 20px;
            border-left: 4px solid #7b2cbf;
            box-shadow: 0 8px 25px rgba(90, 24, 154, 0.08);
            transition: all 0.3s ease;
        }

        /* Hover */
        .policy-section:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(90, 24, 154, 0.15);
        }

        /* Number badge */
        .section-number {
            min-width: 35px;
            height: 35px;
            background: #7b2cbf;
            color: #fff;
            font-weight: 600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Content */
        .section-content h3 {
            color: #5a189a;
            margin-bottom: 6px;
            font-size: 18px;
        }

        .section-content p {
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
                flex-direction: column;
                padding: 18px;
            }

            .section-number {
                width: 30px;
                height: 30px;
                font-size: 13px;
            }

            .section-content h3 {
                font-size: 16px;
            }

            .section-content p {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<div class="policy-container">

    <h1 class="policy-title">Terms & Conditions</h1>
    <p class="policy-subtitle">Please read carefully before using our service.</p>

    <div class="policy-section">
        <div class="section-number">1</div>
        <div class="section-content">
            <h3>Booking Terms</h3>
            <p>All bookings are subject to availability and confirmation from the hotel.</p>
        </div>
    </div>

    <div class="policy-section">
        <div class="section-number">2</div>
        <div class="section-content">
            <h3>Cancellation Policy</h3>
            <p>Cancellation policies vary depending on the hotel. Please review before booking.</p>
        </div>
    </div>

    <div class="policy-section">
        <div class="section-number">3</div>
        <div class="section-content">
            <h3>User Responsibility</h3>
            <p>Users must provide accurate information and comply with platform rules.</p>
        </div>
    </div>

    <div class="policy-section">
        <div class="section-number">4</div>
        <div class="section-content">
            <h3>Limitation of Liability</h3>
            <p>We are not responsible for issues caused by third-party hotel services.</p>
        </div>
    </div>

</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>