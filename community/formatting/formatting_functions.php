<?php

/**
 * Parse and render formatted text from user input
 * 
 * @param string $text The user input text with formatting
 * @return string HTML with formatting applied
 */
function render_formatted_text($text)
{
    $text = process_formatting($text);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = restore_formatting_tags($text);
    $text = final_cleanup($text);

    // Wrap the formatted text in a div so we can apply a specific class
    return '<div class="formatted-text">' . $text . '</div>';
}

/**
 * Process all formatting on raw input
 */
function process_formatting($text)
{
    // Bold: **text** to <strong>text</strong>
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);

    // Italic: _text_ to <em>text</em>
    $text = preg_replace('/_([^_\n]+)_/', '<em>$1</em>', $text);

    // Code: `code` to <code>code</code>
    $text = preg_replace('/`(.+?)`/s', '<code>$1</code>', $text);

    // Blockquotes (multi-line support)
    $text = preg_replace_callback('/(^>+\s*.*(\n>+\s*.*)*)/m', function ($matches) {
        $content = preg_replace('/^>+\s*/m', '', $matches[0]);
        return "<blockquote>" . trim($content) . "</blockquote>";
    }, $text);

    $text = process_lists($text);
    $text = process_links($text);
    return $text;
}

/**
 * Process ordered/unordered lists
 */
function process_lists($text)
{
    // Unordered lists
    $text = preg_replace_callback('/(^- .+(\n- .+)*)/m', function ($matches) {
        $items = preg_replace('/^- (.+)/m', '<li>$1</li>', $matches[0]);
        return "<ul>\n$items\n</ul>";
    }, $text);

    // Ordered lists
    $text = preg_replace_callback('/(^\d+\. .+(\n\d+\. .+)*)/m', function ($matches) {
        $items = preg_replace('/^\d+\. (.+)/m', '<li>$1</li>', $matches[0]);
        return "<ol>\n$items\n</ol>";
    }, $text);

    return $text;
}

/**
 * Process links with Markdown syntax [text](url)
 * 
 * @param string $text The text to process
 * @return string Text with processed links
 */
function process_links($text)
{
    // Process links with Markdown syntax [text](url)
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($matches) {
        $link_text = $matches[1];
        $url = $matches[2];

        if (is_allowed_url($url)) {
            return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($link_text) . '</a>';
        } else {
            // Return just the text if link is not allowed
            return htmlspecialchars($link_text) . ' <span class="invalid-link-warning">(Link to disallowed domain removed)</span>';
        }
    }, $text);

    return $text;
}

/**
 * Validates if a URL is allowed (only argorobots.com and Wikipedia domains)
 *
 * @param string $url The URL to validate
 * @return bool True if URL is allowed, false otherwise
 */
function is_allowed_url($url)
{
    // Parse the URL to get the host
    $parsed_url = parse_url($url);

    if (!isset($parsed_url['host'])) {
        return false;
    }

    $host = strtolower($parsed_url['host']);

    // Check if it's from argorobots.com (including subdomains)
    if ($host === 'argorobots.com' || strpos($host, '.argorobots.com') !== false) {
        return true;
    }

    // Check if it's from Wikipedia
    if ($host === 'wikipedia.org' || strpos($host, '.wikipedia.org') !== false) {
        return true;
    }

    return false;
}

/**
 * Restore our formatting tags after HTML escaping
 */
function restore_formatting_tags($text)
{
    $replacements = [
        '&lt;strong&gt;' => '<strong>',
        '&lt;/strong&gt;' => '</strong>',
        '&lt;em&gt;' => '<em>',
        '&lt;/em&gt;' => '</em>',
        '&lt;blockquote&gt;' => '<blockquote>',
        '&lt;/blockquote&gt;' => '</blockquote>',
        '&lt;ul&gt;' => '<ul>',
        '&lt;/ul&gt;' => '</ul>',
        '&lt;ol&gt;' => '<ol>',
        '&lt;/ol&gt;' => '</ol>',
        '&lt;li&gt;' => '<li>',
        '&lt;/li&gt;' => '</li>',
        '&lt;code&gt;' => '<code>',
        '&lt;/code&gt;' => '</code>',
        '&lt;a href=&quot;' => '<a href="',
        '&quot; target=&quot;_blank&quot; rel=&quot;noopener noreferrer&quot;&gt;' => '" target="_blank" rel="noopener noreferrer">',
        '&lt;/a&gt;' => '</a>',
        '&lt;span class=&quot;invalid-link-warning&quot;&gt;' => '<span class="invalid-link-warning">',
        '&lt;/span&gt;' => '</span>'
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

/**
 * Final cleanup and whitespace handling
 */
function final_cleanup($text)
{
    // Normalize line endings
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // Split into lines
    $lines = explode("\n", $text);
    $output = [];

    // Variable to track consecutive empty lines
    $consecutiveEmptyLines = 0;

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if ($trimmedLine === '') {
            // Only add <br> if we haven't already added one consecutively
            if ($consecutiveEmptyLines < 1) {
                $output[] = '<br>';
                $consecutiveEmptyLines++;
            }
            // If we already have a blank line, skip this one
        } else if (preg_match('/^<(blockquote|ul|ol|li|code|p|h[1-6]|hr|a)/i', $trimmedLine)) {
            // Don't wrap in <p>
            $output[] = $line;
            // Reset consecutive empty lines counter
            $consecutiveEmptyLines = 0;
        } else {
            $output[] = '<p>' . $line . '</p>';
            // Reset consecutive empty lines counter
            $consecutiveEmptyLines = 0;
        }
    }

    // Cleanup empty paragraphs and nested tags
    $text = implode("\n", $output);
    $text = preg_replace('/<p>\s*<\/p>/', '', $text);
    $text = str_replace(['<p><p>', '</p></p>'], ['<p>', '</p>'], $text);

    return $text;
}

/**
 * Add the formatting toolbar to a textarea
 * 
 * @param string $textarea_id The ID of the textarea element
 * @param bool $include_preview Whether to include a live preview area
 */
function add_formatting_toolbar($textarea_id, $include_preview = true)
{
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('<?= $textarea_id ?>');
            if (textarea) {
                textarea.classList.add('formattable');
                <?php if ($include_preview): ?>
                    textarea.dataset.enablePreview = 'true';
                <?php endif; ?>
            }
        });
    </script>
<?php
}
?>