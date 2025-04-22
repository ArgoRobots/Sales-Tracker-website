document.addEventListener("DOMContentLoaded", function () {
  // Check if user is logged in
  function isUserLoggedIn() {
    // We'll check this by looking for disabled vote buttons
    const voteBtn = document.querySelector(".vote-btn");
    return voteBtn && !voteBtn.hasAttribute("disabled");
  }

  attachCommentListeners();

  // Handle post voting
  const voteButtons = document.querySelectorAll(".vote-btn");

  voteButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      // If user is not logged in, redirect to login page
      if (!isUserLoggedIn()) {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = "users/login.php";
        return;
      }

      const postId = this.getAttribute("data-post-id");
      const voteType = this.getAttribute("data-vote") === "up" ? 1 : -1;

      // Disable button temporarily to prevent double-clicks
      voteButtons.forEach((button) => (button.disabled = true));

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
            upvoteBtn.classList.remove("voted");
            downvoteBtn.classList.remove("voted");

            if (data.user_vote === 1) {
              upvoteBtn.style.color = "#2563eb";
              upvoteBtn.classList.add("voted");
            } else if (data.user_vote === -1) {
              downvoteBtn.style.color = "#dc2626";
              downvoteBtn.classList.add("voted");
            }
          } else {
            if (data.message === "You must be logged in to vote") {
              window.location.href = "users/login.php";
            } else {
              alert("Error voting: " + data.message);
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while voting");
        })
        .finally(() => {
          // Re-enable buttons after operation is complete
          voteButtons.forEach((button) => (button.disabled = false));
        });
    });
  });

  // Handle comment voting
  const commentVoteButtons = document.querySelectorAll(".comment-vote-btn");

  commentVoteButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      // If user is not logged in, redirect to login page
      if (!isUserLoggedIn()) {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = "users/login.php";
        return;
      }

      const commentId = this.getAttribute("data-comment-id");
      const voteType = this.getAttribute("data-vote") === "up" ? 1 : -1;

      // Find the parent comment element
      const commentElement = this.closest(".comment");

      // Disable all vote buttons for this comment to prevent double-clicks
      const commentBtns = commentElement.querySelectorAll(".comment-vote-btn");
      commentBtns.forEach((button) => (button.disabled = true));

      fetch("vote.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `comment_id=${commentId}&vote_type=${voteType}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update the vote count
            const voteCountElement = commentElement.querySelector(
              ".comment-vote-count"
            );
            voteCountElement.textContent = data.new_vote_count;

            // Change button colors based on user's vote
            const upvoteBtn = commentElement.querySelector(
              ".comment-vote-btn.upvote"
            );
            const downvoteBtn = commentElement.querySelector(
              ".comment-vote-btn.downvote"
            );

            upvoteBtn.style.color = "";
            downvoteBtn.style.color = "";
            upvoteBtn.classList.remove("voted");
            downvoteBtn.classList.remove("voted");

            if (data.user_vote === 1) {
              upvoteBtn.style.color = "#2563eb";
              upvoteBtn.classList.add("voted");
            } else if (data.user_vote === -1) {
              downvoteBtn.style.color = "#dc2626";
              downvoteBtn.classList.add("voted");
            }
          } else {
            if (data.message === "You must be logged in to vote") {
              window.location.href = "users/login.php";
            } else {
              alert("Error voting: " + data.message);
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while voting");
        })
        .finally(() => {
          // Re-enable buttons after operation is complete
          commentBtns.forEach((button) => (button.disabled = false));
        });
    });
  });

  // Comment submission
  const commentForm = document.getElementById("add-comment-form");

  if (commentForm) {
    commentForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // If user is not logged in, redirect to login page
      if (!isUserLoggedIn()) {
        window.location.href = "users/login.php";
        return;
      }

      const postId = this.getAttribute("data-post-id");
      const contentInput = document.getElementById("comment_content");

      if (!contentInput.value.trim()) {
        alert("Please fill in the comment field");
        return;
      }

      const formData = new FormData();
      formData.append("post_id", postId);
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
            if (data.message === "You must be logged in to comment") {
              window.location.href = "users/login.php";
            } else {
              alert("Error adding comment: " + data.message);
            }
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

  // Edit Post functionality
  const editPostBtn = document.querySelector(".edit-post-btn");
  if (editPostBtn) {
    editPostBtn.addEventListener("click", function () {
      const postId = document
        .querySelector(".post-card")
        .getAttribute("data-post-id");
      const title = document.querySelector(".post-title").textContent.trim();
      const content = document.querySelector(".post-body p").textContent.trim();

      // Create edit form
      const postContent = document.querySelector(".post-content");
      const originalHtml = postContent.innerHTML;

      // Store original content for cancellation
      postContent.setAttribute("data-original-html", originalHtml);

      // Replace with edit form
      postContent.innerHTML = `
        <form id="edit-post-form" class="edit-form">
          <input type="hidden" name="post_id" value="${postId}">
          <div class="form-group">
            <label for="edit-title">Title</label>
            <input type="text" id="edit-title" name="title" value="${title}" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="edit-content">Content</label>
            <textarea id="edit-content" name="content" rows="6" class="form-control" required>${content}</textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-secondary cancel-edit-btn">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      `;

      // Event listeners to edit form
      const editForm = document.getElementById("edit-post-form");
      const cancelBtn = document.querySelector(".cancel-edit-btn");

      cancelBtn.addEventListener("click", function () {
        // Restore original content
        postContent.innerHTML = postContent.getAttribute("data-original-html");
        // Re-attach event listeners for edit button
        attachEditPostListener();
      });

      editForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append("post_id", postId);
        formData.append("title", document.getElementById("edit-title").value);
        formData.append(
          "content",
          document.getElementById("edit-content").value
        );

        fetch("edit_post.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Update the page with new content
              document.querySelector(".post-title").textContent =
                data.post.title;
              document.querySelector(".post-body p").textContent =
                data.post.content;

              // Restore original structure
              postContent.innerHTML =
                postContent.getAttribute("data-original-html");

              // Update the title with new content
              document.querySelector(".post-title").textContent =
                data.post.title;

              // Update the body with new content
              document.querySelector(".post-body p").innerHTML = nl2br(
                data.post.content
              );

              // Re-attach event listeners
              attachEditPostListener();

              // Show success message
              alert("Post updated successfully");
            } else {
              alert("Error updating post: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while updating the post");
          });
      });
    });
  }

  // Function to attach edit post listener
  function attachEditPostListener() {
    const editPostBtn = document.querySelector(".edit-post-btn");
    if (editPostBtn) {
      // Clone and replace to remove old event listeners
      const newEditBtn = editPostBtn.cloneNode(true);
      editPostBtn.parentNode.replaceChild(newEditBtn, editPostBtn);

      // Add event listener to new button
      newEditBtn.addEventListener("click", function () {
        const postId = document
          .querySelector(".post-card")
          .getAttribute("data-post-id");
        const title = document.querySelector(".post-title").textContent.trim();
        const content = document
          .querySelector(".post-body p")
          .textContent.trim();

        // Create edit form
        const postContent = document.querySelector(".post-content");
        const originalHtml = postContent.innerHTML;

        // Store original content for cancellation
        postContent.setAttribute("data-original-html", originalHtml);

        // Replace with edit form
        postContent.innerHTML = `
        <form id="edit-post-form" class="edit-form">
          <input type="hidden" name="post_id" value="${postId}">
          <div class="form-group">
            <label for="edit-title">Title</label>
            <input type="text" id="edit-title" name="title" value="${title}" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="edit-content">Content</label>
            <textarea id="edit-content" name="content" rows="6" class="form-control" required>${content}</textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-secondary cancel-edit-btn">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      `;

        // Add event listeners to edit form
        const editForm = document.getElementById("edit-post-form");
        const cancelBtn = document.querySelector(".cancel-edit-btn");

        cancelBtn.addEventListener("click", function () {
          // Restore original content
          postContent.innerHTML =
            postContent.getAttribute("data-original-html");
          // Re-attach event listeners for edit button
          attachEditPostListener();
        });

        editForm.addEventListener("submit", function (e) {
          e.preventDefault();

          const formData = new FormData();
          formData.append("post_id", postId);
          formData.append("title", document.getElementById("edit-title").value);
          formData.append(
            "content",
            document.getElementById("edit-content").value
          );

          fetch("edit_post.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Restore original structure
                postContent.innerHTML =
                  postContent.getAttribute("data-original-html");

                // Update the title with new content
                document.querySelector(".post-title").textContent =
                  data.post.title;

                // Update the body with new content
                document.querySelector(".post-body p").innerHTML = nl2br(
                  data.post.content
                );

                // Re-attach event listeners
                attachEditPostListener();

                // Show success message
                alert("Post updated successfully");
              } else {
                alert("Error updating post: " + data.message);
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              alert("An error occurred while updating the post");
            });
        });
      });
    }
  }

  // Edit Comment functionality
  // Find all edit comment buttons
  const editCommentButtons = document.querySelectorAll(".edit-comment-btn");

  // Add click event listeners to each edit button
  editCommentButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      // Get the comment ID and element
      const commentElement = this.closest(".comment");
      const commentId = commentElement.getAttribute("data-comment-id");

      // Find the comment content within the new structure
      const commentContent = commentElement.querySelector(".comment-content");
      const originalContent = commentContent.innerHTML;

      // Save original content for cancellation
      commentElement.setAttribute("data-original-content", originalContent);

      // Get the comment text (strip any HTML)
      const commentText = commentContent.textContent.trim();

      // Create and insert the edit form
      const formHtml = `
        <form class="inline-edit-form" data-comment-id="${commentId}">
          <div class="form-group">
            <textarea name="comment_content" rows="4" required>${commentText}</textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn cancel-edit">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      `;

      // Replace the comment content with the form
      commentContent.innerHTML = formHtml;

      // Focus on the textarea
      commentContent.querySelector("textarea").focus();

      // Add event listeners for the new form
      const form = commentContent.querySelector("form");
      const cancelButton = form.querySelector(".cancel-edit");

      // Cancel button event
      cancelButton.addEventListener("click", function () {
        // Restore original content
        commentContent.innerHTML = commentElement.getAttribute(
          "data-original-content"
        );
      });

      // Form submit event
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append("comment_id", commentId);

        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = "Saving...";

        // Send AJAX request to update comment
        fetch("edit_comment.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Format the updated content with line breaks
              const updatedContent = data.comment.content.replace(
                /\n/g,
                "<br>"
              );
              commentContent.innerHTML = updatedContent;

              // Call attachCommentListeners to ensure new comments have proper event handlers
              attachCommentListeners();
            } else {
              alert("Error: " + data.message);
              // Restore form on error
              submitButton.disabled = false;
              submitButton.textContent = originalButtonText;
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while updating the comment");
            // Restore form on error
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
          });
      });
    });
  });

  function attachCommentListeners() {
    // Re-attach edit comment listeners
    document.querySelectorAll(".edit-comment-btn").forEach((btn) => {
      // Clone and replace to remove old event listeners
      const newEditBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newEditBtn, btn);

      // Add event listener to new button
      newEditBtn.addEventListener("click", function (e) {
        e.preventDefault();

        // Get the comment ID and element
        const commentElement = this.closest(".comment");
        const commentId = commentElement.getAttribute("data-comment-id");

        // Find the comment content within the new structure
        const commentContent = commentElement.querySelector(".comment-content");
        const originalContent = commentContent.innerHTML;

        // Save original content for cancellation
        commentElement.setAttribute("data-original-content", originalContent);

        // Get the comment text (strip any HTML)
        const commentText = commentContent.textContent.trim();

        // Create and insert the edit form
        const formHtml = `
          <form class="inline-edit-form" data-comment-id="${commentId}">
            <div class="form-group">
              <textarea name="comment_content" rows="4" required>${commentText}</textarea>
            </div>
            <div class="form-actions">
              <button type="button" class="btn cancel-edit">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        `;

        // Replace the comment content with the form
        commentContent.innerHTML = formHtml;

        // Focus on the textarea
        commentContent.querySelector("textarea").focus();

        // Add event listeners for the new form
        const form = commentContent.querySelector("form");
        const cancelButton = form.querySelector(".cancel-edit");

        // Cancel button event
        cancelButton.addEventListener("click", function () {
          // Restore original content
          commentContent.innerHTML = commentElement.getAttribute(
            "data-original-content"
          );
        });

        // Form submit event
        form.addEventListener("submit", function (e) {
          e.preventDefault();

          const formData = new FormData(form);
          formData.append("comment_id", commentId);

          // Show loading state
          const submitButton = form.querySelector('button[type="submit"]');
          const originalButtonText = submitButton.textContent;
          submitButton.disabled = true;
          submitButton.textContent = "Saving...";

          // Send AJAX request to update comment
          fetch("edit_comment.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Format the updated content with line breaks
                const updatedContent = data.comment.content.replace(
                  /\n/g,
                  "<br>"
                );
                commentContent.innerHTML = updatedContent;

                // Call attachCommentListeners to ensure all comments have proper event handlers
                attachCommentListeners();
              } else {
                alert("Error: " + data.message);
                // Restore form on error
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              alert("An error occurred while updating the comment");
              // Restore form on error
              submitButton.disabled = false;
              submitButton.textContent = originalButtonText;
            });
        });
      });
    });

    // Re-attach delete comment listeners
    document.querySelectorAll(".delete-comment-btn").forEach((btn) => {
      // Clone and replace to remove old event listeners
      const newDeleteBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newDeleteBtn, btn);

      // Add event listener to new button
      newDeleteBtn.addEventListener("click", function () {
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

  // Helper function to convert newlines to <br> tags
  function nl2br(str) {
    return str.replace(/\n/g, "<br>");
  }

  // Update UI for Submit Comment button
  const submitButton = document.querySelector(
    ".comment-form button[type='submit']"
  );
  if (submitButton) {
    const submitParent = submitButton.parentElement;

    // Check if submit button is not already in a form-actions container
    if (!submitParent.classList.contains("form-actions")) {
      // Create a form-actions container if needed
      const formActions = document.createElement("div");
      formActions.className = "form-actions";

      // Move button to the container
      submitButton.parentNode.insertBefore(formActions, submitButton);
      formActions.appendChild(submitButton);
    }
  }
});
