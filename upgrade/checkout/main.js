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
      break;
    default:
      formTitle.textContent = "PayPal Checkout";
      setupPayPalCheckout();
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
    // Create or clear PayPal button container
    let paypalContainer = document.getElementById("paypal-button-container");
    if (!paypalContainer) {
      paypalContainer = document.createElement("div");
      paypalContainer.id = "paypal-button-container";
      document.querySelector(".checkout-form").appendChild(paypalContainer);
    } else {
      paypalContainer.innerHTML = "";
    }

    // Initialize PayPal buttons
    paypal
      .Buttons({
        style: {
          layout: "vertical",
          color: "blue",
          shape: "rect",
          label: "pay",
        },

        // Create order
        createOrder: function (data, actions) {
          console.log("Creating PayPal order");
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
              user_action: "CONTINUE", // Don't close the window automatically
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

        // Process approval - avoid using actions.order.capture()
        onApprove: function (data, actions) {
          // Show loading indication
          document.querySelector(".checkout-form").innerHTML = `
            <div style="text-align: center;">
              <h2>Processing your payment...</h2>
              <p>Please do not close this window.</p>
              <div class="loading-spinner"></div>
            </div>
          `;

          console.log("Payment approved, order ID:", data.orderID);

          // Redirect to server-side processing
          window.location.href =
            "process-paypal-payments.php?order_id=" +
            encodeURIComponent(data.orderID);
          return true;
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
      const stripe = Stripe(
        "pk_live_51PKOfZFxK6AutkEZGGKjiTTL8EdPCOcbAp9ozLxCXi9UxeiUSSqA4SERUCIpRJDDs48wXeNjxmC1qIMZ437eVYlW00ZgneHz6C"
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

            // Process the successful payment on our server to generate license key
            return fetch("process-stripe-payment.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                payment_intent_id: result.paymentIntent.id,
                payment_method_id: result.paymentIntent.payment_method,
                email: emailInput.value,
                amount: "20.00", // Full amount in dollars
                currency: "CAD",
                status: result.paymentIntent.status,
              }),
            });
          })
          .then((response) => response.json())
          .then((data) => {
            if (!data.success) {
              throw new Error(data.message || "Failed to generate license key");
            }

            // Redirect to thank you page with license key and other details
            window.location.href =
              "../thank-you/index.html?order_id=" +
              encodeURIComponent(data.order_id || "") +
              "&transaction_id=" +
              encodeURIComponent(data.transaction_id || "") +
              "&license=" +
              encodeURIComponent(data.license_key) +
              "&email=" +
              encodeURIComponent(emailInput.value) +
              "&method=stripe";
          })
          .catch((error) => {
            console.error("Payment processing error:", error);

            // Show error message and restore form
            document.querySelector(".checkout-form").innerHTML =
              originalFormHTML;

            // Remove processing overlay
            const overlay = document.querySelector(".processing-overlay");
            if (overlay) {
              overlay.remove();
            }

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
            error.message || "An error occurred while processing your payment."
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
