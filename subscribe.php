<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

// Start HTML wrapper
echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>
<title>Subscription Result</title></head><body style='font-family:Arial, sans-serif; background:#f9fafb; display:flex; justify-content:center; align-items:center; height:100vh;'>";

echo "<div style='max-width:500px; background:#fff; padding:2rem; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); text-align:center;'>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<h2 style='color:#dc2626;'>Invalid email format</h2>";
    } else {
        $conn = get_db_connection();

        // Ensure subscribers table exists
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->query($createTableSQL);

        // Check if already subscribed
        $checkStmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "<h2 style='color:#2563eb;'>You're already subscribed with <br><strong>$email</strong></h2>";
        } else {
            $stmt = $conn->prepare("INSERT INTO subscribers (email, created_at) VALUES (?, NOW())");
            $stmt->bind_param("s", $email);

            if ($stmt->execute()) {
                echo "<h2 style='color:#16a34a;'>🎉 Thanks for subscribing!</h2>";
                echo "<p>We've added <strong>$email</strong> to our mailing list.</p>";
            } else {
                echo "<h2 style='color:#dc2626;'>Error: " . $stmt->error . "</h2>";
            }
            $stmt->close();
        }

        $checkStmt->close();
        $conn->close();
    }
} else {
    echo "<h2 style='color:#dc2626;'>Invalid request.</h2>";
}

// Back button
echo "<br><a href='documentation/index.php' style='display:inline-block; margin-top:1rem; padding:0.6rem 1.2rem; background:#2563eb; color:white; text-decoration:none; border-radius:6px; transition:0.3s;'>⬅ Go Back</a>";

echo "</div></body></html>";
?>
