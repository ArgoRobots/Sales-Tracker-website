<?php
session_start();
require_once '../../db_connect.php';
require_once '../community_functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../images/argo-logo/A-logo.ico">
    <title>Reputation System - Argo Community</title>

    <script src="../../resources/scripts/jquery-3.6.0.js"></script>
    <script src="../../resources/scripts/main.js"></script>

    <link rel="stylesheet" href="reputation-help.css">
    <link rel="stylesheet" href="../../resources/styles/button.css">
    <link rel="stylesheet" href="../../resources/styles/custom-colors.css">
    <link rel="stylesheet" href="../../resources/header/style.css">
    <link rel="stylesheet" href="../../resources/header/dark.css">
    <link rel="stylesheet" href="../../resources/footer/style.css">
</head>

<body>
    <header>
        <div id="includeHeader"></div>
    </header>

    <div class="reputation-help-container">
        <div class="reputation-help-header">
            <h1>The Argo Community Reputation System</h1>
            <p>Understanding how reputation works and ways to earn it</p>
        </div>

        <div class="reputation-section full-padding">
            <h2>What is Reputation?</h2>
            <p>Reputation is a measurement of how much the community trusts and values your contributions. As you participate in the Argo Community by submitting bug reports, suggesting features, and helping others, you'll earn reputation points that reflect your contributions to the community.</p>
            <p>Your reputation score is displayed on your profile and next to your username throughout the community. Higher reputation indicates your level of expertise and helpfulness.</p>
        </div>

        <div class="reputation-section">
            <h2>How to Earn Reputation</h2>
            <p>There are several ways to earn (or lose) reputation in the Argo Community:</p>

            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Reputation Change</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Your post receives an upvote</td>
                        <td class="rep-positive">+10</td>
                    </tr>
                    <tr>
                        <td>Your post receives a downvote</td>
                        <td class="rep-negative">-5</td>
                    </tr>
                    <tr>
                        <td>Your comment receives an upvote</td>
                        <td class="rep-positive">+2</td>
                    </tr>
                    <tr>
                        <td>Your comment receives a downvote</td>
                        <td class="rep-negative">-1</td>
                    </tr>
                    <tr>
                        <td>You downvote someone else's post</td>
                        <td class="rep-negative">-2</td>
                    </tr>
                </tbody>
            </table>

            <div class="rep-tip">
                <h4>Why do I lose reputation when I downvote?</h4>
                <p>Downvoting comes with a small reputation cost to prevent abuse and encourage constructive feedback. The -2 reputation cost for downvoting helps ensure that users only downvote when they genuinely believe content isn't helpful or appropriate.</p>
            </div>
        </div>

        <div class="reputation-section">
            <h2>Reputation Example</h2>
            <div class="rep-example">
                <h3>How a user might earn 100 reputation</h3>
                <p>Create a helpful bug report that receives 8 upvotes: <span class="rep-positive">+80 reputation</span></p>
                <p>Write 3 detailed comments that each receive 2 upvotes: <span class="rep-positive">+12 reputation</span></p>
                <p>Post a feature request that receives 2 upvotes and 1 downvote: <span class="rep-positive">+15 reputation</span></p>
                <p>Downvote 3 low-quality posts: <span class="rep-negative">-6 reputation</span></p>
                <p>Receive 1 downvote on a comment: <span class="rep-negative">-1 reputation</span></p>
                <p><strong>Total: +100 reputation</strong></p>
            </div>
        </div>

        <div class="reputation-section">
            <h2>Why Reputation Matters</h2>
            <p>Reputation serves several important purposes in our community:</p>
            <ul class="bullet-list">
                <li><strong>Trust indicator:</strong> It helps other users quickly identify experienced community members.</li>
                <li><strong>Quality control:</strong> The voting system helps surface the most helpful content while filtering out less useful contributions.</li>
                <li><strong>Gamification:</strong> Earning reputation provides an incentive for continued participation and quality contributions.</li>
                <li><strong>Community recognition:</strong> Your reputation reflects your positive impact on the Argo community.</li>
            </ul>
        </div>

        <div class="reputation-section">
            <h2>Tips for Earning Reputation</h2>
            <ul class="bullet-list">
                <li><strong>Be specific and detailed</strong> in your bug reports and feature requests</li>
                <li><strong>Provide clear steps to reproduce</strong> when reporting bugs</li>
                <li><strong>Include screenshots or diagrams</strong> when they help explain the issue</li>
                <li><strong>Be respectful and constructive</strong> in your comments</li>
                <li><strong>Help others</strong> by providing insights on their bug reports or feature requests</li>
                <li><strong>Keep content relevant</strong> to Argo and its community</li>
            </ul>
        </div>

        <a href="profile.php" class="btn btn-blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Profile
        </a>
    </div>

    <footer class="footer">
        <div id="includeFooter"></div>
    </footer>
</body>

</html>