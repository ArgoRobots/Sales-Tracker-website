document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method") || "credit";

  // Update the form based on payment method
  const formTitle = document.querySelector(".checkout-form h2");
  const paymentForm = document.getElementById("payment-form");

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

  // PayPal checkout setup
  // Update your setupPayPalCheckout function
  function setupPayPalCheckout() {
    // Hide the credit card form for PayPal
    if (paymentForm) {
      paymentForm.style.display = "none";
    }

    // Create PayPal button container if it doesn't exist
    let paypalContainer = document.getElementById("paypal-button-container");
    if (!paypalContainer) {
      paypalContainer = document.createElement("div");
      paypalContainer.id = "paypal-button-container";
      document.querySelector(".checkout-form").appendChild(paypalContainer);
    }

    // Initialize PayPal button
    if (typeof paypal !== "undefined") {
      paypal
        .Buttons({
          style: {
            layout: "vertical",
            color: "blue",
            shape: "rect",
            label: "pay",
          },

          // Set up the transaction
          createOrder: function (data, actions) {
            return actions.order.create({
              purchase_units: [
                {
                  description: "Argo Sales Tracker - Lifetime Access",
                  amount: {
                    currency_code: "CAD",
                    value: "20.00",
                  },
                },
              ],
              application_context: {
                shipping_preference: "NO_SHIPPING",
              },
            });
          },

          // Finalize the transaction
          onApprove: function (data, actions) {
            // Show loading indication
            document.querySelector(".checkout-form").innerHTML = `
            <div style="text-align: center;">
              <h2>Processing your payment...</h2>
              <p>Please do not close this window.</p>
              <div class="loading-spinner"></div>
            </div>
          `;

            return actions.order
              .capture()
              .then(function (orderData) {
                // Successful capture
                console.log(
                  "Capture result",
                  orderData,
                  JSON.stringify(orderData, null, 2)
                );
                const transaction =
                  orderData.purchase_units[0].payments.captures[0];

                // Generate a license key based on transaction ID
                const licenseKey = generateLicenseKey(transaction.id);

                // Redirect to thank you page
                window.location.href =
                  "thank-you/index.html?order_id=" +
                  orderData.id +
                  "&transaction_id=" +
                  transaction.id +
                  "&license=" +
                  encodeURIComponent(licenseKey);
              })
              .catch(function (err) {
                // Handle errors during capture
                document.querySelector(".checkout-form").innerHTML = `
              <h2>PayPal Checkout</h2>
              <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="order-item">
                  <span>Argo Sales Tracker - Lifetime Access</span>
                  <span>$20.00 CAD</span>
                </div>
                <div class="order-total">
                  <span>Total</span>
                  <span>$20.00 CAD</span>
                </div>
              </div>
              <div style="color: red; text-align: center; margin: 20px 0;">
                <p>There was an error processing your payment.</p>
                <p>Please try again or contact support.</p>
              </div>
              <div id="paypal-button-container"></div>
            `;

                // Re-render the PayPal buttons
                setupPayPalCheckout();
              });
          },

          // Handle cancellation
          onCancel: function (data) {
            console.log("PayPal payment cancelled", data);
            // Do nothing - user will remain on the checkout page
          },

          // Handle errors
          onError: function (err) {
            console.error("PayPal error", err);

            // Show a more user-friendly error
            const checkoutForm = document.querySelector(".checkout-form");

            // Only modify the content if it hasn't already been changed
            if (
              checkoutForm &&
              !checkoutForm.querySelector(".paypal-error-message")
            ) {
              checkoutForm.innerHTML = `
              <h2>PayPal Checkout</h2>
              <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="order-item">
                  <span>Argo Sales Tracker - Lifetime Access</span>
                  <span>$20.00 CAD</span>
                </div>
                <div class="order-total">
                  <span>Total</span>
                  <span>$20.00 CAD</span>
                </div>
              </div>
              <div class="paypal-error-message" style="color: red; text-align: center; margin: 20px 0;">
                <p>There was an error processing your payment.</p>
                <p>Please try again or contact support.</p>
              </div>
              <div class="payment-buttons">
                <button class="checkout-btn" onclick="window.location.reload()">Try Again</button>
                <button class="checkout-btn" onclick="window.location.href='index.html?method=credit'" style="background-color: #6b7280; margin-top: 10px;">
                  Use Credit Card Instead
                </button>
              </div>
            `;
            }
          },
        })
        .render("#paypal-button-container");
    } else {
      document.getElementById("paypal-button-container").innerHTML = `
      <div style="color: red; text-align: center; padding: 20px;">
        <p>PayPal checkout is currently unavailable.</p>
        <p>Please try another payment method or try again later.</p>
        <button class="checkout-btn" onclick="window.location.href='index.html?method=credit'">
          Use Credit Card Instead
        </button>
      </div>
    `;
    }
  }

  // Helper function to generate a license key
  function generateLicenseKey(seed) {
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    let hash = 0;

    // Create a numeric hash from the seed string
    for (let i = 0; i < seed.length; i++) {
      hash = (hash << 5) - hash + seed.charCodeAt(i);
      hash |= 0; // Convert to 32bit integer
    }

    // Use the hash to generate a deterministic but random-looking key
    let license = "";
    for (let group = 0; group < 4; group++) {
      for (let i = 0; i < 4; i++) {
        // Use different parts of the hash for each character
        const index = Math.abs((hash >> (i * 5 + group * 4)) % chars.length);
        license += chars[index % chars.length];
      }
      if (group < 3) license += "-";
    }

    return license;
  }

  // Form submission handling for credit card payments
  if (paymentForm) {
    paymentForm.addEventListener("submit", function (event) {
      event.preventDefault();

      // Show processing message
      const form = this;
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Processing...";

      // Simulate payment processing (in real implementation, you'd call your payment API)
      setTimeout(function () {
        // Generate mock transaction ID and license key
        const mockTransactionId = "TR" + Date.now();
        const licenseKey = generateLicenseKey(mockTransactionId);

        // Redirect to thank you page
        window.location.href =
          "thank-you/index.html?method=" +
          paymentMethod +
          "&email=" +
          encodeURIComponent(document.getElementById("email").value) +
          "&license=" +
          encodeURIComponent(licenseKey);
      }, 1500);
    });
  }
});
