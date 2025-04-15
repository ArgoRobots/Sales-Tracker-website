document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method") || "credit";

  // Update the form based on payment method
  document.addEventListener("DOMContentLoaded", function () {
    const formTitle = document.querySelector(".checkout-form h2");

    // Customize checkout form based on payment method
    switch (paymentMethod) {
      case "paypal":
        formTitle.textContent = "PayPal Checkout";
        setupPayPalCheckout();
        break;
      case "stripe":
        formTitle.textContent = "Stripe Checkout";
        break;
      case "square":
        formTitle.textContent = "Square Checkout";
        break;
      default:
        formTitle.textContent = "Credit Card Checkout";
    }
  });

  // PayPal checkout setup
  function setupPayPalCheckout() {
    // Hide the credit card form for PayPal
    document.getElementById("payment-form").style.display = "none";

    // Create PayPal button container
    const paypalContainer = document.createElement("div");
    paypalContainer.id = "paypal-button-container";
    document.querySelector(".checkout-form").appendChild(paypalContainer);

    // Initialize PayPal button
    if (typeof paypal !== "undefined") {
      paypal
        .Buttons({
          // Set up the transaction
          createOrder: function (data, actions) {
            return actions.order.create({
              purchase_units: [
                {
                  amount: {
                    value: "20.00",
                    currency_code: "CAD",
                  },
                  description: "Argo Sales Tracker - Lifetime Access",
                },
              ],
            });
          },

          // Finalize the transaction
          onApprove: function (data, actions) {
            return actions.order.capture().then(function (orderData) {
              // Redirect to thank you page
              window.location.href =
                "thank-you/index.html?paypal_order_id=" + orderData.id;
            });
          },

          // Handle errors
          onError: function (err) {
            console.error("PayPal error", err);
            alert(
              "There was an error processing your payment. Please try again or contact support."
            );
          },
        })
        .render("#paypal-button-container");
    } else {
      document.getElementById("paypal-button-container").innerHTML =
        '<p style="color: red;">PayPal checkout is currently unavailable. Please try another payment method.</p>';
    }
  }

  // Form submission handling for credit card payments
  document
    .getElementById("payment-form")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      // Here you would normally process the payment with the selected payment processor
      // For now, we'll just redirect to the thank-you page
      window.location.href =
        "thank-you/index.html?method=" +
        paymentMethod +
        "&email=" +
        encodeURIComponent(document.getElementById("email").value);
    });
});
