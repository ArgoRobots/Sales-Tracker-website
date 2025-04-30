// Text formatting functions for post content and comments
document.addEventListener("DOMContentLoaded", function () {
  // Find all textareas that need formatting capabilities
  const formattableTextareas = document.querySelectorAll(".formattable");

  // Initialize formatting for each textarea
  formattableTextareas.forEach((textarea) => {
    initFormatting(textarea);
  });

  // Watch for dynamically added textareas (like in comment editing)
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.addedNodes) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === Node.ELEMENT_NODE) {
            const newTextareas = node.querySelectorAll(".formattable");
            newTextareas.forEach((textarea) => {
              initFormatting(textarea);
            });
          }
        });
      }
    });
  });

  // Observer configuration - watch for changes to the body and all its children
  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
});

/**
 * Initialize formatting for a textarea
 * @param {HTMLTextAreaElement} textarea
 */
function initFormatting(textarea) {
  // Prevent duplicate initialization
  if (textarea.dataset.formattingInitialized === "true") {
    return;
  }

  // Create the formatting toolbar
  const toolbar = createToolbar(textarea);

  // Insert toolbar before the textarea
  textarea.parentNode.insertBefore(toolbar, textarea);

  // Set up keyboard shortcuts
  setupKeyboardShortcuts(textarea);

  // Mark as initialized
  textarea.dataset.formattingInitialized = "true";
}

/**
 * Create a formatting toolbar for a textarea
 * @param {HTMLTextAreaElement} textarea
 * @returns {HTMLElement} The toolbar element
 */
function createToolbar(textarea) {
  const toolbar = document.createElement("div");
  toolbar.className = "formatting-toolbar";

  // Add formatting buttons
  const buttons = [
    {
      icon: "B",
      title: "Bold (Ctrl+B)",
      format: "**",
      placeholder: "bold text",
    },
    {
      icon: "I",
      title: "Italic (Ctrl+I)",
      format: "_",
      placeholder: "italic text",
    },
    {
      icon: "• List",
      title: "Bulleted List",
      format: "- ",
      multiline: true,
      placeholder: "list item",
    },
    {
      icon: "1. List",
      title: "Numbered List",
      format: "1. ",
      multiline: true,
      placeholder: "list item",
    },
    {
      icon: "> Quote",
      title: "Blockquote",
      format: "> ",
      multiline: true,
      placeholder: "quote",
    },
    {
      icon: "Code",
      title: "Code",
      format: "`",
      placeholder: "code",
    },
    {
      icon: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12" y2="17"></line></svg>`,
      title: "Formatting Help",
      url: "formatting/help.php",
      class: "help-btn",
    },
  ];

  buttons.forEach((btn) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = btn.class ? ` ${btn.class}` : "format-btn";
    button.innerHTML = btn.icon;
    button.title = btn.title;

    button.addEventListener("click", (e) => {
      e.preventDefault();
      if (btn.url) {
        window.open(btn.url, "_blank");
      } else {
        applyFormatting(textarea, btn.format, btn.multiline, btn.placeholder);
      }
    });

    toolbar.appendChild(button);
  });

  return toolbar;
}

/**
 * Apply formatting to selected text or insert at cursor position
 * @param {HTMLTextAreaElement} textarea
 * @param {string} format The formatting characters to apply
 * @param {boolean} multiline Whether this is a multiline format (lists, blockquotes)
 * @param {string} placeholder Text to insert if no selection
 */
function applyFormatting(
  textarea,
  format,
  multiline = false,
  placeholder = ""
) {
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const selectedText = textarea.value.substring(start, end);
  let newText = "";

  if (selectedText) {
    if (multiline) {
      // For multiline formats, apply to each line
      const lines = selectedText.split("\n");
      newText = lines.map((line) => format + line).join("\n");
    } else {
      // For inline formats (bold, italic, code), wrap the selected text
      newText = format + selectedText + format;
    }
  } else {
    // If no text selected, insert the format with a placeholder
    if (multiline) {
      newText = format + placeholder;
    } else {
      newText = format + placeholder + format;
    }
  }

  // Insert the formatted text
  textarea.focus();
  const textBeforeSelection = textarea.value.substring(0, start);
  const textAfterSelection = textarea.value.substring(end);

  textarea.value = textBeforeSelection + newText + textAfterSelection;

  // Move cursor to appropriate position
  if (selectedText) {
    // Position cursor after the inserted formatted text
    textarea.selectionStart = start + newText.length;
    textarea.selectionEnd = start + newText.length;
  } else {
    // Position cursor at the placeholder position for user convenience
    const placeholderStart = start + format.length;
    textarea.selectionStart = placeholderStart;
    textarea.selectionEnd = placeholderStart + placeholder.length;
  }

  // Trigger input event to notify any listeners (like auto-resize)
  const event = new Event("input", { bubbles: true });
  textarea.dispatchEvent(event);
}

/**
 * Set up keyboard shortcuts for formatting
 * @param {HTMLTextAreaElement} textarea
 */
function setupKeyboardShortcuts(textarea) {
  textarea.addEventListener("keydown", function (e) {
    // Ctrl+B for bold
    if (e.ctrlKey && e.key === "b") {
      e.preventDefault();
      applyFormatting(textarea, "**", false, "bold text");
    }
    // Ctrl+I for italic
    else if (e.ctrlKey && e.key === "i") {
      e.preventDefault();
      applyFormatting(textarea, "_", false, "italic text");
    }
  });
}
