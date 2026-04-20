<?php
require_once("includes/config.php");
include("includes/header.php");

$result = $conn->query("SELECT * FROM rooms LIMIT 8");
?>
    <style>

/* ================= RESET ================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', 'Inter', sans-serif;
    background: #f6f7fb;
    color: #1a1a1a;
}

/* ================= HERO ================= */
.hero {
    height: 90vh;
    background: 
        linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
        url('assets/images/hotel.jpg') center/cover no-repeat;
    display: flex;
    align-items: center;
    padding: 0 60px;
}

.hero-content {
    color: white;
    max-width: 500px;
}

.hero h1 {
    font-size: 48px;
    margin-bottom: 15px;
}

.hero p {
    font-size: 16px;
    margin-bottom: 20px;
}

.hero-buttons {
    display: flex;
    gap: 15px;
}

.btn-outline {
    padding: 10px 18px;
    border: 2px solid white;
    color: white;
    border-radius: 10px;
    text-decoration: none;
}

/* ================= ROOM SECTION ================= */
.room-section {
    background: #f6f7fb;
    padding: 80px 0 100px;
    position: relative;
}

/* ================= HEADER ================= */
.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-header h2 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
}

.section-header p {
    color: #6b7280;
    font-size: 15px;
}

/* ================= CONTAINER ================= */
.container {
    padding: 0 60px;
}

/* ================= GRID ================= */
.rooms {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 28px;
}

/* ================= CARD ================= */
.card {
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transition: 0.3s ease;
    position: relative;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
}

.card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

/* HOT */
.hot {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, #ff4d6d, #ff758f);
    color: #fff;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
}

/* CONTENT */
.content {
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.title {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
}

.rating {
    color: #f59e0b;
}

.info {
    font-size: 13px;
    color: #9ca3af;
}

.bottom-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price {
    font-weight: 700;
}

/* BUTTON */
.btn {
    padding: 9px 16px;
    background: linear-gradient(135deg, #7b2cbf, #9d4edd);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
}

.btn:hover {
    box-shadow: 0 6px 14px rgba(123,44,191,0.3);
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {

    .container {
        padding: 0 15px;
    }

    .hero {
        padding: 0 20px;
        height: 70vh;
    }

    .rooms {
        grid-template-columns: 1fr;
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
<section class="room-section">
    <div class="container">

        <div class="section-header">
            <h2>Our Featured Rooms</h2>
            <p>Handpicked stays for comfort, luxury, and unforgettable experience</p>
        </div>

        <div class="rooms">

        <?php while ($room = $result->fetch_assoc()): ?>

            <?php
            $check = $conn->prepare("
            SELECT id FROM orders
            WHERE room_id = ?
            AND CURDATE() BETWEEN booking_date AND check_out
            AND booking_status = 'confirmed'
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
                        <span><?php echo htmlspecialchars($room['name']); ?></span>
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
    </div>
</section>

<script src="/hotel-booking/assets/js/script.js"></script>

</body>
</html>

<?php include 'includes/footer.php'; ?>