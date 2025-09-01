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
    <meta name="author" content="Argo">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Transform your small business with Argo Sales Tracker. Save several hours weekly, reduce errors by 95%, and boost profits. Free Windows software with 22+ interactive charts. No monthly fees.">
    <meta name="keywords"
        content="sales tracker, business software, profit tracking, inventory management, small business automation, Windows software, Calgary business software, time saving, error reduction">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Argo Sales Tracker - Save several Hours Weekly & Boost Profits">
    <meta property="og:description"
        content="Transform your business with automated sales tracking, eliminate errors, and grow profits. Free forever with lifetime upgrades available.">
    <meta property="og:url" content="https://argorobots.com/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Sales Tracker">
    <meta property="og:locale" content="en_CA">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Argo Sales Tracker - Save several Hours Weekly">
    <meta name="twitter:description"
        content="Transform your business with automated sales tracking and boost profits. Free forever.">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="CA-AB">
    <meta name="geo.placename" content="Calgary">
    <meta name="geo.position" content="51.0447;-114.0719">
    <meta name="ICBM" content="51.0447, -114.0719">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://argorobots.com/">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "Argo Sales Tracker",
            "description": "Free Windows sales tracking software that saves several hours weekly for small businesses with 22+ automated charts and profit insights",
            "url": "https://argorobots.com/",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Windows",
            "offers": {
                "@type": "Offer",
                "price": "0",
                "priceCurrency": "CAD",
                "availability": "https://schema.org/InStock"
            },
            "publisher": {
                "@type": "Organization",
                "name": "Argo",
                "url": "https://argorobots.com/",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Calgary",
                    "addressRegion": "AB",
                    "addressCountry": "CA"
                }
            },
            "downloadUrl": "https://argorobots.com/download",
            "softwareVersion": "1.0.4",
            "datePublished": "2025-05-01",
            "dateModified": "2025-07-20"
        }
    </script>

    <link rel="shortcut icon" type="image/x-icon" href="images/argo-logo/A-logo.ico">
    <title>Argo Sales Tracker - Save Several Hours Weekly | Free Business Automation Software</title>

    <?php include 'resources/head/google-analytics.php'; ?>

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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="hero-gradient"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>

        <div class="container">
            <div class="grid-2col">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="badge-pulse"></span>
                        <span>Built for small businesses that value their time</span>
                    </div>

                    <h1 class="hero-title">
                        Transform your business with smart sales tracking
                    </h1>

                    <p class="hero-subtitle">
                        Stop drowning in spreadsheets. Argo automates your sales tracking, eliminates costly errors,
                        and shows you exactly where your money comes from.
                    </p>

                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-number">22+</span>
                            <span class="stat-label">Live Charts</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">95%</span>
                            <span class="stat-label">Error Reduction</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">$0</span>
                            <span class="stat-label">Monthly Fees</span>
                        </div>
                    </div>

                    <div class="hero-cta">
                        <div class="text-center">
                            <a href="/download" class="btn btn-primary btn-large">
                                <span class="btn-text">Get now for Free</span>
                                <span class="btn-subtext">Setup in 3 minutes</span>
                            </a>
                            <p class="windows-only">Windows only</p>
                        </div>
                        <a href="upgrade/index.php" class="btn btn-secondary">
                            Upgrade for $20 CAD
                        </a>
                    </div>

                    <div class="hero-guarantees">
                        <div class="icon-text">
                            <span class="icon-text__icon">💳</span>
                            <span>No bank card needed</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">🛡️</span>
                            <span>30-day money back</span>
                        </div>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="dashboard-container">
                        <img src="images/main.webp" alt="Argo Sales Tracker Dashboard" class="dashboard-main">

                        <!-- Floating Metrics -->
                        <div class="floating-metric metric-1">
                            <div class="metric-icon">💰</div>
                            <div class="metric-content">
                                <div class="metric-label">This Month</div>
                                <div class="metric-value">+$18,520</div>
                                <div class="metric-change">+32% vs last month</div>
                            </div>
                        </div>

                        <div class="floating-metric metric-2">
                            <div class="metric-icon">⚡</div>
                            <div class="metric-content">
                                <div class="metric-label">Time Saved</div>
                                <div class="metric-value">8.4 hrs/week</div>
                                <div class="metric-change">vs manual tracking</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title">Are You Tired of This?</h2>

            <div class="grid">
                <div class="card text-center">
                    <div class="problem-icon">⏰</div>
                    <h3>Spending Hours on Data Entry</h3>
                    <p>Manually typing transactions into spreadsheets, double-checking formulas, and fixing broken
                        calculations every week.</p>
                </div>

                <div class="card text-center">
                    <div class="problem-icon">💸</div>
                    <h3>Losing Money to Errors</h3>
                    <p>Calculation mistakes, missing receipts, and data corruption costing you hundreds or thousands
                        in lost profits.</p>
                </div>

                <div class="card text-center">
                    <div class="problem-icon">📊</div>
                    <h3>No Clear Business Insights</h3>
                    <p>Flying blind with no idea which products make money, when your busy seasons are, or how to
                        grow strategically.</p>
                </div>
            </div>

            <div class="text-center">
                <p class="problem-text">There's a better way. Let us show you how Argo can solve this.</p>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">How Argo Transforms Your Business</h2>

            <!-- Feature 1: Smart Transaction Entry -->
            <div class="feature-showcase grid-2col">
                <div class="feature-content">
                    <div class="feature-badge">Save Several Hours Weekly</div>
                    <h3 class="feature-title">Add Transactions in 15 Seconds</h3>
                    <p class="feature-description">
                        Smart forms with validation and instant calculations.
                        No more manual formulas or data entry errors. Just fast, accurate tracking.
                    </p>
                    <div class="feature-benefits">
                        <div class="icon-text">
                            <span class="icon-text__icon">⚡</span>
                            <span><strong>95% faster</strong> than spreadsheets</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">🎯</span>
                            <span><strong>Zero calculation errors</strong> with built-in validation</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">📝</span>
                            <span><strong>Auto-complete</strong> for products and customers</span>
                        </div>
                    </div>
                </div>
                <div class="feature-visual">
                    <svg width="100%" height="400" viewBox="0 0 500 400" class="transaction-svg">
                        <!-- Main form background -->
                        <rect x="50" y="50" width="400" height="300" rx="20" fill="#ffffff" stroke="#e5e7eb"
                            stroke-width="2" />

                        <!-- Header -->
                        <rect x="50" y="50" width="400" height="60" rx="20" fill="#3b82f6" />
                        <rect x="50" y="90" width="400" height="20" fill="#3b82f6" />
                        <text x="250" y="85" text-anchor="middle" fill="white" font-size="18" font-weight="bold">Add New
                            Transaction</text>

                        <!-- Quick action buttons -->
                        <rect x="70" y="130" width="80" height="35" rx="8" fill="#10b981" />
                        <text x="110" y="152" text-anchor="middle" fill="white" font-size="12"
                            font-weight="500">Sale</text>

                        <rect x="160" y="130" width="80" height="35" rx="8" fill="#f3f4f6" stroke="#d1d5db" />
                        <text x="200" y="152" text-anchor="middle" fill="#6b7280" font-size="12">Purchase</text>

                        <!-- Smart form fields -->
                        <text x="70" y="190" fill="#374151" font-size="12" font-weight="500">Product</text>
                        <rect x="70" y="195" width="280" height="30" rx="6" fill="#f9fafb" stroke="#d1d5db" />
                        <text x="80" y="215" fill="#1f2937" font-size="11">Widget Pro</text>

                        <text x="70" y="245" fill="#374151" font-size="12" font-weight="500">Quantity</text>
                        <rect x="70" y="250" width="100" height="30" rx="6" fill="#f9fafb" stroke="#d1d5db" />
                        <text x="80" y="270" fill="#1f2937" font-size="11">5</text>

                        <text x="190" y="245" fill="#374151" font-size="12" font-weight="500">Price Each</text>
                        <rect x="190" y="250" width="100" height="30" rx="6" fill="#f9fafb" stroke="#d1d5db" />
                        <text x="200" y="270" fill="#1f2937" font-size="11">$89.99</text>

                        <text x="310" y="245" fill="#374151" font-size="12" font-weight="500">Total</text>
                        <rect x="310" y="250" width="100" height="30" rx="6" fill="#ecfdf5" stroke="#10b981" />
                        <text x="320" y="270" fill="#059669" font-size="11" font-weight="bold">$449.95</text>

                        <!-- Auto-calculation indicator -->
                        <circle cx="423" cy="265" r="8" fill="#10b981" />
                        <text x="423" y="268" text-anchor="middle" fill="white" font-size="8"
                            font-weight="bold">✓</text>

                        <!-- Add button -->
                        <rect x="70" y="300" width="120" height="40" rx="8" fill="#3b82f6" />
                        <text x="130" y="325" text-anchor="middle" fill="white" font-size="14" font-weight="500">Add
                            Transaction</text>

                        <!-- Success message -->
                        <rect x="210" y="308" width="200" height="24" rx="12" fill="#ecfdf5" stroke="#10b981" />
                        <circle cx="225" cy="320" r="6" fill="#10b981" />
                        <text x="222" y="325" text-anchor="middle" fill="white" font-size="8"
                            font-weight="bold">✓</text>
                        <text x="240" y="325" fill="#059669" font-size="11">Automatically saved & calculated</text>

                        <!-- Speed indicator -->
                        <circle cx="420" cy="80" r="20" fill="#fbbf24" />
                        <text x="420" y="85" text-anchor="middle" fill="white" font-size="14"
                            font-weight="bold">15s</text>
                    </svg>
                </div>
            </div>

            <!-- Feature 2: Automated Insights -->
            <div class="feature-showcase grid-2col reverse">
                <div class="feature-content">
                    <div class="feature-badge">Boost Profits 25%+</div>
                    <h3 class="feature-title">Discover Your Hidden Profit Drivers</h3>
                    <p class="feature-description">
                        See exactly which products make money, when your busy seasons are, and where to focus your
                        efforts.
                        Real-time insights that actually grow your business.
                    </p>
                    <div class="feature-benefits">
                        <div class="icon-text">
                            <span class="icon-text__icon">📊</span>
                            <span><strong>22+ interactive charts</strong> updated in real-time</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">🎯</span>
                            <span><strong>Profit tracking</strong> per product and category</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">📈</span>
                            <span><strong>Trend analysis</strong> to predict busy seasons</span>
                        </div>
                    </div>
                </div>
                <div class="feature-visual">
                    <div class="analytics-preview">
                        <img src="images/analytics.webp" alt="Real-time Analytics Dashboard" class="analytics-img">
                        <div class="insight-overlay">
                            <div class="insight-card">
                                <div class="insight-icon">💡</div>
                                <div class="insight-text">
                                    <strong>Insight:</strong> Widget Pro has 45% profit margin - your best seller!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature 3: Smart Receipt Management -->
            <div class="feature-showcase grid-2col">
                <div class="feature-content">
                    <div class="feature-badge">Never Lose Another Receipt</div>
                    <h3 class="feature-title">Import & Organize Receipts Effortlessly</h3>
                    <p class="feature-description">
                        Import receipt images, attach them to transactions, and export organized packages for taxes.
                        Everything in one place, searchable and audit-ready.
                    </p>
                    <div class="feature-benefits">
                        <div class="icon-text">
                            <span class="icon-text__icon">📁</span>
                            <span><strong>Import receipt images</strong> from your computer or phone</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">🔍</span>
                            <span><strong>Instant search</strong> by date, amount, or vendor</span>
                        </div>
                        <div class="icon-text">
                            <span class="icon-text__icon">📦</span>
                            <span><strong>Bulk export</strong> for tax season in one click</span>
                        </div>
                    </div>
                </div>
                <div class="feature-visual">
                    <svg width="100%" height="400" viewBox="0 0 500 400" class="receipt-svg">
                        <!-- Main interface background -->
                        <rect x="50" y="50" width="400" height="300" rx="12" fill="#ffffff" stroke="#e5e7eb"
                            stroke-width="2" />

                        <!-- Header -->
                        <rect x="50" y="50" width="400" height="50" rx="12" fill="#3b82f6" />
                        <rect x="50" y="88" width="400" height="12" fill="#3b82f6" />
                        <text x="250" y="80" text-anchor="middle" fill="white" font-size="16" font-weight="bold">Receipt
                            Manager</text>

                        <!-- Import area -->
                        <rect x="70" y="120" width="360" height="80" rx="8" fill="#f8fafc" stroke="#cbd5e1"
                            stroke-dasharray="5,5" />
                        <text x="250" y="150" text-anchor="middle" fill="#64748b" font-size="12">Drag & Drop Receipt
                            Images Here</text>
                        <text x="250" y="170" text-anchor="middle" fill="#64748b" font-size="10">or click to
                            browse</text>
                        <rect x="220" y="180" width="60" height="15" rx="4" fill="#3b82f6" />
                        <text x="250" y="190" text-anchor="middle" fill="white" font-size="8">Browse Files</text>

                        <!-- Receipt list -->
                        <g class="receipt-item">
                            <rect x="70" y="220" width="360" height="30" fill="#ffffff" stroke="#e2e8f0" />
                            <circle cx="85" cy="235" r="6" fill="#10b981" />
                            <path d="M83 235 L85 237 L89 232" stroke="white" stroke-width="1" fill="none" />
                            <text x="100" y="232" fill="#1f2937" font-size="10"
                                font-weight="500">office-supplies-receipt.jpg</text>
                            <text x="100" y="244" fill="#6b7280" font-size="8">Attached to: Office Supplies Purchase -
                                $87.50</text>
                            <rect x="380" y="227" width="40" height="16" rx="3" fill="#dbeafe" />
                            <text x="400" y="237" text-anchor="middle" fill="#1d4ed8" font-size="8">View</text>
                        </g>

                        <g class="receipt-item">
                            <rect x="70" y="260" width="360" height="30" fill="#f8fafc" stroke="#e2e8f0" />
                            <circle cx="85" cy="275" r="6" fill="#10b981" />
                            <path d="M83 275 L85 277 L89 272" stroke="white" stroke-width="1" fill="none" />
                            <text x="100" y="272" fill="#1f2937" font-size="10"
                                font-weight="500">inventory-invoice.pdf</text>
                            <text x="100" y="284" fill="#6b7280" font-size="8">Attached to: Inventory Restock -
                                $1,234.50</text>
                            <rect x="380" y="267" width="40" height="16" rx="3" fill="#dbeafe" />
                            <text x="400" y="277" text-anchor="middle" fill="#1d4ed8" font-size="8">View</text>
                        </g>

                        <!-- Export button -->
                        <rect x="70" y="310" width="100" height="25" rx="6" fill="#10b981" />
                        <text x="120" y="325" text-anchor="middle" fill="white" font-size="10" font-weight="500">Export
                            All</text>

                        <!-- Stats -->
                        <text x="200" y="325" fill="#6b7280" font-size="10">24 receipts organized</text>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="section section-alt">
        <div class="container">
            <h2 class="section-title">The Results You Can Expect</h2>

            <div class="grid">
                <div class="card text-center">
                    <div class="result-metric">Several hours</div>
                    <div class="result-label">Weekly time savings potential</div>
                    <div class="result-description">Based on typical small business data entry workflows vs. Argo's
                        automated systems.</div>
                </div>

                <div class="card text-center">
                    <div class="result-metric">95%</div>
                    <div class="result-label">Reduction in calculation errors</div>
                    <div class="result-description">Automated calculations and validation eliminate the human errors
                        common in manual spreadsheets.</div>
                </div>

                <div class="card text-center">
                    <div class="result-metric">Instant</div>
                    <div class="result-label">Business insights</div>
                    <div class="result-description">See your profit margins, best-selling products, and trends
                        immediately as you add data.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Spreadsheets vs. Argo: The Real Cost</h2>

            <div class="comparison-table">
                <div class="comparison-header">
                    <div class="header-item"></div>
                    <div class="header-item">Basic Spreadsheets</div>
                    <div class="header-item featured">Argo Sales Tracker</div>
                </div>

                <div class="comparison-row">
                    <div class="row-label">Data entry process</div>
                    <div class="row-item negative">Manual & tedious 📝</div>
                    <div class="row-item positive">Automated & easy ⚡</div>
                </div>

                <div class="comparison-row">
                    <div class="row-label">Calculation errors per month</div>
                    <div class="row-item negative">several errors 💸</div>
                    <div class="row-item positive">0 errors ✅</div>
                </div>

                <div class="comparison-row">
                    <div class="row-label">Business insights & analytics</div>
                    <div class="row-item negative">Basic charts that break 📊</div>
                    <div class="row-item positive">22+ real-time charts 📈</div>
                </div>

                <div class="comparison-row">
                    <div class="row-label">Receipt organization</div>
                    <div class="row-item negative">Scattered everywhere 📁</div>
                    <div class="row-item positive">Attached & searchable 🔍</div>
                </div>
            </div>

            <div class="text-center">
                <div class="card" style="display: inline-block; padding: 60px 40px;">
                    <h3>Ready to Stop Wasting Time?</h3>
                    <p>Make the switch to automated sales tracking and see the difference.</p>
                    <div class="text-center">
                        <a href="/download" class="btn btn-primary btn-large">Make the Switch for Free</a>
                        <p class="windows-only">Windows only</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section class="section section-alt">
        <div class="container">
            <div class="grid-2col">
                <div class="security-visual">
                    <svg width="300" height="300" viewBox="0 0 300 300" class="security-svg">
                        <!-- Background circles -->
                        <circle cx="150" cy="150" r="120" fill="#dbeafe" opacity="0.3" />
                        <circle cx="150" cy="150" r="90" fill="#3b82f6" opacity="0.1" />
                        <circle cx="150" cy="150" r="60" fill="#1e40af" opacity="0.1" />

                        <!-- Main shield -->
                        <path d="M150 60 L210 90 L210 150 Q210 190 150 220 Q90 190 90 150 L90 90 Z" fill="#1e40af" />
                        <path d="M150 80 L190 100 L190 145 Q190 175 150 195 Q110 175 110 145 L110 100 Z"
                            fill="#3b82f6" />

                        <!-- Lock icon -->
                        <rect x="130" y="130" width="40" height="35" rx="5" fill="#ffffff" />
                        <circle cx="150" cy="145" r="8" fill="#1e40af" />
                        <rect x="146" y="145" width="8" height="12" fill="#ffffff" />

                        <!-- Lock shackle -->
                        <path d="M135 125 L135 115 Q135 105 150 105 Q165 105 165 115 L165 125" stroke="#ffffff"
                            stroke-width="4" fill="none" />

                        <!-- Security badges -->
                        <rect x="50" y="50" width="60" height="20" rx="10" fill="#10b981" />
                        <text x="80" y="64" text-anchor="middle" fill="white" font-size="10"
                            font-weight="bold">AES-256</text>

                        <rect x="190" y="230" width="80" height="20" rx="10" fill="#f59e0b" />
                        <text x="230" y="244" text-anchor="middle" fill="white" font-size="10" font-weight="bold">Local
                            Storage</text>

                        <rect x="35" y="230" width="80" height="20" rx="10" fill="#8b5cf6" />
                        <text x="75" y="244" text-anchor="middle" fill="white" font-size="10" font-weight="bold">Windows
                            Hello</text>
                    </svg>
                </div>

                <div class="security-text">
                    <h3>Your Data is Protected with Military-Grade Security</h3>
                    <p>We use AES-256 encryption - the same standard trusted by government agencies and military
                        organizations worldwide. Your sensitive business data stays on your computer, encrypted and
                        secure.</p>

                    <div class="security-features">
                        <div class="security-feature">
                            <div class="feature-icon">🏛️</div>
                            <div class="feature-content">
                                <h4>AES-256 Encryption</h4>
                                <p>Military-grade security standard</p>
                            </div>
                        </div>

                        <div class="security-feature">
                            <div class="feature-icon">💻</div>
                            <div class="feature-content">
                                <h4>Local Data Storage</h4>
                                <p>Your data never leaves your computer</p>
                            </div>
                        </div>

                        <div class="security-feature">
                            <div class="feature-icon">🔐</div>
                            <div class="feature-content">
                                <h4>Windows Hello Support</h4>
                                <p>Fingerprint and face unlock (Premium only)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="section">
        <div class="container">
            <div class="pricing-header text-center">
                <h2 class="section-title">Simple, Honest Pricing</h2>
                <p class="section-subtitle">No subscriptions, no hidden fees. Pay once, own forever.</p>
            </div>

            <div class="pricing-cards grid">
                <!-- Free Plan -->
                <div class="pricing-card free-plan card">
                    <div class="plan-header">
                        <div class="plan-badge popular">Basic Version</div>
                        <h3 class="plan-name">Free Forever</h3>
                        <div class="plan-price">
                            <span class="price-currency">$</span>
                            <span class="price-amount">0</span>
                            <span class="price-period">forever</span>
                        </div>
                        <p class="plan-description">Perfect for small businesses getting started</p>
                    </div>

                    <div class="plan-features">
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Up to 10 products</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Unlimited transactions</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Real-time analytics</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Receipt management</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Data export</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Email support</span>
                        </div>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="pricing-card premium-plan card">
                    <div class="plan-header">
                        <div class="plan-badge premium">Full Version</div>
                        <h3 class="plan-name">Premium</h3>
                        <div class="plan-price">
                            <span class="price-currency">$</span>
                            <span class="price-amount">20</span>
                            <span class="price-period">CAD one-time</span>
                        </div>
                        <p class="plan-description">Everything you need to scale</p>
                    </div>

                    <div class="plan-features">
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span><strong>Unlimited products</strong></span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Windows Hello security</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-check">✅</span>
                            <span>Priority support</span>
                        </div>
                        <div class="feature-item premium-feature">
                            <span><strong>Lifetime updates included</strong></span>
                        </div>
                    </div>

                    <div class="plan-cta">
                        <a href="upgrade/index.php" class="btn btn-primary btn-full">Upgrade Now</a>
                        <p class="plan-note">30-day money back guarantee</p>
                    </div>
                </div>
            </div>

            <div class="pricing-guarantee">
                <div class="guarantee-content">
                    <div class="guarantee-text">
                        <h4>Risk-Free Guarantee</h4>
                        <p>Try Premium for 30 days. If you're not completely satisfied, get your money back instantly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="final-cta section">
        <div class="container">
            <div class="cta-content text-center">
                <h2>Ready to Save Several Hours Every Week?</h2>
                <p class="description">Transform your business with automated sales tracking that actually works.</p>

                <div class="cta-buttons">
                    <div class="text-center">
                        <a href="/download" class="btn btn-primary btn-xl">
                            <span class="btn-text">Get now for Free</span>
                            <span class="btn-subtext">Setup in 3 minutes</span>
                        </a>
                        <p class="windows-only">Windows only</p>
                    </div>
                    <a href="upgrade/index.php" class="btn btn-secondary">
                        Upgrade for $20 CAD
                    </a>
                </div>

                <div class="final-guarantees">
                    <div class="icon-text">
                        <span class="icon-text__icon">⚡</span>
                        <span>3-minute setup</span>
                    </div>
                    <div class="icon-text">
                        <span class="icon-text__icon">🔒</span>
                        <span>Military-grade security</span>
                    </div>
                    <div class="icon-text">
                        <span class="icon-text__icon">💳</span>
                        <span>No bank card needed</span>
                    </div>
                    <div class="icon-text">
                        <span class="icon-text__icon">🛡️</span>
                        <span>30-day guarantee</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>