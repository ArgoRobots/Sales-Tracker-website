<?php
// Set headers for API response
header('Content-Type: application/json');

// Include the license functions
require_once 'license_functions.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request method'
];

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the license key from the request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['license_key'])) {
        $license_key = trim($data['license_key']);
        
        // Verify the license key
        if (verify_license_key($license_key)) {
            // Get license details to check if it's already activated
            $license_details = get_license_details($license_key);
            
            if ($license_details['activated']) {
                $response = [
                    'success' => true,
                    'activated' => true,
                    'message' => 'License key is valid and already activated.',
                    'activation_date' => $license_details['activation_date']
                ];
            } else {
                // Activate the license
                $ip_address = $_SERVER['REMOTE_ADDR'];
                if (activate_license_key($license_key, $ip_address)) {
                    $response = [
                        'success' => true,
                        'activated' => true,
                        'message' => 'License key activated successfully.',
                        'activation_date' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Failed to activate license key.'
                    ];
                }
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid license key.'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'License key is required.'
        ];
    }
}

// Return the JSON response
echo json_encode($response);