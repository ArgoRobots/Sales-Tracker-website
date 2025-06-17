/**
 * @mentions system for community pages
 *
 * This module adds @mentions functionality to textareas or content-editable elements
 * and provides an autocomplete dropdown for selecting users.
 */

class MentionsSystem {
  constructor(options = {}) {
    // Default configuration
    this.config = {
      triggerChar: "@",
      maxSuggestions: 5,
      mentionableElements: ".mentionable", // Use the mentionable class consistently
      dropdownClass: "mentions-dropdown",
      linkClass: "link-no-underline",
      postId: null,
      apiEndpoint: "mentions/search.php",
      ...options,
    };

    // State
    this.currentMentionableElement = null;
    this.mentioning = false;
    this.mentionStart = 0;
    this.mentionText = "";
    this.mentionDropdown = null;
    this.selectedIndex = -1;
    this.suggestions = [];
    this.initialized = false;

    // Initialize
    this.init();

    // Set as the global instance
    window.mentionsSystem = this;
    return this;
  }

  /**
   * Initialize the mentions system
   */
  init() {
    if (this.initialized) return;

    // Create the dropdown element if it doesn't exist
    this.createDropdown();

    // Find all mentionable elements and attach event listeners
    document
      .querySelectorAll(this.config.mentionableElements)
      .forEach((element) => {
        this.attachListeners(element);
      });

    // Add click handler to document to close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (
        this.mentionDropdown &&
        !this.mentionDropdown.contains(e.target) &&
        !(
          this.currentMentionableElement &&
          this.currentMentionableElement.contains(e.target)
        )
      ) {
        this.hideDropdown();
      }
    });

    // Add mutation observer to catch dynamically added mentionable elements
    this.observeDynamicElements();

    this.initialized = true;
  }

  /**
   * Create the dropdown element
   */
  createDropdown() {
    if (!document.querySelector(`.${this.config.dropdownClass}`)) {
      this.mentionDropdown = document.createElement("div");
      this.mentionDropdown.className = this.config.dropdownClass;
      this.mentionDropdown.style.display = "none";
      this.mentionDropdown.style.position = "absolute";
      this.mentionDropdown.style.zIndex = "1000"; // Ensure it appears above other elements
      document.body.appendChild(this.mentionDropdown);
    } else {
      this.mentionDropdown = document.querySelector(
        `.${this.config.dropdownClass}`
      );
    }
  }

  /**
   * Observe DOM for dynamically added mentionable elements
   */
  observeDynamicElements() {
    // Create a mutation observer to watch for new elements with the mentionable class
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === "childList") {
          mutation.addedNodes.forEach((node) => {
            // Check if the added node is an element
            if (node.nodeType === Node.ELEMENT_NODE) {
              // Check if the node itself is mentionable
              if (node.classList && node.classList.contains("mentionable")) {
                this.attachListeners(node);
              }

              // Check for mentionable elements inside the added node
              const mentionableElements = node.querySelectorAll(".mentionable");
              mentionableElements.forEach((element) => {
                this.attachListeners(element);
              });
            }
          });
        }
      });
    });

    // Start observing the document with the configured parameters
    observer.observe(document.body, { childList: true, subtree: true });
  }

  /**
   * Attach event listeners to an element
   */
  attachListeners(element) {
    // Skip if already initialized
    if (element.dataset.mentionsInitialized === "true") {
      return;
    }

    // Mark as initialized to prevent duplicate event listeners
    element.dataset.mentionsInitialized = "true";

    // Input events for textarea/input
    element.addEventListener("input", (e) => this.handleInput(e));

    // Keydown for navigation
    element.addEventListener("keydown", (e) => this.handleKeydown(e));

    // Focus to keep track of current element
    element.addEventListener("focus", () => {
      this.currentMentionableElement = element;
    });
  }

  /**
   * Handle input events
   */
  handleInput(e) {
    const el = e.target;
    const text = el.value || el.innerText;
    const cursorPos = this.getCursorPosition(el);

    const mentionState = this.getMentionState(text, cursorPos);

    if (mentionState.mentioning) {
      this.mentioning = true;
      this.mentionStart = mentionState.start;
      this.mentionText = mentionState.text;

      // Always fetch suggestions when mentioning, even with empty text
      this.fetchSuggestions(this.mentionText || "");
    } else {
      this.mentioning = false;
      this.hideDropdown();
    }
  }

  /**
   * Handle keydown events for navigation
   */
  handleKeydown(e) {
    if (!this.mentioning || this.suggestions.length === 0) return;

    switch (e.key) {
      case "ArrowDown":
        e.preventDefault();
        this.selectedIndex = Math.min(
          this.selectedIndex + 1,
          this.suggestions.length - 1
        );
        this.updateDropdownSelection();
        break;

      case "ArrowUp":
        e.preventDefault();
        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
        this.updateDropdownSelection();
        break;

      case "Enter":
      case "Tab":
        if (this.selectedIndex >= 0) {
          e.preventDefault();
          this.selectMention(this.suggestions[this.selectedIndex]);
        }
        break;

      case "Escape":
        e.preventDefault();
        this.hideDropdown();
        break;
    }
  }

  /**
   * Get the current cursor position in an element
   */
  getCursorPosition(element) {
    if (
      element.tagName.toLowerCase() === "textarea" ||
      element.tagName.toLowerCase() === "input"
    ) {
      return element.selectionStart;
    } else {
      // For contenteditable
      const selection = window.getSelection();
      const range = selection.getRangeAt(0);
      return range.startOffset;
    }
  }

  /**
   * Determine if the user is currently typing a mention
   */
  getMentionState(text, cursorPos) {
    let i = cursorPos - 1;

    while (i >= 0) {
      const char = text[i];

      if (char === this.config.triggerChar) {
        const beforeChar = text[i - 1];
        if (i === 0 || /\s/.test(beforeChar)) {
          const mentionText = text.substring(i + 1, cursorPos);
          return {
            mentioning: true,
            start: i,
            text: mentionText,
          };
        } else {
          break;
        }
      }

      if (/\s/.test(char)) break;
      i--;
    }

    return { mentioning: false };
  }

  /**
   * Fetch user suggestions from the server
   */
  async fetchSuggestions(query) {
    try {
      // Build URL parameters properly
      const params = new URLSearchParams();
      params.append("query", query);
      if (this.config.postId) {
        params.append("postId", this.config.postId);
      }

      const requestUrl = `${this.config.apiEndpoint}?${params.toString()}`;
      const response = await fetch(requestUrl);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (!data || !Array.isArray(data.users)) {
        throw new Error("Invalid API response format");
      }

      this.suggestions = data.users;
      this.renderDropdown();
      this.showDropdown();
    } catch (error) {
      console.error("Error fetching suggestions:", error);
      this.hideDropdown();
    }
  }

  /**
   * Render the dropdown with suggestions
   */
  renderDropdown() {
    if (!this.mentionDropdown) this.createDropdown();

    this.mentionDropdown.innerHTML = ""; // Clear previous results

    // Handle empty suggestions
    if (this.suggestions.length === 0) {
      const noResults = document.createElement("div");
      noResults.className = "mention-item-static"; // Different class for non-clickable items

      if (this.mentionText.length === 0) {
        // When user just typed "@" with no additional text
        noResults.textContent = "Type to search for users...";
        noResults.style.color = "#6b7280"; // Lighter color for hint text
        noResults.style.fontStyle = "italic";
        noResults.style.cursor = "default"; // No pointer cursor
      } else {
        // When user typed "@something" but no results found
        noResults.textContent = "No users found";
        noResults.style.color = "#6b7280";
        noResults.style.cursor = "default"; // No pointer cursor
      }

      this.mentionDropdown.appendChild(noResults);
      return;
    }

    // Render actual suggestions
    this.suggestions.forEach((user, index) => {
      const item = document.createElement("div");
      item.className = "mention-item";
      if (index === 0) item.classList.add("selected");

      const avatar = user.avatar
        ? `<img src="${user.avatar}" alt="${user.username}" class="mention-avatar">`
        : `<div class="mention-avatar-placeholder">${user.username
            .charAt(0)
            .toUpperCase()}</div>`;

      item.innerHTML = `
      ${avatar}
      <div class="mention-details">
          <div class="mention-username">${user.username}</div>
          ${user.role ? `<div class="mention-role">${user.role}</div>` : ""}
      </div>
      `;

      item.addEventListener("click", () => this.selectMention(user));
      item.addEventListener("mouseenter", () => {
        this.selectedIndex = index;
        this.updateDropdownSelection();
      });

      this.mentionDropdown.appendChild(item);
    });
  }

  /**
   * Update the selected item in the dropdown
   */
  updateDropdownSelection() {
    const items = this.mentionDropdown.querySelectorAll(".mention-item");
    items.forEach((item, index) => {
      if (index === this.selectedIndex) {
        item.classList.add("selected");
      } else {
        item.classList.remove("selected");
      }
    });
  }

  /**
   * Show the dropdown and position it correctly
   */
  showDropdown() {
    if (!this.currentMentionableElement || !this.mentionDropdown) return;

    const rect = this.currentMentionableElement.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft =
      window.pageXOffset || document.documentElement.scrollLeft;

    // Calculate position based on the current cursor
    let lineHeight =
      parseInt(getComputedStyle(this.currentMentionableElement).lineHeight) ||
      20;
    let inputText =
      this.currentMentionableElement.value ||
      this.currentMentionableElement.innerText;
    let lines = inputText.substr(0, this.mentionStart).split("\n");
    let lineCount = lines.length - 1;

    // Position below the current line
    this.mentionDropdown.style.top = `${
      rect.top + scrollTop + lineCount * lineHeight + 35
    }px`;
    this.mentionDropdown.style.left = `${rect.left + scrollLeft}px`;
    this.mentionDropdown.style.display = "block";
  }

  /**
   * Hide the dropdown
   */
  hideDropdown() {
    if (this.mentionDropdown) {
      this.mentionDropdown.style.display = "none";
      this.mentioning = false;
      this.suggestions = [];
    }
  }

  /**
   * Select a mention and update the input
   */
  selectMention(user) {
    if (!this.currentMentionableElement) return;

    const isTextarea =
      this.currentMentionableElement.tagName.toLowerCase() === "textarea" ||
      this.currentMentionableElement.tagName.toLowerCase() === "input";

    const text = isTextarea
      ? this.currentMentionableElement.value
      : this.currentMentionableElement.innerText;

    const beforeMention = text.substring(0, this.mentionStart);
    const afterMention = text.substring(
      this.mentionStart + this.mentionText.length + 1
    );

    // Create the mention tag with the link class
    const mentionTag = `@${user.username}`;

    // Update the input
    if (isTextarea) {
      this.currentMentionableElement.value =
        beforeMention + mentionTag + " " + afterMention;

      // Move cursor to after the mention
      const newCursorPos = beforeMention.length + mentionTag.length + 1;
      this.currentMentionableElement.setSelectionRange(
        newCursorPos,
        newCursorPos
      );
    } else {
      // For contenteditable
      this.currentMentionableElement.innerText =
        beforeMention + mentionTag + " " + afterMention;

      // Move cursor to after the mention
      const selection = window.getSelection();
      const range = document.createRange();
      const newCursorPos = beforeMention.length + mentionTag.length + 1;

      // Find the text node
      let currentNode = this.currentMentionableElement.firstChild;
      while (currentNode && currentNode.nodeType !== 3) {
        currentNode = currentNode.nextSibling;
      }

      if (currentNode) {
        range.setStart(currentNode, newCursorPos);
        range.setEnd(currentNode, newCursorPos);
        selection.removeAllRanges();
        selection.addRange(range);
      }
    }

    this.hideDropdown();
    this.currentMentionableElement.focus();

    // After insert, process the content to add the link class
    this.processMentions();
  }

  /**
   * Process all mentions in the content and add the link class
   */
  processMentions() {
    // This method is for contenteditable elements
    if (
      !this.currentMentionableElement ||
      (this.currentMentionableElement.tagName.toLowerCase() !== "div" &&
        !this.currentMentionableElement.isContentEditable)
    ) {
      return;
    }

    const content = this.currentMentionableElement.innerHTML;

    // Replace @username with a span that has the link class
    const processedContent = content.replace(
      /@(\w+)/g,
      `<span class="${this.config.linkClass}">@$1</span>`
    );

    this.currentMentionableElement.innerHTML = processedContent;
  }

  /**
   * Add a new mentionable element dynamically
   */
  addMentionableElement(element) {
    if (element) {
      this.attachListeners(element);
      return true;
    }
    return false;
  }
}

// Export the module
window.MentionsSystem = MentionsSystem;
