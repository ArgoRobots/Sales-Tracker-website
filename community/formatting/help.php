<?php
session_start();
require_once '../../db_connect.php';
require_once 'formatting_functions.php';

// Process preview content
$previewContent = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['practice_content'])) {
    $rawContent = trim($_POST['practice_content']);
    $previewContent = render_formatted_text($rawContent);
}

// Sample content for examples
$combinedExample = "- **Bold list item**\n- _Italic list item_\n- List item with `code`";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Text Formatting Guide - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="text-formatting.js" defer></script>

    <link rel="stylesheet" href="help.css">
    <link rel="stylesheet" href="formatted-text.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="formatting-help-container">
        <div class="formatting-help-header">
            <h1>Text Formatting Guide</h1>
            <p>Learn how to format your posts for better readability</p>
        </div>

        <!-- Text Styling Examples -->
        <div class="formatting-section">
            <h3>Text Styling</h3>
            <div class="example-grid">
                <!-- Bold Example -->
                <div class="example">
                    <h4>Bold Text</h4>
                    <div class="example-input">**This text will be bold**</div>
                    <div class="example-output">
                        <?= render_formatted_text('**This text will be bold**') ?>
                    </div>
                    <div class="shortcut-tip">Shortcut: <kbd>Ctrl</kbd> + <kbd>B</kbd></div>
                </div>

                <!-- Italic Example -->
                <div class="example">
                    <h4>Italic Text</h4>
                    <div class="example-input">_This text will be italic_</div>
                    <div class="example-output">
                        <?= render_formatted_text('_This text will be italic_') ?>
                    </div>
                    <div class="shortcut-tip">Shortcut: <kbd>Ctrl</kbd> + <kbd>I</kbd></div>
                </div>

                <!-- Code Example -->
                <div class="example">
                    <h4>Code Text</h4>
                    <div class="example-input">`This is code text`</div>
                    <div class="example-output">
                        <?= render_formatted_text('`This is code text`') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lists Section -->
        <div class="formatting-section">
            <h3>Lists</h3>
            <div class="example-grid">
                <!-- Bulleted List -->
                <div class="example example-wide">
                    <h4>Bulleted List</h4>
                    <div class="example-input">
                        - First item<br>
                        - Second item<br>
                        - Third item
                    </div>
                    <div class="example-output">
                        <?= render_formatted_text("- First item\n- Second item\n- Third item") ?>
                    </div>
                </div>

                <!-- Numbered List -->
                <div class="example example-wide">
                    <h4>Numbered List</h4>
                    <div class="example-input">
                        1. First item<br>
                        2. Second item<br>
                        3. Third item
                    </div>
                    <div class="example-output">
                        <?= render_formatted_text("1. First item\n2. Second item\n3. Third item") ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blockquotes Section -->
        <div class="formatting-section">
            <h3>Blockquotes</h3>
            <div class="example example-wide">
                <h4>Quote Text</h4>
                <div class="example-input">
                    > This is a blockquote.<br>
                    > It can span multiple lines.
                </div>
                <div class="example-output">
                    <?= render_formatted_text("> This is a blockquote\n> spanning multiple lines") ?>
                </div>
            </div>
        </div>

        <!-- Combined Formatting -->
        <div class="formatting-section">
            <h3>Combining Formats</h3>
            <div class="example example-wide">
                <h4>Combined Formatting</h4>
                <div class="example-input">
                    - **Bold list item**<br>
                    - _Italic list item_<br>
                    - List item with `code`
                </div>
                <div class="example-output">
                    <?= render_formatted_text($combinedExample) ?>
                </div>
            </div>
        </div>

        <!-- Live Editor -->
        <div class="formatting-section">
            <h3>Try It Yourself</h3>
            <p>Use the editor below to practice formatting:</p>

            <div class="practice-area">
                <?php add_formatting_toolbar('practice-editor'); ?>
                <textarea id="practice-editor" class="formattable"
                    rows="6" placeholder="Type your text here and use the formatting toolbar..."></textarea>

                <div class="preview-container">
                    <h4>Live Preview</h4>
                    <div id="practice-preview">
                        <em>Preview will update as you type...</em>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const editor = document.getElementById('practice-editor');
                    const preview = document.getElementById('practice-preview');

                    // Live update on every input
                    editor.addEventListener('input', function() {
                        fetch('preview.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'content=' + encodeURIComponent(editor.value)
                            })
                            .then(response => response.text())
                            .then(html => {
                                preview.innerHTML = html || '<em>Empty preview</em>';
                            })
                            .catch(console.error);
                    });
                });
            </script>
        </div>

        <!-- Formatting Tips -->
        <div class="formatting-section">
            <h3>Tips for Better Formatting</h3>
            <ul class="tips-list">
                <li><b>Use formatting sparingly -</b> Too much formatting can make your post harder to read.</li>
                <li><b>Break up long paragraphs -</b> Use lists and line breaks to improve readability.</li>
                <li><b>Emphasize important points -</b> Use bold for key information.</li>
                <li><b>Use code formatting for code -</b> Share code snippets clearly.</li>
                <li><b>Quote properly -</b> Use blockquotes for referencing other content.</li>
            </ul>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>