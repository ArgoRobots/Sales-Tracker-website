<?php
// Start session to handle error messages if they exist
session_start();

// Get error message if exists
$error_message = '';
if (isset($_SESSION['contact_error'])) {
  $error_message = $_SESSION['contact_error'];
  unset($_SESSION['contact_error']); // Clear the error after retrieving
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
  <meta name="description" content="Contact Argo Sales Tracker support team in Calgary, Canada. Get help with sales tracking software, report bugs, request features, or ask questions. Fast response times within 1-8 business hours.">
  <meta name="keywords" content="contact argo sales tracker, customer support calgary, business software help, sales tracker support, contact form, technical support, calgary software company contact, customer service">

  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="Contact Us - Argo Sales Tracker Support">
  <meta property="og:description" content="Contact Argo Sales Tracker support team in Calgary, Canada. Get help with sales tracking software, report bugs, request features, or ask questions. Fast response times within 1-8 business hours.">
  <meta property="og:url" content="https://argorobots.com/contact-us/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Argo Sales Tracker">
  <meta property="og:locale" content="en_CA">

  <!-- Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Contact Us - Argo Sales Tracker Support">
  <meta name="twitter:description" content="Contact Argo Sales Tracker support team in Calgary, Canada. Get help with sales tracking software, report bugs, request features, or ask questions. Fast response times within 1-8 business hours.">

  <!-- Additional SEO Meta Tags -->
  <meta name="geo.region" content="CA-AB">
  <meta name="geo.placename" content="Calgary">
  <meta name="geo.position" content="51.0447;-114.0719">
  <meta name="ICBM" content="51.0447, -114.0719">

  <!-- Canonical URL -->
  <link rel="canonical" href="https://argorobots.com/contact-us/">

  <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
  <title>Contact Us - Argo Sales Tracker Support | Calgary Software Company</title>

  <?php include 'resources/head/google-analytics.php'; ?>

  <script src="../resources/scripts/jquery-3.6.0.js"></script>
  <script src="../resources/scripts/main.js"></script>
  <script src="main.js"></script>

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="../resources/styles/custom-colors.css">
  <link rel="stylesheet" href="../resources/header/style.css">
  <link rel="stylesheet" href="../resources/header/dark.css">
  <link rel="stylesheet" href="../resources/footer/style.css">
</head>

<body>
  <header>
    <div id="includeHeader"></div>
  </header>

  <section class="first">
    <div class="container">
      <h1 class="title">Contact Us</h1>

      <?php if (!empty($error_message)): ?>
        <div class="error-message">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <!-- Contact form using PHP for form submission handling -->
      <form action="contact_process.php" method="POST" id="my-form">
        <!-- Support container -->
        <div class="contact-us-container">
          <div class="support-container">
            <h2 class="sub-title">Support</h2>
            <p class="description">Contact us at <a href="mailto:support@argorobots.com">support@argorobots.com</a> for
              any support. We usually respond within 1-8 business hours.</p>
          </div>

          <!-- Feedback containers -->
          <div class="support-container">
            <h2 class="sub-title"> Leave feedback</h2>
            <p class="description">Provide feedback at <a
                href="mailto:feedback@argorobots.com">feedback@argorobots.com</a>.
              We're always striving to better serve our customers. You can influence our business and help us improve.
            </p>
          </div>
        </div>

        <!-- Additional information and links section -->
        <div class="contact-us-container">
          <div class="support-container-2">
            <h2 class="sub-title">Submit a question or comment</h2>
            <p class="description">Many common questions are answered on our <a
                href="../documentation/index.html">Documentation</a> and <a href="../about-us/index.html">About Us</a>
              pages.</p>
          </div>
          <div class="support-container-3"></div>
        </div>

        <!-- Contact form input fields -->
        <div class="wrapper">
          <!-- Personal information inputs -->
          <div class="input-container">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" maxlength="35" required>

            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName" maxlength="35" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
          </div>

          <!-- Message input and submit button -->
          <div class="question-container">
            <label for="message">Question / Comment</label>
            <textarea class="input-message" name="message" id="message" maxlength="3000" required></textarea>
            <button type="submit">SUBMIT</button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <footer class="footer">
    <div id="includeFooter"></div>
  </footer>
</body>

</html>