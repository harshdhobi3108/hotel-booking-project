<?php
require_once("includes/config.php");

// ✅ SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 AUTH
if (!isset($_SESSION['user_id'])) {
    header("Location: /hotel-booking/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ FETCH BOOKINGS
$stmt = $conn->prepare("
    SELECT o.*, r.name AS room_name, r.image 
    FROM orders o
    JOIN rooms r ON o.room_id = r.id
    WHERE o.user_id = ?
    AND o.status = 'confirmed'
    ORDER BY o.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ HEADER
include("includes/header.php");
?>

<style>
.page-container {
    max-width: 1200px;
    margin: 40px auto 60px;
    padding: 0 20px;
}

.page-title {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 25px;
}

.bookings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

.booking-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    transition: 0.3s;
}

.booking-card:hover {
    transform: translateY(-5px);
}

.booking-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.card-body {
    padding: 16px;
}

.card-body h3 {
    margin: 0;
    font-size: 18px;
}

.meta {
    font-size: 13px;
    color: #666;
    margin-top: 6px;
    line-height: 1.5;
}

.price {
    margin-top: 8px;
    font-weight: 600;
    color: #7b2cbf;
}

.rating {
    margin-top: 10px;
}

.rated-text {
    font-size: 13px;
    color: #555;
}

.stars {
    display: flex;
    gap: 5px;
}

.star {
    font-size: 18px;
    color: #ddd;
    cursor: pointer;
    transition: 0.2s;
}

.star:hover,
.star.active {
    color: #fbbf24;
}

#toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #7b2cbf;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    opacity: 0;
    transform: translateY(-20px);
    transition: 0.3s;
    z-index: 9999;
}

#toast.show {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .booking-card img {
        height: 160px;
    }
}

/* ================= EXTRA RESPONSIVE ================= */

@media (max-width: 992px) {
    .page-container {
        margin: 30px auto 50px;
        padding: 0 15px;
    }

    .page-title {
        font-size: 24px;
    }

    .booking-card img {
        height: 160px;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: 22px;
        text-align: center;
    }

    .bookings-grid {
        gap: 15px;
    }

    .card-body h3 {
        font-size: 16px;
    }

    .meta {
        font-size: 12px;
    }

    .price {
        font-size: 14px;
    }

    .star {
        font-size: 16px;
    }

    #toast {
        right: 10px;
        left: 10px;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .page-container {
        padding: 0 10px;
    }

    .booking-card {
        border-radius: 12px;
    }

    .booking-card img {
        height: 140px;
    }

    .card-body {
        padding: 12px;
    }

    .card-body h3 {
        font-size: 15px;
    }

    .meta {
        font-size: 11px;
    }

    .price {
        font-size: 13px;
    }

    .star {
        font-size: 15px;
    }
}

</style>

<div class="page-container">

<div class="page-title">My Bookings</div>

<div class="bookings-grid">

<?php while ($row = $result->fetch_assoc()): ?>

<div class="booking-card">

    <!-- ✅ FIXED IMAGE -->
    <img src="/hotel-booking/<?php echo htmlspecialchars($row['image']); ?>" alt="room">

    <div class="card-body">
        <h3><?php echo htmlspecialchars($row['room_name']); ?></h3>

        <div class="meta">
            📅 <?php echo $row['booking_date']; ?> → <?php echo $row['check_out']; ?><br>
            ⏰ <?php echo $row['booking_time']; ?>
        </div>

        <div class="price">₹<?php echo $row['amount']; ?></div>

        <div class="rating">
            <?php if ($row['rating']): ?>
                <div class="rated-text">
                    You rated ⭐ <?php echo $row['rating']; ?>/5
                </div>
            <?php else: ?>
                <div class="stars" data-id="<?php echo $row['id']; ?>">
                    <?php for ($i=1; $i<=5; $i++): ?>
                        <span class="star" data-value="<?php echo $i; ?>">★</span>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php endwhile; ?>

</div>
</div>

<div id="toast"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    function showToast(msg) {
        const toast = document.getElementById("toast");
        toast.innerText = msg;
        toast.classList.add("show");

        setTimeout(() => {
            toast.classList.remove("show");
        }, 2500);
    }

    document.querySelectorAll(".stars").forEach(container => {
        const stars = container.querySelectorAll(".star");
        const orderId = container.dataset.id;

        stars.forEach(star => {
            star.addEventListener("click", () => {

                const rating = parseInt(star.dataset.value);

                stars.forEach(s => s.classList.remove("active"));
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add("active");
                }

                fetch("/hotel-booking/rate.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({ order_id: orderId, rating })
                })
                .then(res => res.json())
                .then(() => {
                    container.innerHTML = `You rated ⭐ ${rating}/5`;
                    showToast("Thanks for your feedback! ⭐");
                })
                .catch(() => {
                    showToast("Something went wrong ❌");
                });
            });
        });
    });

});
</script>