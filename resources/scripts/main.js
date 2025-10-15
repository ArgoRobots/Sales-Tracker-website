(function () {
  // Add the async script
  var script = document.createElement("script");
  script.async = true;
  script.src = "https://www.googletagmanager.com/gtag/js?id=AW-17210317271";
  document.head.appendChild(script);

  // Initialize gtag
  window.dataLayer = window.dataLayer || [];
  function gtag() {
    dataLayer.push(arguments);
  }
  gtag("js", new Date());
  gtag("config", "AW-17210317271");

  // Make gtag available globally
  window.gtag = gtag;
})();

function adjustLinksAndImages(containerSelector) {
  var path = window.location.pathname;
  path = path.startsWith("/") ? path.substr(1) : path;
  var segments = path.split("/");
  var linkDepth = segments.length - 2;
  var linkPrefix = "";

  // Calculate path prefixes
  for (var i = 0; i < linkDepth; i++) {
    linkPrefix += "../";
  }

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

// ✅ Single document ready block
$(document).ready(function () {
  // Load header
  $("#includeHeader").load("../../resources/header/index.html", function () {
    adjustLinksAndImages("#includeHeader");

    // Load the avatar after the header is loaded
    const accountAvatar = document.querySelector(".account-avatar");
    fetch("/community/get_avatar_info.php")
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

  // Load footer
  $("#includeFooter").load("../../resources/footer/index.html", function () {
    adjustLinksAndImages("#includeFooter");
  });

  // Collapsible version cards logic
  const versionCards = $(".version-card");
  versionCards.each(function (index) {
    const header = $(this).find(".version-header");
    const featureList = $(this).find(".feature-list");
    const toggleBtn = header.find(".collapse-toggle");

    // open only the first (latest) version
    if (index === 0) {
      featureList.addClass("open");
      toggleBtn.text("▼");
    } else {
      featureList.removeClass("open");
      toggleBtn.text("▶");
    }

    // toggle expand/collapse
    toggleBtn.on("click", function () {
      featureList.toggleClass("open");
      $(this).text(featureList.hasClass("open") ? "▼" : "▶");
    });
  });
});
