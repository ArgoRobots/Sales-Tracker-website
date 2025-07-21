<?php
session_start();
require_once 'community/users/user_functions.php';
require_once 'statistics.php';

track_page_view($_SERVER['REQUEST_URI']);

// Check for remember me cookie and auto-login user if valid
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    check_remember_me();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Argo">
    <link rel="shortcut icon" type="image/x-icon" href="images/argo-logo/A-logo.ico">
    <title>Argo Sales Tracker</title>

    <script src="resources/scripts/jquery-3.6.0.js"></script>
    <script src="resources/scripts/main.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="resources/styles/custom-colors.css">
    <link rel="stylesheet" href="resources/styles/button.css">
    <link rel="stylesheet" href="resources/header/style.css">
    <link rel="stylesheet" href="resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <section class="section gradient-bg">
        <div class="container first-container">
            <h1 class="title">Argo Sales Tracker</h1>

            <div class="hero-grid">
                <div class="hero-content">
                    <h2 class="hero-title">Transform your business with smart sales tracking</h2>
                    <p class="hero-subtitle">Track purchases and sales, and grow your business with our powerful
                        yet simple-to-use software.</p>
                    <div class="hero-buttons">
                        <a href="download.php" class="btn btn-white">Download for free</a>
                        <a href="upgrade/index.html" class="btn btn-white">Buy the full version</a>
                    </div>
                    <p class="system-req">*Windows only</p>
                </div>
                <div>
                    <img src="images/main.png" alt="Dashboard Preview">
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container second-container">
            <h2 class="section-title">Everything You Need</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="image-container">
                        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                            <!-- Dashboard background -->
                            <rect x="10" y="20" width="100" height="80" rx="8" fill="#ffffff" stroke="#e5e7eb" stroke-width="2" />

                            <!-- Bar chart -->
                            <rect x="20" y="70" width="8" height="20" rx="2" fill="#3b82f6" />
                            <rect x="32" y="60" width="8" height="30" rx="2" fill="#10b981" />
                            <rect x="44" y="50" width="8" height="40" rx="2" fill="#f59e0b" />
                            <rect x="56" y="65" width="8" height="25" rx="2" fill="#ef4444" />

                            <!-- Line chart -->
                            <path d="M75 80 L85 70 L95 65 L105 60" stroke="#8b5cf6" stroke-width="3" fill="none" stroke-linecap="round" />
                            <circle cx="75" cy="80" r="3" fill="#8b5cf6" />
                            <circle cx="85" cy="70" r="3" fill="#8b5cf6" />
                            <circle cx="95" cy="65" r="3" fill="#8b5cf6" />
                            <circle cx="105" cy="60" r="3" fill="#8b5cf6" />

                            <!-- Header icons -->
                            <circle cx="25" cy="35" r="4" fill="#22c55e" />
                            <rect x="35" y="32" width="20" height="2" rx="1" fill="#6b7280" />
                            <rect x="35" y="37" width="15" height="2" rx="1" fill="#9ca3af" />

                            <circle cx="85" cy="35" r="4" fill="#ef4444" />
                            <rect x="95" y="32" width="15" height="2" rx="1" fill="#6b7280" />
                            <rect x="95" y="37" width="10" height="2" rx="1" fill="#9ca3af" />

                            <!-- Dollar signs -->
                            <text x="25" y="38" text-anchor="middle" fill="white" font-size="6" font-weight="bold">$</text>
                            <text x="85" y="38" text-anchor="middle" fill="white" font-size="6" font-weight="bold">$</text>
                        </svg>
                    </div>
                    <div class="text-container">
                        <h3>Purchases and Sales Tracking</h3>
                        <p>Keep track of all your purchases and sales with detailed records and receipts.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="image-container">
                        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                            <!-- Form background -->
                            <rect x="20" y="15" width="80" height="90" rx="8" fill="#ffffff" stroke="#e5e7eb" stroke-width="2" />

                            <!-- Form header -->
                            <rect x="20" y="15" width="80" height="20" rx="8" fill="#1e3a8a" />
                            <rect x="20" y="27" width="80" height="8" fill="#1e3a8a" />

                            <!-- Header text -->
                            <rect x="30" y="22" width="30" height="3" rx="1.5" fill="white" />

                            <!-- Form fields -->
                            <rect x="30" y="45" width="60" height="8" rx="2" fill="#f9fafb" stroke="#d1d5db" />
                            <rect x="30" y="58" width="60" height="8" rx="2" fill="#f9fafb" stroke="#d1d5db" />
                            <rect x="30" y="71" width="25" height="8" rx="2" fill="#f9fafb" stroke="#d1d5db" />
                            <rect x="60" y="71" width="30" height="8" rx="2" fill="#f9fafb" stroke="#d1d5db" />
                            <rect x="30" y="84" width="60" height="8" rx="2" fill="#f9fafb" stroke="#d1d5db" />

                            <!-- Add button -->
                            <circle cx="60" cy="25" r="15" fill="#22c55e" stroke="white" stroke-width="2" />
                            <path d="M60 18 L60 32 M53 25 L67 25" stroke="white" stroke-width="3" stroke-linecap="round" />

                            <!-- Form labels -->
                            <rect x="30" y="40" width="20" height="2" rx="1" fill="#6b7280" />
                            <rect x="30" y="53" width="25" height="2" rx="1" fill="#6b7280" />
                            <rect x="30" y="66" width="15" height="2" rx="1" fill="#6b7280" />
                            <rect x="60" y="66" width="20" height="2" rx="1" fill="#6b7280" />
                        </svg>
                    </div>
                    <div class="text-container">
                        <h3>Easily add new transactions</h3>
                        <p>Add new purchases and sales with fields for quantities, shipping costs, taxes, fees and more.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="image-container">
                        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                            <!-- Grid background -->
                            <rect x="15" y="20" width="90" height="80" rx="8" fill="#ffffff" stroke="#e5e7eb" stroke-width="2" />

                            <!-- Product grid items -->
                            <rect x="25" y="30" width="20" height="20" rx="4" fill="#dbeafe" stroke="#3b82f6" />
                            <rect x="50" y="30" width="20" height="20" rx="4" fill="#dcfce7" stroke="#22c55e" />
                            <rect x="75" y="30" width="20" height="20" rx="4" fill="#fef3c7" stroke="#f59e0b" />

                            <rect x="25" y="55" width="20" height="20" rx="4" fill="#fce7f3" stroke="#ec4899" />
                            <rect x="50" y="55" width="20" height="20" rx="4" fill="#f3e8ff" stroke="#8b5cf6" />
                            <rect x="75" y="55" width="20" height="20" rx="4" fill="#fed7d7" stroke="#ef4444" />

                            <!-- Product icons -->
                            <rect x="30" y="35" width="10" height="6" rx="1" fill="#3b82f6" />
                            <rect x="31" y="37" width="8" height="2" rx="0.5" fill="white" />

                            <circle cx="60" cy="40" r="5" fill="#22c55e" />
                            <rect x="58" y="38" width="4" height="4" rx="1" fill="white" />

                            <polygon points="85,35 80,45 90,45" fill="#f59e0b" />
                            <circle cx="85" cy="42" r="1.5" fill="white" />

                            <rect x="30" y="60" width="10" height="10" rx="2" fill="#ec4899" />
                            <circle cx="35" cy="65" r="2" fill="white" />

                            <circle cx="60" cy="65" r="5" fill="#8b5cf6" />
                            <path d="M58 65 L60 67 L62 63" stroke="white" stroke-width="1" fill="none" />

                            <rect x="80" y="60" width="10" height="10" rx="1" fill="#ef4444" />
                            <path d="M83 63 L87 67 M87 63 L83 67" stroke="white" stroke-width="1.5" />

                            <!-- Category tags -->
                            <rect x="25" y="82" width="70" height="4" rx="2" fill="#f3f4f6" />
                            <rect x="25" y="89" width="45" height="4" rx="2" fill="#f3f4f6" />

                            <!-- Category indicators -->
                            <circle cx="28" cy="84" r="1.5" fill="#3b82f6" />
                            <circle cx="55" cy="84" r="1.5" fill="#22c55e" />
                            <circle cx="82" cy="84" r="1.5" fill="#f59e0b" />
                        </svg>
                    </div>
                    <div class="text-container">
                        <h3>Product Management</h3>
                        <p>Organize products with custom categories and track information like country/company of
                            origin.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-gray">
        <div class="container">
            <h2 class="section-title">Discover the Features</h2>

            <!-- Analytics dashboard -->
            <div class="showcase-item">
                <img src="images/analytics.png" alt="Analytics dashboard">
                <div class="showcase-content">
                    <h3 class="showcase-title">Powerful Analytics Dashboard</h3>
                    <p class="showcase-description">Track your business performance with interactive charts and detailed
                        insights. Monitor sales trends, expense distribution, and more in real-time.</p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Interactive data visualization
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Customizable reporting periods
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Export charts to Microsoft Excel and Google Sheets
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Receipt manager -->
            <div class="showcase-item">
                <div class="showcase-content">
                    <h3 class="showcase-title">Manage your receipts</h3>
                    <p class="showcase-description">Manage and export receipts with an easy-to-use interface.</p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Flexible date range filtering
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Bulk selection and export capabilities
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Purchase and sale receipt separation
                        </li>
                    </ul>
                </div>

                <img src="images/export-receipts.png" alt="Receipt manager">
            </div>

            <!-- Spreadsheet manager -->
            <div class="showcase-item">
                <img src="images/import-spreadsheet.png" alt="Spreadsheet manager">

                <div class="showcase-content">
                    <h3 class="showcase-title">Import and export spreadsheets</h3>
                    <p class="showcase-description">Seamlessly manage and export your business data to Excel
                        spreadsheets. Perfect for accounting and taxes. Import existing
                        spreadsheets to quickly populate your database.
                    </p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Complete Data Export - Export all your data including products and transaction records in
                            organized worksheets
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Compatibility - Works with all major spreadsheet software including Microsoft Excel, Google
                            Sheets, and LibreOffice
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Purchases and sales separation - Keep your purchase and sales records organized in
                            separate worksheets for better clarity
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Compare spreadsheets to Argo Sales Tracker -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Why Choose Argo Sales Tracker Over Spreadsheets?</h2>

            <div class="comparison-container">
                <div class="comparison-header">
                    <div class="comparison-title">Spreadsheets</div>
                    <div class="comparison-title">Argo Sales Tracker</div>
                </div>

                <!-- Desktop layout -->
                <div class="comparison-desktop">
                    <div class="comparison-row">
                        <div class="comparison-item">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Error-Prone Data Entry</h3>
                                <p>Manual data entry in spreadsheets is prone to human error, with formula mistakes,
                                    inconsistent formatting, and duplicate entries.</p>
                            </div>
                        </div>

                        <div class="comparison-item">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Automated Error Prevention</h3>
                                <p>Built-in validation prevents common errors, with structured data entry forms
                                    and automated calculations that eliminate formula errors.</p>
                            </div>
                        </div>
                    </div>

                    <div class="comparison-row">
                        <div class="comparison-item">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Limited Data Organization</h3>
                                <p>Organizing receipts and attachments requires separate folders and manual linking,
                                    making it difficult to maintain a clear audit trail.</p>
                            </div>
                        </div>

                        <div class="comparison-item">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Integrated Receipt Management</h3>
                                <p>Directly attach and manage receipts within each transaction, with bulk export
                                    capabilities and organized storage that creates a clear, auditable record.</p>
                            </div>
                        </div>
                    </div>

                    <div class="comparison-row">
                        <div class="comparison-item">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Basic Visualization</h3>
                                <p>Creating and maintaining charts requires manual updates and technical knowledge,
                                    often resulting in outdated or limited insights.</p>
                            </div>
                        </div>

                        <div class="comparison-item">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Advanced Analytics</h3>
                                <p>Real-time interactive dashboards automatically update as you add data, with
                                    sophisticated visualizations that provide actionable insights without any technical
                                    skills required.</p>
                            </div>
                        </div>
                    </div>

                    <div class="comparison-row">
                        <div class="comparison-item">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Security Concerns</h3>
                                <p>Spreadsheets typically lack advanced security features, with limited password
                                    protection and no encryption for sensitive business data.</p>
                            </div>
                        </div>

                        <div class="comparison-item">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Military-Grade Security</h3>
                                <p>AES-256 encryption keeps your data safe, with robust password protection and Windows
                                    Hello integration in the paid version for biometric authentication.</p>
                            </div>
                        </div>
                    </div>

                    <div class="comparison-row">
                        <div class="comparison-item">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Clunky Interface</h3>
                                <p>Grid-based interfaces with small cells and hidden menus make data entry tedious
                                    and analysis difficult for non-technical users.</p>
                            </div>
                        </div>

                        <div class="comparison-item">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="comparison-content">
                                <h3>Modern, Intuitive UI</h3>
                                <p>Clean, purpose-built interface designed for sales tracking, with intuitive navigation
                                    and context-specific tools that streamline and automate your workflow.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile layout -->
                <div class="comparison-mobile">
                    <!-- Data Entry -->
                    <div class="mobile-comparison-section">
                        <div class="mobile-section-header">Data Entry & Validation</div>

                        <div class="mobile-section-spreadsheet">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Spreadsheets: Error-Prone Data Entry</h3>
                                <p>Manual data entry in spreadsheets is prone to human error, with formula mistakes,
                                    inconsistent formatting, and duplicate entries.</p>
                            </div>
                        </div>

                        <div class="mobile-section-argo">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Argo: Automated Error Prevention</h3>
                                <p>Built-in validation prevents common errors, with structured data entry forms
                                    and automated calculations that eliminate formula errors.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Management -->
                    <div class="mobile-comparison-section">
                        <div class="mobile-section-header">Document Organization</div>

                        <div class="mobile-section-spreadsheet">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Spreadsheets: Limited Data Organization</h3>
                                <p>Organizing receipts and attachments requires separate folders and manual linking,
                                    making it difficult to maintain a clear audit trail.</p>
                            </div>
                        </div>

                        <div class="mobile-section-argo">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Argo: Integrated Receipt Management</h3>
                                <p>Directly attach and manage receipts within each transaction, with bulk export
                                    capabilities and organized storage that creates a clear, auditable record.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div class="mobile-comparison-section">
                        <div class="mobile-section-header">Data Visualization</div>

                        <div class="mobile-section-spreadsheet">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Spreadsheets: Basic Visualization</h3>
                                <p>Creating and maintaining charts requires manual updates and technical knowledge,
                                    often resulting in outdated or limited insights.</p>
                            </div>
                        </div>

                        <div class="mobile-section-argo">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Argo: Advanced Analytics</h3>
                                <p>Real-time interactive dashboards automatically update as you add data, with
                                    sophisticated visualizations that provide actionable insights without any technical
                                    skills required.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="mobile-comparison-section">
                        <div class="mobile-section-header">Data Security</div>

                        <div class="mobile-section-spreadsheet">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Spreadsheets: Security Concerns</h3>
                                <p>Spreadsheets typically lack advanced security features, with limited password
                                    protection and no encryption for sensitive business data.</p>
                            </div>
                        </div>

                        <div class="mobile-section-argo">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Argo: Military-Grade Security</h3>
                                <p>AES-256 encryption keeps your data safe, with robust password protection and Windows
                                    Hello integration in the paid version for biometric authentication.</p>
                            </div>
                        </div>
                    </div>

                    <!-- UI -->
                    <div class="mobile-comparison-section">
                        <div class="mobile-section-header">User Experience</div>

                        <div class="mobile-section-spreadsheet">
                            <svg class="error-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Spreadsheets: Clunky Interface</h3>
                                <p>Grid-based interfaces with small cells and hidden menus make data entry tedious
                                    and analysis difficult for non-technical users.</p>
                            </div>
                        </div>

                        <div class="mobile-section-argo">
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <div class="mobile-content">
                                <h3>Argo: Modern, Intuitive UI</h3>
                                <p>Clean, purpose-built interface designed for sales tracking, with intuitive navigation
                                    and context-specific tools that streamline your workflow.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="comparison-cta">
                <p>Stop struggling with spreadsheets. Get started with Argo Sales Tracker today.</p>
                <div class="cta-buttons">
                    <a href="download.php" class="btn btn-blue">Download for free</a>
                    <a href="upgrade/index.html" class="btn btn-blue">Buy the full version</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section security-section  bg-gray">
        <div class="container">
            <img class="security-image" src="images/security.svg" alt="Security Features">
            <div class="showcase-content">
                <h3 class="showcase-title">Military-Grade Security</h3>
                <p class="showcase-description">Keep your business data protected with military-grade AES-256 encryption
                    and modern authentication methods.</p>
                <ul class="check-list">
                    <li>
                        <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        AES-256 encryption - The same standard used by banks and military organizations to protect
                        sensitive data
                    </li>
                    <li>
                        <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Password protection - Secure your data with a strong password of your choice
                    </li>
                    <li>
                        <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Windows Hello integration - Use fingerprint, facial recognition, or PIN for quick and secure
                        access (Full version only)
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section">
        <div class=" container">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">No subscriptions. Just pay once, and own forever.</p>
            <!-- Free plan -->
            <div class="flex-container">
                <div class="pricing-card">
                    <h3>Lifetime Access</h3>
                    <p class="price">Free</p>
                    <p class="description">Free forever</p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Limit of 10 products
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Most features
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Basic Support
                        </li>
                    </ul>
                    <a href="#" class="btn btn-blue" style="width: 100%; margin-top: 36px;">Download now for Windows</a>
                </div>

                <!-- paid plan -->
                <div class="pricing-card">
                    <h3>Lifetime Access</h3>
                    <p class="price">$20 CAD</p>
                    <p class="description">One-time payment</p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Unlimited Products
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            All Features
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Windows Hello
                        </li>
                        <li>
                            <svg class="check-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Priority Support
                        </li>
                    </ul>
                    <a href="upgrade/index.html" class="btn btn-blue" style="width: 100%; margin-top: 36px;">Get
                        Started</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>