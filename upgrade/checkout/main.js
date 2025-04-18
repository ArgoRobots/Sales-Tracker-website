document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method");

  // Update the form based on payment method
  const formTitle = document.querySelector(".checkout-form h2");
  const stripeContainer = document.getElementById("stripe-container");
  const squareContainer = document.getElementById("square-container");

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
      formTitle.textContent = "Square Checkout";
      setupSquareCheckout();
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

    switch (paymentMethod) {
      case "stripe":
        formTitle.textContent = "Stripe Checkout";
        break;
      case "square":
        formTitle.textContent = "Square Checkout";
        break;
      default:
        formTitle.textContent = "PayPal Checkout";
    }

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
    } else if (paymentMethod === "square") {
      setupSquareCheckout();
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

  function setupSquareCheckout() {
    // Create Square container if it doesn't exist or clear existing content
    let squareContainer = document.getElementById("square-container");
    if (!squareContainer) {
      squareContainer = document.createElement("div");
      squareContainer.id = "square-container";
      document.querySelector(".checkout-form").appendChild(squareContainer);
    } else {
      squareContainer.innerHTML = "";
      squareContainer.style.display = "block";
    }

    // Create the Square payment form HTML structure
    squareContainer.innerHTML = `
      <form id="square-payment-form">
        <div class="form-group">
          <label for="square-card-holder">Cardholder Name</label>
          <input type="text" id="square-card-holder" name="square-card-holder" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="square-email">Email Address</label>
          <input type="email" id="square-email" name="square-email" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="square-card">Card Details</label>
          <div id="card-container" class="form-control square-field"></div>
          <div id="square-errors" role="alert" class="payment-error"></div>
        </div>

        <button type="submit" id="square-submit-btn" class="checkout-btn">
          Pay $20.00 CAD
        </button>
      </form>
    `;

    // Show loading indicator while we load the Square JS SDK
    const cardContainer = document.getElementById("card-container");
    if (cardContainer) {
      cardContainer.innerHTML =
        '<div class="loading-square"><div class="spinner"></div><p>Loading payment form...</p></div>';
    }

    // Load Square SDK if not already loaded
    function loadSquareSDK() {
      return new Promise((resolve, reject) => {
        if (window.Square) {
          resolve(window.Square);
          return;
        }

        const script = document.createElement("script");
        script.src = "https://web.squarecdn.com/v1/square.js";
        script.onload = () => {
          if (window.Square) {
            resolve(window.Square);
          } else {
            reject(new Error("Failed to load Square SDK"));
          }
        };
        script.onerror = () => reject(new Error("Failed to load Square SDK"));
        document.head.appendChild(script);
      });
    }

    // Initialize Square payment form
    loadSquareSDK()
      .then((Square) => {
        const appId = "sq0idp-3njfUbN00L39E79k62fTCg"; // Your Square application ID
        const locationId = "LBR20K6QEPC4H"; // Your Square location ID

        // Initialize payments
        const payments = Square.payments(appId, locationId);

        // Create and attach the card payment method
        return payments.card().then((card) => {
          const cardContainer = document.getElementById("card-container");
          if (cardContainer) {
            // Clear loading indicator
            cardContainer.innerHTML = "";

            // Mount the card
            card.attach("#card-container");

            // Return the card instance for use in the form submit handler
            return card;
          } else {
            throw new Error("Card container element not found");
          }
        });
      })
      .then((card) => {
        // Setup the form submit handler
        const form = document.getElementById("square-payment-form");
        if (!form) {
          throw new Error("Payment form not found");
        }

        form.addEventListener("submit", async (event) => {
          event.preventDefault();

          // Validate form fields
          const emailInput = document.getElementById("square-email");
          const cardHolderInput = document.getElementById("square-card-holder");
          const errorContainer = document.getElementById("square-errors");

          if (!emailInput || !emailInput.value) {
            errorContainer.textContent = "Please enter your email address.";
            return;
          }

          if (!cardHolderInput || !cardHolderInput.value) {
            errorContainer.textContent = "Please enter the cardholder name.";
            return;
          }

          // Show processing overlay
          const processingOverlay = document.createElement("div");
          processingOverlay.className = "processing-overlay";
          processingOverlay.innerHTML = `
            <div class="spinner"></div>
            <h2>Processing Your Payment</h2>
            <p>Please do not close this window or refresh the page.</p>
          `;
          document.body.appendChild(processingOverlay);

          // Disable form submission while processing
          const submitButton = document.getElementById("square-submit-btn");
          if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Processing...";
          }

          try {
            // Generate a unique ID for this payment
            const idempotencyKey =
              Date.now().toString() + Math.random().toString(36).substring(2);

            // Tokenize the card
            const tokenResult = await card.tokenize();

            if (tokenResult.status === "OK") {
              // Send payment token to server
              const response = await fetch("process-square-payment.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({
                  source_id: tokenResult.token,
                  idempotency_key: idempotencyKey,
                  email: emailInput.value,
                  reference_id: `ARGO-${Date.now()}`,
                  customer_name: cardHolderInput.value,
                }),
              });

              const data = await response.json();

              if (data.success) {
                // Redirect to thank you page
                window.location.href = `../thank-you/index.html?order_id=${encodeURIComponent(
                  data.order_id || ""
                )}&transaction_id=${encodeURIComponent(
                  data.transaction_id || ""
                )}&license=${encodeURIComponent(
                  data.license_key
                )}&email=${encodeURIComponent(emailInput.value)}&method=square`;
              } else {
                throw new Error(data.message || "Payment processing failed");
              }
            } else {
              throw new Error(
                tokenResult.errors[0]?.message || "Card tokenization failed"
              );
            }
          } catch (error) {
            console.error("Payment error:", error);

            // Remove processing overlay
            const overlay = document.querySelector(".processing-overlay");
            if (overlay) {
              overlay.remove();
            }

            // Show error message
            if (errorContainer) {
              errorContainer.innerHTML = `
                <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-top: 15px;">
                  <strong>Error:</strong> ${
                    error.message ||
                    "An error occurred while processing your payment."
                  }
                  <p>Please try again or contact support if the problem persists.</p>
                </div>
              `;
            }

            // Re-enable submit button
            if (submitButton) {
              submitButton.disabled = false;
              submitButton.textContent = "Pay $20.00 CAD";
            }
          }
        });
      })
      .catch((error) => {
        console.error("Square initialization error:", error);

        // Show initialization error
        const errorMessage = `
          <div class="payment-error" style="background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <strong>Error initializing payment form:</strong> ${error.message}
            <p>Please refresh the page or try a different payment method.</p>
          </div>
        `;

        const squareContainer = document.getElementById("square-container");
        if (squareContainer) {
          squareContainer.innerHTML = errorMessage;
        }
      });
  }
});
