document.addEventListener("DOMContentLoaded", function () {
  // Get payment method from URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentMethod = urlParams.get("method");

  // Get subscription details from PHP
  const subscription = window.AI_SUBSCRIPTION;

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

    // Get the appropriate PayPal plan ID based on billing cycle
    const paypalConfig = window.PAYMENT_CONFIG.paypal;
    const planId = subscription.billing === "yearly"
      ? paypalConfig.yearlyPlanId
      : paypalConfig.monthlyPlanId;

    // Determine if we should use subscription mode (requires plan IDs)
    const useSubscriptionMode = planId && planId.length > 0;

    // Check if PayPal is defined
    if (typeof paypal === "undefined") {
      // Show loading message
      paypalContainer.innerHTML = `
      <div style="text-align: center; padding: 40px 0;">
        <p>Loading PayPal...</p>
        <div class="loading-spinner"></div>
      </div>
    `;

      // Load PayPal SDK dynamically with appropriate intent
      const paypalScript = document.createElement("script");
      if (useSubscriptionMode) {
        paypalScript.src = `https://www.paypal.com/sdk/js?client-id=${paypalConfig.clientId}&currency=CAD&vault=true&intent=subscription`;
      } else {
        paypalScript.src = `https://www.paypal.com/sdk/js?client-id=${paypalConfig.clientId}&currency=CAD`;
      }
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

      // Configure buttons based on mode
      const buttonConfig = {
        style: {
          shape: "rect",
          color: "blue",
          layout: "vertical",
          label: useSubscriptionMode ? "subscribe" : "pay",
        },
        onError: function (err) {
          console.error("PayPal error:", err);
          paypalContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 0; color: #b91c1c;">
              <p>Payment failed. Please try again or choose a different payment method.</p>
              <button onclick="location.reload()" style="margin-top: 15px; padding: 10px 20px; cursor: pointer;">Retry</button>
            </div>
          `;
        },
      };

      if (useSubscriptionMode) {
        // Use PayPal Subscriptions API for true recurring billing
        buttonConfig.createSubscription = function (data, actions) {
          return actions.subscription.create({
            plan_id: planId,
            custom_id: `user_${subscription.userId}`,
            application_context: {
              shipping_preference: "NO_SHIPPING",
            },
          });
        };

        buttonConfig.onApprove = function (data, actions) {
          // Process the successful subscription on our server
          return fetch("process-subscription.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              subscriptionID: data.subscriptionID,
              orderID: data.orderID,
              paypal_subscription_id: data.subscriptionID,
              amount: subscription.finalPrice.toFixed(2),
              currency: "CAD",
              billing: subscription.billing,
              hasDiscount: subscription.hasDiscount,
              premiumLicenseKey: subscription.licenseKey,
              status: "completed",
              email: subscription.userEmail,
              payment_method: "paypal",
              user_id: subscription.userId,
              is_paypal_subscription: true,
              update_payment_method: subscription.isUpdatingPaymentMethod || false,
            }),
          })
            .then((response) => response.json())
            .then((result) => {
              if (result.success) {
                window.location.href =
                  "../thank-you/?subscription_id=" +
                  result.subscription_id +
                  "&email=" +
                  encodeURIComponent(subscription.userEmail);
              } else {
                alert(
                  result.error || "Payment was successful but there was an error setting up your subscription. Please contact support."
                );
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              alert("An error occurred. Please contact support.");
            });
        };
      } else {
        // Use one-time payment (fallback when no plan IDs configured)
        buttonConfig.createOrder = function (data, actions) {
          return actions.order.create({
            purchase_units: [
              {
                description: `Argo AI Subscription (${subscription.billing})`,
                amount: {
                  value: subscription.finalPrice.toFixed(2),
                  currency_code: "CAD",
                },
              },
            ],
          });
        };

        buttonConfig.onApprove = function (data, actions) {
          return actions.order.capture().then(function (details) {
            // Process the successful payment on our server
            return fetch("process-subscription.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                orderID: data.orderID,
                payerID: data.payerID,
                amount: subscription.finalPrice.toFixed(2),
                currency: "CAD",
                billing: subscription.billing,
                hasDiscount: subscription.hasDiscount,
                premiumLicenseKey: subscription.licenseKey,
                status: "completed",
                payer_email: details.payer.email_address,
                payer_name:
                  details.payer.name.given_name +
                  " " +
                  details.payer.name.surname,
                payment_method: "paypal",
                user_id: subscription.userId,
                update_payment_method: subscription.isUpdatingPaymentMethod || false,
              }),
            })
              .then((response) => response.json())
              .then((result) => {
                if (result.success) {
                  window.location.href =
                    "../thank-you/?subscription_id=" +
                    result.subscription_id +
                    "&email=" +
                    encodeURIComponent(details.payer.email_address);
                } else {
                  alert(
                    "Payment was successful but there was an error setting up your subscription. Please contact support."
                  );
                }
              })
              .catch((error) => {
                console.error("Error:", error);
                alert("An error occurred. Please contact support.");
              });
          });
        };
      }

      paypal.Buttons(buttonConfig).render("#paypal-button-container");
    }
  }

  function setupStripeCheckout() {
    const stripeContainer = document.getElementById("stripe-container");
    stripeContainer.style.display = "block";

    // Load Stripe
    const stripeScript = document.createElement("script");
    stripeScript.src = "https://js.stripe.com/v3/";
    stripeScript.onload = initializeStripe;
    document.head.appendChild(stripeScript);

    function initializeStripe() {
      const stripe = Stripe(window.PAYMENT_CONFIG.stripe.publishableKey);
      const elements = stripe.elements();
      const cardElement = elements.create("card", {
        style: {
          base: {
            fontSize: "16px",
            color: "#32325d",
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
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

      cardElement.mount("#card-element");

      // Handle form submission
      const form = document.getElementById("stripe-payment-form");
      form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const submitButton = document.getElementById("stripe-submit-btn");
        submitButton.disabled = true;
        submitButton.textContent = "Processing...";

        // Show processing overlay
        const processingOverlay = document.createElement("div");
        processingOverlay.className = "processing-overlay";
        processingOverlay.innerHTML = `
          <div class="spinner"></div>
          <h2>Processing Your Payment</h2>
          <p>Please do not close this window or refresh the page.</p>
        `;
        document.body.appendChild(processingOverlay);

        const cardHolder = document.getElementById("card-holder").value;
        const email = document.getElementById("email").value;

        try {
          // Create payment method
          const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: "card",
            card: cardElement,
            billing_details: {
              name: cardHolder,
              email: email,
            },
          });

          if (error) {
            document.getElementById("card-errors").textContent = error.message;
            const overlay = document.querySelector(".processing-overlay");
            if (overlay) overlay.remove();
            submitButton.disabled = false;
            submitButton.textContent = subscription.isMonthlyWithCredit
              ? "Subscribe - $0.00 Today (Credit Applied)"
              : `Subscribe - $${subscription.finalPrice.toFixed(2)} CAD/${subscription.billing === "yearly" ? "year" : "month"}`;
            return;
          }

          // Send to server
          const response = await fetch("process-subscription.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              payment_method_id: paymentMethod.id,
              email: email,
              name: cardHolder,
              amount: subscription.finalPrice.toFixed(2),
              currency: "CAD",
              billing: subscription.billing,
              hasDiscount: subscription.hasDiscount,
              premiumLicenseKey: subscription.licenseKey,
              payment_method: "stripe",
              user_id: subscription.userId,
              update_payment_method: subscription.isUpdatingPaymentMethod || false,
            }),
          });

          const result = await response.json();

          if (result.success) {
            window.location.href =
              "../thank-you/?subscription_id=" +
              result.subscription_id +
              "&email=" +
              encodeURIComponent(email);
          } else if (result.requires_action) {
            // Handle 3D Secure
            const { error: confirmError } = await stripe.confirmCardPayment(
              result.client_secret
            );
            if (confirmError) {
              document.getElementById("card-errors").textContent =
                confirmError.message;
              const overlay = document.querySelector(".processing-overlay");
              if (overlay) overlay.remove();
              submitButton.disabled = false;
              submitButton.textContent = subscription.isMonthlyWithCredit
                ? "Subscribe - $0.00 Today (Credit Applied)"
                : `Subscribe - $${subscription.finalPrice.toFixed(2)} CAD/${subscription.billing === "yearly" ? "year" : "month"}`;
            } else {
              window.location.href =
                "../thank-you/?subscription_id=" +
                result.subscription_id +
                "&email=" +
                encodeURIComponent(email);
            }
          } else {
            document.getElementById("card-errors").textContent =
              result.error || "Payment failed. Please try again.";
            const overlay = document.querySelector(".processing-overlay");
            if (overlay) overlay.remove();
            submitButton.disabled = false;
            submitButton.textContent = subscription.isMonthlyWithCredit
              ? "Subscribe - $0.00 Today (Credit Applied)"
              : `Subscribe - $${subscription.finalPrice.toFixed(2)} CAD/${subscription.billing === "yearly" ? "year" : "month"}`;
          }
        } catch (err) {
          console.error("Error:", err);
          document.getElementById("card-errors").textContent =
            "An error occurred. Please try again.";
          const overlay = document.querySelector(".processing-overlay");
          if (overlay) overlay.remove();
          submitButton.disabled = false;
          submitButton.textContent = subscription.isMonthlyWithCredit
            ? "Subscribe - $0.00 Today (Credit Applied)"
            : `Subscribe - $${subscription.finalPrice.toFixed(2)} CAD/${subscription.billing === "yearly" ? "year" : "month"}`;
        }
      });
    }
  }

  function setupSquareCheckout() {
    const squareContainer = document.getElementById("square-container");
    squareContainer.style.display = "block";

    // Show loading state
    squareContainer.innerHTML = `
      <div class="loading-square">
        <div class="spinner"></div>
        <p>Loading Square payment form...</p>
      </div>
    `;

    // Load Square SDK
    const squareScript = document.createElement("script");
    squareScript.src = "https://sandbox.web.squarecdn.com/v1/square.js";
    squareScript.onload = initializeSquare;
    squareScript.onerror = () => {
      squareContainer.innerHTML = `
        <div style="text-align: center; padding: 40px 0; color: #b91c1c;">
          <p>Failed to load Square. Please refresh the page or try another payment method.</p>
        </div>
      `;
    };
    document.head.appendChild(squareScript);

    async function initializeSquare() {
      try {
        const payments = Square.payments(
          window.PAYMENT_CONFIG.square.appId,
          window.PAYMENT_CONFIG.square.locationId
        );

        const card = await payments.card();

        // Create form HTML
        const buttonText = subscription.isMonthlyWithCredit
          ? "Subscribe - $0.00 Today (Credit Applied)"
          : `Subscribe - $${subscription.finalPrice.toFixed(2)} CAD/${subscription.billing === "yearly" ? "year" : "month"}`;

        squareContainer.innerHTML = `
          <form id="square-payment-form">
            <div class="form-group">
              <label for="square-email">Email Address</label>
              <input type="email" id="square-email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Card Details</label>
              <div id="card-container"></div>
            </div>
            <div id="payment-error" class="payment-error"></div>
            <button type="submit" id="square-submit-btn" class="checkout-btn ai-checkout-btn">
              ${buttonText}
            </button>
          </form>
        `;

        await card.attach("#card-container");

        // Handle form submission
        const form = document.getElementById("square-payment-form");
        form.addEventListener("submit", async (event) => {
          event.preventDefault();

          const submitButton = document.getElementById("square-submit-btn");
          const errorDiv = document.getElementById("payment-error");
          submitButton.disabled = true;
          submitButton.textContent = "Processing...";
          errorDiv.textContent = "";

          // Show processing overlay
          const processingOverlay = document.createElement("div");
          processingOverlay.className = "processing-overlay";
          processingOverlay.innerHTML = `
            <div class="spinner"></div>
            <h2>Processing Your Payment</h2>
            <p>Please do not close this window or refresh the page.</p>
          `;
          document.body.appendChild(processingOverlay);

          try {
            const result = await card.tokenize();
            if (result.status === "OK") {
              const email = document.getElementById("square-email").value;

              const response = await fetch("process-subscription.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({
                  source_id: result.token,
                  email: email,
                  amount: subscription.finalPrice.toFixed(2),
                  currency: "CAD",
                  billing: subscription.billing,
                  hasDiscount: subscription.hasDiscount,
                  premiumLicenseKey: subscription.licenseKey,
                  payment_method: "square",
                  user_id: subscription.userId,
                  update_payment_method: subscription.isUpdatingPaymentMethod || false,
                }),
              });

              const data = await response.json();

              if (data.success) {
                window.location.href =
                  "../thank-you/?subscription_id=" +
                  data.subscription_id +
                  "&email=" +
                  encodeURIComponent(email);
              } else {
                errorDiv.textContent =
                  data.error || "Payment failed. Please try again.";
                const overlay = document.querySelector(".processing-overlay");
                if (overlay) overlay.remove();
                submitButton.disabled = false;
                submitButton.textContent = buttonText;
              }
            } else {
              errorDiv.textContent = "Card validation failed. Please try again.";
              const overlay = document.querySelector(".processing-overlay");
              if (overlay) overlay.remove();
              submitButton.disabled = false;
              submitButton.textContent = buttonText;
            }
          } catch (err) {
            console.error("Error:", err);
            errorDiv.textContent = "An error occurred. Please try again.";
            const overlay = document.querySelector(".processing-overlay");
            if (overlay) overlay.remove();
            submitButton.disabled = false;
            submitButton.textContent = buttonText;
          }
        });
      } catch (err) {
        console.error("Square initialization error:", err);
        squareContainer.innerHTML = `
          <div style="text-align: center; padding: 40px 0; color: #b91c1c;">
            <p>Failed to initialize Square payment. Please try another payment method.</p>
          </div>
        `;
      }
    }
  }
});
