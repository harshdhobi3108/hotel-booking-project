<?php
require_once("includes/config.php");

// ✅ Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 BLOCK UNAUTHORIZED USERS
if (!isset($_SESSION['user_email'])) {
    header("Location: /hotel-booking/auth/login.php");
    exit();
}

// ✅ Redirect if no room_id
if (!isset($_GET['room_id'])) {
    header("Location: rooms.php");
    exit();
}

$room_id = $_GET['room_id'];

// ✅ Fetch room securely
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room) {
    echo "Room not found!";
    exit();
}

include("includes/header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Room - HotelLux</title>

    <style>
        body {
            font-family: 'Segoe UI';
            background: #f5f7fb;
            margin: 0;
        }

        .booking-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 40px;
            padding: 60px;
        }

        .room-preview {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .room-preview img {
            width: 100%;
            height: 260px;
            object-fit: cover;
        }

        .room-details {
            padding: 20px;
        }

        .room-details h2 {
            margin-bottom: 5px;
        }

        .location {
            color: gray;
            font-size: 14px;
        }

        .features {
            font-size: 13px;
            color: #666;
            margin: 8px 0;
        }

        .price-box {
            margin-top: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #7b2cbf;
        }

        .price-box span {
            font-size: 14px;
            color: gray;
        }

        .booking-card {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            position: sticky;
            top: 100px;
        }

        .booking-card h2 {
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-size: 13px;
            margin-bottom: 5px;
            color: #555;
        }

        .input-group input,
        .input-group select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            outline: none;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #7b2cbf;
        }

        .input-row {
            display: flex;
            gap: 10px;
        }

        .pay-btn {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: linear-gradient(135deg, #7b2cbf, #9d4edd);
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(123,44,191,0.3);
        }

        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 20px 60px;
            border-radius: 10px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 20px;
            }

            .room-preview img {
                height: 200px;
            }

            .booking-card {
                position: static;
                padding: 20px;
            }

            .input-row {
                flex-direction: column;
            }

            .pay-btn {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>

<?php
$showSuccess = false;

if (isset($_GET['success']) && isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];

    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_email = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $showSuccess = true;
    }
}
?>

<?php if ($showSuccess): ?>
    <div class="success-box">
        ✅ Payment Successful & Booking Confirmed
    </div>
<?php endif; ?>

<div class="booking-container">

    <!-- LEFT -->
    <div class="room-preview">

        <!-- ✅ FIXED IMAGE -->
        <img src="/hotel-booking/<?php echo htmlspecialchars($room['image']); ?>" alt="room">

        <div class="room-details">
            <h2><?php echo htmlspecialchars($room['name']); ?></h2>

            <p class="location">HotelLux, Ahmedabad</p>
            <p class="features">WiFi • AC • Breakfast</p>

            <div class="price-box">
                ₹<?php echo htmlspecialchars($room['price']); ?> <span>/ night</span>
            </div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="booking-card">

        <h2>Complete Your Booking</h2>

        <form id="bookingForm">

            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
            <input type="hidden" name="amount" value="<?php echo $room['price']; ?>">

            <div class="input-group">
                <label>Full Name</label>
                <input type="text" value="<?php echo $_SESSION['user_name']; ?>" readonly>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" value="<?php echo $_SESSION['user_email']; ?>" readonly>
            </div>

            <div class="input-row">
                <div class="input-group">
                    <label>Check-in</label>
                    <input type="date" name="check_in" required>
                </div>

                <div class="input-group">
                    <label>Check-out</label>
                    <input type="date" name="check_out" required>
                </div>
            </div>

            <div class="input-group">
                <label>Time</label>
                <select name="time" required>
                    <option value="">Select Time</option>
                    <option>10:00 AM</option>
                    <option>12:00 PM</option>
                    <option>2:00 PM</option>
                    <option>4:00 PM</option>
                </select>
            </div>

            <button class="pay-btn" type="submit">
                Proceed to Payment →
            </button>

        </form>

    </div>

</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="/hotel-booking/assets/js/script.js"></script>

</body>
</html>

<?php include 'includes/footer.php'; ?>