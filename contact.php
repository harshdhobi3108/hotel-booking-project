<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="assets/css/contact.css">

<section class="contact-hero">
  <div class="contact-hero-content">
    <h1>Contact Us</h1>
    <p>We’re here to help you find your perfect stay</p>
  </div>
</section>

<section class="contact-container">

  <!-- FORM -->
  <div class="contact-form">
    <h2>Send a Message ✨</h2>
    <p class="form-sub">We usually respond within 24 hours</p>

    <form id="contactForm">

      <div class="input-group">
        <input type="text" required>
        <label>Your Name</label>
      </div>

      <div class="input-group">
        <input type="email" required>
        <label>Email Address</label>
      </div>

      <div class="input-group">
        <input type="text" required>
        <label>Subject</label>
      </div>

      <div class="input-group">
        <textarea rows="5" required></textarea>
        <label>Your Message</label>
      </div>

      <button type="submit" class="send-btn" id="submitBtn">
        Send Message
      </button>

    </form>
  </div>

  <!-- INFO -->
  <div class="contact-info">

    <div class="info-card">
      <div class="info-icon">📍</div>
      <div>
        <h3>Address</h3>
        <p>HotelLux, Ahmedabad, Gujarat, India</p>
      </div>
    </div>

    <div class="info-card">
      <div class="info-icon">📞</div>
      <div>
        <h3>Phone</h3>
        <p>+91 98765 43210</p>
      </div>
    </div>

    <div class="info-card">
      <div class="info-icon">✉️</div>
      <div>
        <h3>Email</h3>
        <p>support@hotellux.com</p>
      </div>
    </div>

  </div>

</section>

<!-- MAP -->
<section class="map-section">
  <iframe src="https://www.google.com/maps?q=Ahmedabad&output=embed"></iframe>
</section>

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CONTACT JS -->
<script src="assets/js/contact.js"></script>

<?php include 'includes/footer.php'; ?>