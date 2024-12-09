function adjustLinksAndImages(containerSelector) {
  var path = window.location.pathname;
  // Normalize the path to remove the leading slash if present
  path = path.startsWith("/") ? path.substr(1) : path;
  // Split the path into segments
  var segments = path.split("/");
  // Calculate the depth based on the number of segments - assuming the last segment is a file or an ending slash
  var depth = segments.length - 1;
  var linkDepth = segments.length - 2;
  var prefix = "";
  var linkPrefix = "";
  // Construct the prefix based on the depth for local development
  for (var i = 0; i < depth; i++) {
    prefix += "../";
  }
  for (var i = 0; i < linkDepth; i++) {
    linkPrefix += "../";
  }

  // Append the static server URL to the prefix for adjusting image and script sources
  var staticServerPrefix = prefix + "static.argorobots.ca/";

  // Adjust href for links within the specified container
  $(containerSelector + " a").each(function () {
    var href = $(this).attr("href");
    // Skip adjustment for absolute URLs, apply depth-based prefix for relative navigation
    if (
      !href.startsWith("http://") &&
      !href.startsWith("https://") &&
      !href.startsWith("#")
    ) {
      var newHref = linkPrefix + (href.startsWith("/") ? href.substr(1) : href);
      $(this).attr("href", newHref);
      //console.log("Adjusted href for link:", href, "to", newHref); // Debugging: log adjustment
    }
  });

  // Adjust src for images within the specified container
  $(containerSelector + " img").each(function () {
    var src = $(this).attr("src");
    if (!src.startsWith("http://") && !src.startsWith("https://")) {
      var newSrc =
        staticServerPrefix + (src.startsWith("/") ? src.substr(1) : src);
      $(this).attr("src", newSrc);
      //console.log("Adjusted src for image:", src, "to", newSrc); // Debugging: log adjustment
    }
  });

  // Adjust src for scripts within the specified container
  $(containerSelector + " script").each(function () {
    var src = $(this).attr("src");
    if (src && !src.startsWith("http://") && !src.startsWith("https://")) {
      var newSrc =
        staticServerPrefix + (src.startsWith("/") ? src.substr(1) : src);
      $(this).attr("src", newSrc);
      //console.log("Adjusted src for script:", src, "to", newSrc); // Debugging: log adjustment
    }
  });
}
