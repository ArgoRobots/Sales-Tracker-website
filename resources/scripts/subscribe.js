// Newsletter subscribe script
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#subscribeForm");
  if (!form) return;

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
      const response = await fetch("subscribe.php", {
        method: "POST",
        body: formData,
      });
      const result = await response.json();

      // Show confirmation box
      const messageBox = document.createElement("div");
      messageBox.textContent = result.message;
      messageBox.style.padding = "0.75rem";
      messageBox.style.marginTop = "0.75rem";
      messageBox.style.borderRadius = "6px";
      messageBox.style.fontSize = "0.9rem";
      messageBox.style.color = "#fff";
      messageBox.style.background = result.success ? "#16a34a" : "#dc2626";

      form.appendChild(messageBox);

      // Auto-remove message after 4 seconds
      setTimeout(() => messageBox.remove(), 4000);

      if (result.success) form.reset();
    } catch (err) {
      console.error("Error submitting form:", err);
      alert("Something went wrong. Please try again later.");
    }
  });
});
