<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="assets/css/rooms.css">

<?php
require_once __DIR__ . '/includes/config.php';

// ✅ Show only available rooms
$query = "SELECT * FROM rooms " ;
$result = $conn->query($query);
?>

<section class="container">

  <!-- HEADER -->
  <div class="rooms-header">
    <div class="rooms-left">
      <h2>Our Rooms</h2>
      <p class="rooms-sub">Choose from our premium selection</p>
    </div>

    <select class="sort-dropdown">
      <option>Sort by</option>
      <option>Price Low to High</option>
      <option>Price High to Low</option>
    </select>
  </div>

  <!-- ROOM GRID -->
  <div class="room-grid">

    <?php while($row = $result->fetch_assoc()) { ?>

  <div class="room-card <?php echo ($row['status'] === 'booked') ? 'booked' : ''; ?>">
    
    <div class="image-container">

      <img src="assets/images/<?php echo $row['image']; ?>" alt="room">

      <?php if ($row['status'] === 'booked') { ?>
        <div class="overlay">BOOKED</div>
      <?php } ?>

    </div>

    <div class="room-info">

      <div class="top">
        <h3><?php echo $row['name']; ?></h3>
        <span class="rating">⭐ <?php echo $row['rating']; ?></span>
      </div>

      <p class="location"><?php echo $row['location']; ?></p>
      <p class="features"><?php echo $row['features']; ?></p>

      <div class="bottom">
        <p class="price">₹<?php echo $row['price']; ?></p>

        <?php if ($row['status'] === 'available') { ?>
          <button 
            class="pay-btn"
            data-id="<?php echo $row['id']; ?>"
            data-price="<?php echo $row['price']; ?>"
            data-room="<?php echo $row['name']; ?>">
            Book Now
          </button>
        <?php } else { ?>
          <button class="pay-btn disabled">Booked</button>
        <?php } ?>

      </div>

    </div>

  </div>

<?php } ?>

  </div>

</section>

<!-- ================= RAZORPAY ================= -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.querySelectorAll(".pay-btn").forEach(button => {

  button.addEventListener("click", async function () {

    const roomId = this.dataset.id;
    const price = this.dataset.price;
    const room = this.dataset.room;

    const amount = price * 100;

    try {

      // ✅ Create order with room_id
      const res = await fetch(`create_order.php?amount=${amount}&room_id=${roomId}`);
      const data = await res.json();

      if (data.error) {
        alert("Error: " + data.error);
        return;
      }

      const options = {
        key: "rzp_test_SahxQ39qIdVeKw",
        amount: data.amount,
        currency: "INR",
        name: "HotelLux",
        description: room,
        order_id: data.order_id,

        handler: function (response) {

          // ✅ Send CORRECT DATA to backend
          fetch('verify_payment.php', {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_order_id: response.razorpay_order_id,
              razorpay_signature: response.razorpay_signature,
              room_id: roomId,   // 🔥 FIXED
              amount: price
            })
          })
          .then(res => res.text())
          .then(data => {
            alert(data);
            location.reload(); // ✅ refresh UI
          });

        },

        modal: {
          ondismiss: function () {
            console.log("Payment popup closed");
          }
        }

      };

      const rzp = new Razorpay(options);
      rzp.open();

    } catch (error) {
      console.error(error);
      alert("Something went wrong during payment.");
    }

  });

});
</script>

<?php include 'includes/footer.php'; ?>