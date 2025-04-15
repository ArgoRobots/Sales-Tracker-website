// Updated script for thank you page with test mode support

document.addEventListener("DOMContentLoaded", function () {
  // Get parameters from URL
  const urlParams = new URLSearchParams(window.location.search);
  const orderID = urlParams.get("order_id");
  const transactionID = urlParams.get("transaction_id");
  const paymentMethod = urlParams.get("method");
  const licenseKey = urlParams.get("license");
  const customerEmail = urlParams.get("email");
  const hasError = urlParams.get("error");
  const isTestMode = urlParams.get("test") === "true";

  // Add test mode indicator if applicable
  if (isTestMode) {
    const container = document.querySelector(".thank-you-container");
    const testBanner = document.createElement("div");
    testBanner.className = "test-mode-banner";
    testBanner.innerHTML = "TEST MODE";
    testBanner.style.backgroundColor = "#fdfdea";
    testBanner.style.color = "#92400e";
    testBanner.style.padding = "10px";
    testBanner.style.textAlign = "center";
    testBanner.style.fontWeight = "bold";
    testBanner.style.borderRadius = "4px";
    testBanner.style.marginBottom = "20px";
    testBanner.style.border = "1px solid #fbd38d";

    if (container.firstChild) {
      container.insertBefore(testBanner, container.firstChild);
    } else {
      container.appendChild(testBanner);
    }
  }

  // Get license key element
  const licenseKeyElement = document.getElementById("license-key");

  if (licenseKey) {
    // User has a license key from URL, display it
    licenseKeyElement.textContent = licenseKey;
  } else if (hasError) {
    // Handle error cases
    const thankYouHeading = document.querySelector("h2");
    if (thankYouHeading) {
      thankYouHeading.textContent = "We've received your payment!";
    }

    licenseKeyElement.innerHTML = `
            <div style="color: #B91C1C; text-align: center;">
                <p>We're generating your license key.</p>
                <p>It will be emailed to you shortly.</p>
            </div>
        `;

    // Hide the copy button if there's an error
    const copyBtn = document.querySelector(".copy-btn");
    if (copyBtn) {
      copyBtn.style.display = "none";
    }

    // Add a note about contacting support
    const licenseContainer = document.querySelector(".license-container");
    if (licenseContainer) {
      const supportNote = document.createElement("p");
      supportNote.innerHTML =
        'If you don\'t receive your license key within 15 minutes, please <a href="../../contact-us/index.php" class="link">contact our support team</a>';
      licenseContainer.appendChild(supportNote);
    }
  } else {
    // Generate a license key based on available data
    let generatedKey;

    if (transactionID) {
      // If transaction ID is available, use it to generate the key
      generatedKey = generateLicenseKey(transactionID);
    } else if (orderID) {
      // If order ID is available, use it
      generatedKey = generateLicenseKey(orderID);
    } else {
      // Fallback to using timestamp
      generatedKey = generatePlaceholderLicense();
    }

    licenseKeyElement.textContent = generatedKey;
  }

  // Add the copy button back (unless we're showing an error)
  if (!hasError) {
    const copyBtn = document.createElement("button");
    copyBtn.className = "copy-btn";
    copyBtn.textContent = "Copy";
    copyBtn.onclick = copyLicenseKey;
    licenseKeyElement.appendChild(copyBtn);
  }

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

    // Mark as test transaction if in test mode
    if (isTestMode) {
      paymentMethodDisplay += " (Test)";
    }

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

  // If we have customer email but error generating license, offer manual contact
  if (hasError && customerEmail) {
    const supportMsg = document.createElement("div");
    supportMsg.className = "support-message";
    supportMsg.innerHTML = `
            <p>A confirmation of your purchase has been sent to <strong>${customerEmail}</strong>.</p>
            <p>Our team has been notified and will contact you shortly with your license key.</p>
        `;

    // Find where to insert this message
    const licenseContainer = document.querySelector(".license-container");
    if (licenseContainer && licenseContainer.nextElementSibling) {
      licenseContainer.parentNode.insertBefore(
        supportMsg,
        licenseContainer.nextElementSibling
      );
    }
  }
});

// Function to generate a random license key for demonstration
function generatePlaceholderLicense() {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  let license = "";

  // Generate 4 groups of 4 characters
  for (let group = 0; group < 4; group++) {
    for (let i = 0; i < 4; i++) {
      license += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    if (group < 3) license += "-";
  }

  return license;
}

// Function to generate a license key from a seed value
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
