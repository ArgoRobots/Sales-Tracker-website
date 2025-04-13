<?php
require_once 'totp.php';

// Run the test
$testResult = BasicTOTP::testImplementation();

echo "<h1>TOTP Implementation Test</h1>";
echo "<p>Test " . ($testResult ? "PASSED" : "FAILED") . "</p>";

if (!$testResult) {
    echo "<p style='color:red'>Your TOTP implementation doesn't match RFC 6238 standards.</p>";
    echo "<p>Check the error logs for details.</p>";
}
?>