document.addEventListener("DOMContentLoaded", () => {
  // Function to smoothly scroll to a section
  const smoothScrollToSection = (targetSection) => {
    if (targetSection) {
      const viewportHeight = window.innerHeight;
      const sectionHeight = targetSection.offsetHeight;
      const headerOffset = 80;
      let targetPosition;

      // If section is taller than viewport, scroll to its top with header offset
      // Otherwise, center it in the viewport without offset
      if (sectionHeight > viewportHeight) {
        targetPosition = targetSection.offsetTop - headerOffset;
      } else {
        targetPosition =
          targetSection.offsetTop - viewportHeight / 2 + sectionHeight / 2;
      }

      // Smooth scroll to calculated position
      window.scrollTo({
        top: targetPosition,
        behavior: "smooth",
      });
    }
  };

  // Add click handlers to all scroll trigger elements
  document.querySelectorAll("[data-scroll-to]").forEach((trigger) => {
    trigger.style.cursor = "pointer";

    // Add title attribute if not present
    if (!trigger.title) {
      trigger.title = "Click to scroll to section";
    }

    trigger.addEventListener("click", () => {
      // Get the target section using the data attribute
      const targetId = trigger.getAttribute("data-scroll-to");
      const targetSection = document.getElementById(targetId);

      if (targetSection) {
        smoothScrollToSection(targetSection);
      } else {
        console.warn(`Target section with id "${targetId}" not found`);
      }
    });
  });
});
