document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method");

  // Update the form based on payment method
  const formTitle = document.querySelector(".checkout-form h2");

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

  function setupPayPalCheckout() {
    // Create or clear PayPal button container
    let paypalContainer = document.getElementById("paypal-button-container");
    if (!paypalContainer) {
      paypalContainer = document.createElement("div");
      paypalContainer.id = "paypal-button-container";
      document.querySelector(".checkout-form").appendChild(paypalContainer);
    }

    // Check if PayPal is defined
    if (typeof paypal === "undefined") {
      // Show loading message
      paypalContainer.innerHTML = `
      <div style="text-align: center; padding: 40px 0;">
        <p>Loading PayPal...</p>
        <div class="loading-spinner"></div>
      </div>
    `;

      // Load PayPal SDK dynamically if not available
      const paypalScript = document.createElement("script");
      paypalScript.src = `https://www.paypal.com/sdk/js?client-id=${window.PAYMENT_CONFIG.paypal.clientId}&currency=CAD`;
      paypalScript.onload = initializePayPal;
      paypalScript.onerror = () => {
        paypalContainer.innerHTML = `
        <div style="text-align: center; padding: 40px 0; color: #b91c1c;">
          <p>Failed to load PayPal. Please refresh the page or try another payment method.</p>
        </div>
      `;
      };
      document.head.appendChild(paypalScript);
    } else {
      initializePayPal();
    }

    function initializePayPal() {
      // Clear loading message
      paypalContainer.innerHTML = "";

      // Initialize PayPal buttons
      paypal
        .Buttons({
          createOrder: function (data, actions) {
            return actions.order.create({
              purchase_units: [
                {
                  amount: {
                    value: "20.00",
                    currency_code: "CAD",
                  },
                },
              ],
            });
          },
          onApprove: function (data, actions) {
            return actions.order.capture().then(function (details) {
              // Process the successful payment on our server
              return fetch("process-paypal-payment.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({
                  orderID: data.orderID,
                  payerID: data.payerID,
                  amount: "20.00",
                  currency: "CAD",
                  status: "completed",
                  payer_email: details.payer.email_address,
                  payer_name:
                    details.payer.name.given_name +
                    " " +
                    details.payer.name.surname,
                }),
              })
                .then((response) => response.json())
                .then((data) => {
                  if (data.success) {
                    // Redirect to thank you page with license key and other details
                    window.location.href =
                      "../thank-you/?order_id=" +
                      encodeURIComponent(data.order_id || "") +
                      "&transaction_id=" +
                      encodeURIComponent(data.transaction_id || "") +
                      "&license=" +
                      encodeURIComponent(data.license_key) +
                      "&email=" +
                      encodeURIComponent(details.payer.email_address) +
                      "&method=paypal";
                  } else {
                    throw new Error(
                      data.message || "Failed to generate license key"
                    );
                  }
                });
            });
          },
          onError: function (err) {
            console.error("PayPal payment error:", err);
            alert(
              "There was an error processing your payment. Please try again."
            );
          },
        })
        .render("#paypal-button-container");
    }
  }

  function setupStripeCheckout() {
    // Find the stripe container
    const stripeContainer = document.getElementById("stripe-container");
    if (!stripeContainer) {
      console.error("Stripe container not found");
      return;
    }

    // Show the stripe container
    stripeContainer.style.display = "block";

    // Check if Stripe is already loaded
    if (typeof Stripe === "undefined") {
      // Show loading message in the stripe container
      stripeContainer.innerHTML = `
      <div style="text-align: center; padding: 40px 0;">
        <p>Loading Stripe payment form...</p>
        <div class="loading-spinner"></div>
      </div>
    `;

      // Load Stripe script dynamically
      const stripeScript = document.createElement("script");
      stripeScript.src = "https://js.stripe.com/v3/";
      stripeScript.onload = () => {
        // Once Stripe loads, initialize it
        initializeStripe();
      };
      stripeScript.onerror = () => {
        stripeContainer.innerHTML = `
        <div style="text-align: center; padding: 40px 0; color: #b91c1c;">
          <p>Failed to load Stripe. Please refresh the page or try another payment method.</p>
        </div>
      `;
      };
      document.head.appendChild(stripeScript);
    } else {
      // Stripe is already loaded, initialize immediately
      initializeStripe();
    }

    function initializeStripe() {
      // Create the complete Stripe form
      stripeContainer.innerHTML = `
      <form id="stripe-payment-form">
        <div class="form-group">
          <label for="stripe-card-holder">Cardholder Name</label>
          <input type="text" id="stripe-card-holder" name="stripe-card-holder" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="stripe-card-element">Card Details</label>
          <div id="stripe-card-element" class="form-control">
            <!-- Stripe Elements will be inserted here -->
          </div>
          <div id="stripe-card-errors" role="alert" class="stripe-error"></div>
        </div>

        <div class="form-group">
          <label for="stripe-email">Email Address</label>
          <input type="email" id="stripe-email" name="stripe-email" class="form-control" required>
        </div>

        <button type="submit" id="stripe-submit-btn" class="checkout-btn">
          Pay $20.00 CAD
        </button>
      </form>
    `;

      // Initialize Stripe
      const stripe = Stripe(window.PAYMENT_CONFIG.stripe.publishableKey);
      const elements = stripe.elements();

      // Create card element
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

      // Mount the card element
      const cardElementContainer = document.getElementById(
        "stripe-card-element"
      );
      if (!cardElementContainer) {
        console.error("Stripe card element container not found");
        return;
      }

      cardElement.mount("#stripe-card-element");

      // Handle real-time validation errors
      cardElement.on("change", function (event) {
        const displayError = document.getElementById("stripe-card-errors");
        if (displayError) {
          displayError.textContent = event.error ? event.error.message : "";
        }
      });

      // Handle form submission
      const form = document.getElementById("stripe-payment-form");
      if (!form) {
        console.error("Stripe payment form not found");
        return;
      }

      form.addEventListener("submit", async function (event) {
        event.preventDefault();

        // Get form inputs
        const emailInput = document.getElementById("stripe-email");
        const cardHolderInput = document.getElementById("stripe-card-holder");
        const errorElement = document.getElementById("stripe-card-errors");

        // Validate inputs
        if (!emailInput || !emailInput.value.trim()) {
          if (errorElement)
            errorElement.textContent = "Please enter your email address.";
          return;
        }

        if (!cardHolderInput || !cardHolderInput.value.trim()) {
          if (errorElement)
            errorElement.textContent = "Please enter the cardholder name.";
          return;
        }

        // Clear any previous errors
        if (errorElement) errorElement.textContent = "";

        // Show processing overlay
        const processingOverlay = document.createElement("div");
        processingOverlay.className = "processing-overlay";
        processingOverlay.innerHTML = `
        <div class="spinner"></div>
        <h2>Processing Your Payment</h2>
        <p>Please do not close this window or refresh the page.</p>
      `;
        document.body.appendChild(processingOverlay);

        // Disable submit button
        const submitButton = document.getElementById("stripe-submit-btn");
        if (submitButton) {
          submitButton.disabled = true;
          submitButton.textContent = "Processing...";
        }

        try {
          // Create payment intent
          const paymentIntentResponse = await fetch(
            "stripe-payment-intent.php",
            {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                amount: 2000, // $20.00 in cents
                currency: "CAD",
                email: emailInput.value.trim(),
              }),
            }
          );

          const paymentIntentText = await paymentIntentResponse.text();
          let paymentIntent;

          try {
            paymentIntent = JSON.parse(paymentIntentText);
          } catch (e) {
            throw new Error(
              `Server returned invalid JSON: ${paymentIntentText.substring(
                0,
                100
              )}...`
            );
          }

          if (paymentIntent.error) {
            throw new Error(paymentIntent.error);
          }

          // Confirm payment with Stripe
          const result = await stripe.confirmCardPayment(
            paymentIntent.client_secret,
            {
              payment_method: {
                card: cardElement,
                billing_details: {
                  name: cardHolderInput.value.trim(),
                  email: emailInput.value.trim(),
                },
              },
            }
          );

          if (result.error) {
            throw new Error(result.error.message);
          }

          // Process successful payment
          const processResponse = await fetch("process-stripe-payment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              payment_intent_id: result.paymentIntent.id,
              payment_method_id: result.paymentIntent.payment_method,
              email: emailInput.value.trim(),
              amount: "20.00",
              currency: "CAD",
              status: result.paymentIntent.status,
            }),
          });

          const processData = await processResponse.json();

          if (!processData.success) {
            throw new Error(
              processData.message || "Failed to generate license key"
            );
          }

          // Redirect to success page
          const params = new URLSearchParams({
            order_id: processData.order_id || "",
            transaction_id: processData.transaction_id || "",
            license: processData.license_key,
            email: emailInput.value.trim(),
            method: "stripe",
          });

          window.location.href = `../thank-you/?${params.toString()}`;
        } catch (error) {
          console.error("Stripe payment error:", error);

          // Remove processing overlay
          const overlay = document.querySelector(".processing-overlay");
          if (overlay) overlay.remove();

          // Show error message
          if (errorElement) {
            errorElement.innerHTML = `
            <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-top: 15px;">
              <strong>Error:</strong> ${
                error.message ||
                "An error occurred while processing your payment."
              }
              <p style="margin-top: 8px; margin-bottom: 0;">Please try again or contact support if the problem persists.</p>
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
        // Use sandbox SDK if app ID starts with 'sandbox-'
        const isSandbox =
          window.PAYMENT_CONFIG?.square?.appId?.startsWith("sandbox-");
        script.src = isSandbox
          ? "https://sandbox.web.squarecdn.com/v1/square.js" // Sandbox SDK
          : "https://web.squarecdn.com/v1/square.js"; // Production SDK
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
        const appId = window.PAYMENT_CONFIG.square.appId;
        const locationId = window.PAYMENT_CONFIG.square.locationId;

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
                window.location.href = `../thank-you/?order_id=${encodeURIComponent(
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
