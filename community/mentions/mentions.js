/**
 * @mentions system for community pages
 *
 * This module adds @mentions functionality to textareas or content-editable elements
 * and provides an autocomplete dropdown for selecting users.
 */

class MentionsSystem {
  constructor(options = {}) {
    // Check if a global instance already exists
    if (window.mentionsSystem && this !== window.mentionsSystem) {
      console.log("Using existing MentionsSystem instance");
      return window.mentionsSystem;
    }

    // Default configuration
    this.config = {
      triggerChar: "@",
      minChars: 0, // Show dropdown immediately when @ is typed
      maxSuggestions: 5,
      mentionableElements: ".mentionable", // Use the mentionable class consistently
      dropdownClass: "mentions-dropdown",
      linkClass: "link",
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
    console.log("MentionsSystem initialized");
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

    console.log("Attached listeners to:", element);
  }

  /**
   * Handle input events
   */
  handleInput(e) {
    const el = e.target;
    const text = el.value || el.innerText;
    const cursorPos = this.getCursorPosition(el);

    // Find if we're in the middle of typing a mention
    const mentionState = this.getMentionState(text, cursorPos);

    if (mentionState.mentioning) {
      this.mentioning = true;
      this.mentionStart = mentionState.start;
      this.mentionText = mentionState.text;

      if (this.mentionText.length >= this.config.minChars) {
        this.fetchSuggestions(this.mentionText);
      } else {
        this.hideDropdown();
      }
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
    // Check if we're in the middle of typing a mention
    let start = cursorPos - 1;

    // Move backwards to find the trigger character
    while (
      start >= 0 &&
      text[start] !== this.config.triggerChar &&
      text[start] !== " "
    ) {
      start--;
    }

    // If we found the trigger character
    if (start >= 0 && text[start] === this.config.triggerChar) {
      // Check if it's at the beginning or has a space before it
      if (start === 0 || text[start - 1] === " " || text[start - 1] === "\n") {
        const mentionText = text.substring(start + 1, cursorPos);
        return {
          mentioning: true,
          start: start,
          text: mentionText,
        };
      }
    }

    return { mentioning: false };
  }

  /**
   * Fetch user suggestions from the server
   */
  async fetchSuggestions(query) {
    try {
      const params = new URLSearchParams({
        query: query,
        postId: this.config.postId || "",
      });

      // Use a relative path that works regardless of the current page
      let apiPath = this.config.apiEndpoint;

      // Handle different page contexts by fixing the path
      if (!apiPath.startsWith("/") && !apiPath.includes("://")) {
        // Check if we're in the community directory
        if (window.location.pathname.includes("/community/")) {
          // We're already in the community directory
          if (!apiPath.startsWith("mentions/")) {
            apiPath = "mentions/" + apiPath;
          }
        } else {
          // We might be in a subdirectory, try to get to the mentions directory
          apiPath = "community/mentions/" + apiPath.replace("mentions/", "");
        }
      }

      console.log("Fetching suggestions from:", apiPath);
      const response = await fetch(`${apiPath}?${params}`);

      if (!response.ok) {
        throw new Error("Network response was not ok");
      }

      const data = await response.json();
      this.suggestions = data.users || [];

      if (this.suggestions.length > 0) {
        this.renderDropdown();
        this.showDropdown();
      } else {
        this.hideDropdown();
      }
    } catch (error) {
      console.error("Error fetching suggestions:", error);
      this.hideDropdown();
    }
  }

  /**
   * Render the dropdown with suggestions
   */
  renderDropdown() {
    if (!this.mentionDropdown) {
      this.createDropdown();
    }

    this.mentionDropdown.innerHTML = "";
    this.selectedIndex = 0;

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
                    ${
                      user.role
                        ? `<div class="mention-role">${user.role}</div>`
                        : ""
                    }
                </div>
            `;

      item.addEventListener("click", () => {
        this.selectMention(user);
      });

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

    console.log(
      "Showing dropdown at:",
      this.mentionDropdown.style.top,
      this.mentionDropdown.style.left
    );
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
   * Helper method to apply mentions to the content on form submit
   * This is especially useful for textareas where we can't apply styling directly
   */
  static applyMentionsToContent(content, linkClass = "link") {
    return content.replace(/@(\w+)/g, `<span class="${linkClass}">@$1</span>`);
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

  /**
   * Reset the initialization flag - use this if you need to reinitialize
   */
  reset() {
    this.initialized = false;
    return this;
  }
}

// Export the module
window.MentionsSystem = MentionsSystem;

// Initialize when document is ready - this will create a global instance
document.addEventListener("DOMContentLoaded", function () {
  // Check if one exists already before creating a new one
  if (!window.mentionsSystem) {
    window.mentionsSystem = new MentionsSystem();
  }
});
