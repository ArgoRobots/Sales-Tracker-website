document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method") || "paypal"; // Default to PayPal

  // Update the form based on payment method
  const formTitle = document.querySelector(".checkout-form h2");
  const paymentForm = document.getElementById("payment-form");

  // Hide the credit card form by default (since we're focusing on PayPal)
  if (paymentForm) {
    paymentForm.style.display = "none";
  }

  // Customize checkout form based on payment method
  switch (paymentMethod) {
    case "paypal":
      formTitle.textContent = "PayPal Checkout";
      setupPayPalCheckout();
      break;
    case "stripe":
      formTitle.textContent = "Coming Soon - Stripe Checkout";
      showComingSoon("Stripe payments will be available soon!");
      break;
    case "square":
      formTitle.textContent = "Coming Soon - Square Checkout";
      showComingSoon("Square payments will be available soon!");
      break;
    default:
      formTitle.textContent = "PayPal Checkout";
      setupPayPalCheckout();
  }

  // Show "coming soon" message for payment methods not yet implemented
  function showComingSoon(message) {
    const container = document.querySelector(".checkout-form");
    // Remove order summary and payment form
    const orderSummary = container.querySelector(".order-summary");
    if (orderSummary) {
      orderSummary.insertAdjacentHTML(
        "afterend",
        `
        <div style="text-align: center; padding: 40px 20px;">
          <p style="font-size: 18px; color: #4b5563; margin-bottom: 20px;">${message}</p>
          <p>For now, please use PayPal for your purchase.</p>
          <button class="checkout-btn" onclick="window.location.href='index.html?method=paypal'" style="margin-top: 20px; max-width: 200px;">
            Switch to PayPal
          </button>
        </div>
      `
      );

      // Hide the payment form if it exists
      if (paymentForm) {
        paymentForm.style.display = "none";
      }
    }
  }

  // PayPal checkout setup
  function setupPayPalCheckout() {
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

          // Handle cancellation
          onCancel: function (data) {
            console.log("Payment cancelled by user");
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

            return actions.order.capture().then(function (orderData) {
              // Successful capture! For demo purposes:
              console.log(
                "Capture result",
                orderData,
                JSON.stringify(orderData, null, 2)
              );

              // Get the transaction details
              const transaction =
                orderData.purchase_units[0].payments.captures[0];
              const payerEmail = orderData.payer.email_address;
              const transactionId = transaction.id;

              // Send transaction data to our server to generate and store a license key
              fetch("process-paypal-payment.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({
                  transaction_id: transactionId,
                  order_id: orderData.id,
                  email: payerEmail,
                  amount: transaction.amount.value,
                  currency: transaction.amount.currency_code,
                  status: transaction.status,
                }),
              })
                .then((response) => response.json())
                .then((data) => {
                  // Check if license key was generated successfully
                  if (data.success && data.license_key) {
                    // Redirect to thank you page with the license key
                    window.location.href =
                      "../thank-you/index.html?order_id=" +
                      orderData.id +
                      "&transaction_id=" +
                      transaction.id +
                      "&license=" +
                      encodeURIComponent(data.license_key) +
                      "&email=" +
                      encodeURIComponent(payerEmail);
                  } else {
                    // Show error and redirect to thank you page with error flag
                    console.error(
                      "License key generation failed:",
                      data.message
                    );
                    window.location.href =
                      "../thank-you/index.html?order_id=" +
                      orderData.id +
                      "&transaction_id=" +
                      transaction.id +
                      "&error=license_failed" +
                      "&email=" +
                      encodeURIComponent(payerEmail);
                  }
                })
                .catch((error) => {
                  console.error("Error processing payment:", error);
                  window.location.href =
                    "../thank-you/index.html?order_id=" +
                    orderData.id +
                    "&transaction_id=" +
                    transaction.id +
                    "&error=server_error" +
                    "&email=" +
                    encodeURIComponent(payerEmail);
                });
            });
          },

          // Handle errors
          onError: function (err) {
            console.error("PayPal error", err);
            const paypalContainer = document.getElementById(
              "paypal-button-container"
            );
            paypalContainer.innerHTML = `
              <div style="color: red; text-align: center; padding: 20px;">
                <p>There was an error processing your payment.</p>
                <p>Please try again or contact support.</p>
                <button class="checkout-btn" onclick="window.location.reload()">Try Again</button>
              </div>
            `;
          },
        })
        .render("#paypal-button-container");
    } else {
      document.getElementById("paypal-button-container").innerHTML = `
        <div style="color: red; text-align: center; padding: 20px;">
          <p>PayPal checkout is currently unavailable.</p>
          <p>Please try again later or contact support.</p>
          <button class="checkout-btn" onclick="window.location.reload()">Refresh Page</button>
        </div>
      `;
    }
  }
});
