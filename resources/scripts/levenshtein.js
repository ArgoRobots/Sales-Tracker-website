/**
 * Levenshtein Distance Search Utility
 *
 * Provides fuzzy string matching using the Levenshtein distance algorithm
 * with normalized similarity scoring to handle typos in search queries.
 */

/**
 * Calculates the Levenshtein distance (edit distance) between two strings.
 * The edit distance is the minimum number of single-character edits
 * (insertions, deletions, or substitutions) required to transform one string into another.
 *
 * @param {string} a - First string
 * @param {string} b - Second string
 * @returns {number} The edit distance between the two strings
 */
function getEditDistance(a, b) {
  const matrix = Array.from({ length: b.length + 1 }, (_, i) =>
    Array.from({ length: a.length + 1 }, (_, j) =>
      i === 0 ? j : j === 0 ? i : 0
    )
  );

  for (let i = 1; i <= b.length; i++) {
    for (let j = 1; j <= a.length; j++) {
      matrix[i][j] =
        b[i - 1] === a[j - 1]
          ? matrix[i - 1][j - 1]
          : Math.min(
              matrix[i - 1][j - 1] + 1, // substitution
              matrix[i][j - 1] + 1, // insertion
              matrix[i - 1][j] + 1 // deletion
            );
    }
  }

  return matrix[b.length][a.length];
}

/**
 * Calculates a normalized similarity score between two strings based on Levenshtein distance.
 * Returns a value between 0 and 1, where:
 * - 0.0 = completely different strings
 * - 1.0 = identical strings
 *
 * @param {string} str1 - First string to compare
 * @param {string} str2 - Second string to compare
 * @returns {number} Similarity score between 0 and 1
 */
function getSimilarity(str1, str2) {
  const longer = str1.length > str2.length ? str1 : str2;
  const shorter = str1.length > str2.length ? str2 : str1;
  const longerLength = longer.length;

  if (longerLength === 0) return 1.0;
  return (longerLength - getEditDistance(longer, shorter)) / longerLength;
}
