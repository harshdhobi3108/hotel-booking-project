document.addEventListener("DOMContentLoaded", () => {

  /* ================= THEME TOGGLE ================= */
  const toggleBtn = document.getElementById("theme-toggle");

  // Apply saved theme on load
  const savedTheme = localStorage.getItem("theme");

  if (savedTheme === "dark") {
    document.body.classList.add("dark");
  }

  if (toggleBtn) {
    updateToggleIcon();

    toggleBtn.addEventListener("click", () => {
      document.body.classList.toggle("dark");

      const isDark = document.body.classList.contains("dark");

      // Save preference
      localStorage.setItem("theme", isDark ? "dark" : "light");

      // Update icon
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

    // Toggle menu
    menuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("active");
    });

    // Close menu when clicking any link (better UX)
    const navLinks = document.querySelectorAll(".nav-links a");

    navLinks.forEach(link => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");
      });
    });

    // Optional: close menu when clicking outside
    document.addEventListener("click", (e) => {
      if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        navMenu.classList.remove("active");
      }
    });
  }


  /* ================= TYPEWRITER ================= */
  const texts = [
    "Luxury Stays Redefined",
    "Find Your Dream Hotel",
    "Premium Comfort Experience"
  ];

  const element = document.getElementById("typewriter");

  if (element) {
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    function typeEffect() {
      const currentText = texts[textIndex];

      element.textContent = currentText.substring(0, charIndex);

      if (!isDeleting && charIndex < currentText.length) {
        charIndex++;
        setTimeout(typeEffect, 70);
      } 
      else if (isDeleting && charIndex > 0) {
        charIndex--;
        setTimeout(typeEffect, 40);
      } 
      else {
        isDeleting = !isDeleting;

        if (!isDeleting) {
          textIndex = (textIndex + 1) % texts.length;
        }

        setTimeout(typeEffect, 1200);
      }
    }

    typeEffect();
  }

});