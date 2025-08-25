document.addEventListener("DOMContentLoaded", function () {
  function isUserLoggedIn() {
    // We'll check this by looking for disabled vote buttons
    const voteBtn = document.querySelector(".vote-btn");
    return voteBtn && !voteBtn.hasAttribute("disabled");
  }

  // Set up countdown timer function
  function startCountdown(element, endTime) {
    if (!element) return;

    function updateCountdown() {
      const now = Math.floor(Date.now() / 1000);
      const timeLeft = endTime - now;

      if (timeLeft <= 0) {
        element.textContent = "now";

        // Re-enable submit buttons (both comment and post)
        const commentSubmitBtn = document.querySelector(
          ".comment-form button[type='submit']"
        );
        const postSubmitBtn = document.querySelector(
          ".post-form button[type='submit']"
        );
        const createPostBtn = document.querySelector("#create-post-btn");

        [commentSubmitBtn, postSubmitBtn, createPostBtn].forEach((btn) => {
          if (btn) {
            btn.disabled = false;
            btn.classList.remove("btn-disabled");
          }
        });

        // Remove the rate limit message
        const rateMessage = element.closest(".rate-limit-message");
        if (rateMessage) {
          rateMessage.style.opacity = "0";
          setTimeout(() => {
            if (rateMessage && rateMessage.parentNode) {
              rateMessage.parentNode.removeChild(rateMessage);
            }
          }, 500);
        }

        return;
      }

      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      element.textContent = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
      setTimeout(updateCountdown, 1000);
    }

    updateCountdown();
  }

  // Initialize any countdown timers that exist on page load
  document.querySelectorAll(".countdown-timer").forEach((element) => {
    if (element.dataset.resetTimestamp) {
      startCountdown(element, parseInt(element.dataset.resetTimestamp, 10));
    }
  });

  // Set up voting button visual feedback
  const upvoteBtn = document.querySelector(".upvote");
  const downvoteBtn = document.querySelector(".downvote");

  if (upvoteBtn && upvoteBtn.classList.contains("voted")) {
    upvoteBtn.style.color = "#2563eb";
  }

  if (downvoteBtn && downvoteBtn.classList.contains("voted")) {
    downvoteBtn.style.color = "#dc2626";
  }

  // Set up comment vote buttons
  document.querySelectorAll(".comment-vote-btn.voted").forEach((btn) => {
    if (btn.classList.contains("upvote")) {
      btn.style.color = "#2563eb";
    } else if (btn.classList.contains("downvote")) {
      btn.style.color = "#dc2626";
    }
  });

  function displayServerMessage(messageData) {
    if (!messageData.show_message) return;

    const message = document.createElement("div");

    if (messageData.message_class) {
      message.className = messageData.message_class;
    } else {
      message.className = messageData.success
        ? "success-message"
        : "error-message";
    }

    message.textContent = messageData.message;
    document.body.appendChild(message);

    // Remove after specified duration or default to 3 seconds
    setTimeout(() => {
      message.style.opacity = "0";
      message.style.transform = "translateY(-10px)";
      message.style.transition = "opacity 0.3s, transform 0.3s";

      setTimeout(() => {
        if (message.parentNode) {
          message.parentNode.removeChild(message);
        }
      }, 300);
    }, messageData.message_duration || 3000);
  }

  // Handle rate limit error display
  function handleRateLimitError(data, formContainer, submitButton) {
    // Preserve form content before clearing
    const preservedFormData = {};
    const formInputs = formContainer.querySelectorAll(
      "input, textarea, select"
    );
    formInputs.forEach((input) => {
      if (input.name) {
        preservedFormData[input.name] = input.value;
      }
    });

    // Remove any existing rate limit messages first
    const existingMessages = document.querySelectorAll(".rate-limit-message");
    existingMessages.forEach((el) => el.remove());

    // Create rate limit message container
    const messageContainer = document.createElement("div");
    messageContainer.className = "rate-limit-message";

    let messageHTML;
    // If there's custom HTML provided, use it but clean it first
    if (data.html_message) {
      // Extract just the inner content if it's wrapped in a div with the same class
      if (data.html_message.includes('class="rate-limit-message"')) {
        // Create a temporary element to parse the HTML
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = data.html_message;

        // Get the inner HTML of the first .rate-limit-message div
        const innerMessage = tempDiv.querySelector(".rate-limit-message");
        if (innerMessage) {
          messageHTML = innerMessage.innerHTML;
        } else {
          messageHTML = data.html_message;
        }
      } else {
        messageHTML = data.html_message;
      }
    } else {
      // Otherwise create our own message with countdown
      messageHTML = `You are commenting too frequently. 
      Please wait <span class="countdown-timer" data-reset-timestamp="${data.reset_timestamp}">5m 00s</span> 
      before commenting again.`;
    }

    messageContainer.innerHTML = messageHTML;

    // Insert message before the form
    const commentForm = document.getElementById("add-comment-form");
    if (commentForm) {
      formContainer.insertBefore(messageContainer, commentForm);

      // Restore form content
      setTimeout(() => {
        Object.keys(preservedFormData).forEach((name) => {
          const input = commentForm.querySelector(`[name="${name}"]`);
          if (input) {
            input.value = preservedFormData[name];
          }
        });
      }, 100);

      // Start countdown
      const countdownElement =
        messageContainer.querySelector(".countdown-timer");
      if (countdownElement && data.reset_timestamp) {
        startCountdown(countdownElement, parseInt(data.reset_timestamp, 10));
      }

      // Keep submit button disabled
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add("btn-disabled");
        submitButton.innerHTML = "Submit Comment";
      }
    }
  }

  // Handle post voting
  const voteButtons = document.querySelectorAll(".vote-btn");

  voteButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      const postId = this.getAttribute("data-post-id");
      const voteType = this.getAttribute("data-vote") === "up" ? 1 : -1;

      // Disable all vote buttons in this post to prevent double-clicks
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
            // Display message from server if provided
            if (data.show_message) {
              displayServerMessage(data);
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
            // Display message from server if provided
            if (data.show_message) {
              displayServerMessage(data);
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
      const submitButton = this.querySelector('button[type="submit"]');
      const formContainer = commentForm.parentNode;

      // Validate content
      if (!contentInput.value.trim()) {
        alert("Please fill in the comment field");
        return;
      }

      // Disable submit button while processing
      submitButton.disabled = true;
      submitButton.classList.add("btn-disabled");
      submitButton.innerHTML = "Submitting...";

      const formData = new FormData();
      formData.append("post_id", postId);
      formData.append("comment_content", contentInput.value);

      fetch("add_comment.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Reload the page to show the new comment
            window.location.reload();
          } else {
            // Check for rate limit errors
            if (data.rate_limited) {
              handleRateLimitError(data, formContainer, submitButton);
            } else if (data.message === "You must be logged in to comment") {
              window.location.href = "users/login.php";
            } else {
              alert("Error adding comment: " + data.message);
              // Re-enable submit button
              submitButton.disabled = false;
              submitButton.classList.remove("btn-disabled");
              submitButton.innerHTML = "Submit Comment";
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while adding the comment: " + error.message);
          // Re-enable submit button
          submitButton.disabled = false;
          submitButton.classList.remove("btn-disabled");
          submitButton.innerHTML = "Submit Comment";
        });
    });
  }

  // Admin functionality - Update post status with comment disabling
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

            statusLabel.className =
              "post-status post-status-large " + newStatus;

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

            // Handle comment form visibility based on status
            const commentForm = document.getElementById("add-comment-form");
            const commentsDisabledMessage = document.querySelector(
              ".comments-disabled-message"
            );

            if (newStatus === "completed" || newStatus === "declined") {
              // For completed or declined posts, hide comment form and show disabled message
              if (commentForm) {
                commentForm.style.display = "none";
              }

              if (commentsDisabledMessage) {
                if (newStatus === "completed") {
                  commentsDisabledMessage.innerHTML =
                    "<p>Comments are disabled for completed posts.</p>";
                } else if (newStatus === "declined") {
                  commentsDisabledMessage.innerHTML =
                    "<p>Comments are disabled for declined posts.</p>";
                }
                commentsDisabledMessage.style.display = "block";
              }
            } else {
              // For open or in-progress posts, show comment form and hide disabled message
              if (commentForm) {
                commentForm.style.display = "block";
              }

              if (commentsDisabledMessage) {
                commentsDisabledMessage.style.display = "none";
              }
            }
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

  // Delete post functionality
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

  // Edit and delete comment buttons
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

        // Hide the comment controls (edit/delete buttons)
        const commentControls =
          commentElement.querySelector(".comment-controls");
        if (commentControls) {
          commentControls.style.display = "none";
        }

        // Find the comment content within the new structure
        const commentContent = commentElement.querySelector(".comment-content");
        const originalContent = commentContent.innerHTML;

        // Save original content for cancellation
        commentElement.setAttribute("data-original-content", originalContent);

        // Get the comment text, preserving only the raw @mentions (not the links)
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = originalContent;

        // Replace all @mention links with plain @username text
        const mentionLinks = tempDiv.querySelectorAll("a.link");
        mentionLinks.forEach((link) => {
          // Get the text content (should be @username)
          const mentionText = link.textContent;
          // Replace the link with the plain text mention
          link.replaceWith(mentionText);
        });

        // Get the plain text with preserved @mentions
        const commentText = tempDiv.textContent.trim();

        // Create and insert the edit form - note the action="javascript:void(0);" to prevent page navigation
        const formHtml = `
          <form class="inline-edit-form" action="javascript:void(0);" data-comment-id="${commentId}">
            <div class="form-group">
              <textarea name="comment_content" class="mentionable" rows="4" required>${commentText}</textarea>
            </div>
            <div class="form-actions">
              <button type="button" id="cancel-edit" class="btn btn-gray">Cancel</button>
              <button type="submit" class="btn btn-gray">Save Changes</button>
            </div>
          </form>
        `;

        // Replace the comment content with the form
        commentContent.innerHTML = formHtml;

        // Get the textarea and initialize mentions BEFORE setting focus
        const textarea = commentContent.querySelector("textarea");

        // Initialize mentions system for the new textarea
        initializeMentionsForTextarea(textarea);

        // Set focus and cursor position after a brief delay to ensure mentions is ready
        setTimeout(() => {
          textarea.focus();
          textarea.selectionStart = textarea.selectionEnd =
            textarea.value.length;
        }, 50);

        // Add event listeners for the form
        const form = commentContent.querySelector("form");
        const cancelButton = document.getElementById("cancel-edit");

        // Cancel button event
        cancelButton.addEventListener("click", function () {
          // Restore original content
          commentContent.innerHTML = commentElement.getAttribute(
            "data-original-content"
          );

          // Show the comment controls again
          const commentControls =
            commentElement.querySelector(".comment-controls");
          if (commentControls) {
            commentControls.style.display = "";
          }
        });

        // Form submit event - AJAX implementation to prevent page reload
        form.addEventListener("submit", function (e) {
          e.preventDefault(); // Stop form from submitting normally

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
            // In the form submit event handler, update the success callback:
            .then((data) => {
              if (data.success) {
                // Use the processed_content if available, or fall back to basic formatting
                if (data.comment.processed_content) {
                  commentContent.innerHTML = data.comment.processed_content;
                } else {
                  // If no processed_content provided, use the raw content with line breaks
                  commentContent.innerHTML = data.comment.content.replace(
                    /\n/g,
                    "<br>"
                  );
                }

                // Show the comment controls again
                const commentControls =
                  commentElement.querySelector(".comment-controls");
                if (commentControls) {
                  commentControls.style.display = "";
                }

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
          const commentElement = this.closest(".comment");

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
                // Remove the comment from the DOM
                commentElement.remove();

                // Update comment count heading
                const commentsHeading = document.querySelector(
                  ".comments-section h3"
                );
                // Extract just the number from the heading text
                const currentText = commentsHeading.textContent;
                const currentCount = parseInt(currentText);
                if (!isNaN(currentCount)) {
                  const newCount = currentCount - 1;
                  commentsHeading.textContent = `${newCount} Comments`;
                }
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

  attachCommentListeners();
});

function initializeMentionsForTextarea(textarea) {
  const attemptInit = () => {
    if (
      window.mentionsSystem &&
      typeof window.mentionsSystem.addMentionableElement === "function"
    ) {
      if (textarea.dataset.mentionsInitialized === "true") {
        return true;
      }

      if (!textarea.classList.contains("mentionable")) {
        textarea.classList.add("mentionable");
      }

      const success = window.mentionsSystem.addMentionableElement(textarea);

      if (success) {
        console.log("Mentions initialized for edit textarea");
        return true;
      }
    }
    return false;
  };

  if (!attemptInit()) {
    setTimeout(() => {
      if (!attemptInit()) {
        if (window.mentionsSystem) {
          window.mentionsSystem.attachListeners(textarea);
        }
        console.warn("Had to force initialize mentions for edit textarea");
      }
    }, 100);
  }
}
