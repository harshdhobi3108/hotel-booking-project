document.addEventListener("DOMContentLoaded", () => {

  /* ================= THEME TOGGLE ================= */
  const toggleBtn = document.getElementById("theme-toggle");

  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark");
  }

  if (toggleBtn) {
    updateToggleIcon();

    toggleBtn.addEventListener("click", () => {
      document.body.classList.toggle("dark");

      const isDark = document.body.classList.contains("dark");
      localStorage.setItem("theme", isDark ? "dark" : "light");

      updateToggleIcon();
    });
  }

  function updateToggleIcon() {
    if (!toggleBtn) return;

    toggleBtn.innerHTML = document.body.classList.contains("dark")
      ? "☀️"
      : "🌙";
  }

  /* ================= MOBILE NAVBAR ================= */
  const menuToggle = document.getElementById("menuToggle");
  const navMenu = document.getElementById("navMenu");

  if (menuToggle && navMenu) {
    menuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("active");
    });

    const navLinks = document.querySelectorAll(".nav-links a");

    navLinks.forEach(link => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");
      });
    });

    document.addEventListener("click", (e) => {
      if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        navMenu.classList.remove("active");
      }
    });
  }

  /* ================= TYPEWRITER EFFECT ================= */
  const typewriterElement = document.getElementById("typewriter");

  if (typewriterElement) {
    const texts = [
      "Luxury Stays Redefined",
      "Find Your Dream Hotel",
      "Premium Comfort Experience"
    ];

    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    function typeEffect() {
      const currentText = texts[textIndex];

      typewriterElement.textContent = currentText.substring(0, charIndex);

      if (!isDeleting && charIndex < currentText.length) {
        charIndex++;
        setTimeout(typeEffect, 70);

      } else if (isDeleting && charIndex > 0) {
        charIndex--;
        setTimeout(typeEffect, 40);

      } else {
        isDeleting = !isDeleting;

        if (!isDeleting) {
          textIndex = (textIndex + 1) % texts.length;
        }

        setTimeout(typeEffect, 1200);
      }
    }

    typeEffect();
  }

  /* ================= DATE LOGIC ================= */
  const checkIn = document.getElementById("check_in");
  const checkOut = document.getElementById("check_out");

  if (checkIn && checkOut) {
    const today = new Date().toISOString().split("T")[0];
    checkIn.min = today;

    checkIn.addEventListener("change", () => {
      checkOut.min = checkIn.value;

      if (checkOut.value && checkOut.value <= checkIn.value) {
        checkOut.value = "";
      }
    });
  }

/* ================= BOOKING FLOW ================= */
const bookingForm = document.getElementById("bookingForm");

if (bookingForm) {
  bookingForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const room_id = document.querySelector('[name="room_id"]').value;
    const check_in = document.querySelector('[name="check_in"]').value;
    const check_out = document.querySelector('[name="check_out"]').value;
    const time = document.querySelector('[name="time"]').value;

    // ===== VALIDATION =====
    if (!check_in || !check_out || !time) {
      alert("Please fill all fields");
      return;
    }

    if (check_out <= check_in) {
      alert("Check-out must be after check-in");
      return;
    }

    try {
      /* ================= STEP 1: CHECK AVAILABILITY ================= */
      const checkRes = await fetch("/hotel-booking/check_availability.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ room_id, check_in, check_out })
      });

      const checkData = await checkRes.json();

      if (!checkData.available) {
        alert("❌ Room already booked for selected dates!");
        return;
      }

      /* ================= STEP 2: CREATE ORDER ================= */
      const response = await fetch("/hotel-booking/create_order.php", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ room_id, check_in, check_out, time })
      });

      const data = await response.json();

      if (!response.ok || data.error) {
        throw new Error(data.error || "Order creation failed");
      }

      /* ================= STEP 3: RAZORPAY ================= */
      const options = {
        key: "rzp_test_SahxQ39qIdVeKw",
        amount: data.amount,
        currency: "INR",
        name: "HotelLux",
        description: "Room Booking",
        order_id: data.order_id,

        handler: async function (response) {
          try {
            const verifyRes = await fetch("/hotel-booking/verify_payment.php", {
              method: "POST",
              credentials: "include",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                razorpay_order_id: response.razorpay_order_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_signature: response.razorpay_signature
              })
            });

            const verifyData = await verifyRes.json();

            if (!verifyRes.ok || verifyData.status !== "success") {
              throw new Error(verifyData.message || "Verification failed");
            }

            alert("✅ Booking Confirmed!");
            window.location.href = "/hotel-booking/my-bookings.php";

          } catch (err) {
            console.error(err);
            alert("❌ Payment successful but booking failed");
          }
        },

        modal: {
          ondismiss: async function () {
            // 🔥 USER CLOSED PAYMENT WINDOW
            await fetch("/hotel-booking/verify_payment.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                razorpay_order_id: data.order_id,
                razorpay_payment_id: null,
                razorpay_signature: null
              })
            });

            alert("❌ Payment cancelled");
          }
        },

        theme: { color: "#7b2cbf" }
      };

      const rzp = new Razorpay(options);

      // 🔥 HANDLE PAYMENT FAILURE
      rzp.on('payment.failed', async function (response) {

        await fetch("/hotel-booking/verify_payment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            razorpay_order_id: response.error.metadata.order_id,
            razorpay_payment_id: null,
            razorpay_signature: null
          })
        });

        alert("❌ Payment Failed");
      });

      rzp.open();

    } catch (error) {
      console.error(error);
      alert(error.message || "Something went wrong!");
    }
  });
}

});