document.addEventListener("DOMContentLoaded", function () {
  // Initialize variables for infinite scrolling
  let page = 1;
  const postsPerPage = 15;
  let isLoading = false;
  let hasMorePosts = true;

  // Cache DOM elements
  const postsContainer = document.getElementById("posts-container");
  const loadingIndicator = document.getElementById("loading-indicator");
  const searchInput = document.getElementById("search-posts");
  const searchBtn = document.getElementById("search-btn");
  const categoryFilter = document.getElementById("category-filter");
  const sortFilter = document.getElementById("sort-filter");
  const selectAllCheckbox = document.getElementById("select-all-posts");
  const bulkActionsDiv = document.querySelector(".bulk-actions");
  const deleteSelectedBtn = document.getElementById("delete-selected");
  const selectedCountSpan = document.querySelector(".selected-count");

  // Create search filter label container
  let searchFilterLabel = document.createElement("div");
  searchFilterLabel.className = "search-filter-label";

  // Insert after community actions
  const communityActions = document.querySelector(".community-actions");
  if (communityActions) {
    communityActions.after(searchFilterLabel);
  }

  // Initial setup
  setupVoteHandlers();
  setupDeletePost();
  setupUpdatePostStatus();
  updateSearchFilterLabel();

  // Initialize countdowns for any rate limit messages on page load
  // Find any countdown elements
  const countdownElements = document.querySelectorAll(".countdown-timer");

  countdownElements.forEach((element) => {
    if (element.dataset.resetTimestamp) {
      startCountdown(element, parseInt(element.dataset.resetTimestamp));
    }
  });

  // Listen for rate limit error events
  document.addEventListener("ratelimit:error", function (e) {
    const data = e.detail;

    if (data && data.rate_limited) {
      showRateLimitNotification(data);
    }
  });

  function isUserLoggedIn() {
    // We'll check this by looking for disabled vote buttons
    const voteBtn = document.querySelector(".vote-btn");
    return voteBtn && !voteBtn.hasAttribute("disabled");
  }

  function isUserAdmin() {
    // Check if bulk actions div exists (which should only be created for admins)
    return bulkActionsDiv !== null;
  }

  function displayServerMessage(messageData) {
    if (!messageData.show_message) return;

    const message = document.createElement("div");
    message.className = "login-alert";
    message.innerHTML = messageData.message_html || messageData.message;

    // Apply all styles from the server
    if (messageData.message_style) {
      Object.entries(messageData.message_style).forEach(([key, value]) => {
        message.style[key] = value;
      });
    }

    document.body.appendChild(message);

    // Remove after specified duration or default to 3 seconds
    setTimeout(() => {
      message.remove();
    }, messageData.message_duration || 3000);
  }

  function setupVoteHandlers() {
    const voteButtons = document.querySelectorAll(".vote-btn");

    voteButtons.forEach((btn) => {
      btn.addEventListener("click", function (e) {
        const postId = this.getAttribute("data-post-id");
        const voteType = this.getAttribute("data-vote") === "up" ? 1 : -1;
        const postCard = this.closest(".post-card");

        // Disable all vote buttons in this post to prevent double-clicks
        const postVoteButtons = postCard.querySelectorAll(".vote-btn");
        postVoteButtons.forEach((button) => (button.disabled = true));

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
              const voteCountElement = postCard.querySelector(".vote-count");
              voteCountElement.textContent = data.new_vote_count;

              // Change button colors based on user's vote
              const upvoteBtn = postCard.querySelector(".upvote");
              const downvoteBtn = postCard.querySelector(".downvote");

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
              // Check for rate limiting
              if (data.rate_limited) {
                // Create and dispatch a custom event for the rate limit system
                const rateLimitEvent = new CustomEvent("ratelimit:error", {
                  detail: data,
                });
                document.dispatchEvent(rateLimitEvent);
              } else if (data.show_message) {
                // Use the existing displayServerMessage function
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
            postVoteButtons.forEach((button) => (button.disabled = false));
          });
      });
    });
  }

  // Hide all posts except the first few
  let allPosts = Array.from(document.querySelectorAll(".post-card"));
  initializePostDisplay();

  function initializePostDisplay() {
    // Hide all posts first
    allPosts.forEach((post, index) => {
      if (index >= postsPerPage) {
        post.style.display = "none";
      } else {
        post.dataset.visible = "true";
      }

      // Add checkbox for selection if user is admin
      if (isUserAdmin()) {
        addSelectionCheckbox(post);
      }
    });

    // Show bulk actions immediately for admin users
    if (isUserAdmin()) {
      toggleSelectionMode(true);
    }

    // Show loading indicator if there are more posts
    if (allPosts.length > postsPerPage) {
      loadingIndicator.style.display = "block";
      setTimeout(() => {
        loadingIndicator.style.display = "none";
      }, 500); // Hide it after a brief moment for initial page load
    }
  }

  function addSelectionCheckbox(post) {
    // Create selection container
    const postSelect = document.createElement("div");
    postSelect.className = "post-select";

    // Create checkbox
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "post-checkbox";
    checkbox.addEventListener("change", function () {
      updateSelectedCount();
      updateDeleteButtonState();
    });

    // Add label for better UX
    const label = document.createElement("label");
    label.className = "post-select-label";
    label.appendChild(checkbox);
    postSelect.appendChild(label);

    // Insert at the beginning of the post card
    post.insertBefore(postSelect, post.firstChild);
  }

  function toggleSelectionMode(enable) {
    if (!isUserLoggedIn()) {
      return; // Non-logged users can't select posts
    }

    allPosts.forEach((post) => {
      if (enable) {
        post.classList.add("selectable");
        // Display the selection checkbox
        const postSelect = post.querySelector(".post-select");
        if (postSelect) {
          postSelect.style.display = "flex";
        }
      } else {
        post.classList.remove("selectable");
        const checkbox = post.querySelector(".post-checkbox");
        if (checkbox) checkbox.checked = false;
        // Hide the selection checkbox
        const postSelect = post.querySelector(".post-select");
        if (postSelect) {
          postSelect.style.display = "none";
        }
      }
    });

    if (bulkActionsDiv) {
      bulkActionsDiv.style.display = enable ? "flex" : "none";
    }
    updateSelectedCount();
    updateDeleteButtonState();
  }

  function updateSelectedCount() {
    if (!selectedCountSpan) return;

    const selectedPosts = document.querySelectorAll(
      ".post-checkbox:checked"
    ).length;
    selectedCountSpan.textContent = selectedPosts + " selected";

    updateSelectAllCheckbox();
  }

  function updateDeleteButtonState() {
    if (!deleteSelectedBtn) return;

    const selectedPosts = document.querySelectorAll(
      ".post-checkbox:checked"
    ).length;
    deleteSelectedBtn.disabled = selectedPosts === 0;
  }

  function updateSelectAllCheckbox() {
    if (!selectAllCheckbox) return;

    const checkboxes = document.querySelectorAll(".post-checkbox");
    const visibleCheckboxes = Array.from(checkboxes).filter((checkbox) => {
      const post = checkbox.closest(".post-card");
      return post && post.style.display !== "none";
    });

    const checkedVisibleCheckboxes = Array.from(visibleCheckboxes).filter(
      (checkbox) => checkbox.checked
    );

    // If all visible checkboxes are checked, check the "Select All" checkbox
    // If some or none are checked, uncheck it
    selectAllCheckbox.checked =
      visibleCheckboxes.length > 0 &&
      checkedVisibleCheckboxes.length === visibleCheckboxes.length;

    // Add indeterminate state when some but not all are selected
    selectAllCheckbox.indeterminate =
      checkedVisibleCheckboxes.length > 0 &&
      checkedVisibleCheckboxes.length < visibleCheckboxes.length;
  }

  // Update the search/filter label
  function updateSearchFilterLabel() {
    if (!searchFilterLabel) return;

    const searchTerm = searchInput.value.trim();
    const category = categoryFilter
      ? categoryFilter.options[categoryFilter.selectedIndex].text
      : "All Categories";
    const sortType = sortFilter
      ? sortFilter.options[sortFilter.selectedIndex].text
      : "Newest First";

    let labelText = "";

    // Build the label text based on applied filters
    if (searchTerm) {
      labelText += `Searching for "${searchTerm}"`;
    }

    if (category !== "All Categories") {
      labelText += (labelText ? " in " : "Showing ") + category;
    }

    labelText +=
      (labelText ? " • " : "Showing posts • ") + `Sorted by ${sortType}`;

    // Display the label
    searchFilterLabel.textContent = labelText;
    searchFilterLabel.style.display = "block";
  }

  // Event listener for select all checkbox
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function () {
      const isChecked = this.checked;
      document.querySelectorAll(".post-checkbox").forEach((checkbox) => {
        const post = checkbox.closest(".post-card");
        if (post && post.style.display !== "none") {
          // Only select visible posts
          checkbox.checked = isChecked;
        }
      });
      updateSelectedCount();
      updateDeleteButtonState();
    });
  }

  // Event listener for delete selected button
  if (deleteSelectedBtn) {
    deleteSelectedBtn.addEventListener("click", function () {
      if (!isUserLoggedIn()) {
        window.location.href = "users/login.php";
        return;
      }

      const selectedPosts = document.querySelectorAll(".post-checkbox:checked");
      const numSelected = selectedPosts.length;

      if (numSelected === 0) return;

      if (
        confirm(
          `Are you sure you want to delete ${numSelected} post${
            numSelected === 1 ? "" : "s"
          }? This action cannot be undone.`
        )
      ) {
        const postIds = Array.from(selectedPosts).map((checkbox) => {
          const post = checkbox.closest(".post-card");
          return post.dataset.postId;
        });

        // Call delete API for each selected post
        Promise.all(
          postIds.map((id) => {
            return fetch("delete_post.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
              body: `post_id=${id}`,
            }).then((response) => response.json());
          })
        )
          .then((results) => {
            // Check if all deletions were successful
            const allSuccessful = results.every((result) => result.success);

            if (allSuccessful) {
              // Remove deleted posts from DOM
              selectedPosts.forEach((checkbox) => {
                const post = checkbox.closest(".post-card");
                post.remove();
              });

              // Update allPosts array
              allPosts = Array.from(document.querySelectorAll(".post-card"));

              // Re-enable selection mode for admin users - without disabling first
              // This prevents the "1 selected" issue after deleting
              if (isUserAdmin()) {
                // Reset the selected count
                if (selectedCountSpan) {
                  selectedCountSpan.textContent = "0 selected";
                }

                // Update the delete button state
                if (deleteSelectedBtn) {
                  deleteSelectedBtn.disabled = true;
                }

                // Uncheck the "select all" checkbox
                if (selectAllCheckbox) {
                  selectAllCheckbox.checked = false;
                }
              }

              // Check if we need to load more posts
              if (allPosts.length === 0) {
                postsContainer.innerHTML = `
                    <div class="empty-state">
                        <h3>No posts yet!</h3>
                        <p>Be the first to create a post in our community.</p>
                    </div>
                `;
              } else {
                checkAndLoadMorePosts();
              }
            } else {
              alert("Some posts could not be deleted. Please try again.");
            }
          })
          .catch((error) => {
            console.error("Error deleting posts:", error);
            alert("An error occurred while deleting posts. Please try again.");
          });
      }
    });
  }

  function loadMorePosts() {
    if (isLoading || !hasMorePosts) return;

    isLoading = true;
    loadingIndicator.style.display = "block";

    // Calculate which posts to show next
    const startIndex = page * postsPerPage;
    const endIndex = startIndex + postsPerPage;

    // Get filtered posts based on current filters
    const filteredPosts = getFilteredPosts();

    // Show next batch of posts
    let visibleCount = 0;

    filteredPosts.forEach((post, index) => {
      if (index >= startIndex && index < endIndex) {
        post.style.display = "flex";
        post.dataset.visible = "true";
        visibleCount++;

        // Add fade-in animation
        post.style.opacity = "0";
        setTimeout(() => {
          post.style.opacity = "1";
          post.style.transition = "opacity 0.3s ease";
        }, 50 * (index - startIndex));
      }
    });

    // Update page counter
    page++;

    // Check if we have more posts to load
    hasMorePosts = endIndex < filteredPosts.length;

    // Hide loading indicator if no more posts
    setTimeout(() => {
      isLoading = false;
      loadingIndicator.style.display = hasMorePosts ? "block" : "none";
    }, 500);

    // Set up vote handlers for newly displayed posts
    setupVoteHandlers();
  }

  function checkAndLoadMorePosts() {
    // Calculate how many posts should be visible
    const shouldBeVisible = Math.min(page * postsPerPage, allPosts.length);
    const visiblePosts = document.querySelectorAll(
      ".post-card[style*='display: flex']"
    ).length;

    // If we don't have enough visible posts, load more
    if (visiblePosts < shouldBeVisible && hasMorePosts) {
      loadMorePosts();
    }
  }

  window.addEventListener("scroll", function () {
    if (isLoading || !hasMorePosts) return;

    // Check if user has scrolled to the bottom
    const scrollY = window.scrollY || window.pageYOffset;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    // Load more when user is near the bottom (200px threshold)
    if (scrollY + windowHeight >= documentHeight - 200) {
      loadMorePosts();
    }
  });

  function getFilteredPosts() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const category = categoryFilter.value;

    // First filter the posts
    let filtered = allPosts.filter((post) => {
      // Filter by category
      if (category !== "all" && post.dataset.postType !== category) {
        return false;
      }

      // Filter by search term
      if (searchTerm) {
        const title = post
          .querySelector(".post-title")
          .textContent.toLowerCase();
        const content = post
          .querySelector(".post-body")
          .textContent.toLowerCase();
        const author = post
          .querySelector(".post-author")
          .textContent.toLowerCase();

        return (
          title.includes(searchTerm) ||
          content.includes(searchTerm) ||
          author.includes(searchTerm)
        );
      }

      return true;
    });

    return filtered;
  }

  function sortPosts(posts) {
    if (!sortFilter) return posts;

    const sortBy = sortFilter.value;
    const postsArray = Array.from(posts);

    // Sort the posts array
    postsArray.sort((a, b) => {
      if (sortBy === "most_voted") {
        const votesA = parseInt(
          a.querySelector(".vote-count").textContent.trim()
        );
        const votesB = parseInt(
          b.querySelector(".vote-count").textContent.trim()
        );

        return votesB - votesA;
      } else if (sortBy === "oldest") {
        const dateTextA = a.querySelector(".post-date").textContent.trim();
        const dateTextB = b.querySelector(".post-date").textContent.trim();

        // Parse the dates
        const dateA = new Date(dateTextA);
        const dateB = new Date(dateTextB);

        return dateA - dateB;
      } else {
        // Sort by date (newest first)
        const dateTextA = a.querySelector(".post-date").textContent.trim();
        const dateTextB = b.querySelector(".post-date").textContent.trim();

        // Parse the dates
        const dateA = new Date(dateTextA);
        const dateB = new Date(dateTextB);

        return dateB - dateA;
      }
    });

    // Remove all posts from container
    postsArray.forEach((post) => {
      post.remove();
    });

    // Re-append in sorted order
    postsArray.forEach((post) => {
      postsContainer.appendChild(post);
    });

    return postsArray;
  }

  function applyFilters() {
    page = 1;
    hasMorePosts = true;
    let filteredPosts = getFilteredPosts();
    filteredPosts = sortPosts(filteredPosts);

    // Update the search/filter label
    updateSearchFilterLabel();

    // Hide all posts first
    allPosts.forEach((post) => {
      post.style.display = "none";
      post.dataset.visible = "false";
    });

    // Show first batch of filtered posts
    filteredPosts.forEach((post, index) => {
      if (index < postsPerPage) {
        post.style.display = "flex";
        post.dataset.visible = "true";
      }
    });

    // Update hasMorePosts flag
    hasMorePosts = filteredPosts.length > postsPerPage;

    // Show/hide loading indicator
    loadingIndicator.style.display = hasMorePosts ? "block" : "none";

    // Show empty state if no posts match
    if (filteredPosts.length === 0) {
      const emptyState = document.querySelector(".empty-state");
      if (emptyState) {
        emptyState.style.display = "block";
        emptyState.innerHTML = `
            <h3>No matching posts</h3>
            <p>Try different search terms or filters</p>
        `;
      } else {
        const newEmptyState = document.createElement("div");
        newEmptyState.className = "empty-state";
        newEmptyState.innerHTML = `
            <h3>No matching posts</h3>
            <p>Try different search terms or filters</p>
        `;
        postsContainer.appendChild(newEmptyState);
      }
    } else {
      const emptyState = document.querySelector(".empty-state");
      if (emptyState) {
        emptyState.style.display = "none";
      }
    }

    // Update select all checkbox and selected count
    if (selectAllCheckbox) {
      selectAllCheckbox.checked = false;
      updateSelectedCount();
      updateDeleteButtonState();
    }

    setupVoteHandlers();
  }

  // Event listeners for filters
  if (categoryFilter) {
    categoryFilter.addEventListener("change", function () {
      applyFilters();
      setTimeout(updateSelectAllCheckbox, 100);
    });
  }

  if (sortFilter) {
    sortFilter.addEventListener("change", function () {
      applyFilters();
      setTimeout(updateSelectAllCheckbox, 100);
    });
  }

  if (searchBtn) {
    searchBtn.addEventListener("click", applyFilters);
  }

  if (searchInput) {
    // Handle Enter key press
    searchInput.addEventListener("keyup", function (e) {
      if (e.key === "Enter") {
        applyFilters();
      }
    });

    // Handle input changes - auto search when emptied
    let typingTimer;
    searchInput.addEventListener("input", function () {
      clearTimeout(typingTimer);

      // If the search field is empty, apply filters immediately
      if (this.value.trim() === "") {
        applyFilters();
      } else {
        // Otherwise, add a small delay for typing
        typingTimer = setTimeout(function () {
          applyFilters();
        }, 500);
      }
    });
  }

  function setupDeletePost() {
    const deletePostButtons = document.querySelectorAll(".delete-post-btn");

    deletePostButtons.forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (
          confirm(
            "Are you sure you want to delete this post? This cannot be undone."
          )
        ) {
          const postId = this.getAttribute("data-post-id");
          const postCard = this.closest(".post-card");

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
                // Remove the post from the DOM
                postCard.remove();

                // Update allPosts array to match DOM
                allPosts = Array.from(document.querySelectorAll(".post-card"));

                // Check if we need to load more posts
                if (allPosts.length === 0) {
                  const postsContainer =
                    document.getElementById("posts-container");
                  postsContainer.innerHTML = `
                  <div class="empty-state">
                    <h3>No posts yet!</h3>
                    <p>Be the first to create a post in our community.</p>
                  </div>
                `;
                } else {
                  checkAndLoadMorePosts();
                }
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
    });
  }

  function setupUpdatePostStatus() {
    const statusDropdowns = document.querySelectorAll(".status-update");

    statusDropdowns.forEach((dropdown) => {
      dropdown.addEventListener("change", function () {
        const postId = this.getAttribute("data-post-id");
        const newStatus = this.value;
        const postCard = this.closest(".post-card");

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
              // Update the status label
              const statusLabel = postCard.querySelector(".post-status");

              // Remove old status class and add new one
              statusLabel.className = "post-status " + newStatus;

              // Update text content
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
    });
  }
});
