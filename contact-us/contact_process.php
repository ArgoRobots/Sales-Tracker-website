<?php

/**
 * Processes the contact form submission and sends an email
 */

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($message)) {
        $response['message'] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        // Send the email
        $subject = "Argo Sales Tracker Contact: {$firstName} {$lastName}";

        // Build HTML email content
        $email_html = get_contact_email_template($firstName, $lastName, $email, $message);

        // Email headers for HTML email
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Argo Sales Tracker Website <noreply@argorobots.com>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion()
        ];

        $to_email = 'contact@argorobots.com';

        // Send the email
        $mail_result = mail($to_email, $subject, $email_html, implode("\r\n", $headers));

        if ($mail_result) {
            $response['success'] = true;
            $response['message'] = 'Message sent successfully!';
        } else {
            $response['message'] = 'Failed to send message. Please try again or contact support directly.';
        }
    }
}

// Send JSON response if this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else if ($response['success']) {
    // Redirect to success page for non-AJAX requests
    header('Location: message-sent-successfully/index.html');
    exit;
} else if (!empty($response['message'])) {
    // Store error message in session for non-AJAX requests
    session_start();
    $_SESSION['contact_error'] = $response['message'];
    header('Location: index.php');
    exit;
}

/**
 * Get HTML email template for contact form
 * 
 * @param string $firstName Sender's first name
 * @param string $lastName Sender's last name
 * @param string $email Sender's email
 * @param string $message Message content
 * @return string HTML email content
 */
function get_contact_email_template($firstName, $lastName, $email, $message)
{
    // Format message with line breaks
    $formatted_message = nl2br(htmlspecialchars($message));

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Contact Form Submission</title>
    <style>
        body {
            background-color: #f6f9fc;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        
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
        
        .header {
            background-color: var(--blueText);
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            color: #ffffff;
            font-size: 22px;
        }
        
        .content {
            padding: 30px;
            color: #333;
        }
        
        .field {
            margin-bottom: 20px;
        }
        
        .field-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: var(--blueText);
        }
        
        .field-value {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
        }
        
        .message-content {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 15px;
            margin-top: 5px;
        }
        
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Contact Form Submission</h1>
        </div>
        
        <div class="content">
            <div class="field">
                <span class="field-label">Name:</span>
                <div class="field-value">{$firstName} {$lastName}</div>
            </div>
            
            <div class="field">
                <span class="field-label">Email:</span>
                <div class="field-value">{$email}</div>
            </div>
            
            <div class="field">
                <span class="field-label">Message:</span>
                <div class="message-content">{$formatted_message}</div>
            </div>
        </div>
        
        <div class="footer">
            <p>This message was sent from the Argo Sales Tracker contact form.</p>
            <p>To reply, simply respond to this email which will go to: {$email}</p>
        </div>
    </div>
</body>
</html>
HTML;
}
