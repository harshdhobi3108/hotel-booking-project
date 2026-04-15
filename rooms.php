<?php
require_once("includes/config.php");

// ================= SORT FEATURE (ADDED) =================
$sort = $_GET['sort'] ?? '';

$query = "SELECT * FROM rooms";

if ($sort == "price_low") {
    $query .= " ORDER BY price ASC";
} elseif ($sort == "price_high") {
    $query .= " ORDER BY price DESC";
} elseif ($sort == "rating") {
    $query .= " ORDER BY rating DESC";
}

$result = $conn->query($query);

include("includes/header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rooms - HotelLux</title>

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

/* ================= CONTAINER ================= */
.container {
    padding: 50px 60px;
}

/* ================= TOP BAR ================= */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 60px; /* 🔥 FIXED (important) */
    position: relative;
    z-index: 10;
}

.top-bar h1 {
    font-size: 32px;
    font-weight: 700;
}

.top-bar p {
    color: #6b7280;
    font-size: 15px;
}

/* ================= SORT ================= */
.sort-box {
    display: flex;
    align-items: center;
    position: relative;
}

.sort-box select {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    background: white;
    font-size: 14px;
    cursor: pointer;
    min-width: 180px;
    transition: 0.2s ease;
}

.sort-box select:hover {
    border-color: #7b2cbf;
}

.sort-box select:focus {
    outline: none;
    border-color: #7b2cbf;
    box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.1);
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

/* OVERLAY */
.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 200px;
    background: rgba(0,0,0,0.65);
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 22px;
    font-weight: bold;
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

.disabled {
    background: #9ca3af;
    pointer-events: none;
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {

    .container {
        padding: 25px 15px;
    }

    .top-bar {
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 70px; /* 🔥 extra spacing for dropdown */
    }

    .sort-box {
        width: 100%;
    }

    .sort-box select {
        width: 100%;
    }

    .sort-box select {
        background: #fff;
        border: 1px solid #ddd;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    
    .rooms {
        grid-template-columns: 1fr;
    }

    .top-bar h1 {
        font-size: 24px;
    }
}
    </style>
</head>

<body>

<div class="page-content">
<div class="container">
    <!-- TOP BAR -->
    <div class="top-bar">
        <div>
            <h1 style="margin:0;">Our Rooms</h1>
            <p style="margin:5px 0;">Handpicked luxury rooms for your perfect stay</p>
        </div>

        <!-- ===== SORT DROPDOWN (ADDED ONLY) ===== -->
        <div class="sort-box">
            <form method="GET">
                <select name="sort" onchange="this.form.submit()" size="1">
                    <option value="">Sort By</option>
                    <option value="price_low" <?php if($sort=="price_low") echo "selected"; ?>>Price: Low → High</option>
                    <option value="price_high" <?php if($sort=="price_high") echo "selected"; ?>>Price: High → Low</option>
                    <option value="rating" <?php if($sort=="rating") echo "selected"; ?>>Top Rated</option>
                </select>
            </form>
        </div>
    </div>

    <div class="rooms">

        <?php while ($room = $result->fetch_assoc()): ?>

            <?php
            // ================= BOOKING CHECK =================
            $check = $conn->prepare("
                SELECT id FROM orders 
                WHERE room_id = ? 
                AND booking_date = CURDATE() 
                AND status = 'confirmed'
            ");
            $check->bind_param("i", $room['id']);
            $check->execute();
            $isBooked = $check->get_result()->num_rows > 0;

            // ================= IMAGE FIX =================
            $imageName = $room['image'];

            // Absolute paths (for checking)
            $uploadPath = __DIR__ . "/uploads/rooms/" . $imageName;
            $assetPath  = __DIR__ . "/assets/images/" . $imageName;

            // Web paths (for browser)
            if (file_exists($uploadPath)) {
                $finalImage = "uploads/rooms/" . $imageName;
            } elseif (file_exists($assetPath)) {
                $finalImage = "assets/images/" . $imageName;
            } else {
                $finalImage = "assets/images/default.jpg";
            }
            ?>

            <div class="card">

                <div class="hot">HOT DEAL</div>

                <!-- ORIGINAL LINE KEPT (UNCHANGED) -->
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
</div>
</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>