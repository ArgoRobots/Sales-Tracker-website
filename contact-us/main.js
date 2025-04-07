window.addEventListener("DOMContentLoaded", function () {
  var form = document.getElementById("my-form");

  // Success and Error functions for after the form is submitted
  function success() {
    form.reset();
    window.open("message-sent-successfully/index.html", "_self");
  }

  function error(status, response, type) {
    console.error("Form submission error:", status, response, type);
    alert(
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

    // Create a simple object with form data
    var formData = new FormData(form);
    var data = {};

    for (var pair of formData.entries()) {
      data[pair[0]] = pair[1];
    }

    fetch(form.action, {
      method: form.method,
      body: formData,
      headers: {
        Accept: "application/json",
      },
    })
      .then((response) => {
        if (response.ok) {
          success();
        } else {
          // Parse error response
          return response.json().then((data) => {
            throw new Error(data.error || "Form submission failed");
          });
        }
      })
      .catch((err) => {
        console.error("Submission error:", err);
        error(null, err.message, null);
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
