document.addEventListener("DOMContentLoaded", function () {
  // Get parameters from URL
  const urlParams = new URLSearchParams(window.location.search);
  const orderID = urlParams.get("order_id");
  const transactionID = urlParams.get("transaction_id");
  const paymentMethod = urlParams.get("method");
  const licenseKey = urlParams.get("license");

  // Get license key element
  const licenseKeyElement = document.getElementById("license-key");

  if (licenseKey) {
    // User has a license key from URL, display it
    licenseKeyElement.textContent = licenseKey;
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

  // Add the copy button back
  const copyBtn = document.createElement("button");
  copyBtn.className = "copy-btn";
  copyBtn.textContent = "Copy";
  copyBtn.onclick = copyLicenseKey;
  licenseKeyElement.appendChild(copyBtn);

  // Add transaction details to the page if available
  if (orderID || transactionID) {
    const thankYouCard = document.querySelector(".thank-you-card");
    const transactionDetails = document.createElement("div");
    transactionDetails.className = "transaction-details";
    transactionDetails.innerHTML = `
            <p class="transaction-info">
                ${orderID ? `Order ID: ${orderID}<br>` : ""}
                ${transactionID ? `Transaction ID: ${transactionID}<br>` : ""}
                Payment Method: ${paymentMethod || "Credit Card"}
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
