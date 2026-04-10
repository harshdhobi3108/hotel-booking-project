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

  /* ================= RAZORPAY BOOKING ================= */
  const bookingForm = document.getElementById("bookingForm");

  if (bookingForm) {
    bookingForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const room_id = document.querySelector('[name="room_id"]').value;
      const date = document.querySelector('[name="date"]').value;
      const time = document.querySelector('[name="time"]').value;

      if (!date || !time) {
        alert("Please select date and time");
        return;
      }

      try {
        /* ================= CREATE ORDER ================= */
        const response = await fetch("/hotel-booking/create_order.php", {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ room_id, date, time })
        });

        const data = await response.json();

        if (!response.ok || data.error) {
          throw new Error(data.error || "Failed to create order");
        }

        /* ================= RAZORPAY OPTIONS ================= */
        const options = {
          key: "rzp_test_SahxQ39qIdVeKw",
          amount: data.amount,
          currency: "INR",
          name: "HotelLux",
          description: "Room Booking",
          order_id: data.order_id,

          handler: async function (response) {
            try {
              /* ================= VERIFY PAYMENT ================= */
              const verifyRes = await fetch("/hotel-booking/verify_payment.php", {
                method: "POST",
                credentials: "include",
                headers: {
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({
                  razorpay_order_id: response.razorpay_order_id,
                  razorpay_payment_id: response.razorpay_payment_id,
                  razorpay_signature: response.razorpay_signature,
                  room_id,
                  date,
                  time
                })
              });

              const verifyData = await verifyRes.json();

              if (!verifyRes.ok || verifyData.status !== "success") {
                throw new Error(verifyData.message || "Payment verification failed");
              }

              /* ================= SUCCESS ================= */
              alert("✅ Payment Successful! Booking Confirmed.");
              window.location.href = `booking.php?room_id=${room_id}&success=1`;

            } catch (err) {
              console.error("Verification Error:", err);
              alert("❌ Payment succeeded but booking failed. Contact support.");
            }
          },

          modal: {
            ondismiss: function () {
              console.log("Payment popup closed");
            }
          },

          prefill: {
            name: data.name,
            email: data.email
          },

          theme: {
            color: "#7b2cbf"
          }
        };

        const rzp = new Razorpay(options);
        rzp.open();

      } catch (error) {
        console.error("Error:", error);
        alert(error.message || "Something went wrong!");
      }
    });
  }

});