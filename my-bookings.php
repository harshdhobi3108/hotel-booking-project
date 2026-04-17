<?php
require_once("includes/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= FETCH BOOKINGS ================= */

$query = "
SELECT 
    o.*,
    r.name,
    r.location,
    r.price,
    r.image
FROM orders o
JOIN rooms r ON o.room_id = r.id
WHERE o.user_id = ?
ORDER BY o.id DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include("includes/header.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings - HotelLux</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:#f6f7fb;
    color:#111827;
}

/* ================= SECTION ================= */

.wrapper{
    max-width:1400px;
    margin:auto;
    padding:50px 25px 80px;
}

.heading{
    font-size:42px;
    font-weight:800;
    margin-bottom:35px;
    color:#111827;
}

/* ================= GRID ================= */

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:28px;
}

/* ================= CARD ================= */

.card{
    background:#fff;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 15px 35px rgba(0,0,0,.06);
    border:1px solid #eceff5;
    transition:.35s ease;
}

.card:hover{
    transform:translateY(-8px);
    box-shadow:0 22px 45px rgba(0,0,0,.10);
}

.image-box{
    height:240px;
    position:relative;
    overflow:hidden;
}

.image-box img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.5s ease;
}

.card:hover img{
    transform:scale(1.08);
}

/* ================= STATUS ================= */

.badge{
    position:absolute;
    top:18px;
    right:18px;
    padding:8px 14px;
    border-radius:50px;
    font-size:12px;
    font-weight:700;
    letter-spacing:.3px;
}

.confirmed{
    background:#dcfce7;
    color:#15803d;
}

.cancelled{
    background:#fee2e2;
    color:#dc2626;
}

.pending{
    background:#fef3c7;
    color:#b45309;
}

/* ================= BODY ================= */

.body{
    padding:24px;
}

.room-name{
    font-size:28px;
    font-weight:800;
    margin-bottom:8px;
}

.location{
    color:#6b7280;
    font-size:15px;
    margin-bottom:18px;
}

/* ================= AMENITIES ================= */

.tags{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}

.tag{
    background:#f3f4f6;
    color:#374151;
    padding:8px 12px;
    border-radius:50px;
    font-size:13px;
    font-weight:600;
}

/* ================= INFO ================= */

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
    margin-bottom:22px;
}

.info{
    background:#fafafa;
    border:1px solid #f0f0f0;
    padding:14px;
    border-radius:16px;
}

.label{
    font-size:12px;
    color:#6b7280;
    text-transform:uppercase;
    font-weight:700;
    margin-bottom:6px;
}

.value{
    font-size:15px;
    font-weight:700;
}

/* ================= PRICE ================= */

.price-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:22px;
}

.price{
    font-size:30px;
    font-weight:800;
    color:#7b2cbf;
}

.small{
    color:#6b7280;
    font-size:14px;
    font-weight:600;
}

/* ================= BUTTONS ================= */

.actions{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}

.btn{
    flex:1;
    min-width:140px;
    text-decoration:none;
    text-align:center;
    padding:14px 16px;
    border-radius:14px;
    font-weight:700;
    transition:.3s ease;
    font-size:14px;
}

.btn-primary{
    background:linear-gradient(135deg,#7b2cbf,#9d4edd);
    color:#fff;
}

.btn-primary:hover{
    transform:translateY(-2px);
}

.btn-danger{
    background:#ef4444;
    color:#fff;
}

.btn-danger:hover{
    background:#dc2626;
}

.btn-outline{
    border:2px solid #7b2cbf;
    color:#7b2cbf;
    background:#fff;
}

.btn-outline:hover{
    background:#7b2cbf;
    color:#fff;
}

/* ================= RATING ================= */

.rating{
    margin-top:18px;
    font-weight:600;
    color:#374151;
}

.rating i{
    color:#facc15;
}

/* ================= EMPTY ================= */

.empty{
    background:#fff;
    border-radius:24px;
    padding:70px 30px;
    text-align:center;
    box-shadow:0 15px 35px rgba(0,0,0,.06);
}

.empty h2{
    font-size:34px;
    margin-bottom:12px;
}

.empty p{
    color:#6b7280;
    margin-bottom:22px;
}

.rating-wrap{
    margin-top:18px;
    padding:18px;
    border:1px solid #ececec;
    border-radius:18px;
    background:#fafafa;
}

.rate-title{
    font-size:15px;
    font-weight:700;
    color:#111827;
    margin-bottom:12px;
}

.star-rating{
    display:flex;
    flex-direction:row-reverse;
    justify-content:flex-end;
    gap:6px;
    margin-bottom:16px;
}

.star-rating input{
    display:none;
}

.star-rating label{
    font-size:34px;
    color:#d1d5db;
    cursor:pointer;
    transition:.2s ease;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label{
    color:#facc15;
    transform:scale(1.08);
}

.rate-submit{
    width:100%;
    border:none;
    border-radius:14px;
    padding:14px;
    font-size:15px;
    font-weight:700;
    color:#fff;
    cursor:pointer;
    background:linear-gradient(135deg,#7b2cbf,#9d4edd);
    transition:.3s ease;
}

.rate-submit:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(123,44,191,.18);
}

/* ================= MOBILE ================= */

@media (max-width: 768px) {

    .wrapper{
        padding: 95px 14px 40px;
    }

    .heading{
        font-size: 34px;
        line-height: 1.1;
        margin-bottom: 22px;
        text-align: left;
    }

    .grid{
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .card{
        border-radius: 22px;
    }

    .image-box{
        height: 210px;
    }

    .body{
        padding: 18px;
    }

    .room-name{
        font-size: 20px;
        margin-bottom: 8px;
        line-height: 1.2;
    }

    .location{
        font-size: 14px;
        margin-bottom: 14px;
    }

    .tags{
        gap: 8px;
        margin-bottom: 16px;
    }

    .tag{
        font-size: 12px;
        padding: 7px 10px;
    }

    .info-grid{
        grid-template-columns: 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }

    .info{
        padding: 12px 14px;
        border-radius: 14px;
    }

    .label{
        font-size: 11px;
    }

    .value{
        font-size: 16px;
    }

    .price-row{
        margin-bottom: 16px;
        align-items: flex-start;
        gap: 6px;
        flex-direction: column;
    }

    .price{
        font-size: 22px;
    }

    .small{
        font-size: 13px;
    }

    .actions{
        flex-direction: column;
        gap: 10px;
    }

    .btn{
        width: 100%;
        min-width: 100%;
        padding: 13px 14px;
        font-size: 14px;
        border-radius: 12px;
    }

    .badge{
        top: 12px;
        right: 12px;
        padding: 7px 12px;
        font-size: 11px;
    }

    .rating{
        margin-top: 14px;
        font-size: 14px;
        line-height: 1.5;
    }
}
</style>
</head>

<body>

<div class="wrapper">

<h1 class="heading">My Bookings</h1>

<?php if($result->num_rows > 0): ?>

<div class="grid">

<?php while($row = $result->fetch_assoc()): 

$status = strtolower($row['booking_status'] ?? $row['status']);

if($status == 'confirmed'){
    $class = 'confirmed';
}elseif($status == 'cancelled'){
    $class = 'cancelled';
}else{
    $class = 'pending';
}
?>

<div class="card">

<div class="image-box">
<?php
$image = trim($row['image']);

if (
    strpos($image, 'uploads/') === 0 ||
    strpos($image, 'http') === 0
) {
    $finalImage = $image;
} else {
    $finalImage = 'uploads/rooms/' . $image;
}
?>

<img 
src="<?php echo htmlspecialchars($finalImage); ?>" 
alt="Room Image"
onerror="this.src='uploads/rooms/Standard-Room.jpg';">

<div class="badge <?php echo $class; ?>">
<?php echo ucfirst($status); ?>
</div>
</div>

<div class="body">

<h2 class="room-name"><?php echo htmlspecialchars($row['name']); ?></h2>

<p class="location">
<i class="fa-solid fa-location-dot"></i>
<?php echo htmlspecialchars($row['location']); ?>
</p>

<div class="tags">
<span class="tag"><i class="fa-solid fa-wifi"></i> WiFi</span>
<span class="tag"><i class="fa-solid fa-snowflake"></i> AC</span>
<span class="tag"><i class="fa-solid fa-mug-hot"></i> Breakfast</span>
</div>

<div class="info-grid">

<div class="info">
<div class="label">Check In</div>
<div class="value"><?php echo $row['booking_date']; ?></div>
</div>

<div class="info">
<div class="label">Check Out</div>
<div class="value"><?php echo $row['check_out']; ?></div>
</div>

<div class="info">
<div class="label">Time</div>
<div class="value"><?php echo $row['booking_time']; ?></div>
</div>

<div class="info">
<div class="label">Booking ID</div>
<div class="value">#<?php echo $row['id']; ?></div>
</div>

</div>

<div class="price-row">
<div class="price">₹<?php echo number_format($row['amount']); ?></div>
<div class="small">Total Paid</div>
</div>

<div class="actions">

<a href="invoice.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
<i class="fa-solid fa-download"></i> Invoice
</a>

<?php if($status == 'confirmed'): ?>
<a href="cancel_booking.php?id=<?php echo $row['id']; ?>" 
class="btn btn-danger"
onclick="return confirm('Are you sure you want to cancel this booking?')">
<i class="fa-solid fa-xmark"></i> Cancel
</a>
<?php endif; ?>

</div>

<?php if(isset($row['rating']) && $row['rating'] > 0): ?>
<div class="rating">
You Rated:
<?php for($i=1;$i<=$row['rating'];$i++): ?>
<i class="fa-solid fa-star"></i>
<?php endfor; ?>
<?php echo " ".$row['rating']; ?>/5
</div>

<?php elseif($status == 'confirmed'): ?>

<div class="rating-wrap">

    <div class="rate-title">Rate Your Stay</div>

    <div class="star-rating ajax-rating"
         data-id="<?php echo $row['id']; ?>">

        <span data-value="1">★</span>
        <span data-value="2">★</span>
        <span data-value="3">★</span>
        <span data-value="4">★</span>
        <span data-value="5">★</span>

    </div>

</div>

<?php endif; ?>

</div>
</div>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty">
<h2>No Bookings Yet</h2>
<p>You have not booked any room yet.</p>

<a href="rooms.php" class="btn btn-primary">
Browse Rooms
</a>
</div>

<?php endif; ?>

</div>
<script>
document.addEventListener("DOMContentLoaded", () => {

    function showToast(msg) {
        const toast = document.createElement("div");
        toast.innerText = msg;

        toast.style.position = "fixed";
        toast.style.top = "20px";
        toast.style.right = "20px";
        toast.style.background = "#7b2cbf";
        toast.style.color = "#fff";
        toast.style.padding = "12px 18px";
        toast.style.borderRadius = "12px";
        toast.style.zIndex = "9999";
        toast.style.fontWeight = "600";

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 2500);
    }

    document.querySelectorAll(".ajax-rating").forEach(box => {

        const stars = box.querySelectorAll("span");
        const orderId = box.dataset.id;

        stars.forEach((star, index) => {

            star.addEventListener("click", async () => {

                const rating = star.dataset.value;

                stars.forEach(s => s.style.color = "#ddd");

                for(let i=0; i<rating; i++){
                    stars[i].style.color = "#facc15";
                }

                const res = await fetch("rate.php", {
                    method: "POST",
                    headers: {
                        "Content-Type":"application/json"
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        rating: rating
                    })
                });

                const data = await res.json();

                showToast(data.message);

                if(data.status === "success"){
                    setTimeout(() => location.reload(), 1200);
                }

            });

        });

    });

});
</script>
</body>
</html>

<?php include('includes/footer.php'); ?>