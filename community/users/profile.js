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
});
