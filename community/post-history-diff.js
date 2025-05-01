/**
 * This script enhances the post history page by highlighting differences
 * between versions of posts with character-level precision, showing:
 * - Additions in green
 * - Deletions in red
 */

document.addEventListener("DOMContentLoaded", function () {
  initDiffHighlighter();
});

/**
 * Initialize the diff highlighting functionality
 */
function initDiffHighlighter() {
  const historyEntries = document.querySelectorAll(".history-entry");

  // We need at least 2 entries to compare
  if (historyEntries.length < 2) {
    return;
  }

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
    // Highlight the differences at character level
    const titleDiff = diffText(nextTitle, currentTitle, true);
    currentEntry.querySelector(".history-title").innerHTML = titleDiff;
  }
}

/**
 * Compare content between two history entries with improved newline handling
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
    // Split content into paragraphs for better newline handling
    const currentParagraphs = currentContent.split("\n");
    const nextParagraphs = nextContent.split("\n");

    // Using Longest Common Subsequence algorithm for paragraph alignment
    const alignedParagraphs = alignParagraphs(
      nextParagraphs,
      currentParagraphs
    );

    let htmlResult = "";

    // Process aligned paragraphs
    for (const pair of alignedParagraphs) {
      if (pair.type === "equal") {
        // Just add the unchanged paragraph
        htmlResult += pair.value + "<br>";
      } else if (pair.type === "insert") {
        // New paragraph added
        htmlResult += `<span class="diff-add">${pair.value}</span><br>`;
      } else if (pair.type === "delete") {
        // Paragraph deleted
        htmlResult += `<span class="diff-del">${pair.value}</span><br>`;
      } else if (pair.type === "replace") {
        // Paragraph changed - use character-level diff for this
        htmlResult += diffText(pair.oldValue, pair.newValue, true) + "<br>";
      }
    }

    currentEntry.querySelector(".history-content").innerHTML = htmlResult;
  }
}

/**
 * Align paragraphs to identify added, removed, or modified paragraphs
 * @param {Array} oldParagraphs - Old paragraphs array
 * @param {Array} newParagraphs - New paragraphs array
 * @return {Array} Array of difference operations
 */
function alignParagraphs(oldParagraphs, newParagraphs) {
  const result = [];
  let oldIndex = 0;
  let newIndex = 0;

  while (oldIndex < oldParagraphs.length || newIndex < newParagraphs.length) {
    // If we've reached the end of one array
    if (oldIndex >= oldParagraphs.length) {
      // Add all remaining new paragraphs as insertions
      result.push({
        type: "insert",
        value: newParagraphs[newIndex],
      });
      newIndex++;
      continue;
    }

    if (newIndex >= newParagraphs.length) {
      // Add all remaining old paragraphs as deletions
      result.push({
        type: "delete",
        value: oldParagraphs[oldIndex],
      });
      oldIndex++;
      continue;
    }

    // Get the current paragraphs
    const oldPara = oldParagraphs[oldIndex];
    const newPara = newParagraphs[newIndex];

    // Check for exact match
    if (oldPara === newPara) {
      result.push({
        type: "equal",
        value: newPara,
      });
      oldIndex++;
      newIndex++;
    }
    // Check for similarity (more than 60% the same)
    else if (calculateSimilarity(oldPara, newPara) > 0.6) {
      result.push({
        type: "replace",
        oldValue: oldPara,
        newValue: newPara,
      });
      oldIndex++;
      newIndex++;
    }
    // Check if this is likely a deletion
    else if (
      newIndex + 1 < newParagraphs.length &&
      oldPara === newParagraphs[newIndex + 1]
    ) {
      result.push({
        type: "insert",
        value: newPara,
      });
      newIndex++;
    }
    // Check if this is likely an insertion
    else if (
      oldIndex + 1 < oldParagraphs.length &&
      newPara === oldParagraphs[oldIndex + 1]
    ) {
      result.push({
        type: "delete",
        value: oldPara,
      });
      oldIndex++;
    }
    // Otherwise, treat as a replacement
    else {
      result.push({
        type: "replace",
        oldValue: oldPara,
        newValue: newPara,
      });
      oldIndex++;
      newIndex++;
    }
  }

  return result;
}

/**
 * Calculate similarity between two strings (simple Jaccard index)
 * @param {string} str1 - First string
 * @param {string} str2 - Second string
 * @return {number} Similarity score between 0 and 1
 */
function calculateSimilarity(str1, str2) {
  if (!str1 && !str2) return 1.0;
  if (!str1 || !str2) return 0.0;

  // Convert to sets of characters for simple comparison
  const set1 = new Set(str1.split(""));
  const set2 = new Set(str2.split(""));

  // Calculate intersection size
  let intersectionSize = 0;
  for (const char of set1) {
    if (set2.has(char)) {
      intersectionSize++;
    }
  }

  // Calculate union size
  const unionSize = set1.size + set2.size - intersectionSize;

  // Return Jaccard similarity coefficient
  return intersectionSize / unionSize;
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
      // Use character-level diff for metadata fields
      const fieldDiff = diffText(nextFieldContent, currentFieldContent, true);
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
 * @param {boolean} characterLevel - Whether to perform character-level diffing
 * @return {string} HTML with differences highlighted
 */
function diffText(oldText, newText, characterLevel = false) {
  if (oldText === newText) return newText;

  // Sanitize both strings to prevent HTML issues
  oldText = sanitizeHtml(oldText || "");
  newText = sanitizeHtml(newText || "");

  // If either string is empty, handle as a special case
  if (!oldText) return `<span class="diff-add">${newText}</span>`;
  if (!newText) return `<span class="diff-del">${oldText}</span>`;

  // For character-level diffing
  if (characterLevel) {
    return diffCharacters(oldText, newText);
  }

  // For word-level diffing (original implementation)
  // Split the text into words for word-level diffing
  const oldWords = oldText.split(/(\s+)/);
  const newWords = newText.split(/(\s+)/);

  // Find the longest common subsequence
  const result = findDiff(oldWords, newWords);

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
 * Perform character-level diff between two strings
 * @param {string} oldText - The old text
 * @param {string} newText - The new text
 * @return {string} HTML with character-level differences highlighted
 */
function diffCharacters(oldText, newText) {
  // Convert strings to arrays of characters
  const oldChars = oldText.split("");
  const newChars = newText.split("");

  // Use the Myers diff algorithm for character-level diffing
  const changes = findDiff(oldChars, newChars);

  // Build HTML result with character-level highlighting
  let html = "";
  let inAdd = false;
  let inDel = false;

  for (const part of changes) {
    if (part.added) {
      // Start a new addition span if needed
      if (!inAdd) {
        html += '<span class="diff-add">';
        inAdd = true;
      }
      html += part.value;
    } else if (part.removed) {
      // Start a new deletion span if needed
      if (!inDel) {
        html += '<span class="diff-del">';
        inDel = true;
      }
      html += part.value;
    } else {
      // Close any open spans
      if (inAdd) {
        html += "</span>";
        inAdd = false;
      }
      if (inDel) {
        html += "</span>";
        inDel = false;
      }
      html += part.value;
    }
  }

  // Close any open spans
  if (inAdd) html += "</span>";
  if (inDel) html += "</span>";

  return html;
}

/**
 * Find differences between two arrays
 * Implementation based on Myers diff algorithm
 * @param {Array} oldArr - The old array
 * @param {Array} newArr - The new array
 * @return {Array} Array of diff parts with added, removed, or unchanged status
 */
function findDiff(oldArr, newArr) {
  const changes = [];
  let oldIndex = 0;
  let newIndex = 0;

  // Create LCS (Longest Common Subsequence) matrix
  const matrix = createLCSMatrix(oldArr, newArr);

  // Backtrack through the matrix to find the changes
  backtrack(matrix, oldArr, newArr, oldArr.length, newArr.length, changes);

  // Reverse the changes array as backtracking builds it in reverse order
  return changes.reverse();
}

/**
 * Create a matrix for the Longest Common Subsequence algorithm
 * @param {Array} oldArr - The old array
 * @param {Array} newArr - The new array
 * @return {Array} LCS matrix
 */
function createLCSMatrix(oldArr, newArr) {
  const matrix = [];

  // Initialize the matrix with zeros
  for (let i = 0; i <= oldArr.length; i++) {
    matrix[i] = [];
    for (let j = 0; j <= newArr.length; j++) {
      matrix[i][j] = 0;
    }
  }

  // Fill the matrix
  for (let i = 1; i <= oldArr.length; i++) {
    for (let j = 1; j <= newArr.length; j++) {
      if (oldArr[i - 1] === newArr[j - 1]) {
        matrix[i][j] = matrix[i - 1][j - 1] + 1;
      } else {
        matrix[i][j] = Math.max(matrix[i - 1][j], matrix[i][j - 1]);
      }
    }
  }

  return matrix;
}

/**
 * Backtrack through the LCS matrix to identify changes
 * @param {Array} matrix - The LCS matrix
 * @param {Array} oldArr - The old array
 * @param {Array} newArr - The new array
 * @param {number} i - Current row
 * @param {number} j - Current column
 * @param {Array} changes - Array to collect changes
 */
function backtrack(matrix, oldArr, newArr, i, j, changes) {
  if (i === 0 && j === 0) {
    return;
  }

  if (i > 0 && j > 0 && oldArr[i - 1] === newArr[j - 1]) {
    // Items match - unchanged content
    changes.push({ value: oldArr[i - 1] });
    backtrack(matrix, oldArr, newArr, i - 1, j - 1, changes);
  } else if (j > 0 && (i === 0 || matrix[i][j - 1] >= matrix[i - 1][j])) {
    // Addition
    changes.push({ value: newArr[j - 1], added: true });
    backtrack(matrix, oldArr, newArr, i, j - 1, changes);
  } else if (i > 0 && (j === 0 || matrix[i][j - 1] < matrix[i - 1][j])) {
    // Deletion
    changes.push({ value: oldArr[i - 1], removed: true });
    backtrack(matrix, oldArr, newArr, i - 1, j, changes);
  }
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
