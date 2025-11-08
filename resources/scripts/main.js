// Detect the base path for the application
// This handles both production (root) and local XAMPP (subfolder)
function getBasePath() {
  var path = window.location.pathname;

  // Check if we're in a subfolder (common XAMPP setup)
  // Look for common local folder names
  var match = path.match(/^(\/[\w-]+\/)/);

  // If the path doesn't start with common site paths, assume we're in a subfolder
  var sitePaths = ['/upgrade/', '/community/', '/documentation/', '/about-us/',
                   '/contact-us/', '/whats-new/', '/admin/', '/legal/', '/resources/',
                   '/error-pages/', '/images/', '/older-versions/'];

  var isRootPath = sitePaths.some(function(p) { return path.startsWith(p); }) || path === '/' || path === '/index.php';

  if (!isRootPath && match) {
    return match[1]; // Return the subfolder path (e.g., '/Sales-Tracker-website/')
  }

  return '/'; // Production or root-level local setup
}

var BASE_PATH = getBasePath();

function setDefaultAvatar() {
  const accountAvatar = document.querySelector(".account-avatar");
  if (accountAvatar) {
    accountAvatar.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
           viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>`;
  }
}

// Fix all root-relative links to work with BASE_PATH
function fixLinks(container) {
  $(container + " a").each(function () {
    var href = $(this).attr("href");
    // Only fix links that start with / but not // (protocol-relative)
    if (href && href.startsWith("/") && !href.startsWith("//") && BASE_PATH !== "/") {
      $(this).attr("href", BASE_PATH + href.substring(1));
    }
  });

  $(container + " img").each(function () {
    var src = $(this).attr("src");
    // Only fix images that start with / but not // (protocol-relative)
    if (src && src.startsWith("/") && !src.startsWith("//") && BASE_PATH !== "/") {
      $(this).attr("src", BASE_PATH + src.substring(1));
    }
  });
}

// Load header and footer with dynamic base path
$(document).ready(function () {
  $("#includeHeader").load(BASE_PATH + "resources/header/index.html", function () {
    fixLinks("#includeHeader");

    // Load the avatar after the header is loaded
    const accountAvatar = document.querySelector(".account-avatar");
    fetch(BASE_PATH + "community/get_avatar_info.php")
      .then((response) => response.json())
      .then((data) => {
        if (accountAvatar) {
          if (data.logged_in) {
            if (data.has_avatar) {
              accountAvatar.innerHTML = `<img src="${data.avatar_url}" alt="Profile">`;
            } else {
              accountAvatar.innerHTML = `<span class="author-avatar-placeholder">${data.initial}</span>`;
            }
          } else {
            setDefaultAvatar();
          }
        }
      })
      .catch((error) => {
        console.error("Error fetching avatar info:", error);
        setDefaultAvatar();
      });
  });

  $("#includeFooter").load(BASE_PATH + "resources/footer/index.html", function () {
    fixLinks("#includeFooter");
  });
});
