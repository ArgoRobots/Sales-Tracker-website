function adjustLinksAndImages(containerSelector) {
  var path = window.location.pathname;
  path = path.startsWith("/") ? path.substr(1) : path;
  var segments = path.split("/");
  var linkDepth = segments.length - 2;
  var linkPrefix = "";

  // Calculate path prefixes
  for (var i = 0; i < linkDepth; i++) linkPrefix += "../";

  // Adjust relative links
  $(containerSelector + " a").each(function () {
    var href = $(this).attr("href");
    if (!href || href.startsWith("#")) return;

    if (
      !href.startsWith("http://") &&
      !href.startsWith("https://") &&
      !href.startsWith("/") &&
      !href.startsWith("#")
    ) {
      var newHref = linkPrefix + href;
      $(this).attr("href", newHref);
    }
  });
}

// Avatar handling with absolute path
document.addEventListener("DOMContentLoaded", function () {
  fetch("/community/get_avatar_info.php")
    .then((response) => response.json())
    .then((data) => {
      const accountAvatar = document.querySelector(".account-avatar");

      if (accountAvatar) {
        if (data.logged_in) {
          if (data.has_avatar) {
            accountAvatar.innerHTML = `<img src="${data.avatar_url}" alt="Profile">`;
          } else {
            accountAvatar.innerHTML = `<span class="author-avatar-placeholder">${data.initial}</span>`;
          }
        } else {
          accountAvatar.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
          </svg>`;
        }
      }
    })
    .catch((error) => {
      console.error("Error fetching avatar info:", error);
      const accountAvatar = document.querySelector(".account-avatar");
      if (accountAvatar) {
        accountAvatar.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>`;
      }
    });
});

// Apply adjustments to all pages
$(document).ready(function () {
  // Header adjustments
  $("#includeHeader").load("../../resources/header/index.html", function () {
    adjustLinksAndImages("#includeHeader");
  });

  // Footer adjustments
  $("#includeFooter").load("../../resources/footer/index.html", function () {
    adjustLinksAndImages("#includeFooter");
  });
});
