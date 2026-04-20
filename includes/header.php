<?php
require_once(__DIR__ . "/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HotelLux</title>

<link rel="stylesheet" href="/hotel-booking/assets/css/style.css">

<style>

/* ================= OVERLAY ================= */
#navOverlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    backdrop-filter:blur(4px);
    opacity:0;
    visibility:hidden;
    pointer-events:none;
    transition:.3s ease;
    z-index:9998;
}

#navOverlay.active{
    opacity:1;
    visibility:visible;
    pointer-events:auto;
}

/* ================= NAVBAR ================= */
.navbar{
    width:100%;
    background:#fff;
    box-shadow:0 8px 25px rgba(0,0,0,.05);
    position:sticky;
    top:0;
    z-index:9999;
}

.nav-container{
    max-width:1400px;
    margin:auto;
    padding:0 28px;
    height:78px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
}

.logo{
    font-size:2rem;
    font-weight:800;
    color:#6a11cb;
    letter-spacing:.4px;
    white-space:nowrap;
}

/* ================= DESKTOP NAV ================= */

.nav-links{
    display:flex;
    align-items:center;
    gap:34px;
    list-style:none;
    margin:0;
    padding:0;
}

.nav-links a{
    text-decoration:none;
    color:#222;
    font-weight:600;
    font-size:15px;
    transition:.25s ease;
}

.nav-links a:hover{
    color:#7b2cbf;
}

/* ================= AUTH ================= */
#auth-section{
    display:flex;
    align-items:center;
    gap:14px;
}

.profile-dropdown{
    position:relative;
}

.profile-img{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #7b2cbf;
    cursor:pointer;
}

.dropdown-content{
    position:absolute;
    right:0;
    top:55px;
    width:250px;
    background:#fff;
    border-radius:18px;
    box-shadow:0 18px 40px rgba(0,0,0,.12);
    padding:14px;
    display:none;
}

.profile-dropdown:hover .dropdown-content{
    display:block;
}

.dropdown-user{
    display:flex;
    gap:12px;
    align-items:center;
}

.dropdown-avatar{
    width:46px;
    height:46px;
    border-radius:50%;
    object-fit:cover;
}

.dropdown-divider{
    height:1px;
    background:#eee;
    margin:14px 0;
}

.dropdown-content a{
    display:block;
    padding:10px 12px;
    border-radius:12px;
    text-decoration:none;
    color:#222;
    font-weight:600;
}

.dropdown-content a:hover{
    background:#f7f4ff;
    color:#7b2cbf;
}

.login-btn{
    background:linear-gradient(135deg,#7b2cbf,#9d4edd);
    color:#fff;
    padding:10px 18px;
    border-radius:12px;
    text-decoration:none;
    font-weight:600;
}

/* ================= HAMBURGER ================= */
.menu-toggle{
    display:none;
    flex-direction:column;
    justify-content:space-between;
    width:28px;
    height:20px;
    cursor:pointer;
}

.menu-toggle span{
    height:3px;
    width:100%;
    background:#222;
    border-radius:50px;
    transition:.3s ease;
}

.menu-toggle.active span:nth-child(1){
    transform:translateY(8px) rotate(45deg);
}

.menu-toggle.active span:nth-child(2){
    opacity:0;
}

.menu-toggle.active span:nth-child(3){
    transform:translateY(-8px) rotate(-45deg);
}

/* ================= MOBILE ================= */
@media (max-width:768px){

.nav-container{
    padding:0 16px;
    height:72px;
}

.logo{
    font-size:1.9rem;
}

.menu-toggle{
    display:flex;
    order:3;
}

.profile-dropdown,
.profile-img{
    display:none !important;
}

#auth-section{
    margin-left:auto;
}

#navMenu{
    position:fixed;
    top:82px;
    left:50%;
    transform:translateX(-50%) translateY(-20px);
    width:92%;
    max-width:390px;
    background:#fff;
    border-radius:22px;
    box-shadow:0 30px 60px rgba(0,0,0,.18);
    opacity:0;
    visibility:hidden;
    pointer-events:none;
    transition:.3s ease;
    z-index:9999;
    padding:18px;
}

#navMenu.active{
    opacity:1;
    visibility:visible;
    pointer-events:auto;
    transform:translateX(-50%) translateY(0);
}

.nav-links{
    flex-direction:column;
    gap:0;
}

.nav-links li{
    width:100%;
}

.nav-links a{
    display:block;
    width:100%;
    text-align:center;
    padding:15px;
    border-bottom:1px solid #f1f1f1;
}

.mobile-auth{
    margin-top:15px;
    padding-top:15px;
    border-top:1px solid #eee;
    text-align:center;
}

.mobile-auth p{
    font-weight:700;
    margin-bottom:10px;
}

.mobile-auth a{
    display:block;
    padding:12px;
    text-decoration:none;
    color:#7b2cbf;
    font-weight:700;
}

}


/* ================= DESKTOP RESET ================= */
@media (min-width:769px){

.mobile-auth{
    display:none;
}

}

</style>
</head>

<body>

<div id="navOverlay"></div>

<header class="navbar">
<div class="nav-container">

<div class="logo">HotelLux</div>

<nav id="navMenu">
<ul class="nav-links">
<li><a href="/hotel-booking/">Home</a></li>
<li><a href="/hotel-booking/rooms.php">Rooms</a></li>
<li><a href="/hotel-booking/booking.php">Booking</a></li>
<li><a href="/hotel-booking/contact.php">Contact</a></li>
</ul>

<div class="mobile-auth">
<?php if(isset($_SESSION['user_email'])): ?>
<p><?php echo $_SESSION['user_name']; ?></p>
<a href="/hotel-booking/profile.php">My Profile</a>
<a href="/hotel-booking/auth/logout.php">Logout</a>
<?php else: ?>
<a href="/hotel-booking/auth/login.php">Login</a>
<?php endif; ?>
</div>
</nav>

<div id="auth-section">

<?php if(isset($_SESSION['user_email'])): ?>

<?php
$name = $_SESSION['user_name'] ?? 'User';
$avatar = $_SESSION['user_picture'] ??
'https://ui-avatars.com/api/?name=' . urlencode($name);
?>

<div class="profile-dropdown">
<img src="<?php echo $avatar; ?>" class="profile-img">

<div class="dropdown-content">
<div class="dropdown-user">
<img src="<?php echo $avatar; ?>" class="dropdown-avatar">
<div>
<strong><?php echo $_SESSION['user_name']; ?></strong>
<p><?php echo $_SESSION['user_email']; ?></p>
</div>
</div>

<div class="dropdown-divider"></div>

<a href="/hotel-booking/profile.php">My Profile</a>
<a href="/hotel-booking/auth/logout.php">Logout</a>
</div>
</div>

<?php else: ?>

<a href="/hotel-booking/auth/login.php" class="login-btn">Login</a>

<?php endif; ?>

</div>

<div class="menu-toggle" id="menuToggle">
<span></span>
<span></span>
<span></span>
</div>

</div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function(){

const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");
const overlay = document.getElementById("navOverlay");

function closeMenu(){
    navMenu.classList.remove("active");
    overlay.classList.remove("active");
    menuToggle.classList.remove("active");
}

menuToggle.addEventListener("click", function(e){
    e.preventDefault();
    e.stopPropagation();

    navMenu.classList.toggle("active");
    overlay.classList.toggle("active");
    menuToggle.classList.toggle("active");
});

overlay.addEventListener("click", closeMenu);

document.querySelectorAll("#navMenu a").forEach(link=>{
    link.addEventListener("click", closeMenu);
});

window.addEventListener("resize", function(){
    if(window.innerWidth > 768){
        closeMenu();
    }
});

});
</script>

</body>
</html>