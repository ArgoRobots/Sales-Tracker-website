document.addEventListener("DOMContentLoaded", function () {
  const versionCards = document.querySelectorAll(".version-card");

  versionCards.forEach((card, index) => {
    const header = card.querySelector(".version-header");
    const content = card.querySelector(".version-content");

    // Collapse all except the first one
    if (index !== 0) {
      content.style.maxHeight = "0";
      content.style.overflow = "hidden";
      card.classList.add("collapsed");
    }

    // Add click event to toggle
    header.style.cursor = "pointer";
    header.addEventListener("click", function () {
      const isCollapsed = card.classList.contains("collapsed");

      if (isCollapsed) {
        content.style.maxHeight = content.scrollHeight + "px";
        card.classList.remove("collapsed");
      } else {
        content.style.maxHeight = "0";
        card.classList.add("collapsed");
      }
    });
  });
});
