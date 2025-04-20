document.addEventListener("DOMContentLoaded", function () {
  // Handle voting
  const voteButtons = document.querySelectorAll(".vote-btn");

  voteButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const postId = this.getAttribute("data-post-id");
      const voteType = this.getAttribute("data-vote") === "up" ? 1 : -1;

      fetch("vote.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `post_id=${postId}&vote_type=${voteType}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update the vote count
            const voteCountElement = document.querySelector(".vote-count");
            voteCountElement.textContent = data.new_vote_count;

            // Change button colors based on user's vote
            const upvoteBtn = document.querySelector(".upvote");
            const downvoteBtn = document.querySelector(".downvote");

            upvoteBtn.style.color = "";
            downvoteBtn.style.color = "";

            if (data.user_vote === 1) {
              upvoteBtn.style.color = "#2563eb";
            } else if (data.user_vote === -1) {
              downvoteBtn.style.color = "#dc2626";
            }
          } else {
            alert("Error voting: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while voting");
        });
    });
  });

  // Comment submission
  const commentForm = document.getElementById("add-comment-form");

  if (commentForm) {
    commentForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const postId = this.getAttribute("data-post-id");
      const contentInput = document.getElementById("comment_content");

      if (!contentInput.value.trim()) {
        alert("Please fill in the comment field");
        return;
      }

      const formData = new FormData();
      formData.append("post_id", postId);
      formData.append("user_name", "Anonymous"); // Default username
      formData.append("user_email", "anonymous@example.com"); // Default email
      formData.append("comment_content", contentInput.value);

      fetch("add_comment.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Reload the page to show the new comment
            window.location.reload();
          } else {
            alert("Error adding comment: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while adding the comment");
        });
    });
  }

  // Admin functionality - Update post status
  const statusDropdown = document.querySelector(".status-update");

  if (statusDropdown) {
    statusDropdown.addEventListener("change", function () {
      const postId = this.getAttribute("data-post-id");
      const newStatus = this.value;

      fetch("update_status.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `post_id=${postId}&status=${newStatus}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update status label
            const statusLabel = document.querySelector(".post-status");

            statusLabel.className = "post-status " + newStatus;

            let statusText = "";
            switch (newStatus) {
              case "open":
                statusText = "Open";
                break;
              case "in_progress":
                statusText = "In Progress";
                break;
              case "completed":
                statusText = "Completed";
                break;
              case "declined":
                statusText = "Declined";
                break;
            }

            statusLabel.textContent = statusText;
          } else {
            alert("Error updating status: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while updating the status");
        });
    });
  }

  // Admin functionality - Delete post
  const deletePostBtn = document.querySelector(".delete-post-btn");

  if (deletePostBtn) {
    deletePostBtn.addEventListener("click", function () {
      if (
        confirm(
          "Are you sure you want to delete this post? This cannot be undone."
        )
      ) {
        const postId = this.getAttribute("data-post-id");

        fetch("delete_post.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `post_id=${postId}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              window.location.href = "index.php";
            } else {
              alert("Error deleting post: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while deleting the post");
          });
      }
    });
  }

  // Admin functionality - Delete comment
  const deleteCommentBtns = document.querySelectorAll(".delete-comment-btn");

  deleteCommentBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      if (confirm("Are you sure you want to delete this comment?")) {
        const commentId = this.getAttribute("data-comment-id");

        fetch("delete_comment.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `comment_id=${commentId}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              const commentElement = document.querySelector(
                `.comment[data-comment-id="${commentId}"]`
              );
              commentElement.remove();

              // Update comment count heading
              const commentsHeading = document.querySelector(
                ".comments-section h3"
              );
              const currentCount = parseInt(commentsHeading.textContent);
              const newCount = currentCount - 1;
              commentsHeading.textContent = `${newCount} Comments`;
            } else {
              alert("Error deleting comment: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while deleting the comment");
          });
      }
    });
  });
});
