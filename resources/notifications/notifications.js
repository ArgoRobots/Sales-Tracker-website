document.addEventListener("DOMContentLoaded", function () {
  // Process any existing notification messages
  initializeNotifications();

  // Check for new notifications periodically
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.addedNodes && mutation.addedNodes.length > 0) {
        // Check for newly added success or error messages
        mutation.addedNodes.forEach(function (node) {
          if (
            node.classList &&
            (node.classList.contains("success-message") ||
              node.classList.contains("error-message"))
          ) {
            processNotification(node);
          }
        });
      }
    });
  });

  // Start observing the document for added notifications
  observer.observe(document.body, { childList: true, subtree: true });

  function initializeNotifications() {
    // Find all existing notification messages
    const messages = document.querySelectorAll(
      ".success-message, .error-message"
    );

    messages.forEach(function (message) {
      processNotification(message);
    });
  }

  /**
   * Process a notification message
   * @param {HTMLElement} message - The notification element
   */
  function processNotification(message) {
    // Skip if already processed
    if (message.dataset.processed === "true") {
      return;
    }
    message.dataset.processed = "true";
  }
});
