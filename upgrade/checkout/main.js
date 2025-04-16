document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method");

  // Update the form based on payment method
  const formTitle = document.querySelector(".checkout-form h2");
  const stripeContainer = document.getElementById("stripe-container");

  // Store original form HTML for potential reset
  const originalFormHTML = document.querySelector(".checkout-form").innerHTML;

  // Customize checkout form based on payment method
  switch (paymentMethod) {
    case "paypal":
      formTitle.textContent = "PayPal Checkout";
      setupPayPalCheckout();
      break;
    case "stripe":
      formTitle.textContent = "Stripe Checkout";
      setupStripeCheckout();
      break;
    case "square":
      formTitle.textContent = "Coming Soon - Square Checkout";
      showComingSoon("Square payments will be available soon!");
      break;
    default:
      // Default to PayPal if no method specified
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

      // Hide the payment forms
      if (stripeContainer) {
        stripeContainer.style.display = "none";
      }
    }
  }

  // Show error message to the user
  function showErrorMessage(message) {
    // Reset the form first
    document.querySelector(".checkout-form").innerHTML = originalFormHTML;

    // Re-initialize the form based on payment method
    const formTitle = document.querySelector(".checkout-form h2");
    formTitle.textContent =
      paymentMethod === "stripe" ? "Stripe Checkout" : "PayPal Checkout";

    // Add error message
    const orderSummary = document.querySelector(".order-summary");
    if (orderSummary) {
      const errorDiv = document.createElement("div");
      errorDiv.className = "payment-error";
      errorDiv.innerHTML = `
        <p style="color: #b91c1c; background: #fee2e2; padding: 12px; border-radius: 6px; margin: 15px 0;">
          <strong>Error:</strong> ${message}
        </p>
        <p>Please try again or contact support if the problem persists.</p>
      `;
      orderSummary.after(errorDiv);
    }

    // Re-initialize the appropriate payment method
    if (paymentMethod === "stripe") {
      setupStripeCheckout();
    } else {
      setupPayPalCheckout();
    }
  }

  function setupPayPalCheckout() {
    // Create PayPal button container if it doesn't exist
    let paypalContainer = document.getElementById("paypal-button-container");
    if (!paypalContainer) {
      paypalContainer = document.createElement("div");
      paypalContainer.id = "paypal-button-container";
      document.querySelector(".checkout-form").appendChild(paypalContainer);
    }

    // Check if PayPal script is loaded
    if (typeof paypal === "undefined") {
      // PayPal script not loaded, add loading indicator and retry
      paypalContainer.innerHTML = `
        <div style="text-align: center; padding: 40px 0;">
          <p>Loading PayPal...</p>
          <div class="loading-spinner"></div>
        </div>
      `;

      // Check again in 2 seconds
      setTimeout(() => {
        if (typeof paypal === "undefined") {
          // Still not loaded, show error
          paypalContainer.innerHTML = `
            <div style="color: red; text-align: center; padding: 20px;">
              <p>PayPal checkout could not be loaded.</p>
              <button class="checkout-btn" onclick="window.location.reload()">Try Again</button>
            </div>
          `;
        } else {
          // Now loaded, initialize
          initPayPalButton();
        }
      }, 2000);
    } else {
      // PayPal already loaded, initialize
      initPayPalButton();
    }

    function initPayPalButton() {
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
            showErrorMessage(
              "Payment was cancelled. Please try again when you're ready."
            );
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
              console.log("Capture result", orderData);

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
                .then((response) => {
                  if (!response.ok) {
                    throw new Error(
                      `Server responded with status: ${response.status}`
                    );
                  }
                  return response.json();
                })
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
                  showErrorMessage(
                    "Your payment was successful, but we couldn't generate your license key. Our team has been notified and will contact you shortly."
                  );
                });
            });
          },

          // Handle errors
          onError: function (err) {
            console.error("PayPal error", err);
            showErrorMessage(
              "There was an error processing your PayPal payment. Please try again."
            );
          },
        })
        .render("#paypal-button-container");
    }
  }

  function setupStripeCheckout() {
    if (stripeContainer) {
      stripeContainer.style.display = "block";
    }

    // Check if Stripe is defined
    if (typeof Stripe === "undefined") {
      // Show loading message
      const stripeForm = document.getElementById("stripe-payment-form");
      if (stripeForm) {
        stripeForm.innerHTML = `
          <div style="text-align: center; padding: 40px 0;">
            <p>Loading payment form...</p>
            <div class="loading-spinner"></div>
          </div>
        `;
      }

      // Load Stripe dynamically if not available
      const stripeScript = document.createElement("script");
      stripeScript.src = "https://js.stripe.com/v3/";
      stripeScript.onload = initializeStripe;
      document.head.appendChild(stripeScript);
    } else {
      initializeStripe();
    }

    function initializeStripe() {
      // Restore the original form if it was replaced with a loading indicator
      if (!document.getElementById("stripe-payment-form")) {
        document.querySelector(".checkout-form").innerHTML = originalFormHTML;
        if (stripeContainer) {
          stripeContainer.style.display = "block";
        }
      }

      // Initialize Stripe with your publishable key
      // const stripe = Stripe(
      //   "pk_live_51PKOfZFxK6AutkEZGGKjiTTL8EdPCOcbAp9ozLxCXi9UxeiUSSqA4SERUCIpRJDDs48wXeNjxmC1qIMZ437eVYlW00ZgneHz6C"
      // );
      const stripe = Stripe(
        "pk_test_51PKOfZFxK6AutkEZC2fK8RReBMOOD1WnxVDDG6MSBH10pM3NAxAbfYDbTqvcJS1EA4AarWhmx1aqMysGGnLKoKYe00IszbwVLj"
      );
      const elements = stripe.elements();

      // Create an instance of the card Element
      const cardElement = elements.create("card", {
        style: {
          base: {
            color: "#32325d",
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: "antialiased",
            fontSize: "16px",
            "::placeholder": {
              color: "#aab7c4",
            },
          },
          invalid: {
            color: "#fa755a",
            iconColor: "#fa755a",
          },
        },
      });

      // Add an instance of the card Element into the `card-element` div
      const cardElementContainer = document.getElementById("card-element");
      if (cardElementContainer) {
        cardElement.mount("#card-element");
      } else {
        console.error("Card element container not found");
        return;
      }

      // Handle real-time validation errors from the card Element
      cardElement.on("change", function (event) {
        const displayError = document.getElementById("card-errors");
        if (displayError) {
          if (event.error) {
            displayError.textContent = event.error.message;
          } else {
            displayError.textContent = "";
          }
        }
      });

      // Handle form submission
      const form = document.getElementById("stripe-payment-form");
      if (!form) {
        console.error("Stripe payment form not found");
        return;
      }

      form.addEventListener("submit", function (event) {
        event.preventDefault();

        // Get email input
        const emailInput = document.getElementById("email");
        if (!emailInput || !emailInput.value) {
          const errorElement = document.getElementById("card-errors");
          if (errorElement) {
            errorElement.textContent = "Please enter your email address.";
          }
          return;
        }

        // Get card holder
        const cardHolder = document.getElementById("card-holder");
        if (!cardHolder || !cardHolder.value) {
          const errorElement = document.getElementById("card-errors");
          if (errorElement) {
            errorElement.textContent = "Please enter the cardholder name.";
          }
          return;
        }

        // Create and display processing overlay
        const processingOverlay = document.createElement("div");
        processingOverlay.className = "processing-overlay";
        processingOverlay.innerHTML = `
          <div class="spinner"></div>
          <h2>Processing Your Payment</h2>
          <p>Please do not close this window or refresh the page.</p>
        `;
        document.body.appendChild(processingOverlay);

        // Disable the submit button to prevent repeated clicks
        const submitButton = document.getElementById("stripe-submit-btn");
        if (submitButton) {
          submitButton.disabled = true;
          submitButton.textContent = "Processing...";
        }

        // First create the payment intent on the server
        fetch("create-payment-intent.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            amount: 2000, // $20.00 in cents
            currency: "CAD",
            email: emailInput.value,
          }),
        })
          .then(async (response) => {
            // Read the raw response text first
            const text = await response.text();
            // Try to parse it as JSON
            try {
              return JSON.parse(text);
            } catch (e) {
              // If it's not valid JSON, throw an error with the raw response
              throw new Error(
                `Server returned invalid JSON. Raw response: ${text.substring(
                  0,
                  100
                )}...`
              );
            }
          })
          .then((paymentIntent) => {
            if (paymentIntent.error) {
              throw new Error(paymentIntent.error);
            }

            // Continue with Stripe confirmation
            return stripe.confirmCardPayment(paymentIntent.client_secret, {
              payment_method: {
                card: cardElement,
                billing_details: {
                  name: cardHolder.value,
                  email: emailInput.value,
                },
              },
            });
          })
          .then((result) => {
            if (result.error) {
              throw new Error(result.error.message);
            }

            // Payment success - redirect to thank you page
            window.location.href =
              "../thank-you/index.html?order_id=" +
              result.paymentIntent.id +
              "&transaction_id=" +
              result.paymentIntent.payment_method +
              "&email=" +
              encodeURIComponent(emailInput.value) +
              "&method=stripe";
          })
          .catch((error) => {
            console.error("Payment processing error:", error);

            // Show error message and restore form
            document.querySelector(".checkout-form").innerHTML =
              originalFormHTML;

            // Re-initialize necessary elements
            if (typeof initializeStripe === "function") {
              initializeStripe();
            }

            // Display the error message
            const orderSummary = document.querySelector(".order-summary");
            if (orderSummary) {
              const errorDiv = document.createElement("div");
              errorDiv.className = "payment-error";
              errorDiv.style.backgroundColor = "#fee2e2";
              errorDiv.style.color = "#b91c1c";
              errorDiv.style.padding = "12px";
              errorDiv.style.borderRadius = "6px";
              errorDiv.style.marginTop = "15px";
              errorDiv.style.marginBottom = "15px";
              errorDiv.innerHTML = `
                <strong>Error:</strong> ${
                  error.message ||
                  "An error occurred while processing your payment."
                }
                <p>Please try again or contact support if the problem persists.</p>
              `;
              orderSummary.after(errorDiv);
            }

            // Re-enable the submit button
            const submitButton = document.getElementById("stripe-submit-btn");
            if (submitButton) {
              submitButton.disabled = false;
              submitButton.textContent = "Pay $20.00 CAD";
            }
          });
      });
    }
  }
});
