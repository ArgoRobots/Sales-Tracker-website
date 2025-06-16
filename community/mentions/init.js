/**
 * mentions-init.js
 * Initializes the MentionsSystem for consistent @mentions functionality
 * across all pages in the community forum.
 */

document.addEventListener("DOMContentLoaded", function () {
  // Get the post ID if it exists in the page
  let postId = null;
  const postCard = document.querySelector(".post-card");
  if (postCard) {
    postId = postCard.getAttribute("data-post-id");
  }

  // Initialize @mentions system
  window.mentionsSystem = new MentionsSystem({
    postId: postId,
    mentionableElements: ".mentionable", // Use class-based approach
    minChars: 0, // Show dropdown immediately when @ is typed
  });

  // Make the instance globally available to support dynamic content
  window.initMentionsForElement = function (element) {
    if (window.mentionsSystem) {
      window.mentionsSystem.addMentionableElement(element);
    }
  };
});
