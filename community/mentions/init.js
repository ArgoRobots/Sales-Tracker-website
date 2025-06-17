document.addEventListener("DOMContentLoaded", function () {
  // Get the post ID
  let postId = null;
  const postCard = document.querySelector(".post-card");
  if (postCard) {
    postId =
      postCard.getAttribute("data-post-id") ||
      postCard.dataset.postId ||
      postCard.getAttribute("id").replace("post-", "");
    postId = parseInt(postId, 10);
  }

  // Initialize system
  window.mentionsSystem = new MentionsSystem({
    postId: postId,
    mentionableElements: ".mentionable",
    apiEndpoint: "/community/mentions/search.php",
  });
});
