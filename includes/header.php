<?php
require_once(__DIR__ . "/config.php");

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Booking</title>

  <!-- Global CSS -->
  <link rel="stylesheet" href="/hotel-booking/assets/css/style.css">

<style>

/* ===== OVERLAY ===== */
#navOverlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(4px);
  opacity: 0;
  pointer-events: none;
  transition: 0.3s;
  z-index: 999;
}

#navOverlay.active {
  opacity: 1;
  pointer-events: auto;
}

/* ================= MOBILE NAV ONLY ================= */
@media (max-width: 768px) {

  nav {
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%) scale(0.96);

    width: 92%;
    max-width: 380px;

    background: #ffffff;
    border-radius: 20px;

    box-shadow: 
      0 20px 50px rgba(0,0,0,0.15),
      0 5px 15px rgba(0,0,0,0.08);

    opacity: 0;
    pointer-events: none;
    transition: all 0.25s ease;
    z-index: 1000;
  }

  nav.active {
    opacity: 1;
    transform: translateX(-50%) scale(1);
    pointer-events: auto;
  }

  .nav-links {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 28px 0 18px;
    gap: 20px;
  }

  .nav-links a {
    font-size: 16px;
    font-weight: 500;
    transition: 0.2s;
  }

  .nav-links a:hover {
    color: #7b2cbf;
  }

  .nav-right {
    display: none;
  }

  .mobile-auth {
    display: block;
    text-align: center;
    border-top: 1px solid #eee;
    margin-top: 10px;
    padding: 18px 0 22px;
  }

  /* ===== HAMBURGER ===== */
  .menu-toggle {
    display: flex;
    width: 25px;
    height: 18px;
    flex-direction: column;
    justify-content: space-between;
    cursor: pointer;
    position: relative;
    z-index: 1100;
  }

  .menu-toggle span {
    height: 3px;
    background: #333;
    border-radius: 2px;
    transition: 0.3s;
  }

  /* ANIMATION */
  .menu-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translateY(7px);
  }

  .menu-toggle.active span:nth-child(2) {
    opacity: 0;
  }

  .menu-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translateY(-7px);
  }

}

/* ================= DESKTOP RESET (CRITICAL FIX) ================= */
@media (min-width: 769px) {

  nav {
    position: static;
    transform: none;
    opacity: 1;
    pointer-events: auto;
    box-shadow: none;
    background: transparent;
  }

  #navOverlay {
    display: none;
  }

  .menu-toggle {
    display: none;
  }

}

</style>

</head>

<body>

<!-- ===== OVERLAY ===== -->
<div id="navOverlay"></div>

<!-- ================= NAVBAR ================= -->
<header class="navbar">
  <div class="nav-container">

    <div class="logo">HotelLux</div>

    <!-- HAMBURGER -->
    <div class="menu-toggle" id="menuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>

    <nav id="navMenu">
      <ul class="nav-links">
        <li><a href="/hotel-booking/">Home</a></li>
        <li><a href="/hotel-booking/rooms.php">Rooms</a></li>
        <li><a href="/hotel-booking/rooms.php">Booking</a></li>
        <li><a href="/hotel-booking/contact.php">Contact</a></li>
      </ul>

      <!-- MOBILE AUTH -->
      <div class="mobile-auth">
        <?php if (isset($_SESSION['user_email'])): ?>
          <p><?php echo $_SESSION['user_name']; ?></p>
          <a href="/hotel-booking/profile.php">My Profile</a>
          <a href="/hotel-booking/auth/logout.php">Logout</a>
        <?php else: ?>
          <a href="/hotel-booking/auth/login.php" class="login-btn">Login</a>
        <?php endif; ?>
      </div>
    </nav>

    <div class="nav-right">
      <button id="theme-toggle">🌙</button>

      <div id="auth-section">
        <?php if (isset($_SESSION['user_email'])): ?>

        <div class="profile-dropdown">
          <?php
          $name = $_SESSION['user_name'] ?? "User";
          $avatar = $_SESSION['user_picture'] ?? 
                    "https://ui-avatars.com/api/?name=" . urlencode($name);
          ?>

          <img src="<?php echo $avatar; ?>" 
              class="profile-img" 
              alt="User Avatar"
              referrerpolicy="no-referrer">

          <div class="dropdown-content">
            <div class="dropdown-user">
              <img src="<?php echo $avatar; ?>" class="dropdown-avatar">
              <div>
                <strong><?php echo $_SESSION['user_name'] ?? 'User'; ?></strong>
                <p class="email"><?php echo $_SESSION['user_email'] ?? ''; ?></p>
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
    </div>

  </div>
</header>

<!-- ================= SCRIPT ================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menuToggle");
    const navMenu = document.getElementById("navMenu");
    const overlay = document.getElementById("navOverlay");

    if (menuToggle && navMenu && overlay) {

        menuToggle.addEventListener("click", function (e) {
            e.stopPropagation();

            navMenu.classList.toggle("active");
            overlay.classList.toggle("active");
            menuToggle.classList.toggle("active");
        });

        overlay.addEventListener("click", function () {
            navMenu.classList.remove("active");
            overlay.classList.remove("active");
            menuToggle.classList.remove("active");
        });

        document.querySelectorAll(".nav-links a").forEach(link => {
            link.addEventListener("click", () => {
                navMenu.classList.remove("active");
                overlay.classList.remove("active");
                menuToggle.classList.remove("active");
            });
        });
    }
});
</script>