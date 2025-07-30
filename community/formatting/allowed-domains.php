<?php
session_start();
require_once '../../db_connect.php';
require_once 'formatting_functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Allowed Domains - Argo Community</title>

    <?php include 'resources/head/google-analytics.php'; ?>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="allowed-domains.css">
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

    <div class="domains-container">
        <a href="help.php" class="link-no-underline back-link">← Back to Guide</a>

        <div class="domains-header">
            <h1>Allowed Domains for Links</h1>
            <p>For security reasons, links are only permitted to the domains listed below. Links to other domains will be displayed as text only.</p>
            <input type="text" class="search-box" id="domainSearch" placeholder="Search domains...">
        </div>

        <div class="domains-category">
            <h3>Company Domain</h3>
            <ul class="domains-list">
                <li>argorobots.com</li>
            </ul>
        </div>

        <div class="domains-category">
            <h3>Educational & Reference</h3>
            <ul class="domains-list">
                <li>wikipedia.org</li>
                <li>w3schools.com</li>
                <li>developer.mozilla.org</li>
                <li>docs.microsoft.com</li>
                <li>dev.to</li>
                <li>medium.com</li>
            </ul>
            <div class="pattern-note">
                <strong>Note:</strong> Wikipedia subdomains for different languages (e.g., en.wikipedia.org, fr.wikipedia.org) are also allowed.
            </div>
        </div>

        <div class="domains-category">
            <h3>Developer Communities & Q&A</h3>
            <ul class="domains-list">
                <li>stackoverflow.com</li>
                <li>stackexchange.com</li>
                <li>superuser.com</li>
                <li>serverfault.com</li>
                <li>askubuntu.com</li>
                <li>mathoverflow.net</li>
                <li>reddit.com</li>
                <li>news.ycombinator.com</li>
            </ul>
            <div class="pattern-note">
                <strong>Note:</strong> Stack Exchange network sites (e.g., gaming.stackexchange.com, meta.stackoverflow.com) are also allowed.
            </div>
        </div>

        <div class="domains-category">
            <h3>Code Repositories & Development Tools</h3>
            <ul class="domains-list">
                <li>github.com</li>
                <li>gitlab.com</li>
                <li>bitbucket.org</li>
                <li>codepen.io</li>
                <li>jsfiddle.net</li>
                <li>replit.com</li>
                <li>codesandbox.io</li>
            </ul>
            <div class="pattern-note">
                <strong>Note:</strong> GitHub Pages (*.github.io) and GitLab Pages (*.gitlab.io) are also allowed.
            </div>
        </div>

        <div class="domains-category">
            <h3>Google Services</h3>
            <ul class="domains-list">
                <li>google.com</li>
                <li>youtube.com</li>
                <li>googledocs.com</li>
                <li>googleusercontent.com</li>
                <li>google.dev</li>
                <li>cloud.google.com</li>
            </ul>
            <div class="pattern-note">
                <strong>Note:</strong> Google country domains (e.g., google.co.uk, google.ca) and Google subdomains are also allowed.
            </div>
        </div>

        <div class="domains-category">
            <h3>Microsoft Services</h3>
            <ul class="domains-list">
                <li>microsoft.com</li>
                <li>visualstudio.com</li>
                <li>azure.microsoft.com</li>
            </ul>
            <div class="pattern-note">
                <strong>Note:</strong> Microsoft subdomains (e.g., docs.microsoft.com, learn.microsoft.com) are also allowed.
            </div>
        </div>

        <div class="domains-category">
            <h3>Programming Languages & Frameworks</h3>
            <ul class="domains-list">
                <li>php.net</li>
                <li>python.org</li>
                <li>nodejs.org</li>
                <li>reactjs.org</li>
                <li>vuejs.org</li>
                <li>angular.io</li>
                <li>laravel.com</li>
                <li>symfony.com</li>
                <li>wordpress.org</li>
                <li>jquery.com</li>
            </ul>
        </div>

        <div class="domains-category">
            <h3>Package Managers & Libraries</h3>
            <ul class="domains-list">
                <li>npmjs.com</li>
                <li>packagist.org</li>
                <li>pypi.org</li>
                <li>nuget.org</li>
                <li>mvnrepository.com</li>
                <li>crates.io</li>
            </ul>
        </div>

        <div class="domains-category">
            <h3>Cloud Platforms & Hosting</h3>
            <ul class="domains-list">
                <li>aws.amazon.com</li>
                <li>digitalocean.com</li>
                <li>heroku.com</li>
                <li>netlify.com</li>
                <li>vercel.com</li>
            </ul>
            <div class="pattern-note">
                <strong>Note:</strong> Netlify (*.netlify.app), Vercel (*.vercel.app), and AWS subdomains are also allowed.
            </div>
        </div>

        <div class="domains-category">
            <h3>Development & DevOps Tools</h3>
            <ul class="domains-list">
                <li>postman.com</li>
                <li>insomnia.rest</li>
                <li>docker.com</li>
                <li>kubernetes.io</li>
                <li>jenkins.io</li>
                <li>atlassian.com</li>
            </ul>
        </div>

        <div class="domains-category">
            <h3>Tech News & Publications</h3>
            <ul class="domains-list">
                <li>techcrunch.com</li>
                <li>arstechnica.com</li>
                <li>wired.com</li>
                <li>theverge.com</li>
            </ul>
        </div>

        <div class="domains-category">
            <h3>Standards & Specifications</h3>
            <ul class="domains-list">
                <li>w3.org</li>
                <li>ietf.org</li>
                <li>whatwg.org</li>
                <li>ecma-international.org</li>
            </ul>
        </div>

        <div class="domains-category">
            <h3>Security & Best Practices</h3>
            <ul class="domains-list">
                <li>owasp.org</li>
                <li>cve.mitre.org</li>
                <li>nvd.nist.gov</li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('domainSearch');
            const categories = document.querySelectorAll('.domains-category');

            searchBox.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                categories.forEach(category => {
                    const domains = category.querySelectorAll('.domains-list li');
                    let hasVisibleDomains = false;

                    domains.forEach(domain => {
                        const domainText = domain.textContent.toLowerCase();
                        if (domainText.includes(searchTerm)) {
                            domain.style.display = '';
                            hasVisibleDomains = true;
                        } else {
                            domain.style.display = 'none';
                        }
                    });

                    // Hide category if no domains match
                    category.style.display = hasVisibleDomains || searchTerm === '' ? '' : 'none';
                });
            });
        });
    </script>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>