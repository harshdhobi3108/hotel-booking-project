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

// ================= USER DATA =================
$name    = $_SESSION['user_name'];
$email   = $_SESSION['user_email'];
$user_id = $_SESSION['user_id'] ?? 1;
$picture = $_SESSION['user_picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($name);

// ================= BOOKINGS =================
$query = "
SELECT o.id as order_id, r.name, r.price, o.status, o.created_at
FROM orders o
JOIN rooms r ON o.room_id = r.id
WHERE o.user_id = ? AND o.status = 'confirmed'
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ================= STATS =================
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
      <button class="edit-btn" onclick="openModal()">Edit Profile</button>
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

      <!-- BOOKINGS -->
      <div class="card">
        <h3>Your Bookings</h3>

        <?php if (empty($bookings)) { ?>
          <p>No confirmed bookings yet.</p>
        <?php } else { ?>

          <?php foreach ($bookings as $b) { ?>

            <div class="booking">

              <div class="booking-left">
                <h4><?php echo htmlspecialchars($b['name']); ?></h4>
                <p><?php echo date("d M Y", strtotime($b['created_at'])); ?></p>

                <div class="booking-actions">

                  <a href="/hotel-booking/my-bookings.php?id=<?php echo $b['order_id']; ?>" 
                     class="primary-btn">
                     Open Booking →
                  </a>

                  <a href="/hotel-booking/invoice.php?id=<?php echo $b['order_id']; ?>" 
                     class="invoice-btn" target="_blank">
                     Invoice
                  </a>

                </div>
              </div>

              <div class="booking-right">
                <span class="status confirmed">Confirmed</span>
              </div>

            </div>

          <?php } ?>

        <?php } ?>

      </div>

      <!-- SETTINGS -->
      <div class="card">
        <h3>Account Settings</h3>
        <button class="full-btn" onclick="openModal()">Update Profile</button>
      </div>

    </div>

  </div>

</div>

<!-- ================= MODAL ================= -->
<div id="editModal" class="modal">
  <div class="modal-content">

    <h2>Edit Profile</h2>

    <form action="/hotel-booking/update_profile.php" method="POST" enctype="multipart/form-data">
      <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
      <input type="file" name="profile_pic">
      <button class="btn-primary" type="submit">Update Profile</button>
    </form>

    <hr>

    <h3>Change Password</h3>

    <form action="/hotel-booking/change_password.php" method="POST">
      <input type="password" name="old_password" placeholder="Old Password" required>
      <input type="password" name="new_password" placeholder="New Password" required>
      <button class="btn-primary" type="submit">Change Password</button>
    </form>

    <button class="btn-secondary" onclick="closeModal()">Close</button>

  </div>
</div>

<!-- ================= JS ================= -->
<script>
function openModal() {
  document.getElementById("editModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("editModal").style.display = "none";
}
</script>

<?php include("includes/footer.php"); ?>