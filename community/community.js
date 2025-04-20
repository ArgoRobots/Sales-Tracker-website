document.addEventListener("DOMContentLoaded", function () {
  // Initialize variables for infinite scrolling
  let page = 1;
  const postsPerPage = 5;
  let isLoading = false;
  let hasMorePosts = true;

  // Cache DOM elements
  const postsContainer = document.getElementById("posts-container");
  const loadingIndicator = document.getElementById("loading-indicator");
  const searchInput = document.getElementById("search-posts");
  const searchBtn = document.getElementById("search-btn");
  const categoryFilter = document.getElementById("category-filter");
  const selectAllCheckbox = document.getElementById("select-all-posts");
  const bulkActionsDiv = document.querySelector(".bulk-actions");
  const deleteSelectedBtn = document.getElementById("delete-selected");
  const selectedCountSpan = document.querySelector(".selected-count");

  // Get all posts on the page
  let allPosts = Array.from(document.querySelectorAll(".post-card"));

  // Hide all posts except the first few
  initializePostDisplay();

  // Function to initialize post display
  function initializePostDisplay() {
    // Hide all posts first
    allPosts.forEach((post, index) => {
      if (index >= postsPerPage) {
        post.style.display = "none";
      } else {
        post.dataset.visible = "true";
      }

      // Add checkbox for selection
      addSelectionCheckbox(post);
    });

    // Show loading indicator if there are more posts
    if (allPosts.length > postsPerPage) {
      loadingIndicator.style.display = "block";
      setTimeout(() => {
        loadingIndicator.style.display = "none";
      }, 500); // Hide it after a brief moment for initial page load
    }
  }

  // Add checkbox for post selection
  function addSelectionCheckbox(post) {
    const postSelect = document.createElement("div");
    postSelect.className = "post-select";

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "post-checkbox";
    checkbox.addEventListener("change", function () {
      updateSelectedCount();
      updateDeleteButtonState();
    });

    postSelect.appendChild(checkbox);
    post.insertBefore(postSelect, post.firstChild);
  }

  // Function to toggle selection mode
  function toggleSelectionMode(enable) {
    allPosts.forEach((post) => {
      if (enable) {
        post.classList.add("selectable");
      } else {
        post.classList.remove("selectable");
        const checkbox = post.querySelector(".post-checkbox");
        if (checkbox) checkbox.checked = false;
      }
    });

    bulkActionsDiv.style.display = enable ? "flex" : "none";
    updateSelectedCount();
    updateDeleteButtonState();
  }

  // Event listener for Ctrl+A (select all posts)
  document.addEventListener("keydown", function (e) {
    // Check if Ctrl+A is pressed
    if (e.ctrlKey && e.key === "a" && document.activeElement !== searchInput) {
      e.preventDefault(); // Prevent default select all behavior

      // Enable selection mode if not already enabled
      if (bulkActionsDiv.style.display === "none") {
        toggleSelectionMode(true);
      }

      // Check all visible checkboxes
      selectAllCheckbox.checked = true;
      const visiblePosts = allPosts.filter(
        (post) => post.style.display !== "none"
      );
      visiblePosts.forEach((post) => {
        const checkbox = post.querySelector(".post-checkbox");
        if (checkbox) checkbox.checked = true;
      });

      updateSelectedCount();
      updateDeleteButtonState();
    }
  });

  // Event listener for "Select All" checkbox
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function () {
      const visiblePosts = allPosts.filter(
        (post) => post.style.display !== "none"
      );
      visiblePosts.forEach((post) => {
        const checkbox = post.querySelector(".post-checkbox");
        if (checkbox) checkbox.checked = this.checked;
      });

      updateSelectedCount();
      updateDeleteButtonState();
    });
  }

  // Update selected count
  function updateSelectedCount() {
    const selectedPosts = document.querySelectorAll(
      ".post-checkbox:checked"
    ).length;
    selectedCountSpan.textContent = selectedPosts + " selected";
  }

  // Update delete button state
  function updateDeleteButtonState() {
    const selectedPosts = document.querySelectorAll(
      ".post-checkbox:checked"
    ).length;
    deleteSelectedBtn.disabled = selectedPosts === 0;
  }

  // Event listener for delete selected button
  if (deleteSelectedBtn) {
    deleteSelectedBtn.addEventListener("click", function () {
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

              // Disable selection mode
              toggleSelectionMode(false);

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

  // Function to load more posts
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
  }

  // Function to check and load more posts if needed
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

  // Infinite scroll event listener
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

  // Function to get filtered posts
  function getFilteredPosts() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const category = categoryFilter.value;

    return allPosts.filter((post) => {
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
  }

  // Apply filters function
  function applyFilters() {
    // Reset page counter
    page = 1;
    hasMorePosts = true;

    // Get filtered posts
    const filteredPosts = getFilteredPosts();

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
  }

  // Event listeners for filters
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

  if (categoryFilter) {
    categoryFilter.addEventListener("change", applyFilters);
  }

  // Initialize long-press detection for enabling selection mode
  let longPressTimer;

  allPosts.forEach((post) => {
    post.addEventListener("mousedown", function (e) {
      // Only trigger on the post itself, not on buttons or links inside
      if (
        e.target.closest(".post-votes") ||
        e.target.closest(".post-link") ||
        e.target.closest(".post-actions")
      ) {
        return;
      }

      longPressTimer = setTimeout(() => {
        toggleSelectionMode(true);

        // Select the long-pressed post
        const checkbox = post.querySelector(".post-checkbox");
        if (checkbox) {
          checkbox.checked = true;
          updateSelectedCount();
          updateDeleteButtonState();
        }
      }, 500); // 500ms for long press
    });

    post.addEventListener("mouseup", function () {
      clearTimeout(longPressTimer);
    });

    post.addEventListener("mouseleave", function () {
      clearTimeout(longPressTimer);
    });
  });
});

// Create Post Page - Confirm before leaving with unsaved changes
document.addEventListener("DOMContentLoaded", function () {
  // Check if we're on the create post page
  const createPostForm = document.getElementById("community-post-form");

  if (createPostForm) {
    // Store original form state
    const originalFormState = createPostForm.innerHTML;

    // Flag to track if form has been modified
    let formModified = false;

    // Function to check if form has been modified
    function isFormModified() {
      const inputs = createPostForm.querySelectorAll("input, textarea, select");

      for (const input of inputs) {
        // Skip hidden inputs
        if (input.type === "hidden") continue;

        // Check if text/textarea has been modified
        if (
          (input.type === "text" ||
            input.type === "textarea" ||
            input.type === "email" ||
            input.tagName === "TEXTAREA") &&
          input.value.trim() !== ""
        ) {
          return true;
        }

        // Check if select has been modified
        if (input.tagName === "SELECT" && input.value !== "") {
          return true;
        }
      }

      return false;
    }

    // Add input event listeners to all form fields
    const formElements = createPostForm.querySelectorAll(
      "input, textarea, select"
    );

    formElements.forEach((element) => {
      element.addEventListener("input", function () {
        formModified = isFormModified();
      });
    });

    // Listen for cancel button clicks
    const cancelButton = document.querySelector(".btn.btn-black");

    if (cancelButton) {
      cancelButton.addEventListener("click", function (e) {
        if (formModified) {
          if (
            !confirm(
              "You have unsaved changes. Are you sure you want to leave?"
            )
          ) {
            e.preventDefault();
          }
        }
      });
    }

    // Listen for beforeunload event
    window.addEventListener("beforeunload", function (e) {
      if (formModified) {
        // Standard message for modern browsers
        const message =
          "You have unsaved changes. Are you sure you want to leave?";
        e.returnValue = message;
        return message;
      }
    });
  }
});
