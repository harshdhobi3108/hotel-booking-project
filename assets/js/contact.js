document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("contactForm");
  const btn = document.getElementById("submitBtn");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // Loading state
      btn.innerHTML = "Sending...";
      btn.disabled = true;
      btn.style.opacity = "0.7";

      setTimeout(() => {

        Swal.fire({
          icon: "success",
          title: "Message Sent!",
          text: "We will get back to you within 24 hours.",
          confirmButtonColor: "#7b2cbf",
          background: "#1a1a2e",
          color: "#fff"
        });

        form.reset();

        btn.innerHTML = "Send Message";
        btn.disabled = false;
        btn.style.opacity = "1";

      }, 1500);
    });
  }

});