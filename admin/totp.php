<?php
/**
 * Improved TOTP implementation fully compatible with Google Authenticator
 */
class TOTP {
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
        
        // Calculate counter value (30-second window)
        $counter = floor($time / 30);
        
        // Pack time counter as binary (big endian)
        $binary = pack('N*', 0) . pack('N*', $counter); // Ensure 8 bytes (64 bits)
        
        // Decode the secret
        $secretkey = self::base32_decode($secret);
        
        // HMAC-SHA1
        $hash = hash_hmac('SHA1', $binary, $secretkey, true);
        
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
     * Verify a TOTP code with an expanded time window
     * 
     * @param string $secret Base32 encoded secret
     * @param string $code TOTP code to verify
     * @param int $window Time window in 30-second units (default: 1 for ±30 seconds)
     * @return bool True if code is valid
     */
    public static function verify($secret, $code) {
        // Clean inputs
        $secret = strtoupper(trim(str_replace(' ', '', $secret)));
        $code = trim($code);
        
        // Validate code format
        if (!preg_match('/^\d{6}$/', $code)) {
            error_log("TOTP: Invalid code format: " . $code);
            return false;
        }
        
        // Get current time in UTC
        $currentTime = time();
        error_log("TOTP: Verification at: " . gmdate('Y-m-d H:i:s', $currentTime) . " UTC");
        
        // Use a wider time window (±4 windows = ±2 minutes)
        // This helps with clock drift between server and client
        $window = 4;
        
        for ($i = -$window; $i <= $window; $i++) {
            $checkTime = $currentTime + ($i * 30);
            $calculatedCode = self::getCode($secret, $checkTime);
            
            error_log("TOTP: Window $i (" . gmdate('Y-m-d H:i:s', $checkTime) . 
                    "): Generated '$calculatedCode' vs Input '$code'");
            
            if ($calculatedCode === $code) {
                error_log("TOTP: MATCH FOUND at window $i");
                return true;
            }
        }
        
        error_log("TOTP: No matching code found in any time window");
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
            $secret .= $chars[random_int(0, 31)];
        }
        
        return $secret;
    }

    public static function testImplementation() {
        // RFC 6238 test vectors
        $tests = [
            [
                'secret' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',
                'time' => 59,
                'expected' => '287082' // Last 6 digits of the RFC value
            ],
            [
                'secret' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',
                'time' => 1111111109,
                'expected' => '081804' // Last 6 digits of the RFC value
            ],
            [
                'secret' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',
                'time' => 1111111111,
                'expected' => '050471' // Last 6 digits of the RFC value
            ]
        ];
        
        $allPassed = true;
        
        foreach ($tests as $index => $test) {
            $code = self::getCode($test['secret'], $test['time']);
            $passed = $code === $test['expected'];
            
            error_log("TOTP Test " . ($index + 1) . ":");
            error_log("  Secret: " . $test['secret']);
            error_log("  Time: " . $test['time']);
            error_log("  Expected: " . $test['expected']);
            error_log("  Generated: " . $code);
            error_log("  Result: " . ($passed ? "PASS" : "FAIL"));
            
            if (!$passed) {
                $allPassed = false;
            }
        }
        
        return $allPassed;
    }
}