<?php
// Platform configuration
$platforms = [
    'windows' => [
        'name' => 'Windows',
        'pattern' => '/^Argo Books Installer V\.(.+)\.exe$/i',
        'available' => true,
        'icon' => 'M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801'
    ],
    'macos' => [
        'name' => 'macOS',
        'pattern' => '/^Argo Books.*\.dmg$/i',
        'available' => false,
        'icon' => 'M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z'
    ],
    'linux' => [
        'name' => 'Linux',
        'pattern' => '/^Argo Books.*\.(deb|rpm|AppImage)$/i',
        'available' => false,
        'icon' => 'M12.504 0c-.155 0-.315.008-.48.021-4.226.333-3.105 4.807-3.17 6.298-.076 1.092-.3 1.953-1.05 3.02-.885 1.051-2.127 2.75-2.716 4.521-.278.832-.41 1.684-.287 2.489a.424.424 0 00-.11.135c-.26.268-.45.6-.663.839-.199.199-.485.267-.797.4-.313.136-.658.269-.864.68-.09.189-.136.394-.132.602 0 .199.027.4.055.536.058.399.116.728.04.97-.249.68-.28 1.145-.106 1.484.174.334.535.47.94.601.81.2 1.91.135 2.774.6.926.466 1.866.67 2.616.47.526-.116.97-.464 1.208-.946.587-.003 1.23-.269 2.26-.334.699-.058 1.574.267 2.577.2.025.134.063.198.114.333l.003.003c.391.778 1.113 1.132 1.884 1.071.771-.06 1.592-.536 2.257-1.306.631-.765 1.683-1.084 2.378-1.503.348-.199.629-.469.649-.853.023-.4-.2-.811-.714-1.376v-.097l-.003-.003c-.17-.2-.25-.535-.338-.926-.085-.401-.182-.786-.492-1.046h-.003c-.059-.054-.123-.067-.188-.135a.357.357 0 00-.19-.064c.431-1.278.264-2.55-.173-3.694-.533-1.41-1.465-2.638-2.175-3.483-.796-1.005-1.576-1.957-1.56-3.368.026-2.152.236-6.133-3.544-6.139z'
    ]
];

// Get available versions for a specific platform
function getOlderVersions($pattern)
{
    $versionsPath = '../resources/downloads/versions/';
    $versions = [];

    if (!is_dir($versionsPath)) {
        return $versions;
    }

    $versionFolders = scandir($versionsPath);

    foreach ($versionFolders as $folder) {
        if ($folder === '.' || $folder === '..') continue;

        $versionPath = $versionsPath . $folder . '/';

        if (!is_dir($versionPath)) continue;

        $files = scandir($versionPath);

        foreach ($files as $file) {
            if (preg_match($pattern, $file, $matches)) {
                $version = $matches[1];
                $filepath = $versionPath . $file;

                if (file_exists($filepath)) {
                    $versions[] = [
                        'version' => $version,
                        'filename' => $file,
                        'filepath' => $filepath,
                        'filesize' => filesize($filepath),
                        'modified' => filemtime($filepath)
                    ];
                }
                break;
            }
        }
    }

    // Sort versions in descending order
    usort($versions, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });

    // Remove the latest version
    if (count($versions) > 1) {
        array_shift($versions);
    } else {
        $versions = [];
    }

    return $versions;
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

// Get versions for each platform
$platformVersions = [];
foreach ($platforms as $key => $platform) {
    $platformVersions[$key] = getOlderVersions($platform['pattern']);
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
        content="Download older versions of Argo Books. Access previous releases for compatibility or testing purposes. software downloads.">
    <meta name="keywords"
        content="argo books older versions, previous releases, legacy versions, software downloads, version history, software">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Older Versions - Argo Books | Previous Releases">
    <meta property="og:description"
        content="Download older versions of Argo Books. Access previous releases for compatibility or testing purposes.">
    <meta property="og:url" content="https://argorobots.com/older-versions/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Books">
    <meta property="og:locale" content="en_CA">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Older Versions - Argo Books | Previous Releases">
    <meta name="twitter:description"
        content="Download older versions of Argo Books. Access previous releases for compatibility or testing purposes.">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="CA-AB">
    <meta name="geo.placename" content="Calgary">
    <meta name="geo.position" content="51.0447;-114.0719">
    <meta name="ICBM" content="51.0447, -114.0719">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://argorobots.com/older-versions/">

    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Older Versions - Argo Books | Previous Releases</title>

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
            <h1>Older Versions</h1>
            <p>Download previous releases of Argo Books</p>
        </div>
    </section>

    <div class="warning-box">
        <h3>⚠️ Important Notice</h3>
        <p><strong>We recommend using the <a href="../downloads">latest version</a>.</strong> Older versions may have
            security vulnerabilities, compatibility issues, or missing features. Only use previous versions if you have
            a specific need for it.</p>
    </div>

    <div class="container">
        <!-- Platform Tabs -->
        <div class="platform-tabs">
            <?php foreach ($platforms as $key => $platform): ?>
            <button class="platform-tab <?php echo $key === 'windows' ? 'active' : ''; ?>"
                    data-platform="<?php echo $key; ?>"
                    <?php echo !$platform['available'] ? 'disabled' : ''; ?>>
                <svg viewBox="0 0 24 24" fill="currentColor" class="tab-icon">
                    <path d="<?php echo $platform['icon']; ?>"/>
                </svg>
                <span><?php echo $platform['name']; ?></span>
                <?php if (!$platform['available']): ?>
                <span class="coming-soon-label">Coming Soon</span>
                <?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Platform Content -->
        <?php foreach ($platforms as $key => $platform): ?>
        <div class="platform-content <?php echo $key === 'windows' ? 'active' : ''; ?>" id="platform-<?php echo $key; ?>">
            <?php if (!$platform['available']): ?>
                <div class="version-card">
                    <div class="no-versions">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="platform-icon-large">
                            <path d="<?php echo $platform['icon']; ?>"/>
                        </svg>
                        <h3><?php echo $platform['name']; ?> Version Coming Soon</h3>
                        <p>We're working on bringing Argo Books to <?php echo $platform['name']; ?>.
                           Check back later for updates.</p>
                        <a href="../downloads" class="btn btn-blue" style="margin-top: 15px;">View All Downloads</a>
                    </div>
                </div>
            <?php elseif (empty($platformVersions[$key])): ?>
                <div class="version-card">
                    <div class="no-versions">
                        <h3>No Older Versions Available</h3>
                        <p>Currently, only the latest version is available for download. Check back later for access to
                            previous releases.</p>
                        <a href="../downloads" class="btn btn-blue" style="margin-top: 15px;">Download Latest Version</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="version-grid">
                    <?php foreach ($platformVersions[$key] as $version): ?>
                    <div class="version-download-card">
                        <div class="version-info">
                            <h3>Version <?php echo htmlspecialchars($version['version']); ?></h3>
                            <div class="version-meta">
                                <div class="meta-item">
                                    <strong>Released:</strong>
                                    <?php echo date('F j, Y', $version['modified']); ?>
                                </div>
                                <div class="meta-item">
                                    <strong>File Size:</strong>
                                    <?php echo formatFileSize($version['filesize']); ?>
                                </div>
                                <div class="meta-item">
                                    <strong>File:</strong>
                                    <?php echo htmlspecialchars($version['filename']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="download-section">
                            <a href="../resources/downloads/versions/<?php echo htmlspecialchars($version['version']); ?>/<?php echo urlencode($version['filename']); ?>"
                                class="btn btn-blue download-btn"
                                download="<?php echo htmlspecialchars($version['filename']); ?>"
                                title="Download Version <?php echo htmlspecialchars($version['version']); ?>"
                                data-version="<?php echo htmlspecialchars($version['version']); ?>"
                                data-platform="<?php echo $key; ?>">
                                Download V.<?php echo htmlspecialchars($version['version']); ?>
                            </a>
                            <div class="version-badge">
                                Legacy Version
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        // Platform tab switching
        document.querySelectorAll('.platform-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                if (this.disabled) return;

                // Update active tab
                document.querySelectorAll('.platform-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Update active content
                const platform = this.getAttribute('data-platform');
                document.querySelectorAll('.platform-content').forEach(c => c.classList.remove('active'));
                document.getElementById('platform-' + platform).classList.add('active');
            });
        });

        // Add download tracking
        document.querySelectorAll('.download-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const version = this.getAttribute('data-version');
                const platform = this.getAttribute('data-platform');
                if (version && typeof gtag !== 'undefined') {
                    gtag('event', 'download', {
                        'event_category': 'software',
                        'event_label': 'argo_books_v' + version + '_' + platform,
                        'version': version,
                        'platform': platform
                    });
                }
            });
        });
    </script>
</body>

</html>