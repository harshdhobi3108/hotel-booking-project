<?php
require_once("includes/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email'])) {
    header("Location: /hotel-booking/auth/login.php");
    exit();
}

include("includes/header.php");

$name    = $_SESSION['user_name'];
$email   = $_SESSION['user_email'];
$picture = $_SESSION['user_picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($name);
?>

<link rel="stylesheet" href="/hotel-booking/assets/css/profile.css">

<div class="profile-page">

  <!-- TOP HERO -->
  <div class="profile-hero">

  <div class="hero-left">
    <img src="<?php echo $picture; ?>" class="avatar">

    <div class="user-info">
      <h1><?php echo htmlspecialchars($name); ?></h1>
      <p><?php echo htmlspecialchars($email); ?></p>
      <span class="badge">Verified User</span>
    </div>
  </div>

  <div class="hero-actions">
    <button class="edit-btn">Edit Profile</button>
    <button class="logout" onclick="window.location.href='/hotel-booking/auth/logout.php'">Logout</button>
  </div>

</div>

  <!-- MAIN GRID -->
  <div class="profile-grid">

    <!-- LEFT -->
    <div class="left-panel">

      <div class="card">
        <h3>Account Info</h3>
        <p><strong>Name:</strong> <?php echo $name; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
        <p><strong>Type:</strong> Google User</p>
        <p><strong>Status:</strong> <span class="active">Active</span></p>
      </div>

      <div class="card">
        <h3>Quick Stats</h3>
        <div class="stats">
          <div>
            <h4>3</h4>
            <p>Bookings</p>
          </div>
          <div>
            <h4>₹12,499</h4>
            <p>Total Spent</p>
          </div>
        </div>
      </div>

    </div>

    <!-- RIGHT -->
    <div class="right-panel">

      <div class="card">
        <h3>Recent Bookings</h3>

        <div class="booking">
          <div>
            <h4>Deluxe Room</h4>
            <p>12 Apr 2026</p>
          </div>
          <span class="status confirmed">Confirmed</span>
        </div>

        <div class="booking">
          <div>
            <h4>Luxury Suite</h4>
            <p>20 Apr 2026</p>
          </div>
          <span class="status pending">Pending</span>
        </div>

      </div>

      <div class="card">
        <h3>Account Settings</h3>
        <button class="full-btn">Change Password</button>
        <button class="full-btn">Update Profile</button>
      </div>

    </div>

  </div>

</div>

<?php include("includes/footer.php"); ?>