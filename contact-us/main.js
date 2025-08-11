window.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("my-form");

  // Success and Error functions for after the form is submitted
  function success() {
    form.reset();
    window.open("message-sent-successfully/index.php", "_self");
  }

  function error(message) {
    console.error("Form submission error:", message);
    alert(
      message ||
        "There was an error sending your message. Please try again or contact support directly at support@argorobots.com"
    );
  }

  // Handle the form submission event
  form.addEventListener("submit", function (ev) {
    ev.preventDefault();

    // Disable the submit button to prevent multiple submissions
    var submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = "SENDING...";
    }

    var formData = new FormData(form);

    fetch(form.action, {
      method: form.method,
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          success();
        } else {
          error(data.message);
        }
      })
      .catch((err) => {
        console.error("Submission error:", err);
        error("Network error. Please try again later.");
      })
      .finally(() => {
        // Re-enable submit button
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = "SUBMIT";
        }
      });
  });
});
