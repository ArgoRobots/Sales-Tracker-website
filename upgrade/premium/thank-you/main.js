document.addEventListener("DOMContentLoaded", function () {
  // Get parameters from URL
  const urlParams = new URLSearchParams(window.location.search);
  const orderID = urlParams.get("order_id");
  const transactionID = urlParams.get("transaction_id");
  const paymentMethod = urlParams.get("method");
  const licenseKey = urlParams.get("license");
  const customerEmail = urlParams.get("email");

  // Show license key
  const licenseKeyElement = document.getElementById("license-key");
  licenseKeyElement.textContent = licenseKey;

  // Add the copy button back
  const copyBtn = document.createElement("button");
  copyBtn.className = "copy-btn";
  copyBtn.textContent = "Copy";
  copyBtn.onclick = copyLicenseKey;
  licenseKeyElement.appendChild(copyBtn);

  // Add transaction details to the page if available
  if (orderID || transactionID || customerEmail) {
    const thankYouCard = document.querySelector(".thank-you-card");
    const transactionDetails = document.createElement("div");
    transactionDetails.className = "transaction-details";

    let paymentMethodDisplay =
      paymentMethod ||
      (transactionID && transactionID.indexOf("CARD_") === 0
        ? "Credit Card"
        : "PayPal");

    transactionDetails.innerHTML = `
            <p class="transaction-info">
                ${customerEmail ? `Email: ${customerEmail}<br>` : ""}
                ${orderID ? `Order ID: ${orderID}<br>` : ""}
                ${transactionID ? `Transaction ID: ${transactionID}<br>` : ""}
                Payment Method: ${paymentMethodDisplay}
            </p>
        `;

    // Add it right before the next steps
    const nextSteps = document.querySelector(".next-steps");
    if (nextSteps) {
      thankYouCard.insertBefore(transactionDetails, nextSteps);
    } else {
      thankYouCard.appendChild(transactionDetails);
    }
  }
});

// Function to copy license key to clipboard
function copyLicenseKey() {
  const licenseText = document.getElementById("license-key").textContent.trim();
  navigator.clipboard
    .writeText(licenseText)
    .then(() => {
      const copyBtn = document.querySelector(".copy-btn");
      copyBtn.textContent = "Copied!";
      setTimeout(() => {
        copyBtn.textContent = "Copy";
      }, 2000);
    })
    .catch((err) => {
      console.error("Failed to copy: ", err);
    });
}
