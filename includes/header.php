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
</head>

<body>

<!-- ================= NAVBAR ================= -->
<header class="navbar">
  <div class="nav-container">

    <div class="logo">HotelLux</div>

    <!-- HAMBURGER -->
    <div class="menu-toggle" id="menuToggle">☰</div>

    <nav id="navMenu">
  <ul class="nav-links">
    <li><a href="/hotel-booking/">Home</a></li>
    <li><a href="/hotel-booking/rooms.php">Rooms</a></li>
    <li><a href="/hotel-booking/booking.php">Booking</a></li>
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

        <?php else: ?>
          <a href="/hotel-booking/auth/login.php" class="login-btn">Login</a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</header>