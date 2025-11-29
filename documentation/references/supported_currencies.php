<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Argo Books supports 28 international currencies with real-time exchange rates. View the complete list of supported currencies with their symbols and regions.">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Supported Currencies - Argo Community</title>

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

        .currency-item {
            background: #ffffff;
            border-radius: 6px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .currency-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .currency-item h4 {
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
        <a href="../#supported-currencies" class="link-no-underline back-link">← Back to Documentation</a>

        <div class="reference-header">
            <h1>Supported Currencies</h1>
            <p>Argo Books supports 28 international currencies with real-time exchange rates for accurate conversions between any supported currencies.</p>
            <input type="text" class="search-box" id="currencySearch" placeholder="Search currencies...">
        </div>

        <div class="reference-category">
            <h3>Most Common Currencies</h3>
            <div class="item-group three-column-grid">
                <div class="currency-item">
                    <h4>United States Dollar (USD)</h4>
                    <div class="item-info">
                        <span class="currency-symbol">$</span>
                        <span class="currency-region">United States</span>
                    </div>
                </div>
                <div class="currency-item">
                    <h4>Euro (EUR)</h4>
                    <div class="item-info">
                        <span class="currency-symbol">€</span>
                        <span class="currency-region">European Union</span>
                    </div>
                </div>
                <div class="currency-item">
                    <h4>British Pound (GBP)</h4>
                    <div class="item-info">
                        <span class="currency-symbol">£</span>
                        <span class="currency-region">United Kingdom</span>
                    </div>
                </div>
                <div class="currency-item">
                    <h4>Canadian Dollar (CAD)</h4>
                    <div class="item-info">
                        <span class="currency-symbol">$</span>
                        <span class="currency-region">Canada</span>
                    </div>
                </div>
                <div class="currency-item">
                    <h4>Japanese Yen (JPY)</h4>
                    <div class="item-info">
                        <span class="currency-symbol">¥</span>
                        <span class="currency-region">Japan</span>
                    </div>
                </div>
                <div class="currency-item">
                    <h4>Chinese Yuan (CNY)</h4>
                    <div class="item-info">
                        <span class="currency-symbol">¥</span>
                        <span class="currency-region">China</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="reference-category">
            <h3>All Supported Currencies (A-Z)</h3>
            <div class="alphabetical-grid">
                <div class="alphabet-section">
                    <h4>A</h4>
                    <div class="item-group-small">
                        <h5>Albanian Lek (ALL)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">L</span>
                            <span class="currency-region">Albania</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Australian Dollar (AUD)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">$</span>
                            <span class="currency-region">Australia</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>B</h4>
                    <div class="item-group-small">
                        <h5>Bosnia and Herzegovina Convertible Mark (BAM)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">KM</span>
                            <span class="currency-region">Bosnia and Herzegovina</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Bulgarian Lev (BGN)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">лв</span>
                            <span class="currency-region">Bulgaria</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Brazilian Real (BRL)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">R$</span>
                            <span class="currency-region">Brazil</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Belarusian Ruble (BYN)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">Br</span>
                            <span class="currency-region">Belarus</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>C</h4>
                    <div class="item-group-small">
                        <h5>Swiss Franc (CHF)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">CHF</span>
                            <span class="currency-region">Switzerland</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Czech Koruna (CZK)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">Kč</span>
                            <span class="currency-region">Czech Republic</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>D</h4>
                    <div class="item-group-small">
                        <h5>Danish Krone (DKK)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">kr</span>
                            <span class="currency-region">Denmark</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>H</h4>
                    <div class="item-group-small">
                        <h5>Hungarian Forint (HUF)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">Ft</span>
                            <span class="currency-region">Hungary</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>I</h4>
                    <div class="item-group-small">
                        <h5>Icelandic Króna (ISK)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">kr</span>
                            <span class="currency-region">Iceland</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>K</h4>
                    <div class="item-group-small">
                        <h5>South Korean Won (KRW)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">₩</span>
                            <span class="currency-region">South Korea</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>M</h4>
                    <div class="item-group-small">
                        <h5>Macedonian Denar (MKD)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">ден</span>
                            <span class="currency-region">North Macedonia</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>N</h4>
                    <div class="item-group-small">
                        <h5>Norwegian Krone (NOK)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">kr</span>
                            <span class="currency-region">Norway</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>P</h4>
                    <div class="item-group-small">
                        <h5>Polish Złoty (PLN)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">zł</span>
                            <span class="currency-region">Poland</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>R</h4>
                    <div class="item-group-small">
                        <h5>Romanian Leu (RON)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">lei</span>
                            <span class="currency-region">Romania</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Serbian Dinar (RSD)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">дин</span>
                            <span class="currency-region">Serbia</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Russian Ruble (RUB)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">₽</span>
                            <span class="currency-region">Russia</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>S</h4>
                    <div class="item-group-small">
                        <h5>Swedish Krona (SEK)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">kr</span>
                            <span class="currency-region">Sweden</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>T</h4>
                    <div class="item-group-small">
                        <h5>Turkish Lira (TRY)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">₺</span>
                            <span class="currency-region">Turkey</span>
                        </div>
                    </div>
                    <div class="item-group-small">
                        <h5>Taiwan Dollar (TWD)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">NT$</span>
                            <span class="currency-region">Taiwan</span>
                        </div>
                    </div>
                </div>

                <div class="alphabet-section">
                    <h4>U</h4>
                    <div class="item-group-small">
                        <h5>Ukrainian Hryvnia (UAH)</h5>
                        <div class="item-info">
                            <span class="currency-symbol">₴</span>
                            <span class="currency-region">Ukraine</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="noResults" class="no-results" style="display: none;">
            <div class="no-results-content">
                <h3>No currencies found</h3>
                <p>No currencies match your search criteria. Try searching for:</p>
                <ul>
                    <li>Currency names (e.g., "Euro", "Dollar")</li>
                    <li>Currency codes (e.g., "USD", "EUR", "GBP")</li>
                    <li>Country names (e.g., "United States", "Japan")</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('currencySearch');
            const categories = document.querySelectorAll('.reference-category');
            const noResults = document.getElementById('noResults');
            const similarityThreshold = 0.6; // 0.0 = no match, 1.0 = perfect match

            searchBox.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let totalVisibleItems = 0;

                categories.forEach(category => {
                    const currencyGroups = category.querySelectorAll('.item-group, .item-group-small, .alphabet-section');
                    let hasVisibleItems = false;

                    currencyGroups.forEach(group => {
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