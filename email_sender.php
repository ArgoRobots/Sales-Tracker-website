<?php
/**
 * Send license key via email using FormSpree
 * 
 * @param string $to_email Recipient email address
 * @param string $license_key The license key to send
 * @return bool True if successful, false otherwise
 */
function send_license_email($to_email, $license_key) {
    $formspree_id = 'mqkggyyd';
    $formspree_url = "https://formspree.io/f/{$formspree_id}";
    
    // Prepare email content
    $email_html = get_license_email_template($to_email, $license_key);
    
    // FormSpree data
    $data = [
        'email' => $to_email,
        'subject' => 'Your Argo Sales Tracker License Key',
        'message' => $email_html,
        '_template' => 'box'
    ];
    
    // Use cURL to send the email
    $curl = curl_init($formspree_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return $status >= 200 && $status < 300;
}

/**
 * Get HTML email template for license key
 * 
 * @param string $email Recipient email
 * @param string $license_key The license key
 * @return string HTML email content
 */
function get_license_email_template($email, $license_key) {
    // Base URL for images - update this to your actual domain
    $base_url = 'https://yourdomain.com';
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Your Argo Sales Tracker License</title>
    <style>
        @media only screen and (max-width: 620px) {
            table.body h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }
            
            table.body p,
            table.body ul,
            table.body ol,
            table.body td,
            table.body span,
            table.body a {
                font-size: 16px !important;
            }
            
            table.body .wrapper,
            table.body .article {
                padding: 10px !important;
            }
            
            table.body .content {
                padding: 0 !important;
            }
            
            table.body .container {
                padding: 0 !important;
                width: 100% !important;
            }
            
            table.body .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }
            
            table.body .btn table {
                width: 100% !important;
            }
            
            table.body .btn a {
                width: 100% !important;
            }
            
            table.body .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }
        
        @media all {
            .ExternalClass {
                width: 100%;
            }
            
            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }
            
            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important;
            }
            
            #MessageViewBody a {
                color: inherit;
                text-decoration: none;
                font-size: inherit;
                font-family: inherit;
                font-weight: inherit;
                line-height: inherit;
            }
            
            .btn-primary table td:hover {
                background-color: #1e40af !important;
            }
            
            .btn-primary a:hover {
                background-color: #1e40af !important;
                border-color: #1e40af !important;
            }
        }
    </style>
</head>
<body style="background-color: #f6f9fc; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f6f9fc; width: 100%;" width="100%" bgcolor="#f6f9fc">
        <tr>
            <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;" valign="top">&nbsp;</td>
            <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; max-width: 580px; padding: 10px; width: 580px; margin: 0 auto;" width="580" valign="top">
                <div class="header" style="padding: 20px 0; text-align: center;">
                    <img src="{$base_url}/images/argo-logo/A-logo.png" alt="Argo Sales Tracker" width="48" height="48">
                </div>
                <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 580px; padding: 10px;">
                    <!-- START CENTERED WHITE CONTAINER -->
                    <table role="presentation" class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border-radius: 3px; width: 100%;" width="100%">
                        <!-- START MAIN CONTENT AREA -->
                        <tr>
                            <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;" valign="top">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                                    <tr>
                                        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;" valign="top">
                                            <div style="text-align: center; margin-bottom: 24px;">
                                                <h1 style="color: #1e3a8a; font-family: sans-serif; font-weight: 700; margin: 0; margin-bottom: 20px;">Thank You for Your Purchase!</h1>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">Here is your Argo Sales Tracker license key:</p>
                                            </div>
                                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-family: monospace; font-size: 18px; letter-spacing: 1px; margin: 0 auto 25px; max-width: 350px; padding: 16px; text-align: center;">{$license_key}</div>
                                            <div style="text-align: center; margin-bottom: 15px;">
                                                <h2 style="color: #1e3a8a; font-family: sans-serif; font-weight: 500; margin: 0; margin-bottom: 15px;">How to Activate Your License</h2>
                                                <ol style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px; text-align: left; padding-left: 20px; max-width: 350px; margin-left: auto; margin-right: auto;">
                                                    <li style="margin-bottom: 10px;">Open Argo Sales Tracker on your computer</li>
                                                    <li style="margin-bottom: 10px;">Go to Settings > License</li>
                                                    <li style="margin-bottom: 10px;">Enter your license key</li>
                                                    <li style="margin-bottom: 10px;">Enjoy all premium features!</li>
                                                </ol>
                                            </div>
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; box-sizing: border-box; width: 100%;" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td align="center" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;" valign="top">
                                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; border-radius: 5px; text-align: center; background-color: #2563eb;" valign="top" align="center" bgcolor="#2563eb">
                                                                            <a href="{$base_url}/documentation/index.html" target="_blank" style="border: solid 1px #2563eb; border-radius: 5px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-decoration: none; text-transform: capitalize; background-color: #2563eb; border-color: #2563eb; color: #ffffff;">View Documentation</a>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 15px;">If you have any questions or need assistance, please don't hesitate to <a href="{$base_url}/contact-us/index.html" style="color: #2563eb; text-decoration: underline;">contact our support team</a>.</p>
                                            <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 15px;">Thank you for choosing Argo Sales Tracker!</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- END MAIN CONTENT AREA -->
                    </table>
                    <!-- END CENTERED WHITE CONTAINER -->
                    <!-- START FOOTER -->
                    <div class="footer" style="clear: both; margin-top: 10px; text-align: center; width: 100%;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                            <tr>
                                <td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; color: #999999; font-size: 12px; text-align: center;" valign="top" align="center">
                                    <span class="apple-link" style="color: #999999; font-size: 12px; text-align: center;">Argo Sales Tracker &copy; 2025. All rights reserved.</span>
                                    <br>
                                    <span style="color: #999999; font-size: 12px; text-align: center;">This email was sent to {$email}.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- END FOOTER -->
                </div>
            </td>
            <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;" valign="top">&nbsp;</td>
        </tr>
    </table>
</body>
</html>
HTML;
}