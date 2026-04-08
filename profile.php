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
$user_id = $_SESSION['user_id'] ?? 1; // replace later
$picture = $_SESSION['user_picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($name);

// ===============================
// ✅ FETCH ONLY CONFIRMED BOOKINGS
// ===============================
$query = "
SELECT r.name, r.price, o.status, o.created_at
FROM orders o
JOIN rooms r ON o.room_id = r.id
WHERE o.user_id = ? AND o.status = 'confirmed'
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ===============================
// ✅ STATS
// ===============================
$totalBookings = 0;
$totalSpent = 0;
$bookings = [];

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
    $totalBookings++;
    $totalSpent += $row['price'];
}
?>

<link rel="stylesheet" href="/hotel-booking/assets/css/profile.css">

<div class="profile-page">

  <!-- HERO -->
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

  <!-- GRID -->
  <div class="profile-grid">

    <!-- LEFT -->
    <div class="left-panel">

      <div class="card">
        <h3>Account Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Type:</strong> Google User</p>
        <p><strong>Status:</strong> <span class="active">Active</span></p>
      </div>

      <div class="card">
        <h3>Quick Stats</h3>
        <div class="stats">
          <div>
            <h4><?php echo $totalBookings; ?></h4>
            <p>Bookings</p>
          </div>
          <div>
            <h4>₹<?php echo $totalSpent; ?></h4>
            <p>Total Spent</p>
          </div>
        </div>
      </div>

    </div>

    <!-- RIGHT -->
    <div class="right-panel">

      <div class="card">
        <h3>Recent Bookings</h3>

        <?php if (empty($bookings)) { ?>
          <p>No confirmed bookings yet.</p>
        <?php } else { ?>

          <?php foreach ($bookings as $b) { ?>

            <div class="booking">
              <div>
                <h4><?php echo htmlspecialchars($b['name']); ?></h4>
                <p><?php echo date("d M Y", strtotime($b['created_at'])); ?></p>
              </div>

              <span class="status confirmed">Confirmed</span>
            </div>

          <?php } ?>

        <?php } ?>

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