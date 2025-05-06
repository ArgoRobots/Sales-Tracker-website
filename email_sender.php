<?php

/**
 * Send license key via email using PHP mail
 * 
 * @param string $to_email Recipient email address
 * @param string $license_key The license key to send
 * @return bool True if successful, false otherwise
 */
function send_license_email($to_email, $license_key)
{
    // Email subject
    $subject = 'Your Argo Sales Tracker License Key';

    // Email HTML content
    $email_html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>Your Argo Sales Tracker License</title>
            <style>
                /* Base styles */
                body {
                    background-color: #f6f9fc;
                    font-family: 'Segoe UI', Arial, sans-serif;
                    font-size: 14px;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                    -webkit-font-smoothing: antialiased;
                }
                
                /* Container styles */
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    margin-top: 20px;
                    margin-bottom: 20px;
                }
                
                /* Header styles */
                .header {
                    background-color: #1e3a8a;
                    padding: 20px;
                    text-align: center;
                }
                
                .header img {
                    width: 100px;
                    height: auto;
                }
                
                /* Content styles */
                .content {
                    padding: 30px;
                    color: #333;
                }
                
                h1 {
                    color: #1e3a8a;
                    font-size: 24px;
                    margin-top: 0;
                    text-align: center;
                }
                
                .license-key {
                    background-color: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 4px;
                    font-family: monospace;
                    font-size: 18px;
                    letter-spacing: 1px;
                    margin: 25px auto;
                    max-width: 400px;
                    padding: 16px;
                    text-align: center;
                }
                
                /* Steps section */
                .steps {
                    margin: 25px 0;
                }
                
                .steps h2 {
                    color: #1e3a8a;
                    font-size: 18px;
                    margin-bottom: 15px;
                    text-align: center;
                }
                
                .steps ol {
                    margin-left: 20px;
                    padding-left: 0;
                }
                
                .steps li {
                    margin-bottom: 10px;
                }
                
                /* Button styles */
                .button {
                    background-color: #2563eb;
                    border-radius: 4px;
                    color: #ffffff;
                    display: inline-block;
                    font-weight: bold;
                    margin-top: 15px;
                    padding: 12px 24px;
                    text-decoration: none;
                    text-align: center;
                }
                
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
                
                /* Footer styles */
                .footer {
                    background-color: #f8fafc;
                    border-top: 1px solid #e2e8f0;
                    color: #64748b;
                    font-size: 12px;
                    padding: 20px;
                    text-align: center;
                }
                
                /* Mobile responsive */
                @media only screen and (max-width: 620px) {
                    .container {
                        width: 100% !important;
                        margin-top: 0;
                        margin-bottom: 0;
                        border-radius: 0;
                    }
                    
                    .content {
                        padding: 20px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://argorobots.com/images/argo-logo/Argo-white.svg" alt="Argo Sales Tracker Logo">
                </div>
                
                <div class="content">
                    <h1>Thank You for Your Purchase!</h1>
                    <p>Here is your Argo Sales Tracker license key:</p>
                    
                    <div class="license-key">{$license_key}</div>
                    
                    <div class="steps">
                        <h2>How to Activate Your License</h2>
                        <ol>
                            <li>Open Argo Sales Tracker on your computer</li>
                            <li>Click the blue upgrade button on the top right</li>
                            <li>Enter your license key</li>
                            <li>Enjoy unlimited access to all premium features!</li>
                        </ol>
                    </div>
                    
                    <div class="button-container">
                        <a href="https://argorobots.com/documentation/index.html" class="button">View Documentation</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to <a href="https://argorobots.com/contact-us/index.php" style="color: #2563eb;">contact our support team</a>.</p>
                    <p>Thank you for choosing Argo Sales Tracker!</p>
                </div>
                
                <div class="footer">
                    <p>Argo Sales Tracker &copy; 2025. All rights reserved.</p>
                    <p>This email was sent to {$to_email}</p>
                </div>
            </div>
        </body>
        </html>
    HTML;

    // Email headers for HTML email
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Argo Sales Tracker <noreply@argorobots.com>',
        'Reply-To: support@argorobots.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Send the email
    $mail_result = mail($to_email, $subject, $email_html, implode("\r\n", $headers));

    return $mail_result;
}
