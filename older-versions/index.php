<?php
// Get available versions from filesystem
function getOlderVersions()
{
    $downloadPath = '../resources/downloads/';
    $versions = [];

    // Debug: Check if directory exists
    if (!is_dir($downloadPath)) {
        error_log("Directory does not exist: " . $downloadPath);
        return $versions;
    }

    // Scan the downloads directory
    $files = scandir($downloadPath);

    foreach ($files as $file) {
        // Look for installer files with the pattern "Argo Sales Tracker Installer V.{version}.exe"
        if (preg_match('/^Argo Sales Tracker Installer V\.(.+)\.exe$/i', $file, $matches)) {
            $version = $matches[1];
            $filepath = $downloadPath . $file;

            if (file_exists($filepath)) {
                $filesize = filesize($filepath);
                $modified = filemtime($filepath);

                $versions[] = [
                    'version' => $version,
                    'filename' => $file,
                    'filepath' => $filepath,
                    'filesize' => $filesize,
                    'modified' => $modified,
                    'release_date' => date('Y-m-d', $modified)
                ];
            }
        }
    }

    // Debug: Log found versions
    error_log("Found versions: " . print_r(array_column($versions, 'version'), true));

    // Sort versions in descending order
    usort($versions, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });

    // Only remove the latest version if we have more than 2 versions
    // This ensures we show at least one older version
    if (count($versions) > 2) {
        array_shift($versions); // Remove the latest version
    } elseif (count($versions) == 2) {
        array_shift($versions); // Remove latest, show the one older version
    } else {
        // If only one version or none, don't show anything
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

$older_versions = getOlderVersions();
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
        content="Download older versions of Argo Sales Tracker. Access previous releases for compatibility or testing purposes. Windows software downloads.">
    <meta name="keywords"
        content="argo sales tracker older versions, previous releases, legacy versions, software downloads, version history, Windows software">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Older Versions - Argo Sales Tracker | Previous Releases">
    <meta property="og:description"
        content="Download older versions of Argo Sales Tracker. Access previous releases for compatibility or testing purposes.">
    <meta property="og:url" content="https://argorobots.com/older-versions/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Argo Sales Tracker">
    <meta property="og:locale" content="en_CA">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Older Versions - Argo Sales Tracker | Previous Releases">
    <meta name="twitter:description"
        content="Download older versions of Argo Sales Tracker. Access previous releases for compatibility or testing purposes.">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.region" content="CA-AB">
    <meta name="geo.placename" content="Calgary">
    <meta name="geo.position" content="51.0447;-114.0719">
    <meta name="ICBM" content="51.0447, -114.0719">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://argorobots.com/older-versions/">

    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Older Versions - Argo Sales Tracker | Previous Releases</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../resources/scripts/main.js"></script>

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
        <div class="description-container">
            <h1>Older Versions</h1>
            <p>Download previous releases of Argo Sales Tracker</p>
        </div>
    </section>

    <div class="warning-box">
        <h3>⚠️ Important Notice</h3>
        <p><strong>We recommend using the <a href="../download">latest version</a>.</strong> Older versions may have
            security vulnerabilities, compatibility issues, or missing features. Only use previous versions if you have
            a specific need for it.</p>
    </div>

    <div class="container">
        <div class="version-grid">
            <?php if (empty($older_versions)): ?>
                <div class="version-card">
                    <div class="no-versions">
                        <h3>No Older Versions Available</h3>
                        <p>Currently, only the latest version is available for download. Check back later for access to
                            previous releases.</p>
                        <a href="../download" class="btn btn-blue btn-large" style="margin-top: 15px;">Download Latest Version</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($older_versions as $version): ?>
                    <div class="version-download-card">
                        <div class="version-info">
                            <h3>Version
                                <?php echo htmlspecialchars($version['version']); ?>
                            </h3>
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
                            <a href="resources/downloads/<?php echo urlencode($version['filename']); ?>"
                                class="btn btn-blue download-btn"
                                download="<?php echo htmlspecialchars($version['filename']); ?>"
                                title="Download Version <?php echo htmlspecialchars($version['version']); ?>">
                                ⬇ Download V.<?php echo htmlspecialchars($version['version']); ?>
                            </a>
                            <div class="version-badge">
                                Legacy Version
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>

    <script>
        // Add download tracking and confirmation
        document.querySelectorAll('.download-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const version = this.textContent.match(/v([\d\.]+)/);
                if (version) {
                    // Show confirmation for older version downloads
                    const confirmed = confirm(
                        'You are downloading an older version (v' + version[1] + ') of Argo Sales Tracker.\n\n' +
                        'We recommend using the latest version for the best experience and security.\n\n' +
                        'Continue with download?'
                    );

                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }

                    // Track download event if analytics is available
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'download', {
                            'event_category': 'software',
                            'event_label': 'argo_sales_tracker_v' + version[1],
                            'version': version[1]
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>