<?php
/**
 * Simple TOTP implementation fully compatible with Google Authenticator
 */
class BasicTOTP {
    /**
     * Generate a TOTP code
     * 
     * @param string $secret Base32 encoded secret
     * @param int $time Current timestamp (optional)
     * @return string 6-digit code
     */
    public static function getCode($secret, $time = null) {
        if ($time === null) {
            $time = time();
        }
        
        // Convert to uppercase and remove spaces
        $secret = strtoupper(str_replace(' ', '', $secret));
        
        // Calculate counter value
        $counter = floor($time / 30);
        
        // Pack time counter as binary (big endian)
        $time = "\0\0\0\0" . pack('N*', $counter);
        
        // Decode the secret
        $secretkey = self::base32_decode($secret);
        
        // HMAC-SHA1
        $hash = hash_hmac('SHA1', $time, $secretkey, true);
        
        // Dynamic truncation
        $offset = ord($hash[19]) & 0x0F;
        $value = (
            ((ord($hash[$offset + 0]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000; // 6-digit code
        
        // Zero-pad to 6 digits
        return str_pad($value, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify a TOTP code
     * 
     * @param string $secret Base32 encoded secret
     * @param string $code TOTP code to verify
     * @param int $window Number of 30-second windows to check before/after
     * @return bool True if code is valid
     */
    public static function verify($secret, $code, $window = 3) {
        // Clean inputs
        $secret = strtoupper(trim(str_replace(' ', '', $secret)));
        $code = trim($code);
        
        // Validate code format
        if (!preg_match('/^\d{6}$/', $code)) {
            error_log("Invalid code format");
            return false;
        }
        
        // Get current time in UTC
        $currentTime = time();
        error_log("Verification at: " . gmdate('Y-m-d H:i:s', $currentTime));
        
        // Check multiple time windows
        for ($i = -$window; $i <= $window; $i++) {
            $checkTime = $currentTime + ($i * 30);
            $calculatedCode = self::getCode($secret, $checkTime);
            
            error_log("Window $i (" . gmdate('Y-m-d H:i:s', $checkTime) . 
                    "): Generated '$calculatedCode' vs Input '$code'");
            
            if (hash_equals($calculatedCode, $code)) {
                error_log("MATCH FOUND at window $i");
                return true;
            }
        }
        
        error_log("No matching code found in any time window");
        return false;
    }
    
    /**
     * Base32 decode function
     * 
     * @param string $secret Base32 encoded string
     * @return string Decoded binary string
     */
    private static function base32_decode($secret) {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));
        
        // Remove padding
        $secret = rtrim($secret, '=');
        
        // Process the string
        $buffer = 0;
        $bitsLeft = 0;
        $result = '';
        
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            if (!isset($base32charsFlipped[$char])) {
                continue; // Skip invalid characters
            }
            
            // Add 5 bits to buffer
            $buffer = ($buffer << 5) | $base32charsFlipped[$char];
            $bitsLeft += 5;
            
            // If we have at least 8 bits, extract a byte
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        return $result;
    }
    
    /**
     * Generate a random base32 secret
     * 
     * @param int $length Length of the secret
     * @return string Base32 encoded secret
     */
    public static function generateSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[rand(0, 31)];
        }
        
        return $secret;
    }

    public static function testImplementation() {
        // Known test vector adapted for 6 digits
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $time = 59;
        $expected = '287082'; // Last 6 digits of the RFC test vector
        
        $code = self::getCode($secret, $time);
        error_log("TOTP Implementation Test:");
        error_log("Secret: $secret");
        error_log("Time: $time");
        error_log("Expected: $expected");
        error_log("Actual: $code");
        error_log("Test " . ($code === $expected ? "PASSED" : "FAILED"));
        
        return $code === $expected;
    }
}