/**
 * Post History Diff Display
 *
 * This script enhances the post history page by highlighting differences
 * between versions of posts, showing additions in green and deletions in red.
 */

document.addEventListener("DOMContentLoaded", function () {
  // Initialize the diff highlighter
  initDiffHighlighter();
});

/**
 * Initialize the diff highlighting functionality
 */
function initDiffHighlighter() {
  // Get all history entries
  const historyEntries = document.querySelectorAll(".history-entry");

  // We need at least 2 entries to compare
  if (historyEntries.length < 2) return;

  // Process each pair of consecutive versions
  for (let i = 0; i < historyEntries.length - 1; i++) {
    const currentEntry = historyEntries[i];
    const nextEntry = historyEntries[i + 1];

    compareTitles(currentEntry, nextEntry);
    compareContent(currentEntry, nextEntry);
    compareMetadataFields(currentEntry, nextEntry);
  }
}

/**
 * Compare titles between two history entries
 * @param {Element} currentEntry - The newer version
 * @param {Element} nextEntry - The older version
 */
function compareTitles(currentEntry, nextEntry) {
  const currentTitle = currentEntry
    .querySelector(".history-title")
    .textContent.trim();
  const nextTitle = nextEntry
    .querySelector(".history-title")
    .textContent.trim();

  if (currentTitle !== nextTitle) {
    // Highlight the differences
    const titleDiff = diffText(nextTitle, currentTitle);
    currentEntry.querySelector(".history-title").innerHTML = titleDiff;
  }
}

/**
 * Compare content between two history entries
 * @param {Element} currentEntry - The newer version
 * @param {Element} nextEntry - The older version
 */
function compareContent(currentEntry, nextEntry) {
  const currentContent = currentEntry
    .querySelector(".history-content")
    .textContent.trim();
  const nextContent = nextEntry
    .querySelector(".history-content")
    .textContent.trim();

  if (currentContent !== nextContent) {
    // Split content into paragraphs and compare them
    const currentParagraphs = currentContent.split("\n");
    const nextParagraphs = nextContent.split("\n");

    let htmlResult = "";

    // Compare each paragraph
    const maxLength = Math.max(currentParagraphs.length, nextParagraphs.length);

    for (let i = 0; i < maxLength; i++) {
      const currentPara =
        i < currentParagraphs.length ? currentParagraphs[i] : "";
      const nextPara = i < nextParagraphs.length ? nextParagraphs[i] : "";

      if (currentPara !== nextPara) {
        htmlResult += diffText(nextPara, currentPara) + "<br>";
      } else {
        htmlResult += currentPara + "<br>";
      }
    }

    currentEntry.querySelector(".history-content").innerHTML = htmlResult;
  }
}

/**
 * Compare metadata fields between two history entries
 * @param {Element} currentEntry - The newer version
 * @param {Element} nextEntry - The older version
 */
function compareMetadataFields(currentEntry, nextEntry) {
  // Get all metadata fields in the current entry
  const currentMetadataFields = currentEntry.querySelectorAll(
    ".metadata-field-value"
  );
  const nextMetadataFields = nextEntry.querySelectorAll(
    ".metadata-field-value"
  );

  if (currentMetadataFields.length === 0 || nextMetadataFields.length === 0)
    return;

  // Compare each field
  for (let i = 0; i < currentMetadataFields.length; i++) {
    if (i >= nextMetadataFields.length) break;

    const currentFieldContent = currentMetadataFields[i].textContent.trim();
    const nextFieldContent = nextMetadataFields[i].textContent.trim();

    if (currentFieldContent !== nextFieldContent) {
      // Highlight the differences
      const fieldDiff = diffText(nextFieldContent, currentFieldContent);
      currentMetadataFields[i].innerHTML = fieldDiff;

      // Add the changed class to highlight the entire field
      const fieldContainer =
        currentMetadataFields[i].closest(".metadata-field");
      if (fieldContainer) {
        fieldContainer.classList.add("metadata-changed");
      }
    }
  }
}

/**
 * Compare two strings and return HTML with differences highlighted
 * @param {string} oldText - The old text
 * @param {string} newText - The new text
 * @return {string} HTML with differences highlighted
 */
function diffText(oldText, newText) {
  if (oldText === newText) return newText;

  // Sanitize both strings to prevent HTML issues
  oldText = sanitizeHtml(oldText || "");
  newText = sanitizeHtml(newText || "");

  // If either string is empty, handle as a special case
  if (!oldText) return `<span class="diff-add">${newText}</span>`;
  if (!newText) return `<span class="diff-del">${oldText}</span>`;

  // Split the text into words for better diffing
  const oldWords = oldText.split(/(\s+)/);
  const newWords = newText.split(/(\s+)/);

  // Find the longest common subsequence
  const result = findLongestCommonSubsequence(oldWords, newWords);

  // Create HTML with highlighted differences
  let html = "";

  for (const part of result) {
    if (part.added) {
      html += `<span class="diff-add">${part.value}</span>`;
    } else if (part.removed) {
      html += `<span class="diff-del">${part.value}</span>`;
    } else {
      html += part.value;
    }
  }

  return html;
}

/**
 * Find the longest common subsequence between two arrays
 * Implementation based on Myers difference algorithm
 * @param {Array} oldArr - The old array
 * @param {Array} newArr - The new array
 * @return {Array} Array of diff parts with added, removed, or unchanged status
 */
function findLongestCommonSubsequence(oldArr, newArr) {
  const oldLen = oldArr.length;
  const newLen = newArr.length;

  // Use a simple word-by-word comparison for this implementation
  // For larger texts, a more efficient algorithm should be used

  let i = 0;
  let j = 0;

  const result = [];
  let currentPart = null;

  while (i < oldLen || j < newLen) {
    // Case: both arrays still have elements and they match
    if (i < oldLen && j < newLen && oldArr[i] === newArr[j]) {
      if (currentPart && !currentPart.added && !currentPart.removed) {
        currentPart.value += oldArr[i];
      } else {
        if (currentPart) result.push(currentPart);
        currentPart = { value: oldArr[i] };
      }
      i++;
      j++;
    }
    // Case: elements don't match, decide which array to advance
    else {
      // Try advancing through the new array (addition)
      if (
        j < newLen &&
        (i >= oldLen ||
          (i + 1 < oldLen && j + 1 < newLen && oldArr[i + 1] === newArr[j + 1]))
      ) {
        if (currentPart && currentPart.added) {
          currentPart.value += newArr[j];
        } else {
          if (currentPart) result.push(currentPart);
          currentPart = { value: newArr[j], added: true };
        }
        j++;
      }
      // Try advancing through the old array (deletion)
      else if (i < oldLen) {
        if (currentPart && currentPart.removed) {
          currentPart.value += oldArr[i];
        } else {
          if (currentPart) result.push(currentPart);
          currentPart = { value: oldArr[i], removed: true };
        }
        i++;
      }
    }
  }

  if (currentPart) result.push(currentPart);

  return result;
}

/**
 * Sanitize HTML to prevent XSS attacks
 * @param {string} text - The text to sanitize
 * @return {string} Sanitized text
 */
function sanitizeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
