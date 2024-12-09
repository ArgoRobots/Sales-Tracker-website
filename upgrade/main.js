// Run once when the page loads
window.addEventListener("DOMContentLoaded", () => {
  const mediaQuery = window.matchMedia("(min-width: 768px)");
  const details = document.querySelectorAll(".faq-card");

  // Function to set initial state based on screen size
  function setInitialState(e) {
    details.forEach((detail) => {
      detail.open = e.matches;
    });
  }

  // Set initial state
  setInitialState(mediaQuery);

  // Listen for window resize
  mediaQuery.addListener(setInitialState);
});
