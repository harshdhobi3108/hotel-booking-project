<?php
require_once("includes/config.php");
include("includes/header.php");

$result = $conn->query("SELECT * FROM rooms LIMIT 8");
?>
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

        /* ================= HOMEPAGE MOBILE FIX ================= */

        @media (max-width: 768px) {

        .container {
            padding: 20px 15px !important;
        }

        .rooms {
            grid-template-columns: 1fr !important;
            gap: 20px;
        }

        .card {
            border-radius: 12px;
        }

        .card img {
            height: 200px;
        }

        .content {
            padding: 12px;
        }

        .title {
            font-size: 16px;
        }

        .price {
            font-size: 16px;
        }

        .btn {
            padding: 10px;
            font-size: 14px;
        }

        }

        /* ================= EXTRA SMALL DEVICES ================= */

        @media (max-width: 480px) {

        .container {
            padding: 15px 10px !important;
        }

        .hero {
            padding: 60px 15px;
        }

        .hero-title {
            font-size: 1.6rem;
        }

        }
    </style>
</head>

<body>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="overlay"></div>

    <div class="hero-content-left">
        <h1 class="hero-title">
            <span id="typewriter"></span>
        </h1>

        <p>
            Experience world-class comfort, breathtaking views, and premium hospitality.
            Discover hand-picked hotels tailored for your perfect getaway.
        </p>

        <div class="hero-buttons">
            <a href="rooms.php" class="btn-primary">Explore Rooms</a>
            <a href="rooms.php" class="btn-secondary">Book Now</a>
        </div>
    </div>
</section>

<!-- ================= DYNAMIC ROOMS ================= -->
<section class="container">
    <div class="rooms">

        <?php while ($room = $result->fetch_assoc()): ?>

            <?php
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

                <!-- ✅ FIXED IMAGE -->
                <img src="/hotel-booking/<?php echo htmlspecialchars($room['image']); ?>" alt="room">

                <?php if ($isBooked): ?>
                    <div class="overlay">BOOKED</div>
                <?php endif; ?>

                <div class="content">

                    <div class="title">
                        <?php echo htmlspecialchars($room['name']); ?>
                        <span class="rating">⭐ <?php echo htmlspecialchars($room['rating']); ?></span>
                    </div>

                    <div class="info">
                        HotelLux, Ahmedabad<br>
                        WiFi • AC • Breakfast
                    </div>

                    <div class="bottom-row">

                        <div class="price">₹<?php echo htmlspecialchars($room['price']); ?></div>

                        <?php if ($isBooked): ?>
                            <a class="btn disabled">Booked</a>

                        <?php else: ?>

                            <?php if (isset($_SESSION['user_email'])): ?>
                                <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn">
                                    Book Now
                                </a>
                            <?php else: ?>
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
</section>

<script src="/hotel-booking/assets/js/script.js"></script>

</body>
</html>

<?php include 'includes/footer.php'; ?>