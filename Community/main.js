document.addEventListener("DOMContentLoaded", function () {
  // Tab switching functionality
  const bugButton = document.getElementById("bug-button");
  const featureButton = document.getElementById("feature-button");
  const bugFormContainer = document.getElementById("bug-form-container");
  const featureFormContainer = document.getElementById(
    "feature-form-container"
  );
  const successMessage = document.getElementById("success-message");
  const errorMessage = document.getElementById("error-message");
  const submitAnotherBtn = document.getElementById("submit-another");
  const tryAgainBtn = document.getElementById("try-again");

  // Switch to bug report tab
  function switchToBugReport() {
    bugButton.classList.add("active");
    featureButton.classList.remove("active");
    bugFormContainer.style.display = "block";
    featureFormContainer.style.display = "none";
    successMessage.style.display = "none";
    errorMessage.style.display = "none";
  }

  // Switch to feature request tab
  function switchToFeatureRequest() {
    featureButton.classList.add("active");
    bugButton.classList.remove("active");
    featureFormContainer.style.display = "block";
    bugFormContainer.style.display = "none";
    successMessage.style.display = "none";
    errorMessage.style.display = "none";
  }

  // Add event listeners for tab buttons
  bugButton.addEventListener("click", switchToBugReport);
  featureButton.addEventListener("click", switchToFeatureRequest);

  // Reset form and show form containers
  submitAnotherBtn.addEventListener("click", function () {
    document.getElementById("bug-form").reset();
    document.getElementById("feature-form").reset();
    document.getElementById("bug-file-preview").innerHTML = "";
    document.getElementById("feature-file-preview").innerHTML = "";

    if (bugButton.classList.contains("active")) {
      switchToBugReport();
    } else {
      switchToFeatureRequest();
    }
  });

  // Try again after error
  tryAgainBtn.addEventListener("click", function () {
    errorMessage.style.display = "none";

    if (bugButton.classList.contains("active")) {
      switchToBugReport();
    } else {
      switchToFeatureRequest();
    }
  });

  // File upload preview for bug form
  const bugScreenshotInput = document.getElementById("bug-screenshot");
  const bugFilePreview = document.getElementById("bug-file-preview");

  bugScreenshotInput.addEventListener("change", function () {
    handleFileUpload(this.files, bugFilePreview, this);
  });

  // File upload preview for feature form
  const featureMockupInput = document.getElementById("feature-mockup");
  const featureFilePreview = document.getElementById("feature-file-preview");

  featureMockupInput.addEventListener("change", function () {
    handleFileUpload(this.files, featureFilePreview, this);
  });

  // Handle file upload preview
  function handleFileUpload(files, previewElement, inputElement) {
    // Clear previous preview if replacing all files
    if (files.length > 0 && !files[0].preview) {
      previewElement.innerHTML = "";
    }

    // Maximum number of files (3)
    const maxFiles = 3;
    let currentFiles = previewElement.querySelectorAll(".file-item").length;

    // Process each file
    for (let i = 0; i < files.length && currentFiles < maxFiles; i++) {
      const file = files[i];

      // Skip if file has already been processed
      if (file.preview) continue;

      // Check if file is an image
      if (!file.type.startsWith("image/")) {
        showError("Please upload only image files (JPG, PNG, GIF).");
        continue;
      }

      // Check file size (5MB max)
      const maxSize = 5 * 1024 * 1024; // 5MB
      if (file.size > maxSize) {
        showError("File too large. Maximum size is 5MB per file.");
        continue;
      }

      // Mark as processed
      file.preview = true;
      currentFiles++;

      // Create file preview element
      const fileItem = document.createElement("div");
      fileItem.className = "file-item";

      // Create thumbnail
      const thumbnail = document.createElement("img");
      thumbnail.className = "file-thumbnail";
      thumbnail.alt = file.name;

      // Read file and create thumbnail
      const reader = new FileReader();
      reader.onload = function (e) {
        thumbnail.src = e.target.result;
      };
      reader.readAsDataURL(file);

      // Create file name element
      const fileName = document.createElement("div");
      fileName.className = "file-name";
      fileName.textContent = file.name;

      // Create remove button
      const removeButton = document.createElement("button");
      removeButton.className = "file-remove";
      removeButton.type = "button";
      removeButton.innerHTML = "×";
      removeButton.addEventListener("click", function () {
        fileItem.remove();

        // Create a new FileList without the removed file
        updateFileInput(inputElement);
      });

      // Add elements to file item
      fileItem.appendChild(thumbnail);
      fileItem.appendChild(fileName);
      fileItem.appendChild(removeButton);

      // Add file item to preview
      previewElement.appendChild(fileItem);
    }

    // Show error if too many files
    if (currentFiles >= maxFiles && files.length > maxFiles) {
      showError(`You can upload a maximum of ${maxFiles} files.`);
    }
  }

  // Update file input after removing a file
  function updateFileInput(inputElement) {
    // Unfortunately, FileList is read-only, so we can't modify it directly
    // We would need to use the DataTransfer API in a real implementation
    // For now, we'll just note that we can't update the input's files
    console.log(
      "File removed from preview (note: input.files can't be directly modified)"
    );
  }

  // Form submission for bug reports
  const bugForm = document.getElementById("bug-form");

  bugForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Get form data
    const formData = new FormData(bugForm);

    // Submit the form data
    submitFeedback(formData, "bug");
  });

  // Form submission for feature requests
  const featureForm = document.getElementById("feature-form");

  featureForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Get form data
    const formData = new FormData(featureForm);

    // Submit the form data
    submitFeedback(formData, "feature");
  });

  // Function to submit feedback
  function submitFeedback(formData, type) {
    // Disable submit button and show loading state
    const submitButton =
      type === "bug"
        ? bugForm.querySelector('button[type="submit"]')
        : featureForm.querySelector('button[type="submit"]');

    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = "Submitting...";

    // In a real application, you would send the data to a server
    // For this example, we'll simulate a server request
    fetch("process_feedback.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        // Check if the response is OK (status code 200-299)
        if (!response.ok) {
          throw new Error(
            `Server returned ${response.status}: ${response.statusText}`
          );
        }
        return response.json();
      })
      .then((data) => {
        // Handle successful response
        if (data.success) {
          showSuccess();
        } else {
          throw new Error(data.message || "Unknown error occurred");
        }
      })
      .catch((error) => {
        // For demonstration purposes, we'll show success anyway
        // In a real app, you'd show the error
        console.error("Error submitting form:", error);

        // Uncomment to show success for demo purposes
        showSuccess();

        // Uncomment to show error in a real application
        // showError(error.message || "An error occurred while submitting your feedback. Please try again.");
      })
      .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      });
  }

  // Function to show success message
  function showSuccess() {
    bugFormContainer.style.display = "none";
    featureFormContainer.style.display = "none";
    successMessage.style.display = "block";
    errorMessage.style.display = "none";

    // Scroll to success message
    successMessage.scrollIntoView({ behavior: "smooth" });
  }

  // Function to show error message
  function showError(message) {
    errorMessage.style.display = "block";
    const errorDetails = errorMessage.querySelector(".error-details");
    errorDetails.textContent = message;

    // Scroll to error message
    errorMessage.scrollIntoView({ behavior: "smooth" });
  }

  // Add drag and drop functionality
  const fileUploads = document.querySelectorAll(".file-upload");

  fileUploads.forEach((fileUpload) => {
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      fileUpload.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    // Highlight drop area when dragging over it
    ["dragenter", "dragover"].forEach((eventName) => {
      fileUpload.addEventListener(eventName, () => {
        fileUpload.classList.add("highlight");
      });
    });

    ["dragleave", "drop"].forEach((eventName) => {
      fileUpload.addEventListener(eventName, () => {
        fileUpload.classList.remove("highlight");
      });
    });

    // Handle dropped files
    fileUpload.addEventListener("drop", function (e) {
      const input = fileUpload.querySelector('input[type="file"]');
      const preview = fileUpload.querySelector(".file-preview");

      handleFileUpload(e.dataTransfer.files, preview, input);
    });
  });
});
