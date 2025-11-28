<?php
// Start session to handle error messages if they exist
session_start();

// Get error/success messages if exists
$error_message = '';
$success_message = '';
if (isset($_SESSION['contact_error'])) {
  $error_message = $_SESSION['contact_error'];
  unset($_SESSION['contact_error']);
}
if (isset($_SESSION['contact_success'])) {
  $success_message = $_SESSION['contact_success'];
  unset($_SESSION['contact_success']);
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
    content="Contact Argo Books support team in Calgary, Canada. Get help with sales tracking software, report bugs, request features, or ask questions. Fast response times within 1-8 business hours.">
  <meta name="keywords"
    content="contact argo books, customer support calgary, business software help, finance tracker, sales tracker support, contact form, technical support, calgary software company contact, customer service">

  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="Contact Us - Argo Books Support">
  <meta property="og:description"
    content="Contact Argo Books support team in Calgary, Canada. Get help with sales tracking software, report bugs, request features, or ask questions. Fast response times within 1-8 business hours.">
  <meta property="og:url" content="https://argorobots.com/contact-us/">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Argo Books">
  <meta property="og:locale" content="en_CA">

  <!-- Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Contact Us - Argo Books Support">
  <meta name="twitter:description"
    content="Contact Argo Books support team in Calgary, Canada. Get help with sales tracking software, report bugs, request features, or ask questions. Fast response times within 1-8 business hours.">

  <!-- Additional SEO Meta Tags -->
  <meta name="geo.region" content="CA-AB">
  <meta name="geo.placename" content="Calgary">
  <meta name="geo.position" content="51.0447;-114.0719">
  <meta name="ICBM" content="51.0447, -114.0719">

  <!-- Canonical URL -->
  <link rel="canonical" href="https://argorobots.com/contact-us/">

  <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
  <title>Contact Us - Argo Books Support | Calgary Software Company</title>

  <script src="../resources/scripts/jquery-3.6.0.js"></script>
  <script src="../resources/scripts/main.js"></script>

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

  <!-- Hero Section -->
  <section class="contact-hero">
    <div class="hero-bg">
      <div class="hero-gradient-orb hero-orb-1"></div>
      <div class="hero-gradient-orb hero-orb-2"></div>
    </div>
    <div class="container">
      <h1 class="animate-fade-in">Get in Touch</h1>
      <p class="hero-subtitle animate-fade-in">Have a question or need help? We're here for you.</p>
    </div>
  </section>

  <!-- Contact Options -->
  <section class="contact-options">
    <div class="container">
      <div class="options-grid">
        <div class="option-card animate-on-scroll">
          <div class="option-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <h3>Email Support</h3>
          <p>Get help with technical issues, account questions, or general inquiries.</p>
          <a href="mailto:support@argorobots.com" class="option-link">
            support@argorobots.com
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </a>
          <span class="response-time">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 6v6l4 2"/>
            </svg>
            1-8 business hours
          </span>
        </div>

        <div class="option-card animate-on-scroll">
          <div class="option-icon feedback">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"/>
            </svg>
          </div>
          <h3>Send Feedback</h3>
          <p>Share ideas, feature requests, or suggestions to help us improve Argo Books.</p>
          <a href="mailto:feedback@argorobots.com" class="option-link">
            feedback@argorobots.com
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </a>
          <span class="response-time">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>
            </svg>
            We read every message
          </span>
        </div>

        <div class="option-card animate-on-scroll">
          <div class="option-icon community">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <h3>Community Forum</h3>
          <p>Connect with other users, share tips, and get help from the community.</p>
          <a href="/community/" class="option-link">
            Visit Community
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </a>
          <span class="response-time">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
            </svg>
            Active community
          </span>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Form Section -->
  <section class="contact-form-section">
    <div class="container">
      <div class="form-wrapper animate-on-scroll">
        <div class="form-header">
          <h2>Send us a Message</h2>
          <p>Fill out the form below and we'll get back to you as soon as possible.</p>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="success-message">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="error-message">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <form action="contact_process.php" method="POST" id="contact-form">
          <div class="form-row">
            <div class="form-group">
              <label for="firstName">First Name</label>
              <input type="text" id="firstName" name="firstName" maxlength="35" placeholder="John" required>
            </div>
            <div class="form-group">
              <label for="lastName">Last Name</label>
              <input type="text" id="lastName" name="lastName" maxlength="35" placeholder="Doe" required>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" required>
          </div>

          <div class="form-group">
            <label for="subject">Subject</label>
            <select id="subject" name="subject">
              <option value="general">General Inquiry</option>
              <option value="support">Technical Support</option>
              <option value="billing">Billing Question</option>
              <option value="feature">Feature Request</option>
              <option value="bug">Bug Report</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" maxlength="3000" rows="6" placeholder="How can we help you?" required></textarea>
          </div>

          <button type="submit" class="submit-btn">
            <span>Send Message</span>
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13"/>
              <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </form>
      </div>

      <div class="form-sidebar animate-on-scroll">
        <div class="sidebar-card">
          <div class="sidebar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <h4>Check the Docs First</h4>
          <p>Many common questions are already answered in our documentation.</p>
          <a href="/documentation/" class="sidebar-link">View Documentation</a>
        </div>

        <div class="sidebar-card">
          <div class="sidebar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <h4>Based in Calgary</h4>
          <p>We're a Canadian company proudly serving businesses worldwide.</p>
          <span class="location-badge">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            Calgary, Alberta, Canada
          </span>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div id="includeFooter"></div>
  </footer>

  <script>
    // Scroll animations
    document.addEventListener('DOMContentLoaded', function() {
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-visible');
          }
        });
      }, observerOptions);

      document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
      });
    });
  </script>
</body>

</html>
