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
    return final_cleanup($text);
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
        return "\n<blockquote>" . trim($content) . "</blockquote>\n";
    }, $text);

    $text = process_lists($text);
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
        '&lt;/code&gt;' => '</code>'
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

/**
 * Final cleanup and whitespace handling
 */
function final_cleanup($text)
{
    // Convert double newlines to paragraphs
    $text = preg_replace('/(\n{2,})/', '</p><p>', $text);
    // Replace single newlines with a space to prevent line breaks
    $text = str_replace("\n", ' ', $text);
    $text = '<p>' . $text . '</p>';
    $text = str_replace('<p></p>', '', $text);
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