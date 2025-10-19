<?php
session_start();
require_once '../../db_connect.php';
require_once '../../community/formatting/formatting_functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Accepted Country Names - Argo Community</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../resources/styles/help.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="reference-container">
        <a href="../index.php#keyboard-shortcuts" class="link-no-underline back-link">← Back to Documentation</a>

        <div class="reference-header">
            <h1>Report Generator Keyboard Shortcuts</h1>
            <p>Speed up your workflow with these keyboard shortcuts.</p>
        </div>

        <div class="reference-category">
            <div class="shortcut-section">
                <h4>General Actions</h4>
                <div class="shortcut-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>Z</kbd>
                        </div>
                        <div class="shortcut-description">Undo last action</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>Y</kbd>
                        </div>
                        <div class="shortcut-description">Redo last undone action</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd>
                        </div>
                        <div class="shortcut-description">Redo last undone action (alternative)</div>
                    </div>
                </div>
            </div>

            <div class="shortcut-section">
                <h4>Selection & Editing</h4>
                <div class="shortcut-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>A</kbd>
                        </div>
                        <div class="shortcut-description">Select all elements on the canvas</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>D</kbd>
                        </div>
                        <div class="shortcut-description">Duplicate selected element(s)</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Delete</kbd>
                        </div>
                        <div class="shortcut-description">Delete selected element(s)</div>
                    </div>
                </div>
            </div>

            <div class="shortcut-section">
                <h4>Element Movement (Fine Control)</h4>
                <div class="shortcut-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>←</kbd>
                        </div>
                        <div class="shortcut-description">Move element 1 pixel left</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>→</kbd>
                        </div>
                        <div class="shortcut-description">Move element 1 pixel right</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>↑</kbd>
                        </div>
                        <div class="shortcut-description">Move element 1 pixel up</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>↓</kbd>
                        </div>
                        <div class="shortcut-description">Move element 1 pixel down</div>
                    </div>
                </div>
            </div>

            <div class="shortcut-section">
                <h4>Element Movement (Large Steps)</h4>
                <div class="shortcut-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Shift</kbd> + <kbd>←</kbd>
                        </div>
                        <div class="shortcut-description">Move element 10 pixels left</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Shift</kbd> + <kbd>→</kbd>
                        </div>
                        <div class="shortcut-description">Move element 10 pixels right</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Shift</kbd> + <kbd>↑</kbd>
                        </div>
                        <div class="shortcut-description">Move element 10 pixels up</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Shift</kbd> + <kbd>↓</kbd>
                        </div>
                        <div class="shortcut-description">Move element 10 pixels down</div>
                    </div>
                </div>
            </div>

            <div class="shortcut-section">
                <h4>Alignment</h4>
                <div class="shortcut-grid">
                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>←</kbd>
                        </div>
                        <div class="shortcut-description">Align selected elements to the left</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>→</kbd>
                        </div>
                        <div class="shortcut-description">Align selected elements to the right</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>↑</kbd>
                        </div>
                        <div class="shortcut-description">Align selected elements to the top</div>
                    </div>

                    <div class="shortcut-item">
                        <div class="shortcut-keys">
                            <kbd>Ctrl</kbd> + <kbd>↓</kbd>
                        </div>
                        <div class="shortcut-description">Align selected elements to the bottom</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .shortcut-section {
            margin-bottom: 30px;
        }

        .shortcut-section h4 {
            margin-bottom: 15px;
            color: #374151;
            font-size: 1rem;
            font-weight: 600;
        }

        .shortcut-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .shortcut-item {
            background: #ffffff;
            border-radius: 6px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s;
        }

        .shortcut-item:hover {
            border-color: var(--primary-blue, #2563eb);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        }

        .shortcut-keys {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
            min-width: 120px;
        }

        kbd {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 4px 8px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            display: inline-block;
            min-width: 30px;
            text-align: center;
        }

        .shortcut-description {
            color: #6b7280;
            font-size: 14px;
            flex: 1;
        }

        .tips-list {
            list-style: none;
            padding: 0;
            margin: 15px 0 0;
        }

        .tips-list li {
            background: #f8fafc;
            padding: 12px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 3px solid var(--primary-blue, #2563eb);
            font-size: 14px;
            color: #374151;
        }

        .tips-list li strong {
            color: var(--primary-blue, #2563eb);
        }

        .tips-list kbd {
            margin: 0 2px;
        }

        @media (max-width: 768px) {
            .shortcut-grid {
                grid-template-columns: 1fr;
            }

            .shortcut-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .shortcut-keys {
                min-width: auto;
            }
        }
    </style>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>