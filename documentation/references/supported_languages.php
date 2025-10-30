<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Supported Languages - Argo Community</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>
    <script src="../../resources/scripts/levenshtein.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../resources/styles/help.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/link.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
        <style>
        /* Grid System */
        .three-column-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 0;
        }

        .language-item {
            background: #ffffff;
            border-radius: 6px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .language-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .language-item h4 {
            margin-bottom: 10px;
            color: #374151;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Responsive design */
        @media (max-width: 1024px) {
            .three-column-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .three-column-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="reference-container">
        <a href="../index.php#supported-languages" class="link-no-underline back-link">← Back to Documentation</a>

        <div class="reference-header">
            <h1>Supported Languages</h1>
            <p>Argo Sales Tracker supports 54 languages to help users worldwide work in their preferred language. Change your language anytime in Settings > General.</p>
            <input type="text" class="search-box" id="languageSearch" placeholder="Search languages...">
        </div>

        <div class="reference-category">
            <h3>Most Common Languages</h3>
            <div class="item-group three-column-grid">
                <div class="language-item">
                    <h4>English</h4>
                    <div class="item-info">
                        <span class="language-region">North America, UK, Australia</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>Spanish</h4>
                    <div class="item-info">
                        <span class="language-region">Spain, Latin America</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>French</h4>
                    <div class="item-info">
                        <span class="language-region">France, Canada, Belgium</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>German</h4>
                    <div class="item-info">
                        <span class="language-region">Germany, Austria</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>Chinese (Simplified)</h4>
                    <div class="item-info">
                        <span class="language-region">Mainland China</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>Chinese (Traditional)</h4>
                    <div class="item-info">
                        <span class="language-region">Taiwan, Hong Kong</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>Japanese</h4>
                    <div class="item-info">
                        <span class="language-region">Japan</span>
                    </div>
                </div>
                <div class="language-item">
                    <h4>Arabic</h4>
                    <div class="item-info">
                        <span class="language-region">Middle East, North Africa</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="reference-category">
            <h3>All Supported Languages (A-Z)</h3>
            <div class="alphabetical-grid">
                <div class="alphabet-section">
                    <h4>A</h4>
                    <div class="item-group-small">
                        <h5>Albanian</h5>
                        <div class="item-info">
                            <span class="language-region">Albania</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>B</h4>
                    <div class="item-group-small">
                        <h5>Basque</h5>
                        <div class="item-info">
                            <span class="language-region">Basque Country</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Belarusian</h5>
                        <div class="item-info">
                            <span class="language-region">Belarus</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Bengali</h5>
                        <div class="item-info">
                            <span class="language-region">Bangladesh, India</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Bosnian</h5>
                        <div class="item-info">
                            <span class="language-region">Bosnia and Herzegovina</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Bulgarian</h5>
                        <div class="item-info">
                            <span class="language-region">Bulgaria</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>C</h4>
                    <div class="item-group-small">
                        <h5>Catalan</h5>
                        <div class="item-info">
                            <span class="language-region">Catalonia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Croatian</h5>
                        <div class="item-info">
                            <span class="language-region">Croatia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Czech</h5>
                        <div class="item-info">
                            <span class="language-region">Czech Republic</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>D</h4>
                    <div class="item-group-small">
                        <h5>Danish</h5>
                        <div class="item-info">
                            <span class="language-region">Denmark</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Dutch</h5>
                        <div class="item-info">
                            <span class="language-region">Netherlands, Belgium</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>E</h4>
                    <div class="item-group-small">
                        <h5>Estonian</h5>
                        <div class="item-info">
                            <span class="language-region">Estonia</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>F</h4>
                    <div class="item-group-small">
                        <h5>Filipino</h5>
                        <div class="item-info">
                            <span class="language-region">Philippines</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Finnish</h5>
                        <div class="item-info">
                            <span class="language-region">Finland</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>G</h4>
                    <div class="item-group-small">
                        <h5>Galician</h5>
                        <div class="item-info">
                            <span class="language-region">Galicia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Greek</h5>
                        <div class="item-info">
                            <span class="language-region">Greece</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>H</h4>
                    <div class="item-group-small">
                        <h5>Hebrew</h5>
                        <div class="item-info">
                            <span class="language-region">Israel</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Hindi</h5>
                        <div class="item-info">
                            <span class="language-region">India</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Hungarian</h5>
                        <div class="item-info">
                            <span class="language-region">Hungary</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>I</h4>
                    <div class="item-group-small">
                        <h5>Icelandic</h5>
                        <div class="item-info">
                            <span class="language-region">Iceland</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Indonesian</h5>
                        <div class="item-info">
                            <span class="language-region">Indonesia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Irish</h5>
                        <div class="item-info">
                            <span class="language-region">Ireland</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Italian</h5>
                        <div class="item-info">
                            <span class="language-region">Italy</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>K</h4>
                    <div class="item-group-small">
                        <h5>Korean</h5>
                        <div class="item-info">
                            <span class="language-region">South Korea</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>L</h4>
                    <div class="item-group-small">
                        <h5>Latvian</h5>
                        <div class="item-info">
                            <span class="language-region">Latvia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Lithuanian</h5>
                        <div class="item-info">
                            <span class="language-region">Lithuania</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Luxembourgish</h5>
                        <div class="item-info">
                            <span class="language-region">Luxembourg</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>M</h4>
                    <div class="item-group-small">
                        <h5>Macedonian</h5>
                        <div class="item-info">
                            <span class="language-region">North Macedonia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Malay</h5>
                        <div class="item-info">
                            <span class="language-region">Malaysia, Brunei</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Maltese</h5>
                        <div class="item-info">
                            <span class="language-region">Malta</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>N</h4>
                    <div class="item-group-small">
                        <h5>Norwegian</h5>
                        <div class="item-info">
                            <span class="language-region">Norway</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>P</h4>
                    <div class="item-group-small">
                        <h5>Persian</h5>
                        <div class="item-info">
                            <span class="language-region">Iran, Afghanistan</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Polish</h5>
                        <div class="item-info">
                            <span class="language-region">Poland</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Portuguese</h5>
                        <div class="item-info">
                            <span class="language-region">Portugal, Brazil</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>R</h4>
                    <div class="item-group-small">
                        <h5>Romanian</h5>
                        <div class="item-info">
                            <span class="language-region">Romania</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Russian</h5>
                        <div class="item-info">
                            <span class="language-region">Russia, Eastern Europe</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>S</h4>
                    <div class="item-group-small">
                        <h5>Serbian</h5>
                        <div class="item-info">
                            <span class="language-region">Serbia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Slovak</h5>
                        <div class="item-info">
                            <span class="language-region">Slovakia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Slovenian</h5>
                        <div class="item-info">
                            <span class="language-region">Slovenia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Swahili</h5>
                        <div class="item-info">
                            <span class="language-region">East Africa</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Swedish</h5>
                        <div class="item-info">
                            <span class="language-region">Sweden</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>T</h4>
                    <div class="item-group-small">
                        <h5>Thai</h5>
                        <div class="item-info">
                            <span class="language-region">Thailand</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Turkish</h5>
                        <div class="item-info">
                            <span class="language-region">Turkey</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>U</h4>
                    <div class="item-group-small">
                        <h5>Ukrainian</h5>
                        <div class="item-info">
                            <span class="language-region">Ukraine</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Urdu</h5>
                        <div class="item-info">
                            <span class="language-region">Pakistan, India</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>V</h4>
                    <div class="item-group-small">
                        <h5>Vietnamese</h5>
                        <div class="item-info">
                            <span class="language-region">Vietnam</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="noResults" class="no-results" style="display: none;">
            <div class="no-results-content">
                <h3>No languages found</h3>
                <p>No languages match your search criteria. Try searching for:</p>
                <ul>
                    <li>Language names (e.g., "English", "Spanish")</li>
                    <li>Country names (e.g., "Germany", "Japan")</li>
                    <li>Region names (e.g., "Europe", "Asia")</li>
                </ul>
            </div>
        </div>

        <div class="pattern-note">
            <h3>Changing Your Language</h3>
            <ol class="steps-list">
                <li>Go to "Settings > General" in the application</li>
                <li>Find the "Language" dropdown menu</li>
                <li>Select your preferred language from the list</li>
            </ol>

            <div class="info-box">
                <strong>Installation Note:</strong> The installer itself is currently only available in English, but once installed, you can switch to any of these 54 supported languages through the application settings.
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('languageSearch');
            const categories = document.querySelectorAll('.reference-category');
            const noResults = document.getElementById('noResults');
            const similarityThreshold = 0.6; // 0.0 = no match, 1.0 = perfect match

            searchBox.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let totalVisibleItems = 0;

                categories.forEach(category => {
                    const languageGroups = category.querySelectorAll('.item-group, .item-group-small, .alphabet-section');
                    let hasVisibleItems = false;

                    languageGroups.forEach(group => {
                        const groupText = group.textContent.toLowerCase();
                        let isMatch = false;

                        // Check for exact substring match first (faster)
                        if (groupText.includes(searchTerm) || searchTerm === '') {
                            isMatch = true;
                        } else {
                            // Fuzzy matching with Levenshtein distance
                            const words = groupText.split(/\s+/);
                            isMatch = words.some(word =>
                                getSimilarity(word, searchTerm) >= similarityThreshold
                            );
                        }

                        if (isMatch) {
                            group.style.display = '';
                            hasVisibleItems = true;
                            totalVisibleItems++;
                        } else {
                            group.style.display = 'none';
                        }
                    });

                    // Hide category if no items match
                    category.style.display = hasVisibleItems || searchTerm === '' ? '' : 'none';
                });

                // Show/hide no results message
                if (searchTerm !== '' && totalVisibleItems === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            });
        });
    </script>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>
