document.addEventListener("DOMContentLoaded", function () {
  const deleteBtn = document.getElementById("delete-account-btn");
  const deleteModal = document.getElementById("delete-account-modal");
  const deleteInput = document.getElementById("delete-confirm-input");
  const confirmDelete = document.getElementById("confirm-delete");
  const cancelDelete = document.getElementById("cancel-delete");

  // Show message in modal
  function showModalMessage(message, isSuccess = false) {
    // Remove any existing message
    const existingMessage = deleteModal.querySelector(".modal-message");
    if (existingMessage) {
      existingMessage.remove();
    }

    // Create new message element
    const messageDiv = document.createElement("div");
    messageDiv.className = `modal-message ${
      isSuccess ? "success-message" : "error-message"
    }`;
    messageDiv.textContent = message;

    // Insert after the h2 title
    const title = deleteModal.querySelector("h2");
    title.insertAdjacentElement("afterend", messageDiv);
  }

  // Show success message on main page
  function showPageSuccessMessage(message) {
    // Create success message element
    const successDiv = document.createElement("div");
    successDiv.className = "page-success-message";
    successDiv.innerHTML = `
      <div class="success-content">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18ZM13.7071 8.70711C14.0976 8.31658 14.0976 7.68342 13.7071 7.29289C13.3166 6.90237 12.6834 6.90237 12.2929 7.29289L9 10.5858L7.70711 9.29289C7.31658 8.90237 6.68342 8.90237 6.29289 9.29289C5.90237 9.68342 5.90237 10.3166 6.29289 10.7071L8.29289 12.7071C8.68342 13.0976 9.31658 13.0976 9.70711 12.7071L13.7071 8.70711Z" fill="currentColor"/>
        </svg>
        <span>${message}</span>
      </div>
      <div class="countdown">Redirecting in <span id="countdown">10</span> seconds...</div>
    `;

    // Insert at the top of the profile container
    const profileContainer = document.querySelector(".profile-container");
    profileContainer.insertBefore(successDiv, profileContainer.firstChild);

    // Scroll to top to ensure message is visible
    window.scrollTo({ top: 0, behavior: "smooth" });

    // Start countdown
    let timeLeft = 10;
    const countdownElement = successDiv.querySelector("#countdown");
    const countdownInterval = setInterval(() => {
      timeLeft--;
      if (countdownElement) {
        countdownElement.textContent = timeLeft;
      }
      if (timeLeft <= 0) {
        clearInterval(countdownInterval);
        window.location.href = "../index.php";
      }
    }, 1000);
  }

  if (deleteBtn && deleteModal) {
    deleteBtn.addEventListener("click", function () {
      deleteModal.style.display = "flex";
      deleteInput.value = "";
      confirmDelete.disabled = true;
      confirmDelete.textContent = "Schedule Deletion"; // Reset button text
      deleteInput.focus();

      // Clear any existing messages
      const existingMessage = deleteModal.querySelector(".modal-message");
      if (existingMessage) {
        existingMessage.remove();
      }
    });

    deleteInput.addEventListener("input", function () {
      confirmDelete.disabled =
        deleteInput.value.trim().toLowerCase() !== "delete";
    });

    cancelDelete.addEventListener("click", function () {
      deleteModal.style.display = "none";
    });

    confirmDelete.addEventListener("click", function () {
      // Disable button and show loading state
      confirmDelete.disabled = true;
      confirmDelete.textContent = "Processing...";

      fetch("delete_account.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "confirm=1",
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Close modal immediately
            deleteModal.style.display = "none";

            // Show success message on main page
            showPageSuccessMessage(
              data.message || "Account scheduled for deletion successfully!"
            );
          } else {
            showModalMessage(
              data.message || "Error scheduling deletion. Please try again.",
              false
            );
            // Re-enable the button
            confirmDelete.disabled = false;
            confirmDelete.textContent = "Schedule Deletion";
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showModalMessage("Network error. Please try again.", false);
          // Re-enable the button
          confirmDelete.disabled = false;
          confirmDelete.textContent = "Schedule Deletion";
        });
    });
  }
});
