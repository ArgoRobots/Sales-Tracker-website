<?php
// Include the database connection
require_once 'db_connect.php';

// Only define these functions if they haven't been defined already
// Check for one of our custom functions, not get_db_connection
if (!function_exists('generate_license_key')) {
    /**
     * Generate a random license key
     * 
     * @return string A 20-character alphanumeric license key
     */
    function generate_license_key() {
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
    function license_key_exists($key) {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM license_keys WHERE license_key = :key');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray();
        
        return $result['count'] > 0;
    }

    /**
     * Store a new license key in the database
     * 
     * @param string $email The email associated with the license
     * @return string The generated license key
     */
    function create_license_key($email) {
        $db = get_db_connection();
        
        // Generate a unique key
        do {
            $key = generate_license_key();
        } while (license_key_exists($key));  // Updated function name here
        
        // Store the key in the database
        $stmt = $db->prepare('INSERT INTO license_keys (license_key, email) VALUES (:key, :email)');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->execute();
        
        return $key;
    }

    /**
     * Verify if a license key is valid
     * 
     * @param string $key The license key to verify
     * @return bool True if the key is valid, false otherwise
     */
    function verify_license_key($key) {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT * FROM license_keys WHERE license_key = :key');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        return $result !== false;
    }

    /**
     * Mark a license key as activated
     * 
     * @param string $key The license key to activate
     * @param string $ip_address The IP address of the activator
     * @return bool True if successful, false otherwise
     */
    function activate_license_key($key, $ip_address) {
        $db = get_db_connection();
        $stmt = $db->prepare('UPDATE license_keys SET activated = 1, activation_date = CURRENT_TIMESTAMP, ip_address = :ip WHERE license_key = :key');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip_address, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        return $result !== false;
    }

    /**
     * Get license key details
     * 
     * @param string $key The license key
     * @return array|false The license details or false if not found
     */
    function get_license_details($key) {
        $db = get_db_connection();
        $stmt = $db->prepare('SELECT * FROM license_keys WHERE license_key = :key');
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        return $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    }
}
?>