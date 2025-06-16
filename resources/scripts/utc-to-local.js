document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("[data-timestamp]").forEach(function (element) {
    const timestamp = parseInt(element.getAttribute("data-timestamp")) * 1000;
    const date = new Date(timestamp);

    // Check if this should show time or just date
    const originalText = element.textContent;
    const hasTime =
      originalText.includes(":") ||
      originalText.includes("am") ||
      originalText.includes("pm");

    if (hasTime) {
      // Format with time: "Jun 15, 2025 7:16 pm"
      const options = {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
      };
      element.textContent = date.toLocaleString("en-US", options);
    } else {
      // Format date only: "Jun 15, 2025"
      const options = {
        month: "short",
        day: "numeric",
        year: "numeric",
      };
      element.textContent = date.toLocaleDateString("en-US", options);
    }
  });
});
