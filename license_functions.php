<?php
require_once 'db_connect.php';

if (!function_exists('generate_license_key')) {
    /**
     * Generate a random license key
     * 
     * @return string A 20-character alphanumeric license key
     */
    function generate_license_key()
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key_length = 20;
        $key = '';

        // Format: XXXXX-XXXXX-XXXXX-XXXXX
        for ($i = 0; $i < $key_length; $i++) {
            if ($i > 0 && $i % 5 == 0) {
                $key .= '-';
            }
            $key .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $key;
    }

    /**
     * Check if a license key exists in the database
     * 
     * @param string $key The license key to check
     * @return bool True if the key exists, false otherwise
     */
    function license_key_exists($key)
    {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM license_keys WHERE license_key = ?');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] > 0;
    }

    /**
     * Store a new license key in the database
     *
     * @param string $email The email associated with the license
     * @param int|null $user_id Optional user ID to link the license to an account
     * @return string The generated license key
     */
    function create_license_key($email, $user_id = null)
    {
        $db = get_db_connection();

        // Generate a unique key
        do {
            $key = generate_license_key();
        } while (license_key_exists($key));

        // Store the key in the database
        if ($user_id !== null) {
            $stmt = $db->prepare('INSERT INTO license_keys (license_key, email, user_id) VALUES (?, ?, ?)');
            $stmt->bind_param('ssi', $key, $email, $user_id);
        } else {
            $stmt = $db->prepare('INSERT INTO license_keys (license_key, email) VALUES (?, ?)');
            $stmt->bind_param('ss', $key, $email);
        }
        $stmt->execute();
        $stmt->close();

        return $key;
    }

    /**
     * Verify if a license key is valid
     * 
     * @param string $key The license key to verify
     * @return bool True if the key is valid, false otherwise
     */
    function verify_premium_license_key($key)
    {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT * FROM license_keys WHERE license_key = ?');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row !== null;
    }

    /**
     * Verify if an AI subscription key is valid
     * @param string $subscription_id The subscription ID to validate
     * @return array Response array with validation result
     */
    function validate_ai_subscription_key($subscription_id) {
        global $pdo;

        try {
            $stmt = $pdo->prepare("
                SELECT subscription_id, user_id, email, billing_cycle, status,
                    start_date, end_date, created_at
                FROM ai_subscriptions
                WHERE subscription_id = ?
            ");
            $stmt->execute([$subscription_id]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$subscription) {
                return [
                    'success' => false,
                    'message' => 'Invalid subscription ID.'
                ];
            }

            // Check subscription status
            $now = new DateTime();
            $end_date = new DateTime($subscription['end_date']);

            if ($subscription['status'] === 'active' && $end_date > $now) {
                return [
                    'success' => true,
                    'type' => 'ai_subscription',
                    'status' => 'active',
                    'message' => 'AI subscription is valid and active.',
                    'subscription_id' => $subscription['subscription_id'],
                    'billing_cycle' => $subscription['billing_cycle'],
                    'end_date' => $subscription['end_date'],
                    'days_remaining' => $now->diff($end_date)->days
                ];
            } elseif ($subscription['status'] === 'cancelled' && $end_date > $now) {
                return [
                    'success' => true,
                    'type' => 'ai_subscription',
                    'status' => 'cancelled',
                    'message' => 'AI subscription is cancelled but still active until end of billing period.',
                    'subscription_id' => $subscription['subscription_id'],
                    'billing_cycle' => $subscription['billing_cycle'],
                    'end_date' => $subscription['end_date'],
                    'days_remaining' => $now->diff($end_date)->days
                ];
            } else {
                // Subscription expired - update status if needed
                if ($subscription['status'] !== 'expired') {
                    $stmt = $pdo->prepare("UPDATE ai_subscriptions SET status = 'expired' WHERE subscription_id = ?");
                    $stmt->execute([$subscription_id]);
                }

                return [
                    'success' => false,
                    'type' => 'ai_subscription',
                    'status' => 'expired',
                    'message' => 'AI subscription has expired.',
                    'subscription_id' => $subscription['subscription_id'],
                    'end_date' => $subscription['end_date']
                ];
            }
        } catch (PDOException $e) {
            error_log("AI Subscription validation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error validating subscription. Please try again.'
            ];
        }
    }

    /**
     * Mark a license key as activated
     * 
     * @param string $key The license key to activate
     * @param string $ip_address The IP address of the activator
     * @return bool True if successful, false otherwise
     */
    function activate_license_key($key, $ip_address)
    {
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE license_keys SET activated = 1, activation_date = NOW(), ip_address = ? WHERE license_key = ?');
        $stmt->bind_param('ss', $ip_address, $key);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $affected_rows > 0;
    }

    /**
     * Get license key details
     * 
     * @param string $key The license key
     * @return array|false The license details or false if not found
     */
    function get_license_details($key)
    {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT * FROM license_keys WHERE license_key = ?');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row;
    }
}
