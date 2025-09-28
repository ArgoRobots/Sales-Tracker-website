<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Argo">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Complete Argo Sales Tracker documentation and user guide. Learn installation, features, tutorials for sales tracking, product management, analytics, Excel import/export, and security settings.">
    <meta name="keywords"
        content="argo sales tracker documentation, sales tracker tutorial, business software guide, how to use sales tracker, installation guide, user manual, sales tracking help, product management tutorial, analytics dashboard guide">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Documentation & User Guide - Argo Sales Tracker">
    <meta property="og:description"
        content="Complete Argo Sales Tracker documentation and user guide. Learn installation, features, tutorials for sales tracking, product management, analytics, Excel import/export, and security settings.">
    <meta property="og:url" content="https://argorobots.com/documentation/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Sales Tracker">
    <meta property="og:locale" content="en_CA">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Documentation & User Guide - Argo Sales Tracker">
    <meta name="twitter:description"
        content="Complete Argo Sales Tracker documentation and user guide. Learn installation, features, tutorials for sales tracking, product management, analytics, Excel import/export, and security settings.">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="CA-AB">
    <meta name="geo.placename" content="Calgary">
    <meta name="geo.position" content="51.0447;-114.0719">
    <meta name="ICBM" content="51.0447, -114.0719">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://argorobots.com/documentation/">

    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Documentation & User Guide - Argo Sales Tracker</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="main.js"></script>
    <script src="pdf-export.js"></script>
    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="../resources/scripts/ScrollToCenter.js"></script>
    <script src="../resources/scripts/levenshtein.js"></script>


    <link rel="stylesheet" href="style.css">
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
                        <li data-scroll-to="analytics" title="View analytics features">Analytics Dashboard</li>
                        <li data-scroll-to="receipts" title="Learn about receipt management">Receipt Management</li>
                        <li data-scroll-to="spreadsheet-import" title="Learn about importing spreadsheets">Spreadsheet
                            Import</li>
                        <li data-scroll-to="spreadsheet-export" title="Learn about exporting spreadsheets">Spreadsheet
                            Export</li>
                        <li data-scroll-to="search-bar" title="Learn about search features">Advanced Search</li>
                    </ul>
                </div>

                <!-- Reference section -->
                <div class="nav-section">
                    <h3>Reference</h3>
                    <ul class="nav-links">
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
                <h1>Argo Sales Tracker Documentation</h1>
                <p>Welcome to the Argo Sales Tracker documentation. This guide will help you get started and make the
                    most of our software.</p>

                <div class="contact-box">
                    <p><strong>Need Help?</strong> If you have questions or need assistance, please <a
                            href="../contact-us/index.php" class="link">contact us</a>. Our support team is here to help
                        you succeed with Argo Sales Tracker.</p>
                </div>
            </section>

            <!-- Getting Started section -->
            <!-- System Requirements Section -->
            <section id="system-requirements" class="article">
                <h2>System Requirements</h2>
                <ul>
                    <li>Windows 10 or newer</li>
                    <li>4GB RAM minimum</li>
                    <li>1GB free disk space</li>
                    <li>1280*720 screen resolution or higher</li>
                </ul>
            </section>

            <!-- Installation section -->
            <section id="installation" class="article">
                <h2>Installation Guide</h2>
                <ol class="steps-list">
                    <li>Download the installer from our website <a class="link" href="../index.php">here</a>
                    </li>
                    <li>Run the installer file (Argo Sales Tracker Installer.exe)</li>
                    <li>Follow the installation wizard instructions</li>
                    <li>Launch Argo Sales Tracker from your desktop or start menu</li>
                </ol>

                <div class="info-box">
                    <strong>Tip:</strong> Windows may display a security warning. This is normal for new applications.
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
                        title="Argo Sales Tracker Quick Start Tutorial" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div> -->
            </section>

            <!-- Free vs. Paid Version section -->
            <section id="version-comparison" class="article">
                <div class="description">
                    <h2>Free vs. Paid Version</h2>
                    <p>Argo Sales Tracker offers two versions to match your business needs. Start with our free version,
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

            <!-- Analytics Dashboard section -->
            <section id="analytics" class="article">
                <h2>Analytics Dashboard</h2>
                <p>The Analytics Dashboard provides comprehensive insights into your business performance through
                    interactive charts and visualizations organized into six specialized tabs. Each chart can be
                    exported to Excel or Google Sheets for further analysis.</p>

                <h3>Overview Tab</h3>
                <p>Get a high-level view of your business performance with key metrics at a glance:</p>
                <ul>
                    <li><strong>Sales vs Expenses:</strong> Compares your total sales revenue against expenses over
                        time, providing a clear view of business volume and cash flow.</li>
                    <li><strong>Total Profits:</strong> Shows your profit margins by calculating revenue minus expenses,
                        helping you track overall profitability trends.</li>
                    <li><strong>Total Transactions:</strong> Displays the number of purchase and sale transactions over
                        time, helping you understand business activity levels.</li>
                    <li><strong>Average Transaction Value:</strong> Tracks the average value of your purchases and
                        sales, helping identify trends in spending and revenue patterns.</li>
                </ul>

                <h3>Geographic Tab</h3>
                <p>Analyze the geographic distribution of your business operations:</p>
                <ul>
                    <li><strong>Countries of Origin:</strong> Shows where your purchased products come from, helping you
                        understand your supply chain geography.</li>
                    <li><strong>Countries of Destination:</strong> Displays where your products are being sold to,
                        helping track your market reach and customer base.</li>
                    <li><strong>Companies of Origin:</strong> Visualizes your supplier distribution, showing what
                        percentage of products come from different vendors and manufacturers.</li>
                </ul>

                <h3>Financial Tab</h3>
                <p>Deep dive into your financial data with detailed breakdowns:</p>
                <ul>
                    <li><strong>Total Revenue:</strong> Line or bar chart showing total sales revenue over time.</li>
                    <li><strong>Total Expenses:</strong> Line or bar chart displaying total purchase expenses over time.
                    </li>
                    <li><strong>Distribution of Revenue:</strong> Pie chart breaking down revenue by product categories,
                        showing which categories generate the most income.</li>
                    <li><strong>Distribution of Expenses:</strong> Pie chart showing expense breakdown by categories,
                        including shipping, taxes, fees, and product costs.</li>
                </ul>

                <h3>Performance Tab</h3>
                <p>Monitor your business growth and performance trends:</p>
                <ul>
                    <li><strong>Growth Rates:</strong> Shows percentage growth of both expenses and revenue over time,
                        helping identify seasonal patterns and business trends.</li>
                    <li><strong>Average Transaction Value:</strong> Tracks average purchase and sale values to
                        understand spending and pricing patterns.</li>
                    <li><strong>Total Transactions:</strong> Monitors transaction volume to gauge business activity
                        levels.</li>
                </ul>

                <h3>Operational Tab</h3>
                <p>Analyze operational aspects of your business:</p>
                <ul>
                    <li><strong>Transactions by Accountant:</strong> Shows the distribution of transactions handled by
                        each accountant, helping monitor workload distribution and performance.</li>
                    <li><strong>Average Shipping Costs:</strong> Tracks shipping expenses over time for both purchases
                        and sales, with an option to include or exclude free shipping transactions.</li>
                </ul>

                <h3>Returns Tab</h3>
                <p>Comprehensive analysis of product returns and their impact:</p>
                <ul>
                    <li><strong>Returns Over Time:</strong> Shows the number of returned transactions over time, helping
                        identify return patterns and trends.</li>
                    <li><strong>Return Reasons:</strong> Pie chart displaying the most common reasons for returns,
                        helping you address quality or service issues.</li>
                    <li><strong>Return Financial Impact:</strong> Shows the monetary value of returns over time, helping
                        quantify the financial impact of returns on your business.</li>
                    <li><strong>Returns by Category:</strong> Breaks down returns by product category, helping identify
                        which product types have higher return rates.</li>
                    <li><strong>Returns by Product:</strong> Shows which specific products are returned most frequently,
                        helping identify problem items.</li>
                    <li><strong>Purchase vs Sale Returns:</strong> Compares the volume of purchase returns versus sale
                        returns, providing insight into different types of return scenarios.</li>
                </ul>

                <div class="info-box">
                    <strong>Filtering:</strong> You can customize the time range for all charts using the date range
                    selector, or filter results using the search bar. This allows you to focus on specific time periods
                    or transaction types.
                </div>

                <div class="info-box">
                    <strong>Export Options:</strong> Right-click any chart to access additional options including
                    exporting to Microsoft Excel or Google Sheets. The exported files include both the chart
                    visualization and the underlying data for further analysis.
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
                <p>Import your existing business data from Excel spreadsheets into Argo Sales Tracker. The import system
                    supports multiple currencies and can automatically detect the currency used in your data.</p>

                <h3>Preparing Your Spreadsheet</h3>
                <p>Download our <a class="link" href="../resources/downloads/Argo Sales Tracker format.xlsx">template
                        spreadsheet</a> to see the exact format required. Your Excel file can include any combination of
                    the following sheets (not all are required):</p>

                <div class="info-box">
                   <ul>
  <li><strong>Accountants:</strong> Name</li>
  <li><strong>Companies:</strong> Company Name</li>
  <li><strong>Purchase products:</strong> Product Name, Category (optional)</li>
  <li><strong>Sale products:</strong> Product Name, Category (optional)</li>
  <li><strong>Purchases:</strong> Date, Product, Quantity, Cost, Returned (Yes/No), Receipt (optional)</li>
  <li><strong>Sales:</strong> Date, Product, Quantity, Price, Returned (Yes/No), Receipt (optional)</li>
</ul>
                </div>

           <h3>Formatting Requirements</h3>
<div class="info-box">
  <p>Download our <a class="link" href="../resources/downloads/Argo Sales Tracker format.xlsx">
    template spreadsheet</a> and follow the exact format shown.</p>

  <p>Key points:</p>
  <ul>
    <li><strong>Sheet names:</strong> Use "Accountants", "Companies", "Purchase products", 
        "Sale products", "Purchases", "Sales" (case doesn't matter)</li>
    <li><strong>Date format:</strong> YYYY-MM-DD (e.g., 2025-01-15)</li>
    <li><strong>Returned items:</strong> Purchases and Sales sheets must include a "Returned" column (Yes/No)</li>
    <li><strong>Everything else:</strong> Follow the template format exactly</li>
  </ul>
</div>

<h4>Required Columns</h4>
<ul>
  <li><strong>Accountants:</strong> Name</li>
  <li><strong>Companies:</strong> Company Name</li>
  <li><strong>Purchase products:</strong> Product Name, Category (optional)</li>
  <li><strong>Sale products:</strong> Product Name, Category (optional)</li>
  <li><strong>Purchases:</strong> Date, Product, Quantity, Cost, Returned (Yes/No), Receipt (optional)</li>
  <li><strong>Sales:</strong> Date, Product, Quantity, Price, Returned (Yes/No), Receipt (optional)</li>
</ul>


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
                <p>The import system is smart about handling missing data:</p>
                <ul>
                    <li><strong>Companies:</strong> If a transaction references a company that doesn't exist, it will be
                        automatically created</li>
                    <li><strong>Categories:</strong> If a product references a category that doesn't exist, it will be
                        automatically created</li>
                </ul>

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

                <h3>Error Handling</h3>
                <p>If the import encounters errors (like invalid monetary values), you'll be given options to:</p>
                <ul>
                    <li><strong>Skip:</strong> Skip the problematic transaction and continue importing others</li>
                    <li><strong>Retry:</strong> Correct the value in the spreadsheet and try again</li>
                    <li><strong>Cancel:</strong> Stop the import process entirely</li>
                </ul>

                <div class="info-box">
                    <strong>Rollback Protection:</strong> If you cancel an import or encounter serious errors, all
                    changes are automatically rolled back, leaving your data unchanged.
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
                    <li>Choose a destination folder</li>
                    <li>All receipts will be organized in a dated folder structure</li>
                </ol>

                <h3>Chart Export</h3>
                <p>Charts from the Analytics Dashboard can also be exported to Excel with full data:</p>
                <ol class="steps-list">
                    <li>Right-click any chart in the Analytics Dashboard</li>
                    <li>Select "Export to Microsoft Excel"</li>
                    <li>Choose a save location</li>
                    <li>The exported file will include both the chart and the underlying data</li>
                </ol>

                <div class="info-box">
                    <strong>File Naming:</strong> If a file with the same name already exists, the system automatically
                    adds a number to create a unique filename.
                </div>
            </section>

            <!-- Search Bar section -->
            <section id="search-bar" class="article">
                <h2>Advanced Search Features</h2>
                <p>Argo Sales Tracker includes a powerful search system with advanced operators to help you find exactly
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
                    AI search requires an internet connection and is only available in the paid
                    version. <a class="link" href="../upgrade/index.php">Upgrade here</a> to access this feature.
                </div>
            </section>

            <!-- Supported Currencies section -->
            <section id="supported-currencies" class="article">
                <h2>Supported Currencies</h2>
                <p>Argo Sales Tracker supports 28 international currencies for import, export, and display. The system
                    uses real-time exchange rates to convert between currencies accurately.</p>

                <h3>Complete Currency List</h3>
                <div class="info-box">
                    <table class="comparison-table">
                        <tr>
                            <th>Currency Code</th>
                            <th>Currency Name</th>
                            <th>Symbol</th>
                            <th>Country/Region</th>
                        </tr>
                        <tr>
                            <td>ALL</td>
                            <td>Albanian Lek</td>
                            <td>L</td>
                            <td>Albania</td>
                        </tr>
                        <tr>
                            <td>AUD</td>
                            <td>Australian Dollar</td>
                            <td>$</td>
                            <td>Australia</td>
                        </tr>
                        <tr>
                            <td>BAM</td>
                            <td>Bosnia and Herzegovina Convertible Mark</td>
                            <td>KM</td>
                            <td>Bosnia and Herzegovina</td>
                        </tr>
                        <tr>
                            <td>BGN</td>
                            <td>Bulgarian Lev</td>
                            <td>лв</td>
                            <td>Bulgaria</td>
                        </tr>
                        <tr>
                            <td>BRL</td>
                            <td>Brazilian Real</td>
                            <td>R$</td>
                            <td>Brazil</td>
                        </tr>
                        <tr>
                            <td>BYN</td>
                            <td>Belarusian Ruble</td>
                            <td>Br</td>
                            <td>Belarus</td>
                        </tr>
                        <tr>
                            <td>CAD</td>
                            <td>Canadian Dollar</td>
                            <td>$</td>
                            <td>Canada</td>
                        </tr>
                        <tr>
                            <td>CHF</td>
                            <td>Swiss Franc</td>
                            <td>CHF</td>
                            <td>Switzerland</td>
                        </tr>
                        <tr>
                            <td>CNY</td>
                            <td>Chinese Yuan Renminbi</td>
                            <td>¥</td>
                            <td>China</td>
                        </tr>
                        <tr>
                            <td>CZK</td>
                            <td>Czech Koruna</td>
                            <td>Kč</td>
                            <td>Czech Republic</td>
                        </tr>
                        <tr>
                            <td>DKK</td>
                            <td>Danish Krone</td>
                            <td>kr</td>
                            <td>Denmark</td>
                        </tr>
                        <tr>
                            <td>EUR</td>
                            <td>Euro</td>
                            <td>€</td>
                            <td>European Union</td>
                        </tr>
                        <tr>
                            <td>GBP</td>
                            <td>British Pound</td>
                            <td>£</td>
                            <td>United Kingdom</td>
                        </tr>
                        <tr>
                            <td>HUF</td>
                            <td>Hungarian Forint</td>
                            <td>Ft</td>
                            <td>Hungary</td>
                        </tr>
                        <tr>
                            <td>ISK</td>
                            <td>Icelandic Króna</td>
                            <td>kr</td>
                            <td>Iceland</td>
                        </tr>
                        <tr>
                            <td>JPY</td>
                            <td>Japanese Yen</td>
                            <td>¥</td>
                            <td>Japan</td>
                        </tr>
                        <tr>
                            <td>KRW</td>
                            <td>South Korean Won</td>
                            <td>₩</td>
                            <td>South Korea</td>
                        </tr>
                        <tr>
                            <td>MKD</td>
                            <td>Macedonian Denar</td>
                            <td>ден</td>
                            <td>North Macedonia</td>
                        </tr>
                        <tr>
                            <td>NOK</td>
                            <td>Norwegian Krone</td>
                            <td>kr</td>
                            <td>Norway</td>
                        </tr>
                        <tr>
                            <td>PLN</td>
                            <td>Polish Złoty</td>
                            <td>zł</td>
                            <td>Poland</td>
                        </tr>
                        <tr>
                            <td>RON</td>
                            <td>Romanian Leu</td>
                            <td>lei</td>
                            <td>Romania</td>
                        </tr>
                        <tr>
                            <td>RSD</td>
                            <td>Serbian Dinar</td>
                            <td>дин</td>
                            <td>Serbia</td>
                        </tr>
                        <tr>
                            <td>RUB</td>
                            <td>Russian Ruble</td>
                            <td>₽</td>
                            <td>Russia</td>
                        </tr>
                        <tr>
                            <td>SEK</td>
                            <td>Swedish Krona</td>
                            <td>kr</td>
                            <td>Sweden</td>
                        </tr>
                        <tr>
                            <td>TRY</td>
                            <td>Turkish Lira</td>
                            <td>₺</td>
                            <td>Turkey</td>
                        </tr>
                        <tr>
                            <td>TWD</td>
                            <td>Taiwan Dollar</td>
                            <td>NT$</td>
                            <td>Taiwan</td>
                        </tr>
                        <tr>
                            <td>UAH</td>
                            <td>Ukrainian Hryvnia</td>
                            <td>₴</td>
                            <td>Ukraine</td>
                        </tr>
                        <tr>
                            <td>USD</td>
                            <td>United States Dollar</td>
                            <td>$</td>
                            <td>United States</td>
                        </tr>
                    </table>
                </div>

                <h3>How Currency Conversion Works</h3>
                <p>The system uses the OpenExchangeRates API to get accurate, historical exchange rates:</p>
                <ul>
                    <li><strong>Real-time rates:</strong> Exchange rates are fetched for the exact date of each
                        transaction</li>
                    <li><strong>Caching:</strong> Exchange rates are cached locally to minimize API calls and improve
                        performance</li>
                    <li><strong>Internet required:</strong> An internet connection is needed when importing or exporting
                        data with currency conversion</li>
                    <li><strong>Automatic detection:</strong> During import, the system can often detect the currency
                        automatically from symbols and formatting</li>
                </ul>

                <div class="warning-box">
                    <strong>Internet Connection Required:</strong> Currency conversion requires an internet connection
                    to fetch current and historical exchange rates. The rates are cached locally to minimize future
                    requests.
                </div>

                <h3>Setting Your Default Currency</h3>
                <p>You can set your preferred default currency during initial setup or change it later in the settings.
                    This currency will be used for:</p>
                <ul>
                    <li>Displaying transaction amounts in the main interface</li>
                    <li>Analytics and chart calculations</li>
                    <li>Default export currency (though you can choose a different one during export)</li>
                </ul>
            </section>

            <!-- Supported Languages section -->
            <section id="supported-languages" class="article">
                <h2>Supported Languages</h2>
                <p>Argo Sales Tracker supports 54 languages to help users worldwide work in their preferred language.
                    The installer is currently only available in English, but you can change the application language in
                    the settings after installation.</p>

                <h3>Changing Your Language</h3>
                <ol class="steps-list">
                    <li>Go to "Settings > General" in the application</li>
                    <li>Find the "Language" dropdown menu</li>
                    <li>Select your preferred language from the list</li>
                </ol>

                <h3>Complete Language List</h3>
                <div class="info-box">
                    <table class="comparison-table">
                        <tr>
                            <th>Language</th>
                            <th>Region/Countries</th>
                        </tr>
                        <tr>
                            <td>Albanian</td>
                            <td>Albania</td>
                        </tr>
                        <tr>
                            <td>Arabic</td>
                            <td>Middle East, North Africa</td>
                        </tr>
                        <tr>
                            <td>Basque</td>
                            <td>Basque Country</td>
                        </tr>
                        <tr>
                            <td>Belarusian</td>
                            <td>Belarus</td>
                        </tr>
                        <tr>
                            <td>Bengali</td>
                            <td>Bangladesh, India</td>
                        </tr>
                        <tr>
                            <td>Bosnian</td>
                            <td>Bosnia and Herzegovina</td>
                        </tr>
                        <tr>
                            <td>Bulgarian</td>
                            <td>Bulgaria</td>
                        </tr>
                        <tr>
                            <td>Catalan</td>
                            <td>Catalonia</td>
                        </tr>
                        <tr>
                            <td>Chinese (Simplified)</td>
                            <td>Mainland China</td>
                        </tr>
                        <tr>
                            <td>Chinese (Traditional)</td>
                            <td>Taiwan, Hong Kong</td>
                        </tr>
                        <tr>
                            <td>Croatian</td>
                            <td>Croatia</td>
                        </tr>
                        <tr>
                            <td>Czech</td>
                            <td>Czech Republic</td>
                        </tr>
                        <tr>
                            <td>Danish</td>
                            <td>Denmark</td>
                        </tr>
                        <tr>
                            <td>Dutch</td>
                            <td>Netherlands, Belgium</td>
                        </tr>
                        <tr>
                            <td>English</td>
                            <td>North America, UK, Australia</td>
                        </tr>
                        <tr>
                            <td>Estonian</td>
                            <td>Estonia</td>
                        </tr>
                        <tr>
                            <td>Filipino</td>
                            <td>Philippines</td>
                        </tr>
                        <tr>
                            <td>Finnish</td>
                            <td>Finland</td>
                        </tr>
                        <tr>
                            <td>French</td>
                            <td>France, Canada, Belgium</td>
                        </tr>
                        <tr>
                            <td>Galician</td>
                            <td>Galicia</td>
                        </tr>
                        <tr>
                            <td>German</td>
                            <td>Germany, Austria</td>
                        </tr>
                        <tr>
                            <td>Greek</td>
                            <td>Greece</td>
                        </tr>
                        <tr>
                            <td>Hebrew</td>
                            <td>Israel</td>
                        </tr>
                        <tr>
                            <td>Hindi</td>
                            <td>India</td>
                        </tr>
                        <tr>
                            <td>Hungarian</td>
                            <td>Hungary</td>
                        </tr>
                        <tr>
                            <td>Icelandic</td>
                            <td>Iceland</td>
                        </tr>
                        <tr>
                            <td>Indonesian</td>
                            <td>Indonesia</td>
                        </tr>
                        <tr>
                            <td>Irish</td>
                            <td>Ireland</td>
                        </tr>
                        <tr>
                            <td>Italian</td>
                            <td>Italy</td>
                        </tr>
                        <tr>
                            <td>Japanese</td>
                            <td>Japan</td>
                        </tr>
                        <tr>
                            <td>Korean</td>
                            <td>South Korea</td>
                        </tr>
                        <tr>
                            <td>Latvian</td>
                            <td>Latvia</td>
                        </tr>
                        <tr>
                            <td>Lithuanian</td>
                            <td>Lithuania</td>
                        </tr>
                        <tr>
                            <td>Luxembourgish</td>
                            <td>Luxembourg</td>
                        </tr>
                        <tr>
                            <td>Macedonian</td>
                            <td>North Macedonia</td>
                        </tr>
                        <tr>
                            <td>Malay</td>
                            <td>Malaysia, Brunei</td>
                        </tr>
                        <tr>
                            <td>Maltese</td>
                            <td>Malta</td>
                        </tr>
                        <tr>
                            <td>Norwegian</td>
                            <td>Norway</td>
                        </tr>
                        <tr>
                            <td>Persian</td>
                            <td>Iran, Afghanistan</td>
                        </tr>
                        <tr>
                            <td>Polish</td>
                            <td>Poland</td>
                        </tr>
                        <tr>
                            <td>Portuguese</td>
                            <td>Portugal, Brazil</td>
                        </tr>
                        <tr>
                            <td>Romanian</td>
                            <td>Romania</td>
                        </tr>
                        <tr>
                            <td>Russian</td>
                            <td>Russia, Eastern Europe</td>
                        </tr>
                        <tr>
                            <td>Serbian</td>
                            <td>Serbia</td>
                        </tr>
                        <tr>
                            <td>Slovak</td>
                            <td>Slovakia</td>
                        </tr>
                        <tr>
                            <td>Slovenian</td>
                            <td>Slovenia</td>
                        </tr>
                        <tr>
                            <td>Spanish</td>
                            <td>Spain, Latin America</td>
                        </tr>
                        <tr>
                            <td>Swahili</td>
                            <td>East Africa</td>
                        </tr>
                        <tr>
                            <td>Swedish</td>
                            <td>Sweden</td>
                        </tr>
                        <tr>
                            <td>Thai</td>
                            <td>Thailand</td>
                        </tr>
                        <tr>
                            <td>Turkish</td>
                            <td>Turkey</td>
                        </tr>
                        <tr>
                            <td>Ukrainian</td>
                            <td>Ukraine</td>
                        </tr>
                        <tr>
                            <td>Urdu</td>
                            <td>Pakistan, India</td>
                        </tr>
                        <tr>
                            <td>Vietnamese</td>
                            <td>Vietnam</td>
                        </tr>
                    </table>
                </div>

                <div class="info-box">
                    <strong>Installation Note:</strong> The installer itself is currently only available in English, but
                    once installed, you can switch to any of these 54 supported languages through the application
                    settings.
                </div>
            </section>

            <!-- Security section -->
            <!-- Encryption -->
            <section id="encryption" class="article">
                <h2>Encryption</h2>
                <p>Argo Sales Tracker uses AES-256 encryption to protect your business data, the same standard used by
                    banks and military organizations.</p>

                <p> Encryption is automatic and requires no additional setup from users. It's enabled by
                    default, but can be disabled in the settings under the "Security" menu. </p>
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
                <p>Argo Sales Tracker desktop application collects anonymous usage statistics and geo-location data to
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