document.addEventListener("DOMContentLoaded", () => {
  const priceTag = document.querySelector(".price-tag");

  if (priceTag) {
    priceTag.style.cursor = "pointer";
    priceTag.title = "Click to see payment options";

    priceTag.addEventListener("click", () => {
      const upgradeHeading = Array.from(document.querySelectorAll("h2")).find(
        (h2) => h2.textContent.includes("Ready to Upgrade?")
      );

      if (upgradeHeading?.closest("section")) {
        const section = upgradeHeading.closest("section");
        const viewportHeight = window.innerHeight;
        const sectionHeight = section.offsetHeight;

        // Calculate position to center the section vertically
        const targetPosition =
          section.offsetTop - viewportHeight / 2 + sectionHeight / 2;

        // Smooth scroll to calculated position
        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        });
      }
    });
  }
});
