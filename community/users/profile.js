document.addEventListener("DOMContentLoaded", function () {
  // Variables for avatar handling
  const profileAvatar = document.getElementById("profile-avatar");
  const fileInput = document.getElementById("avatar");
  const avatarForm = document.getElementById("avatar-form");

  // Avatar click to upload and auto-submit
  if (profileAvatar && fileInput) {
    profileAvatar.addEventListener("click", function (e) {
      if (profileAvatar.classList.contains("editable")) {
        fileInput.click();
      }
    });

    // Auto-submit form when file is selected
    fileInput.addEventListener("change", function () {
      if (this.files && this.files[0]) {
        // Create a preview before submitting the form
        const reader = new FileReader();
        reader.onload = function (e) {
          // Update profile preview
          const avatarPreview = document.getElementById("avatar-preview");
          if (avatarPreview) {
            avatarPreview.src = e.target.result;
          } else {
            // Create preview if it doesn't exist
            const placeholder = document.getElementById("avatar-placeholder");
            if (placeholder) {
              placeholder.style.display = "none";
              const img = document.createElement("img");
              img.id = "avatar-preview";
              img.src = e.target.result;
              img.alt = "Profile preview";
              profileAvatar.prepend(img);
            }
          }

          avatarForm.submit();
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // Check if we need to restore the scroll position
  if (sessionStorage.getItem("scrollPosition")) {
    // Restore the scroll position
    window.scrollTo(0, sessionStorage.getItem("scrollPosition"));
    sessionStorage.removeItem("scrollPosition");
  }

  // Store the current scroll position when clicking on sort links
  document.querySelectorAll(".sort-option").forEach(function (link) {
    link.addEventListener("click", function () {
      // Store current scroll position
      sessionStorage.setItem("scrollPosition", window.scrollY);
    });
  });
});
