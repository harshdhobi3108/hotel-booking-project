document.addEventListener("DOMContentLoaded", () => {

  /* ================= SWEET ALERT HELPERS ================= */
  const toast = (icon, title) => {
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: icon,
      title: title,
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true
    });
  };

  const popup = (icon, title, text = "") => {
    Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: "#7b2cbf"
    });
  };

  /* =====================================================
     IMPORTANT FIX:
     MOBILE NAVBAR CODE REMOVED
     Navbar already handled in header.php
     Duplicate listeners were breaking menu on other pages
  ===================================================== */


  /* ================= TYPEWRITER ================= */
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

      typewriterElement.textContent =
        currentText.substring(0, charIndex);

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


  /* ================= DATE PICKER ================= */
  const checkIn = document.getElementById("check_in");
  const checkOut = document.getElementById("check_out");

  if (
    checkIn &&
    checkOut &&
    typeof flatpickr !== "undefined"
  ) {

    const today = new Date();

    const fpCheckOut = flatpickr(checkOut, {
      dateFormat: "Y-m-d",
      minDate: today
    });

    flatpickr(checkIn, {
      dateFormat: "Y-m-d",
      minDate: today,
      onChange: function (selectedDates, dateStr) {
        fpCheckOut.set("minDate", dateStr);
      }
    });
  }


  /* ================= BOOKING FLOW ================= */
  const bookingForm =
    document.getElementById("bookingForm");

  if (bookingForm) {

    bookingForm.addEventListener(
      "submit",
      async function (e) {

        e.preventDefault();

        const room_id =
          document.querySelector('[name="room_id"]').value;

        const check_in =
          document.querySelector('[name="check_in"]').value;

        const check_out =
          document.querySelector('[name="check_out"]').value;

        const time =
          document.querySelector('[name="time"]').value;

        if (!check_in || !check_out || !time) {
          toast("warning", "Please fill all fields");
          return;
        }

        if (check_out <= check_in) {
          toast(
            "warning",
            "Check-out must be after check-in"
          );
          return;
        }

        try {

          Swal.fire({
            title: "Checking availability...",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          const checkRes = await fetch(
            "/hotel-booking/check_availability.php",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                room_id,
                check_in,
                check_out
              })
            }
          );

          const checkData =
            await checkRes.json();

          if (!checkData.available) {

            Swal.close();

            popup(
              "error",
              "Room Unavailable",
              "Already booked for selected dates."
            );

            return;
          }

          const response = await fetch(
            "/hotel-booking/create_order.php",
            {
              method: "POST",
              credentials: "include",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                room_id,
                check_in,
                check_out,
                time
              })
            }
          );

          const data = await response.json();

          Swal.close();

          if (!response.ok || data.error) {
            throw new Error(
              data.error ||
              "Order creation failed"
            );
          }

          const options = {

            key: "rzp_test_SahxQ39qIdVeKw",
            amount: data.amount,
            currency: "INR",
            name: "HotelLux",
            description: "Room Booking",
            order_id: data.order_id,

            handler: async function (response) {

              try {

                Swal.fire({
                  title: "Verifying payment...",
                  allowOutsideClick: false,
                  didOpen: () => {
                    Swal.showLoading();
                  }
                });

                const verifyRes = await fetch(
                  "/hotel-booking/verify_payment.php",
                  {
                    method: "POST",
                    credentials: "include",
                    headers: {
                      "Content-Type":
                        "application/json"
                    },
                    body: JSON.stringify({
                      razorpay_order_id:
                        response.razorpay_order_id,

                      razorpay_payment_id:
                        response.razorpay_payment_id,

                      razorpay_signature:
                        response.razorpay_signature
                    })
                  }
                );

                const verifyData =
                  await verifyRes.json();

                Swal.close();

                if (
                  !verifyRes.ok ||
                  verifyData.status !== "success"
                ) {
                  throw new Error(
                    verifyData.message ||
                    "Verification failed"
                  );
                }

                await Swal.fire({
                  icon: "success",
                  title: "Booking Confirmed!",
                  text:
                    "Your room has been booked successfully.",
                  confirmButtonColor:
                    "#7b2cbf"
                });

                window.location.href =
                  "/hotel-booking/my-bookings.php";

              } catch (err) {

                Swal.close();

                popup(
                  "error",
                  "Payment Failed",
                  err.message
                );
              }
            },

            modal: {
              ondismiss: function () {
                toast(
                  "info",
                  "Payment window closed"
                );
              }
            },

            theme: {
              color: "#7b2cbf"
            }
          };

          const rzp =
            new Razorpay(options);

          rzp.open();

        } catch (error) {

          Swal.close();

          popup(
            "error",
            "Something went wrong",
            error.message || "Try again."
          );
        }
      }
    );
  }

});