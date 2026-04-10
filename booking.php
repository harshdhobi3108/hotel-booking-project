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
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            display: flex;
            gap: 30px;
            padding: 40px;
        }

        /* LEFT SIDE */
        .room-card {
            width: 40%;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .room-card img {
            width: 100%;
            border-radius: 10px;
        }

        /* RIGHT SIDE */
        .booking-form {
            width: 60%;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn {
            background: #7b2cbf;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn:hover {
            background: #5a189a;
        }

        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 20px 40px;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<!-- ✅ SUCCESS MESSAGE -->
<?php
$showSuccess = false;

if (isset($_GET['success']) && isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_email = ? ORDER BY id DESC LIMIT 1");
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

<div class="container">

    <!-- LEFT SIDE -->
    <div class="room-card">
        <img src="/hotel-booking/assets/images/<?php echo $room['image']; ?>" alt="room">

        <h2><?php echo $room['name']; ?></h2>

        <p style="font-weight:bold; font-size:18px;">
            ₹<?php echo $room['price']; ?>
        </p>

        <p style="color:gray;">
            HotelLux, Ahmedabad <br>
            WiFi • AC • Breakfast
        </p>
    </div>

    <!-- RIGHT SIDE -->
    <div class="booking-form">
        <h2>Complete Your Booking</h2>

        <form id="bookingForm">

            <!-- HIDDEN DATA -->
            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
            <input type="hidden" name="amount" value="<?php echo $room['price']; ?>">

            <!-- ✅ USER FROM SESSION (SECURE) -->
            <input type="text" name="name" value="<?php echo $_SESSION['user_name']; ?>" readonly>
            <input type="email" name="email" value="<?php echo $_SESSION['user_email']; ?>" readonly>

            <!-- DATE -->
            <label>Select Date</label>
            <input type="date" name="date" required>

            <!-- TIME -->
            <label>Select Time</label>
            <select name="time" required>
                <option value="">Select Time</option>
                <option>10:00 AM</option>
                <option>12:00 PM</option>
                <option>2:00 PM</option>
                <option>4:00 PM</option>    
            </select>

            <button class="btn" type="submit">Proceed to Payment</button>

        </form>
    </div>

</div>

<!-- ✅ Razorpay -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- ✅ Your JS -->
<script src="/hotel-booking/assets/js/script.js"></script>

</body>
</html>