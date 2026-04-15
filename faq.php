<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>

    <style>
        /* ===== PREMIUM FAQ PAGE ===== */
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

        /* Title */
        .policy-title {
            font-size: 34px;
            font-weight: 700;
            color: #3c096c;
            margin-bottom: 10px;
        }

        .policy-subtitle {
            color: #666;
            margin-bottom: 35px;
            font-size: 15px;
        }

        /* FAQ Card */
        .faq-item {
            border-radius: 14px;
            margin-bottom: 18px;
            background: #ffffff;
            border: 1px solid #e0aaff;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Hover effect */
        .faq-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(90, 24, 154, 0.15);
        }

        /* Question */
        .faq-question {
            padding: 18px 22px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #240046;
        }

        /* Icon */
        .icon {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        /* Answer (smooth animation) */
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            padding: 0 22px;
            color: #555;
            font-size: 14px;
            transition: all 0.4s ease;
        }

        /* Active */
        .faq-item.active .faq-answer {
            max-height: 200px;
            padding: 0 22px 18px;
        }

        .faq-item.active .icon {
            transform: rotate(45deg);
        }

        /* ===== MOBILE RESPONSIVE ===== */

        @media (max-width: 768px) {

            .policy-container {
                margin: 60px 15px;
                padding: 30px 20px;
                border-radius: 16px;
            }

            .policy-title {
                font-size: 26px;
                line-height: 1.3;
            }

            .policy-subtitle {
                font-size: 14px;
                margin-bottom: 25px;
            }

            .faq-question {
                font-size: 14px;
                padding: 15px;
            }

            .faq-answer {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<div class="policy-container">
    <h1 class="policy-title">Frequently Asked Questions</h1>
    <p class="policy-subtitle">Quick answers to common questions.</p>

    <div class="faq-item">
        <div class="faq-question">How can I book a hotel? <span>+</span></div>
        <div class="faq-answer">Select hotel, choose dates, and confirm booking.</div>
    </div>

    <div class="faq-item">
        <div class="faq-question">Can I cancel my booking? <span>+</span></div>
        <div class="faq-answer">Yes, cancellation depends on hotel policy.</div>
    </div>

    <div class="faq-item">
        <div class="faq-question">Is refund available? <span>+</span></div>
        <div class="faq-answer">Refund depends on cancellation timing.</div>
    </div>

</div>

<script>
document.querySelectorAll(".faq-question").forEach(item => {
    item.addEventListener("click", () => {
        const parent = item.parentElement;

        document.querySelectorAll(".faq-item").forEach(el => {
            if (el !== parent) el.classList.remove("active");
        });

        parent.classList.toggle("active");
    });
});
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>