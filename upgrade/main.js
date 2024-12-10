document.addEventListener("DOMContentLoaded", () => {
  const priceTag = document.querySelector(".price-tag");
  if (priceTag) {
    priceTag.style.cursor = "pointer";
    priceTag.title = "Click to see payment options";
    priceTag.addEventListener("click", () => {
      const upgradeHeading = Array.from(document.querySelectorAll("h2")).find(
        (h2) => h2.textContent.includes("Ready to Upgrade?")
      );
      upgradeHeading
        ?.closest("section")
        ?.scrollIntoView({ behavior: "smooth" });
    });
  }
});
