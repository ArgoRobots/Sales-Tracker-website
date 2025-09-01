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
 * Compare content between two history entries with newline handling
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
 * Calculate similarity between two strings (Jaccard index)
 * @param {string} str1 - First string
 * @param {string} str2 - Second string
 * @return {number} Similarity score between 0 and 1
 */
function calculateSimilarity(str1, str2) {
  if (!str1 && !str2) return 1.0;
  if (!str1 || !str2) return 0.0;

  // Split by words instead of characters for better similarity comparison
  const words1 = str1.split(/\s+/);
  const words2 = str2.split(/\s+/);

  const set1 = new Set(words1);
  const set2 = new Set(words2);

  // Calculate intersection size
  let intersectionSize = 0;
  for (const word of set1) {
    if (set2.has(word)) {
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
  const changes = computeWordBasedDiff(oldWords, newWords);

  // Create HTML with highlighted differences
  let html = "";
  for (const part of changes) {
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
 * Character-level diff between two strings
 * This version better handles word-level changes while preserving formatting
 * @param {string} oldText - The old text
 * @param {string} newText - The new text
 * @return {string} HTML with character-level differences highlighted
 */
function diffCharacters(oldText, newText) {
  // If texts are identical, return unchanged
  if (oldText === newText) return newText;

  // If either text is empty, handle specially
  if (!oldText) return `<span class="diff-add">${newText}</span>`;
  if (!newText) return `<span class="diff-del">${oldText}</span>`;

  // Check for numeric extensions (like "12" to "123")
  if (/^\d+$/.test(oldText) && /^\d+$/.test(newText)) {
    // Both are numeric strings - check for prefixes
    if (newText.startsWith(oldText)) {
      const addedPortion = newText.substring(oldText.length);
      return oldText + `<span class="diff-add">${addedPortion}</span>`;
    } else if (oldText.startsWith(newText)) {
      const removedPortion = oldText.substring(newText.length);
      return newText + `<span class="diff-del">${removedPortion}</span>`;
    }
  }

  // Split texts into words to handle word-level changes better
  const oldWords = oldText.split(/(\s+)/);
  const newWords = newText.split(/(\s+)/);

  // Use diffWords approach for more precise word-level diffing
  const changes = diffWords(oldWords, newWords);

  // Construct the HTML result
  let htmlResult = "";
  for (const change of changes) {
    if (change.added) {
      htmlResult += `<span class="diff-add">${change.value}</span>`;
    } else if (change.removed) {
      htmlResult += `<span class="diff-del">${change.value}</span>`;
    } else {
      htmlResult += change.value;
    }
  }

  return htmlResult;
}

/**
 * Word-level diffing that properly handles sequential changes
 * @param {Array} oldWords - Old words array
 * @param {Array} newWords - New words array
 * @return {Array} Array of change objects
 */
function diffWords(oldWords, newWords) {
  // Create a mapping of words to their positions in both arrays
  const oldWordMap = createWordMap(oldWords);
  const newWordMap = createWordMap(newWords);

  const changes = [];
  let oldIndex = 0;
  let newIndex = 0;

  while (oldIndex < oldWords.length || newIndex < newWords.length) {
    // Handle the case where all old words are processed
    if (oldIndex >= oldWords.length) {
      changes.push({
        added: true,
        value: newWords.slice(newIndex).join(""),
      });
      break;
    }

    // Handle the case where all new words are processed
    if (newIndex >= newWords.length) {
      changes.push({
        removed: true,
        value: oldWords.slice(oldIndex).join(""),
      });
      break;
    }

    const oldWord = oldWords[oldIndex];
    const newWord = newWords[newIndex];

    // If words are the same, add as common
    if (oldWord === newWord) {
      changes.push({ value: oldWord });
      oldIndex++;
      newIndex++;
      continue;
    }

    // Special case for numbers: check for prefixes
    if (/^\d+$/.test(oldWord) && /^\d+$/.test(newWord)) {
      if (newWord.startsWith(oldWord)) {
        // "12" to "123" case - keep common prefix and add suffix
        changes.push({ value: oldWord });
        changes.push({ added: true, value: newWord.substring(oldWord.length) });
        oldIndex++;
        newIndex++;
        continue;
      } else if (oldWord.startsWith(newWord)) {
        // "123" to "12" case - keep common prefix and mark suffix as removed
        changes.push({ value: newWord });
        changes.push({
          removed: true,
          value: oldWord.substring(newWord.length),
        });
        oldIndex++;
        newIndex++;
        continue;
      }
    }

    // Look ahead to see if current word appears later
    const oldWordPosInNew = findNextOccurrence(oldWord, newWords, newIndex);
    const newWordPosInOld = findNextOccurrence(newWord, oldWords, oldIndex);

    // Choose the closest match to minimize diff size
    if (
      oldWordPosInNew !== -1 &&
      (newWordPosInOld === -1 ||
        oldWordPosInNew - newIndex < newWordPosInOld - oldIndex)
    ) {
      // The old word appears later in new text - mark words up to that point as added
      const addedContent = newWords.slice(newIndex, oldWordPosInNew).join("");
      if (addedContent) {
        changes.push({ added: true, value: addedContent });
      }
      newIndex = oldWordPosInNew;
    } else if (newWordPosInOld !== -1) {
      // The new word appears later in old text - mark words up to that point as removed
      const removedContent = oldWords.slice(oldIndex, newWordPosInOld).join("");
      if (removedContent) {
        changes.push({ removed: true, value: removedContent });
      }
      oldIndex = newWordPosInOld;
    } else {
      // Check for character-level changes in non-whitespace words
      if (!oldWord.match(/^\s+$/) && !newWord.match(/^\s+$/)) {
        const charDiff = findCharacterLevelChanges(oldWord, newWord);
        if (charDiff) {
          changes.push(...charDiff);
          oldIndex++;
          newIndex++;
          continue;
        }
      }

      // Neither word appears in the other text - mark as removed and added
      changes.push({ removed: true, value: oldWord });
      changes.push({ added: true, value: newWord });
      oldIndex++;
      newIndex++;
    }
  }

  // Merge adjacent changes of the same type
  return mergeAdjacentChanges(changes);
}

/**
 * Find the next occurrence of a word in an array starting from a position
 * @param {string} word - Word to find
 * @param {Array} words - Array of words to search in
 * @param {number} startPos - Position to start searching from
 * @return {number} Position of the next occurrence or -1 if not found
 */
function findNextOccurrence(word, words, startPos) {
  for (let i = startPos; i < words.length; i++) {
    if (words[i] === word) {
      return i;
    }
  }
  return -1;
}

/**
 * Create a mapping of words to their positions
 * @param {Array} words - Array of words
 * @return {Object} Map of words to their positions
 */
function createWordMap(words) {
  const map = {};
  for (let i = 0; i < words.length; i++) {
    const word = words[i];
    if (!map[word]) {
      map[word] = [];
    }
    map[word].push(i);
  }
  return map;
}

/**
 * Find character-level changes between two words
 * Especially useful for number sequences like "12" to "123"
 * @param {string} oldWord - The old word
 * @param {string} newWord - The new word
 * @return {Array|null} Array of changes or null if too different
 */
function findCharacterLevelChanges(oldWord, newWord) {
  // For numbers, find common prefix
  if (/^\d+$/.test(oldWord) && /^\d+$/.test(newWord)) {
    let commonPrefix = "";
    const minLength = Math.min(oldWord.length, newWord.length);

    for (let i = 0; i < minLength; i++) {
      if (oldWord[i] === newWord[i]) {
        commonPrefix += oldWord[i];
      } else {
        break;
      }
    }

    // If we have a common prefix
    if (commonPrefix.length > 0) {
      const oldSuffix = oldWord.substring(commonPrefix.length);
      const newSuffix = newWord.substring(commonPrefix.length);

      const result = [{ value: commonPrefix }];

      if (oldSuffix) {
        result.push({ removed: true, value: oldSuffix });
      }

      if (newSuffix) {
        result.push({ added: true, value: newSuffix });
      }

      return result;
    }
  }

  // For other words with high similarity, try common prefix/suffix approach
  const similarity = calculateStringSimilarity(oldWord, newWord);
  if (similarity > 0.5) {
    let i = 0;
    let commonPrefix = "";

    // Find common prefix
    while (
      i < oldWord.length &&
      i < newWord.length &&
      oldWord[i] === newWord[i]
    ) {
      commonPrefix += oldWord[i];
      i++;
    }

    // Find common suffix by working backwards
    let j = 1;
    let commonSuffix = "";
    while (
      j <= oldWord.length - i &&
      j <= newWord.length - i &&
      oldWord[oldWord.length - j] === newWord[newWord.length - j]
    ) {
      commonSuffix = oldWord[oldWord.length - j] + commonSuffix;
      j++;
    }

    // Extract the different middle parts
    const oldMiddle = oldWord.substring(
      commonPrefix.length,
      oldWord.length - commonSuffix.length
    );
    const newMiddle = newWord.substring(
      commonPrefix.length,
      newWord.length - commonSuffix.length
    );

    const result = [];

    if (commonPrefix) {
      result.push({ value: commonPrefix });
    }

    if (oldMiddle) {
      result.push({ removed: true, value: oldMiddle });
    }

    if (newMiddle) {
      result.push({ added: true, value: newMiddle });
    }

    if (commonSuffix) {
      result.push({ value: commonSuffix });
    }

    return result;
  }

  return null;
}

/**
 * Calculate string similarity based on characters
 * @param {string} str1 - First string
 * @param {string} str2 - Second string
 * @return {number} Similarity score between 0 and 1
 */
function calculateStringSimilarity(str1, str2) {
  if (!str1 && !str2) return 1.0;
  if (!str1 || !str2) return 0.0;

  // Simple algorithm: number of matching characters / max length
  const maxLength = Math.max(str1.length, str2.length);
  let matchCount = 0;

  for (let i = 0; i < Math.min(str1.length, str2.length); i++) {
    if (str1[i] === str2[i]) {
      matchCount++;
    }
  }

  return matchCount / maxLength;
}

/**
 * Merge adjacent changes of the same type
 * @param {Array} changes - Array of change objects
 * @return {Array} Merged array of change objects
 */
function mergeAdjacentChanges(changes) {
  if (changes.length <= 1) return changes;

  const result = [];
  let current = changes[0];

  for (let i = 1; i < changes.length; i++) {
    const next = changes[i];

    // If current and next are of the same type, merge them
    if (
      (current.added && next.added) ||
      (current.removed && next.removed) ||
      (!current.added && !current.removed && !next.added && !next.removed)
    ) {
      current.value += next.value;
    } else {
      // Different types, add current to result and move to next
      result.push(current);
      current = next;
    }
  }

  // Add the last change
  result.push(current);
  return result;
}

/**
 * Word-based diff implementation for more natural diffing
 * @param {Array} oldWords - The old array of words
 * @param {Array} newWords - The new array of words
 * @return {Array} Array of diff parts with added, removed, or unchanged status
 */
function computeWordBasedDiff(oldWords, newWords) {
  // Find the longest common subsequence indices
  const lcsMatrix = createLCSMatrix(oldWords, newWords);

  // Backtrack to find the changes
  const changes = [];
  let i = oldWords.length;
  let j = newWords.length;

  while (i > 0 || j > 0) {
    if (i > 0 && j > 0 && oldWords[i - 1] === newWords[j - 1]) {
      // Common word
      changes.unshift({ value: oldWords[i - 1] });
      i--;
      j--;
    } else if (
      j > 0 &&
      (i === 0 || lcsMatrix[i][j - 1] >= lcsMatrix[i - 1][j])
    ) {
      // Addition
      changes.unshift({ value: newWords[j - 1], added: true });
      j--;
    } else if (
      i > 0 &&
      (j === 0 || lcsMatrix[i][j - 1] < lcsMatrix[i - 1][j])
    ) {
      // Deletion
      changes.unshift({ value: oldWords[i - 1], removed: true });
      i--;
    }
  }

  // Merge adjacent changes of the same type
  const mergedChanges = [];
  let currentChange = null;

  for (const change of changes) {
    if (!currentChange) {
      currentChange = { ...change };
    } else if (
      (currentChange.added && change.added) ||
      (currentChange.removed && change.removed) ||
      (!currentChange.added &&
        !currentChange.removed &&
        !change.added &&
        !change.removed)
    ) {
      // Merge with previous change of same type
      currentChange.value += change.value;
    } else {
      // Different type, push current and start new
      mergedChanges.push(currentChange);
      currentChange = { ...change };
    }
  }

  if (currentChange) {
    mergedChanges.push(currentChange);
  }

  return mergedChanges;
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
 * Sanitize HTML to prevent XSS attacks
 * @param {string} text - The text to sanitize
 * @return {string} Sanitized text
 */
function sanitizeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
