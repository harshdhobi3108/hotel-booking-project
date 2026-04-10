<?php
require_once("includes/config.php");

$result = $conn->query("SELECT * FROM rooms");

include("includes/header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rooms - HotelLux</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI';
            background: #f4f6f9;
        }

        .container {
            padding: 40px;
        }

        .rooms {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: 0.3s;
            position: relative;
        }

        .card:hover {
            transform: translateY(-6px);
        }

        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .hot {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #ff4d6d;
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 180px;
            background: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

       .content {
          padding: 15px;
          display: flex;
          flex-direction: column;
          gap: 8px;
      }

      .bottom-row {
          display: flex;
          justify-content: space-between;
          align-items: center;
      }

        .title {
            font-size: 18px;
            font-weight: 600;
        }

        .rating {
            float: right;
            color: orange;
        }

        .info {
            font-size: 13px;
            color: gray;
            margin-top: 5px;
        }

        .price {
            font-weight: bold;
            margin-top: 10px;
        }

        .btn {
            padding: 8px 14px;
            background: #7b2cbf;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn:hover {
            background: #5a189a;
        }

        .disabled {
            background: gray;
            pointer-events: none;
        }
    </style>
</head>

<body>

<div class="container">
    <h1>Our Rooms</h1>
    <p>Choose from our premium selection</p>

    <div class="rooms">

        <?php while ($room = $result->fetch_assoc()): ?>

            <?php
            // ✅ FIXED QUERY (booking_date instead of date)
            $check = $conn->prepare("
                SELECT id FROM orders 
                WHERE room_id = ? 
                AND booking_date = CURDATE() 
                AND status = 'confirmed'
            ");
            $check->bind_param("i", $room['id']);
            $check->execute();
            $isBooked = $check->get_result()->num_rows > 0;
            ?>

            <div class="card">

                <div class="hot">HOT DEAL</div>

                <img src="/hotel-booking/assets/images/<?php echo $room['image']; ?>" alt="room">

                <?php if ($isBooked): ?>
                    <div class="overlay">BOOKED</div>
                <?php endif; ?>

                <div class="content">
                    <div class="title">
                        <?php echo $room['name']; ?>
                        <span class="rating">⭐ <?php echo $room['rating']; ?></span>
                    </div>

                    <div class="info">
                        HotelLux, Ahmedabad<br>
                        WiFi • AC • Breakfast
                    </div>

                    <div class="bottom-row">
                        <div class="price">₹<?php echo $room['price']; ?></div>

                        <?php if ($isBooked): ?>
    <a class="btn disabled">Booked</a>

<?php else: ?>

    <?php if (isset($_SESSION['user_email'])): ?>
        <!-- ✅ Logged in user -->
        <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn">
            Book Now
        </a>

    <?php else: ?>
        <!-- ❌ Not logged in -->
        <a href="/hotel-booking/auth/login.php" class="btn">
            Login to Book
        </a>
    <?php endif; ?>

<?php endif; ?>
                    </div>
                </div>

            </div>

        <?php endwhile; ?>

    </div>
</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>