<?php
// Load system requirements from JSON
function getSystemRequirements()
{
    $jsonPath = '../resources/data/system-requirements.json';
    if (file_exists($jsonPath)) {
        $json = file_get_contents($jsonPath);
        return json_decode($json, true);
    }
    return [];
}

// Get platform icon SVG path
function getPlatformIconPath($platform)
{
    $icons = [
        'windows' => 'M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801',
        'macos' => 'M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z',
        'linux' => 'M12.504 0c-.155 0-.315.008-.48.021-4.226.333-3.105 4.807-3.17 6.298-.076 1.092-.3 1.953-1.05 3.02-.885 1.051-2.127 2.75-2.716 4.521-.278.832-.41 1.684-.287 2.489a.424.424 0 00-.11.135c-.26.268-.45.6-.663.839-.199.199-.485.267-.797.4-.313.136-.658.269-.864.68-.09.189-.136.394-.132.602 0 .199.027.4.055.536.058.399.116.728.04.97-.249.68-.28 1.145-.106 1.484.174.334.535.47.94.601.81.2 1.91.135 2.774.6.926.466 1.866.67 2.616.47.526-.116.97-.464 1.208-.946.587-.003 1.23-.269 2.26-.334.699-.058 1.574.267 2.577.2.025.134.063.198.114.333l.003.003c.391.778 1.113 1.132 1.884 1.071.771-.06 1.592-.536 2.257-1.306.631-.765 1.683-1.084 2.378-1.503.348-.199.629-.469.649-.853.023-.4-.2-.811-.714-1.376v-.097l-.003-.003c-.17-.2-.25-.535-.338-.926-.085-.401-.182-.786-.492-1.046h-.003c-.059-.054-.123-.067-.188-.135a.357.357 0 00-.19-.064c.431-1.278.264-2.55-.173-3.694-.533-1.41-1.465-2.638-2.175-3.483-.796-1.005-1.576-1.957-1.56-3.368.026-2.152.236-6.133-3.544-6.139z'
    ];
    return $icons[$platform] ?? '';
}

$systemRequirements = getSystemRequirements();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Argo">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Complete Argo Books documentation and user guide. Learn installation, features, tutorials for sales tracking, product management, analytics, Excel import/export, and security settings.">
    <meta name="keywords"
        content="argo books documentation, argo books tutorial, business software guide, how to use argo books, installation guide, user manual, sales tracking help, product management tutorial">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Documentation & User Guide - Argo Books">
    <meta property="og:description"
        content="Complete Argo Books documentation and user guide. Learn installation, features, tutorials for sales tracking, product management, analytics, Excel import/export, and security settings.">
    <meta property="og:url" content="https://argorobots.com/documentation/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Books">
    <meta property="og:locale" content="en_CA">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Documentation & User Guide - Argo Books">
    <meta name="twitter:description"
        content="Complete Argo Books documentation and user guide. Learn installation, features, tutorials for sales tracking, product management, analytics, Excel import/export, and security settings.">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="CA-AB">
    <meta name="geo.placename" content="Calgary">
    <meta name="geo.position" content="51.0447;-114.0719">
    <meta name="ICBM" content="51.0447, -114.0719">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://argorobots.com/documentation/">

    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Documentation & User Guide - Argo Books</title>

    <script src="main.js"></script>
    <script src="pdf-export.js"></script>
    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="../resources/scripts/ScrollToCenter.js"></script>
    <script src="../resources/scripts/levenshtein.js"></script>
    <script src="search.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="search.css">
    <link rel="stylesheet" href="pdf-export.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/styles/link.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/header/dark.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
</head>

<body>
    <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle documentation menu">
        <span class="toggle-text">Docs Menu</span>
        <svg class="menu-icon" width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M2 4h12M2 8h16M2 12h12M2 16h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
    </button>

    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="docs-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <!-- Getting Started section -->
                <div class="nav-section">
                    <h3>Getting Started</h3>
                    <ul class="nav-links">
                        <li data-scroll-to="system-requirements" title="View system requirements">System Requirements
                        </li>
                        <li data-scroll-to="installation" title="View installation guide">Installation Guide</li>
                        <li data-scroll-to="quick-start" title="View quick start tutorial">Quick Start Tutorial</li>
                        <li data-scroll-to="version-comparison" title="Compare versions">Free vs. Paid Version</li>
                    </ul>
                </div>

                <!-- Core Features section -->
                <div class="nav-section">
                    <h3>Core Features</h3>
                    <ul class="nav-links">
                        <li data-scroll-to="product-management" title="Learn about product management">Product
                            Management</li>
                        <li data-scroll-to="sales-tracking" title="Learn about sales tracking">Purchase/Sales Tracking
                        </li>
                        <li data-scroll-to="receipts" title="Learn about receipt management">Receipt Management</li>
                        <li data-scroll-to="spreadsheet-import" title="Learn about importing spreadsheets">Spreadsheet
                            Import</li>
                        <li data-scroll-to="spreadsheet-export" title="Learn about exporting spreadsheets">Spreadsheet
                            Export</li>
                        <li data-scroll-to="report-generator" title="Learn about report generator">Report Generator</li>
                        <li data-scroll-to="search-bar" title="Learn about search features">Advanced Search</li>
                    </ul>
                </div>

                <!-- Reference section -->
                <div class="nav-section">
                    <h3>Reference</h3>
                    <ul class="nav-links">
                        <li data-scroll-to="accepted-countries" title="View accepted country names">Accepted Countries
                        </li>
                        <li data-scroll-to="supported-currencies" title="View supported currencies">Supported Currencies
                        </li>
                        <li data-scroll-to="supported-languages" title="View supported languages">Supported Languages
                        </li>
                    </ul>
                </div>

                <!-- Security section -->
                <div class="nav-section">
                    <h3>Security</h3>
                    <ul class="nav-links">
                        <li data-scroll-to="encryption" title="Learn about encryption">Encryption</li>
                        <li data-scroll-to="password" title="Learn about password protection">Password Protection</li>
                        <li data-scroll-to="backups" title="Learn about backups">Regular Backups</li>
                        <li data-scroll-to="anonymous-data" title="Learn about anonymous data collection">Anonymous
                            Usage Data</li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="content">
            <section class="article">
                <h1>Argo Books Documentation</h1>
                <!-- SEARCH BAR -->
                    <div class="search-container">
                        <div class="search-box">
                            <input type="text" id="docSearchInput" placeholder="Search documentation... (e.g., 'installation', 'export', 'password')" 
                                aria-label="Search documentation">
                            <button id="searchButton" aria-label="Search">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="searchResults" class="search-results" style="display: none;"></div>
                    </div>
                <!-- SEARCH BAR -->
                <p>Welcome to the Argo Books documentation. This guide will help you get started and make the
                    most of our software.</p>

                <div class="contact-box">
                    <p><strong>Need Help?</strong> If you have questions or need assistance, please <a
                            href="../contact-us/index.php" class="link">contact us</a>. Our support team is here to help
                        you succeed with Argo Books.</p>
                </div>
            </section>

            <!-- Getting Started section -->
            <!-- System Requirements Section -->
            <section id="system-requirements" class="article">
                <h2>System Requirements</h2>
                <div class="requirements-grid">
                    <?php foreach ($systemRequirements as $platform => $data): ?>
                    <div class="requirement-card">
                        <h3>
                            <svg viewBox="0 0 24 24" fill="currentColor" class="req-icon">
                                <path d="<?php echo getPlatformIconPath($platform); ?>"/>
                            </svg>
                            <?php echo htmlspecialchars($data['name']); ?>
                        </h3>
                        <ul>
                            <?php foreach ($data['requirements'] as $req): ?>
                            <li><?php echo htmlspecialchars($req); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Installation section -->
            <section id="installation" class="article">
                <h2>Installation Guide</h2>
                <ol class="steps-list">
                    <li>Download the installer <a class="link" href="../downloads">here</a></li>
                    <li>Run the installer file (Argo Books Installer.exe)</li>
                    <li>Follow the installation wizard</li>
                    <li>Launch Argo Books from your desktop or start menu</li>
                </ol>

                <div class="info-box">
                    <strong>Tip:</strong> Your computer may display a security warning. This is normal for new applications.
                    Click "More info" and then "Run anyway" to proceed.
                </div>
            </section>

            <!-- Quick Start Tutorial section -->
            <section id="quick-start" class="article">
                <h2>Quick Start Tutorial</h2>
                <ol class="steps-list">
                    <li>Choose your default currency and create your first company</li>
                    <li>Add accountants and companies you will be working with</li>
                    <li>Set up categories to organize your products</li>
                    <li>Add your initial products</li>
                    <li>Add purchases and sales</li>
                </ol>

                <div class="info-box">
                    <strong>Note:</strong> A video tutorial is coming soon to help you visualize these steps. Check back
                    later for a comprehensive walkthrough.
                </div>

                <!-- <div class="video-container">
                    <iframe src="https://www.youtube-nocookie.com/embed/5aCbWqKl-wU"
                        title="Argo Books Quick Start Tutorial" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div> -->
            </section>

            <!-- Free vs. Paid Version section -->
            <section id="version-comparison" class="article">
                <div class="description">
                    <h2>Free vs. Paid Version</h2>
                    <p>Argo Books offers two versions to match your business needs. Start with our free version,
                        perfect for small businesses just getting started with inventory tracking. As your business
                        grows, seamlessly upgrade to our paid version for unlimited products and advanced features.</p>
                    <p>Not sure which version is right for you? <a href="../index.php" class="link">Try our free
                            version first</a> – you can always <a href="../upgrade/index.php" class="link">upgrade
                            later</a> when you need more features.</p>
                </div>

                <div class="version-cards">
                    <div class="version-card">
                        <div class="card-header">
                            <h3 class="version-title">Free Version</h3>
                            <p class="version-subtitle">Perfect for small businesses</p>
                        </div>
                        <ul class="feature-list">
                            <li class="feature-item">
                                <svg class="limit-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M8 12h8"></path>
                                </svg>
                                <span class="feature-text">Limited to 10 products</span>
                            </li>
                            <li class="feature-item">
                                <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                <span class="feature-text">Basic password protection</span>
                            </li>
                            <li class="feature-item">
                                <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                <span class="feature-text">Basic support via email</span>
                            </li>
                        </ul>
                    </div>

                    <div class="version-card">
                        <div class="card-header">
                            <h3 class="version-title">Paid Version</h3>
                            <p class="version-subtitle">For growing businesses</p>
                        </div>
                        <ul class="feature-list">
                            <li class="feature-item">
                                <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                <span class="feature-text">Unlimited products</span>
                            </li>
                            <li class="feature-item">
                                <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                <span class="feature-text">Windows Hello integration</span>
                            </li>
                            <li class="feature-item">
                                <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                <span class="feature-text">AI search</span>
                            </li>
                            <li class="feature-item">
                                <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>
                                <span class="feature-text">Priority support</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Core Features section -->
            <!-- Product Management section -->
            <section id="product-management" class="article">
                <h2>Product Management</h2>
                <h3>Creating Categories to organize your products</h3>
                <ol class="steps-list">
                    <li>Go to "Manage Categories" in the top menu
                    <li>Choose whether the category is for purchases or sales
                    <li>Enter the category name
                    <li>Click "Add Category"
                </ol>

                <h3>Adding Products</h3>
                <ol class="steps-list">
                    <li>Go to "Manage Products" in the top menu
                    <li>Select whether the product is for purchases or sales
                    <li>Enter the product ID and name
                    <li>Select a category to keep things organized
                    <li>Enter country and company of origin
                    <li>Click "Add Product"
                </ol>

                <div class="info-box">
                    <p>Let's say you run a t-shirt store. Here's how you might set up three products within 2
                        categories:
                    </p>

                    <table class="comparison-table">
                        <tr>
                            <th>Category</th>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Country</th>
                            <th>Company</th>
                        </tr>
                        <tr>
                            <td>Men's T-Shirts</td>
                            <td>TS001</td>
                            <td>Black T-Shirt</td>
                            <td>United States</td>
                            <td>Cotton Mills Ltd</td>
                        </tr>
                        <tr>
                            <td>Men's T-Shirts</td>
                            <td>TS002</td>
                            <td>White Linen T-Shirt</td>
                            <td>Italy</td>
                            <td>FabricCo</td>
                        </tr>
                        <tr>
                            <td>Women's T-Shirts</td>
                            <td>TS003</td>
                            <td>White T-Shirt</td>
                            <td>Germany</td>
                            <td>TextileCo</td>
                        </tr>
                    </table>
                </div>

                <div class="warning-box">
                    <strong>Important:</strong> Free version users are limited to 10 products. Upgrade to the paid
                    version <a class="link" href="../upgrade/index.php">here</a> for unlimited products.
                </div>
            </section>

            <!-- Purchase/Sales Tracking section -->
            <section id="sales-tracking" class="article">
                <h2>Adding Purchases and Sales</h2>
                <ol class="steps-list">
                    <li>Click "Add Purchase" or "Add Sale" in the top menu</li>
                    <li>Enter the order number and select your name from the accountants list</li>
                    <li>Select the product from the dropdown (must be added in <a class="link"
                            data-scroll-to="product-management">Product Management</a> first)</li>

                    <li>Enter the quantity and price per unit</li>
                    <li>Add shipping costs, taxes, and any other fees</li>
                    <li>Optionally attach a receipt</li>
                    <li>Click "Add" to save</li>
                </ol>

                <div class="info-box">
                    <strong>Tip:</strong> Use the "Multiple items" checkbox when adding multiple products to a single
                    purchase or sale.
                </div>
            </section>

            <!-- Receipt Management section -->
            <section id="receipts" class="article">
                <h2>Receipt Management</h2>
                <p>Keep your records organized by attaching and managing digital receipts for all transactions.</p>

                <h3>Adding Receipts</h3>
                <p>When adding a purchase or sale, click the "Add Receipt" button, then select the receipt file from
                    your computer.</p>
                <p>To add receipts to existing transactions, right-click the transaction, select "Modify", and click the
                    "Add receipt" button.</p>

                <h3>Digitizing Physical Receipts</h3>
                <p>You can quickly digitize paper receipts by taking a photo with your smartphone:</p>
                <ol class="steps-list">
                    <li>Install Microsoft Lens on your smartphone - it's free and available for
                        both iOS and Android</li>
                    <li>Open Microsoft Lens and select "Document" mode</li>
                    <li>Position your phone's camera over the receipt</li>
                    <li>The app will automatically detect the receipt's edges and optimize the image</li>
                    <li>Save the digitized receipt as a PDF or image file</li>
                    <li>Upload the digital copy to your computer or sync with your cloud storage</li>
                </ol>

                <h3>Exporting Receipts from the main screen</h3>
                <ol class="steps-list">
                    <li>Select the transactions you want to export receipts for. You can hold down the Ctrl key or use
                        the Shift key</li>
                    <li>Right-click on any of the selected transaction and click "Export receipts"</li>
                    <li>Choose a destination folder</li>
                </ol>

                <h3>Exporting Receipts from the Receipt Manager</h3>
                <ol class="steps-list">
                    <li>Click the file button on the top left, then click "Export Receipts"</li>
                    <li>Optionally filter the receipts you want to export</li>
                    <li>Select the receipts you want to export. You can click the "Select all" button or press "Ctrl+A"
                    </li>
                    <li>Click the "Export" button and choose the destination</li>
                </ol>

                <div class="info-box">
                    <strong>Tip:</strong> When exporting multiple receipts, they will be organized in a dated folder.
                </div>
            </section>

            <!-- Spreadsheet Import section -->
            <section id="spreadsheet-import" class="article">
                <h2>Spreadsheet Import</h2>
                <p>Import your existing business data from Excel spreadsheets into Argo Books. The import system
                    supports multiple currencies and can automatically detect the currency used in your data.</p>

                <h3>Preparing Your Spreadsheet</h3>
                <p>Download our <a class="link" href="../resources/downloads/Argo Books format.xlsx">template
                        spreadsheet</a> to see the exact format required. Your Excel file can include any combination of
                    the following sheets (not all are required):</p>

                <div class="info-box">
                    <ul>
                        <li><strong>Accountants</strong> - A simple list with accountant names</li>
                        <li><strong>Companies</strong> - A simple list with company names</li>
                        <li><strong>Purchase products</strong> - Products for purchasing</li>
                        <li><strong>Sale products</strong> - Products for selling</li>
                        <li><strong>Purchases</strong> - Purchase transaction records</li>
                        <li><strong>Sales</strong> - Sales transaction records</li>
                    </ul>
                </div>

                <h3>Formatting Requirements</h3>
                <div class="info-box">
                    <p>Download our <a class="link"
                            href="../resources/downloads/Argo Books format.xlsx">template spreadsheet</a> and
                        follow the exact format shown. This is much easier than trying to remember formatting rules!</p>

                    <p>Key points:</p>
                    <ul>
                        <li><strong>Sheet names:</strong> Use "Accountants", "Companies", "Purchase products", "Sale
                            products", "Purchases", "Sales" (case doesn't matter)</li>
                        <li><strong>Date format:</strong> YYYY-MM-DD (e.g., 2025-01-15)</li>
                        <li><strong>Country names:</strong> Must match the <a class="link"
                                href="references/accepted_countries.php">accepted country list</a></li>
                        <li><strong>Everything else:</strong> Follow the template format exactly</li>
                    </ul>
                </div>

                <h3>Currency Support</h3>
                <p>The import system supports <a class="link" data-scroll-to="supported-currencies">28 different
                        currencies</a>. The system will attempt to automatically detect the currency from your
                    spreadsheet data, but you can also manually select the source currency during import.</p>

                <div class="info-box">
                    <strong>Multi-Currency Support:</strong> If your spreadsheet contains data in a different currency
                    than your default, the system will automatically convert all values using real-time exchange rates
                    for the transaction dates.
                </div>

                <h3>How to Import</h3>
                <ol class="steps-list">
                    <li>Click "File > Import spreadsheet"</li>
                    <li>Select your Excel file</li>
                    <li>The system will detect which data sheets are available and show a preview</li>
                    <li>Review the detected currency (or select manually if needed)</li>
                    <li>Select which data sections you want to import using the checkboxes</li>
                    <li>Optionally select a receipts folder if you have receipt files to import</li>
                    <li>Click "Import" to begin the process</li>
                </ol>

                <h3>What Gets Created Automatically</h3>
                <p>The import system automatically creates any missing companies, categories, or accountants referenced
                    in your transaction data.</p>

                <h3>Receipt Import</h3>
                <p>If you have receipt files to import alongside your data:</p>
                <ol class="steps-list">
                    <li>Organize your receipt files in a folder on your computer</li>
                    <li>In your spreadsheet, include the receipt filename in the "Receipt" column</li>
                    <li>During import, select the folder containing your receipt files</li>
                    <li>The system will automatically link receipts to their corresponding transactions</li>
                </ol>

                <div class="info-box">
                    <strong>Tip:</strong> The system automatically looks for a receipts folder next to your
                    spreadsheet file with names like "receipts".
                </div>
            </section>

            <!-- Spreadsheet Export section -->
            <section id="spreadsheet-export" class="article">
                <h2>Spreadsheet Export</h2>
                <p>Export your data to Excel spreadsheets for backup, analysis, or sharing with accountants and business
                    partners.</p>

                <h3>Exporting Your Data</h3>
                <ol class="steps-list">
                    <li>Click "File > Export"</li>
                    <li>Select "Excel spreadsheet (.xlsx)" from the dropdown menu</li>
                    <li>Choose your preferred currency for the export</li>
                    <li>Select a location to save your export file</li>
                </ol>

                <h3>What Gets Exported</h3>
                <p>The exported Excel file contains separate worksheets for each data type:</p>
                <ul>
                    <li><strong>Purchases:</strong> All purchase transactions with complete details</li>
                    <li><strong>Sales:</strong> All sales transactions with complete details</li>
                    <li><strong>Purchase products:</strong> All products available for purchasing</li>
                    <li><strong>Sale products:</strong> All products available for selling</li>
                    <li><strong>Companies:</strong> List of all companies</li>
                    <li><strong>Accountants:</strong> List of all accountants</li>
                </ul>

                <h3>Currency Conversion</h3>
                <p>When exporting, you can choose any of the <a class="link"
                        data-scroll-to="supported-currencies">supported currencies</a>. The system will:</p>
                <ul>
                    <li>Convert all monetary values to your chosen currency using historical exchange rates</li>
                    <li>Use the exact exchange rate that was valid on each transaction's date</li>
                    <li>Display values with proper currency formatting</li>
                    <li>Add a note at the top indicating which currency is being used</li>
                </ul>

                <div class="info-box">
                    <strong>Multi-Item Transactions:</strong> Transactions with multiple items are exported with the
                    main transaction details on the first row, and additional items on subsequent rows with empty
                    transaction ID cells.
                </div>

                <h3>Receipt Export</h3>
                <p>Receipt filenames are included in the exported spreadsheet. If you need the actual receipt files:</p>
                <ol class="steps-list">
                    <li>Select the transactions you want to export receipts for in the main view</li>
                    <li>Right-click and select "Export receipts"</li>
                    <li>Choose a save location</li>
                </ol>

                <h3>Chart Export</h3>
                <p>Charts from the Analytics Dashboard can also be exported to Excel with full data:</p>
                <ol class="steps-list">
                    <li>Right-click any chart in the Analytics Dashboard</li>
                    <li>Select "Export to Microsoft Excel"</li>
                    <li>Choose a save location</li>
                </ol>
            </section>

            <section id="report-generator" class="article">
                <h2>Report Generator</h2>
                <p>Create professional, customized reports with charts and analytics for presentations, and financial analysis. The Report Generator uses a simple 3-step wizard to guide you through the process.</p>

                <h3>How to Generate a Report</h3>
                <ol class="steps-list">
                    <li>Go to "File > Generate Report"</li>
                    <li>Follow the 3-step wizard to create your custom report</li>
                </ol>

                <h3>Step 1: Data Selection</h3>
                <p>Choose what data to include in your report.</p>
                <ul>
                    <li><strong>Start with Templates:</strong> Use pre-built templates like Monthly Sales, Financial Overview, Performance Analysis, Returns Analysis, Losses Analysis, or Geographic Analysis</li>
                    <li><strong>Select Charts:</strong> Choose from available charts including sales, purchases, profits, distributions, returns, losses, and geographic data</li>
                    <li><strong>Set Date Range:</strong> Use quick presets (Last Month, Last 3 Months, etc.) or choose custom dates</li>
                    <li><strong>Apply Filters:</strong> Filter by categories, products, companies, countries, or include/exclude returns and losses</li>
                </ul>

                <h3>Step 2: Layout Designer</h3>
                <p>Arrange your report using drag-and-drop functionality.</p>
                <ul>
                    <li><strong>Add Elements:</strong> Include text labels, images, logos, date ranges, and summary boxes</li>
                    <li><strong>Drag and Drop:</strong> Click and drag elements to position them on the page</li>
                    <li><strong>Resize:</strong> Select an element and drag the corner handles to resize</li>
                    <li><strong>Customize:</strong> Adjust colors, borders, alignment, and other properties</li>
                    <li><strong>Undo/Redo:</strong> Use Ctrl+Z and Ctrl+Y to undo or redo changes</li>
                </ul>

                <h3>Step 3: Preview and Export</h3>
                <p>Review your report and export in your preferred format.</p>
                <ul>
                    <li><strong>Preview:</strong> Use zoom controls to examine your report in detail</li>
                    <li><strong>Export Format:</strong> Choose PNG (high quality), JPG (smaller files), or PDF (professional printing)</li>
                    <li><strong>Quality:</strong> Adjust the quality slider to balance file size and image quality</li>
                    <li><strong>Export:</strong> Select your save location and click "Export"</li>
                </ul>

                <div class="info-box">
                    <strong>Tip:</strong> The Report Generator supports keyboard shortcuts for faster workflow. <a class="link" href="references/keyboard_shortcuts.php">View all available shortcuts</a>.
                </div>
            </section>

            <!-- Search Bar section -->
            <section id="search-bar" class="article">
                <h2>Advanced Search Features</h2>
                <p>Argo Books includes a powerful search system with advanced operators to help you find exactly
                    what you need. The search bar works across all your transactions, making it easy to filter and
                    locate specific data.</p>

                <h3>Basic Search</h3>
                <p>Simply type a word or phrase to search across all fields in your transactions:</p>
                <ul class="examples-list">
                    <li><code>shirt</code> - Finds all transactions containing "shirt" in any field</li>
                    <li><code>cotton mills</code> - Finds transactions containing both "cotton" and "mills"</li>
                </ul>

                <p>Basic search tolerates small spelling errors and variations. This helps you find records even if
                    there are minor typos in your data.</p>

                <h3>Search Operators</h3>
                <div class="info-box">
                    <h4>Exact Phrase Matching with Double Quotes (" ")</h4>
                    <p>Double quotes search for an <strong>exact sequence of words in that precise order</strong>:</p>
                    <ul class="examples-list">
                        <li><code>"black t-shirt"</code> - Finds only transactions containing these exact words together
                            in this exact order
                        </li>
                        <li>Will NOT match "t-shirt black" or "black cotton t-shirt"</li>
                    </ul>
                    <br>

                    <h4>Required Terms with Plus Sign (+)</h4>
                    <p>The plus sign marks words that <strong>must be present somewhere</strong> in the transaction, but
                        not necessarily together or in any specific order:</p>
                    <ul class="examples-list">
                        <li><code>+shirt +cotton</code> - Finds transactions that contain both "shirt" AND "cotton"
                            anywhere in the record
                        </li>
                        <li>Would match "cotton shirt," "shirt made of cotton," or even records where "shirt" appears in
                            one field and "cotton" in another</li>
                    </ul>
                    <br>

                    <h4>Exclusion Terms with Minus Sign (-)</h4>
                    <p>Use the minus sign to exclude words from your search:</p>
                    <ul class="examples-list">
                        <li><code>shirt -white</code> - Finds transactions containing "shirt" but NOT "white"</li>
                        <li><code>"t-shirt" -black -white</code> - Finds t-shirts that are neither black nor white</li>
                    </ul>
                </div>

                <h3>AI-Powered Search (Paid Version)</h3>
                <p>The paid version includes AI-powered search capabilities that understand natural language queries.
                </p>
                <ol class="steps-list">
                    <li>Start your search with an exclamation mark (!)</li>
                    <li>Type your query in natural language</li>
                    <li>Press Enter to execute the search</li>
                </ol>

                <div class="info-box">
                    <h4>AI Search Examples</h4>
                    <ul class="examples-list">
                        <li><code>!show me expensive items purchased last month</code>
                        <li><code>!orders from germany for ball bearings over $25</code>
                        <li><code>!sales with shipping costs over $10 in april 2025</code>
                        </li>
                    </ul>
                </div>

                <div class="warning-box">
                    <strong>Internet Connection Required:</strong> AI search requires an internet connection and is only
                    available in the paid version. <a class="link" href="../upgrade/index.php">Upgrade here</a> to
                    access this feature.
                </div>
            </section>

            <!-- Accepted Countries section -->
            <section id="accepted-countries" class="article">
                <h2>Accepted Countries</h2>
                <p>When importing spreadsheet data, country names must match the system's accepted country list or use
                    recognized variants. The system accepts standard country names, ISO codes, and common alternative
                    names.</p>

                <h3>Common Examples</h3>
                <p>Popular countries with their accepted variants:</p>
                <ul>
                    <li><strong>United States:</strong> US, USA, U.S., America</li>
                    <li><strong>United Kingdom:</strong> UK, U.K., Great Britain, Britain, England</li>
                    <li><strong>Germany:</strong> DE, Deutschland</li>
                </ul>
                <br>
                <p><a class="link" href="references/accepted_countries.php">View complete list of all accepted country
                        names and variants</a></p>
            </section>

            <!-- Supported Currencies section -->
            <section id="supported-currencies" class="article">
                <h2>Supported Currencies</h2>
                <p>Argo Books supports 28 international currencies including USD, EUR, GBP, CAD, JPY, CNY, and
                    others. The system uses real-time exchange rates to convert between currencies accurately for
                    import, export, and display.</p>

                <p><a class="link" href="references/supported_currencies.php">View complete list of all 28 supported
                        currencies</a></p>

                <div class="warning-box">
                    <strong>Internet Connection Required:</strong> Currency conversion requires an internet connection
                    to fetch current and historical exchange rates. The rates are cached locally to minimize future
                    requests.
                </div>
            </section>

            <!-- Supported Languages section -->
            <section id="supported-languages" class="article">
                <h2>Supported Languages</h2>
                <p>Choose from 54 languages including English, Spanish, French, German, Chinese, Arabic, and many
                    others. The installer is currently only available in English, but you can change the application
                    language in "Settings > General" after installation.</p>

                <h3>Changing Your Language</h3>
                <ol class="steps-list">
                    <li>Go to "Settings > General" in the application</li>
                    <li>Find the "Language" dropdown menu</li>
                    <li>Select your preferred language from the list</li>
                </ol>

                <p><a class="link" href="references/supported_languages.php">View complete list of all 54 supported
                        languages</a></p>
            </section>

            <!-- Security section -->
            <!-- Encryption -->
            <section id="encryption" class="article">
                <h2>Encryption</h2>
                <p>Argo Books uses AES-256 encryption to protect your business data, the same standard used by
                    banks and military organizations.</p>

                <p> Encryption is automatic and requires no additional setup from users. It's enabled by
                    default, but can be disabled in the settings under the "Security" menu.</p>
            </section>

            <!-- Password Protection -->
            <section id="password" class="article">
                <h2>Password Protection</h2>
                <p>Secure access to your business data with robust password protection and Windows Hello integration.
                </p>

                <h3>Setting Up Password Protection</h3>
                <ol class="steps-list">
                    <li>Go to "Account > Settings > Security"</li>
                    <li>Click "Enable Password Protection"</li>
                    <li>Create a strong password</li>
                </ol>

                <h3>Setting Up Windows Hello (Paid Version)</h3>
                <ol class="steps-list">
                    <li>After setting up password protection, a "Enable Windows Hello" button will appear in the
                        Security settings</li>
                    <li>Click the button and Windows will prompt you to verify your identity</li>
                    <li>Once configured, you can use Windows Hello instead of your password for future logins</li>
                </ol>

                <div class="info-box">
                    <strong>Tip:</strong> Windows Hello options will only appear if your device has compatible hardware
                    (e.g., fingerprint reader or facial recognition camera) and Windows Hello is properly configured in
                    Windows Settings.
                </div>

                <div class="warning-box">
                    <strong>Important:</strong> Store your password securely in multiple locations. If you forget it,
                    your data cannot be recovered and will be lost forever!
                </div>
            </section>

            <!-- Backup section -->
            <section id="backups" class="article">
                <h2>Regular Backups</h2>
                <p>It's crucial to regularly back up your business data to prevent any potential loss. We recommend
                    making backups at least weekly, or after entering significant amounts of new data.</p>

                <h3>Creating a Backup</h3>
                <ol class="steps-list">
                    <li>Click "File > Export as..."</li>
                    <li>Select "ArgoSales (.zip)" from the drop-down menu</li>
                    <li>Choose a location for your backup</li>
                    <li>Store backups in a secure location, preferably on a different device or in the cloud</li>
                </ol>

                <div class="warning-box">
                    <strong>Important:</strong> Regular backups are your safeguard against data loss due to hardware
                    failure, accidents, or other unforeseen circumstances. Make it a habit to back up your data
                    frequently!
                </div>
            </section>

            <!-- Anonymous usage data -->
            <section id="anonymous-data" class="article">
                <h2>Anonymous Usage Data</h2>
                <p>Argo Books desktop application collects anonymous usage statistics and geo-location data to
                    help us improve the software by understanding how it's being used, identifying performance issues,
                    and prioritizing new features. This feature is enabled by default.</p>

                <h3>Managing Anonymous Data Collection</h3>
                <ol class="steps-list">
                    <li>Go to "Settings > General" in the desktop application</li>
                    <li>Find the "Anonymous Usage Data" setting</li>
                    <li>Toggle the switch to disable data collection if desired</li>
                </ol>

                <h3>What Data is Collected</h3>
                <p>Only anonymous usage statistics about the desktop application are collected, such as:</p>
                <ul>
                    <li>Export operations (type, duration, file size)</li>
                    <li>API usage (type, duration, tokens)</li>
                    <li>Error tracking (error category, error code, timestamp) - helps us identify and fix software
                        issues</li>
                    <li>Session data (session start/end, duration)</li>
                    <li>Geographic location (country, region, city, timezone)</li>
                    <li>Hashed IP addresses (one-way encrypted, cannot be reversed to identify you)</li>
                </ul>
                <br>
                <p><b>No personal information or business data is ever collected.</b></p>

                <h3>Exporting Your Anonymous Data</h3>
                <p>You can export and review all the anonymous data stored on your device:</p>
                <ol class="steps-list">
                    <li>Go to "Settings > General" in the desktop application</li>
                    <li>Next to the "Anonymous Usage Data" setting, click "Export Data"</li>
                    <li>Choose a location to save the JSON file</li>
                    <li>Open the file with any text editor to review its contents</li>
                </ol>
            </section>
        </main>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>