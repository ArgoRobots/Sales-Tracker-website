<?php
// Get latest version information from filesystem
function getLatestVersion()
{
    $versionsPath = '../resources/downloads/versions/';
    $latestVersion = null;

    if (!is_dir($versionsPath)) {
        return null;
    }

    $versionFolders = scandir($versionsPath);

    foreach ($versionFolders as $folder) {
        if ($folder === '.' || $folder === '..') continue;

        $versionPath = $versionsPath . $folder . '/';

        if (!is_dir($versionPath)) continue;

        $files = scandir($versionPath);

        foreach ($files as $file) {
            if (preg_match('/^Argo Books Installer V\.(.+)\.exe$/i', $file, $matches)) {
                $version = $matches[1];
                $filepath = $versionPath . $file;

                if (file_exists($filepath)) {
                    $versionData = [
                        'version' => $version,
                        'filename' => $file,
                        'filepath' => $filepath,
                        'filesize' => filesize($filepath),
                        'modified' => filemtime($filepath)
                    ];

                    if ($latestVersion === null || version_compare($version, $latestVersion['version']) > 0) {
                        $latestVersion = $versionData;
                    }
                }
                break;
            }
        }
    }

    return $latestVersion;
}

function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, 1) . ' ' . $units[$pow];
}

$latestVersion = getLatestVersion();
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
        content="Download Argo Books for Windows, macOS, and Linux. Free bookkeeping software for small businesses. Get started with easy invoicing, expense tracking, and financial reports.">
    <meta name="keywords"
        content="argo books download, bookkeeping software, Windows, macOS, Linux, free accounting software, small business software, invoice software">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Download Argo Books | Windows, macOS & Linux">
    <meta property="og:description"
        content="Download Argo Books for your platform. Free bookkeeping software with invoicing, expense tracking, and financial reports.">
    <meta property="og:url" content="https://argorobots.com/downloads/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Books">
    <meta property="og:locale" content="en_CA">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Download Argo Books | Windows, macOS & Linux">
    <meta name="twitter:description"
        content="Download Argo Books for your platform. Free bookkeeping software with invoicing, expense tracking, and financial reports.">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="CA-AB">
    <meta name="geo.placename" content="Calgary">
    <meta name="geo.position" content="51.0447;-114.0719">
    <meta name="ICBM" content="51.0447, -114.0719">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://argorobots.com/downloads/">

    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Download Argo Books | Windows, macOS & Linux</title>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>
    <script src="../resources/scripts/cursor-orb.js" defer></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../resources/styles/button.css">
    <link rel="stylesheet" href="../resources/styles/icons.css">
    <link rel="stylesheet" href="../resources/styles/link.css">
    <link rel="stylesheet" href="../resources/header/style.css">
    <link rel="stylesheet" href="../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <section class="hero">
        <div class="hero-bg">
            <div class="hero-orb hero-orb-1"></div>
            <div class="hero-orb hero-orb-2"></div>
        </div>
        <div class="hero-content">
            <h1>Download Argo Books</h1>
            <p>Choose your platform and get started for free</p>
        </div>
    </section>

    <div class="container">
        <div class="platform-grid">
            <!-- Windows -->
            <div class="platform-card platform-windows">
                <div class="platform-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/>
                    </svg>
                </div>
                <div class="platform-info">
                    <h2>Windows</h2>
                    <p class="platform-desc">For Windows 10 and later</p>
                    <?php if ($latestVersion): ?>
                        <div class="version-details">
                            <span class="version-tag">v<?php echo htmlspecialchars($latestVersion['version']); ?></span>
                            <span class="file-size"><?php echo formatFileSize($latestVersion['filesize']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="platform-actions">
                    <a href="../download" class="btn btn-blue download-btn" data-platform="windows">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download for Windows
                    </a>
                    <span class="platform-badge available">Available Now</span>
                </div>
            </div>

            <!-- macOS -->
            <div class="platform-card platform-macos">
                <div class="platform-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                </div>
                <div class="platform-info">
                    <h2>macOS</h2>
                    <p class="platform-desc">For macOS 11 Big Sur and later</p>
                </div>
                <div class="platform-actions">
                    <button class="btn btn-gray download-btn disabled" disabled>
                        Coming Soon
                    </button>
                    <span class="platform-badge coming-soon">In Development</span>
                </div>
            </div>

            <!-- Linux -->
            <div class="platform-card platform-linux">
                <div class="platform-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.504 0c-.155 0-.315.008-.48.021-4.226.333-3.105 4.807-3.17 6.298-.076 1.092-.3 1.953-1.05 3.02-.885 1.051-2.127 2.75-2.716 4.521-.278.832-.41 1.684-.287 2.489a.424.424 0 00-.11.135c-.26.268-.45.6-.663.839-.199.199-.485.267-.797.4-.313.136-.658.269-.864.68-.09.189-.136.394-.132.602 0 .199.027.4.055.536.058.399.116.728.04.97-.249.68-.28 1.145-.106 1.484.174.334.535.47.94.601.81.2 1.91.135 2.774.6.926.466 1.866.67 2.616.47.526-.116.97-.464 1.208-.946.587-.003 1.23-.269 2.26-.334.699-.058 1.574.267 2.577.2.025.134.063.198.114.333l.003.003c.391.778 1.113 1.132 1.884 1.071.771-.06 1.592-.536 2.257-1.306.631-.765 1.683-1.084 2.378-1.503.348-.199.629-.469.649-.853.023-.4-.2-.811-.714-1.376v-.097l-.003-.003c-.17-.2-.25-.535-.338-.926-.085-.401-.182-.786-.492-1.046h-.003c-.059-.054-.123-.067-.188-.135a.357.357 0 00-.19-.064c.431-1.278.264-2.55-.173-3.694-.533-1.41-1.465-2.638-2.175-3.483-.796-1.005-1.576-1.957-1.56-3.368.026-2.152.236-6.133-3.544-6.139zm.529 3.405h.013c.213 0 .396.062.584.198.19.135.33.332.438.533.105.259.158.459.166.724 0-.02.006-.04.006-.06v.105a.086.086 0 01-.004-.021l-.004-.024a1.807 1.807 0 01-.15.706.953.953 0 01-.213.335.71.71 0 00-.088-.042c-.104-.045-.198-.064-.284-.133a1.312 1.312 0 00-.22-.066c.05-.06.146-.133.183-.198.053-.128.082-.264.088-.402v-.02a1.21 1.21 0 00-.061-.4c-.045-.134-.101-.2-.183-.333-.084-.066-.167-.132-.267-.132h-.016c-.093 0-.176.03-.262.132a.8.8 0 00-.205.334 1.18 1.18 0 00-.09.4v.019c.002.089.008.179.02.267-.193-.067-.438-.135-.607-.202a1.635 1.635 0 01-.018-.2v-.02a1.772 1.772 0 01.15-.768c.082-.22.232-.406.43-.534a.985.985 0 01.594-.2zm-2.962.059h.036c.142 0 .27.048.399.135.146.129.264.288.344.465.09.199.14.4.153.667l.002.021.003.021v.02c0 .133-.012.267-.03.397-.106-.065-.25-.133-.393-.133h-.027c-.26.002-.491.135-.665.334a1.063 1.063 0 00-.198.467c-.013.066-.018.135-.018.2v.02c.002.133.018.267.058.398a.09.09 0 01-.016-.003l-.021-.007a1.966 1.966 0 01-.282-.065c-.058-.2-.09-.387-.09-.6a1.784 1.784 0 01.142-.656c.088-.2.214-.397.388-.535.205-.156.428-.2.65-.2zm1.956 1.609c.36.001.686.132.918.331.265.2.437.468.522.8.053.2.066.401.066.602 0 .135-.012.267-.03.4l-.018.129a.077.077 0 01-.002.01 2.05 2.05 0 01-.106.401c-.06.132-.164.265-.184.333-.022.065-.03.132-.03.199 0 .066.007.133.016.2l.012.066c-.066.043-.132.132-.199.132h-.003c-.073 0-.15-.035-.22-.133-.067-.066-.135-.2-.185-.467-.039-.2-.058-.401-.058-.602v-.066a1.27 1.27 0 01.036-.333c.043-.132.043-.2.043-.265a.563.563 0 00-.09-.334 1.157 1.157 0 00-.203-.264c-.113-.066-.24-.132-.393-.132h-.006c-.143 0-.268.066-.379.199a.758.758 0 00-.17.332c-.02.066-.03.132-.03.199 0 .066.006.133.015.2.02.133.04.266.04.4 0 .134-.013.266-.058.398a.93.93 0 01-.137.267c-.06.065-.116.132-.174.199-.055.066-.11.133-.146.2-.053.133-.082.267-.082.4v.067c0 .135.028.267.082.4.053.135.142.266.248.4.053.066.113.133.172.2.059.066.114.133.164.2.16.2.279.399.359.6.116.199.176.398.212.598a3.23 3.23 0 01.024.333c0 .333-.049.667-.144 1-.096.334-.234.667-.416.934a2.385 2.385 0 01-.624.663c-.148.092-.293.148-.44.198.072-.333.112-.668.112-1 0-.333-.049-.667-.146-1-.096-.334-.235-.667-.416-.934a2.305 2.305 0 00-.626-.663c-.085-.067-.173-.12-.263-.174-.113-.135-.243-.267-.373-.4-.13-.133-.26-.267-.386-.4-.212-.2-.388-.4-.535-.602-.146-.2-.262-.398-.336-.598-.083-.2-.127-.4-.127-.6 0-.135.012-.267.041-.4a1.5 1.5 0 01.137-.4c.06-.132.137-.265.23-.398.095-.135.206-.267.335-.4.06-.067.121-.133.177-.2.054-.067.097-.133.123-.2.03-.066.041-.132.041-.199 0-.066-.006-.132-.018-.199a1.05 1.05 0 00-.124-.334 1.02 1.02 0 00-.222-.267.637.637 0 00-.328-.132h-.003c-.145 0-.277.041-.396.132-.117.09-.217.201-.299.334-.082.133-.147.266-.195.4-.047.132-.077.265-.087.397v.135c0 .066.006.133.017.2.011.065.023.132.04.199.006.031.01.067.014.1a1.84 1.84 0 01-.413-.201 1.81 1.81 0 01-.348-.267 1.96 1.96 0 01-.097-.135c-.045-.067-.077-.135-.097-.2a.682.682 0 01-.043-.199v-.066c0-.334.09-.668.274-1 .089-.168.204-.332.345-.465.14-.135.304-.2.493-.2h.07c.177.012.332.066.463.132.13.068.245.135.346.2.076.068.136.135.176.2.04.066.06.135.06.2v.066c0 .133-.006.266-.006.4 0 .133.012.265.035.398.024.135.06.267.106.4z"/>
                    </svg>
                </div>
                <div class="platform-info">
                    <h2>Linux</h2>
                    <p class="platform-desc">Ubuntu, Debian, Fedora & more</p>
                </div>
                <div class="platform-actions">
                    <button class="btn btn-gray download-btn disabled" disabled>
                        Coming Soon
                    </button>
                    <span class="platform-badge coming-soon">In Development</span>
                </div>
            </div>
        </div>

        <!-- System Requirements -->
        <div class="requirements-section">
            <h2>System Requirements</h2>
            <div class="requirements-grid">
                <div class="requirement-card">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="currentColor" class="req-icon">
                            <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/>
                        </svg>
                        Windows
                    </h3>
                    <ul>
                        <li>Windows 10 or later (64-bit)</li>
                        <li>4 GB RAM minimum</li>
                        <li>200 MB available disk space</li>
                        <li>.NET Framework 4.7.2 or later</li>
                    </ul>
                </div>
                <div class="requirement-card">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="currentColor" class="req-icon">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                        </svg>
                        macOS
                    </h3>
                    <ul>
                        <li>macOS 11 Big Sur or later</li>
                        <li>Apple Silicon or Intel processor</li>
                        <li>4 GB RAM minimum</li>
                        <li>200 MB available disk space</li>
                    </ul>
                </div>
                <div class="requirement-card">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="currentColor" class="req-icon">
                            <path d="M12.504 0c-.155 0-.315.008-.48.021-4.226.333-3.105 4.807-3.17 6.298-.076 1.092-.3 1.953-1.05 3.02-.885 1.051-2.127 2.75-2.716 4.521-.278.832-.41 1.684-.287 2.489a.424.424 0 00-.11.135c-.26.268-.45.6-.663.839-.199.199-.485.267-.797.4-.313.136-.658.269-.864.68-.09.189-.136.394-.132.602 0 .199.027.4.055.536.058.399.116.728.04.97-.249.68-.28 1.145-.106 1.484.174.334.535.47.94.601.81.2 1.91.135 2.774.6.926.466 1.866.67 2.616.47.526-.116.97-.464 1.208-.946.587-.003 1.23-.269 2.26-.334.699-.058 1.574.267 2.577.2.025.134.063.198.114.333l.003.003c.391.778 1.113 1.132 1.884 1.071.771-.06 1.592-.536 2.257-1.306.631-.765 1.683-1.084 2.378-1.503.348-.199.629-.469.649-.853.023-.4-.2-.811-.714-1.376v-.097l-.003-.003c-.17-.2-.25-.535-.338-.926-.085-.401-.182-.786-.492-1.046h-.003c-.059-.054-.123-.067-.188-.135a.357.357 0 00-.19-.064c.431-1.278.264-2.55-.173-3.694-.533-1.41-1.465-2.638-2.175-3.483-.796-1.005-1.576-1.957-1.56-3.368.026-2.152.236-6.133-3.544-6.139zm.529 3.405h.013c.213 0 .396.062.584.198.19.135.33.332.438.533.105.259.158.459.166.724 0-.02.006-.04.006-.06v.105a.086.086 0 01-.004-.021l-.004-.024a1.807 1.807 0 01-.15.706.953.953 0 01-.213.335.71.71 0 00-.088-.042c-.104-.045-.198-.064-.284-.133a1.312 1.312 0 00-.22-.066c.05-.06.146-.133.183-.198.053-.128.082-.264.088-.402v-.02a1.21 1.21 0 00-.061-.4c-.045-.134-.101-.2-.183-.333-.084-.066-.167-.132-.267-.132h-.016c-.093 0-.176.03-.262.132a.8.8 0 00-.205.334 1.18 1.18 0 00-.09.4v.019c.002.089.008.179.02.267-.193-.067-.438-.135-.607-.202a1.635 1.635 0 01-.018-.2v-.02a1.772 1.772 0 01.15-.768c.082-.22.232-.406.43-.534a.985.985 0 01.594-.2zm-2.962.059h.036c.142 0 .27.048.399.135.146.129.264.288.344.465.09.199.14.4.153.667l.002.021.003.021v.02c0 .133-.012.267-.03.397-.106-.065-.25-.133-.393-.133h-.027c-.26.002-.491.135-.665.334a1.063 1.063 0 00-.198.467c-.013.066-.018.135-.018.2v.02c.002.133.018.267.058.398a.09.09 0 01-.016-.003l-.021-.007a1.966 1.966 0 01-.282-.065c-.058-.2-.09-.387-.09-.6a1.784 1.784 0 01.142-.656c.088-.2.214-.397.388-.535.205-.156.428-.2.65-.2zm1.956 1.609c.36.001.686.132.918.331.265.2.437.468.522.8.053.2.066.401.066.602 0 .135-.012.267-.03.4l-.018.129a.077.077 0 01-.002.01 2.05 2.05 0 01-.106.401c-.06.132-.164.265-.184.333-.022.065-.03.132-.03.199 0 .066.007.133.016.2l.012.066c-.066.043-.132.132-.199.132h-.003c-.073 0-.15-.035-.22-.133-.067-.066-.135-.2-.185-.467-.039-.2-.058-.401-.058-.602v-.066a1.27 1.27 0 01.036-.333c.043-.132.043-.2.043-.265a.563.563 0 00-.09-.334 1.157 1.157 0 00-.203-.264c-.113-.066-.24-.132-.393-.132h-.006c-.143 0-.268.066-.379.199a.758.758 0 00-.17.332c-.02.066-.03.132-.03.199 0 .066.006.133.015.2.02.133.04.266.04.4 0 .134-.013.266-.058.398a.93.93 0 01-.137.267c-.06.065-.116.132-.174.199-.055.066-.11.133-.146.2-.053.133-.082.267-.082.4v.067c0 .135.028.267.082.4.053.135.142.266.248.4.053.066.113.133.172.2.059.066.114.133.164.2.16.2.279.399.359.6.116.199.176.398.212.598a3.23 3.23 0 01.024.333c0 .333-.049.667-.144 1-.096.334-.234.667-.416.934a2.385 2.385 0 01-.624.663c-.148.092-.293.148-.44.198.072-.333.112-.668.112-1 0-.333-.049-.667-.146-1-.096-.334-.235-.667-.416-.934a2.305 2.305 0 00-.626-.663c-.085-.067-.173-.12-.263-.174-.113-.135-.243-.267-.373-.4-.13-.133-.26-.267-.386-.4-.212-.2-.388-.4-.535-.602-.146-.2-.262-.398-.336-.598-.083-.2-.127-.4-.127-.6 0-.135.012-.267.041-.4a1.5 1.5 0 01.137-.4c.06-.132.137-.265.23-.398.095-.135.206-.267.335-.4.06-.067.121-.133.177-.2.054-.067.097-.133.123-.2.03-.066.041-.132.041-.199 0-.066-.006-.132-.018-.199a1.05 1.05 0 00-.124-.334 1.02 1.02 0 00-.222-.267.637.637 0 00-.328-.132h-.003c-.145 0-.277.041-.396.132-.117.09-.217.201-.299.334-.082.133-.147.266-.195.4-.047.132-.077.265-.087.397v.135c0 .066.006.133.017.2.011.065.023.132.04.199.006.031.01.067.014.1a1.84 1.84 0 01-.413-.201 1.81 1.81 0 01-.348-.267 1.96 1.96 0 01-.097-.135c-.045-.067-.077-.135-.097-.2a.682.682 0 01-.043-.199v-.066c0-.334.09-.668.274-1 .089-.168.204-.332.345-.465.14-.135.304-.2.493-.2h.07c.177.012.332.066.463.132.13.068.245.135.346.2.076.068.136.135.176.2.04.066.06.135.06.2v.066c0 .133-.006.266-.006.4 0 .133.012.265.035.398.024.135.06.267.106.4z"/>
                        </svg>
                        Linux
                    </h3>
                    <ul>
                        <li>Ubuntu 20.04+, Debian 11+, Fedora 35+</li>
                        <li>4 GB RAM minimum</li>
                        <li>200 MB available disk space</li>
                        <li>GTK 3.0 or later</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Additional Links -->
        <div class="additional-section">
            <div class="additional-card">
                <div class="additional-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="additional-content">
                    <h3>Looking for older versions?</h3>
                    <p>Access previous releases of Argo Books for compatibility or testing purposes.</p>
                    <a href="../older-versions/" class="link-arrow">
                        View older versions
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="additional-card">
                <div class="additional-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="additional-content">
                    <h3>Need help getting started?</h3>
                    <p>Check out our documentation for installation guides and tutorials.</p>
                    <a href="../documentation/" class="link-arrow">
                        View documentation
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        // Add download tracking
        document.querySelectorAll('.download-btn:not(.disabled)').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const platform = this.getAttribute('data-platform');
                if (platform && typeof gtag !== 'undefined') {
                    gtag('event', 'download_click', {
                        'event_category': 'software',
                        'event_label': 'argo_books_' + platform,
                        'platform': platform
                    });
                }
            });
        });
    </script>
</body>

</html>
