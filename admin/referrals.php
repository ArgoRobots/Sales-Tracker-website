<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../db_connect.php';

// ✅ Set page title for header
$page_title = "Referral Links";

// ✅ Include the shared header (adds navbar)
include "admin_header.php";

// ✅ Get DB connection
$conn = get_db_connection();

// ✅ Auto-create the referral_links table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS referral_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ref_name VARCHAR(100) NOT NULL UNIQUE,
        url VARCHAR(255) NOT NULL,
        clicks INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ✅ Handle form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ref_name'])) {
    $ref_name = trim($_POST['ref_name']);

    if ($ref_name !== '') {
        $baseUrl = "http://localhost/Sales-Tracker-website/index.php"; // adjust for live site
        $url = $baseUrl . "?ref=" . urlencode($ref_name);

        $stmt = $conn->prepare("INSERT INTO referral_links (ref_name, url) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $ref_name, $url);
            if ($stmt->execute()) {
                $message = "<p style='color:green;'>Referral link created: <a href='$url' target='_blank'>$url</a></p>";
            } else {
                if ($conn->errno === 1062) {
                    $message = "<p style='color:red;'>Referral name already exists.</p>";
                } else {
                    $message = "<p style='color:red;'>Error: " . $stmt->error . "</p>";
                }
            }
            $stmt->close();
        } else {
            $message = "<p style='color:red;'>Error preparing statement: " . $conn->error . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Please enter a referral name.</p>";
    }
}

// ✅ Fetch existing referral links
$result = $conn->query("SELECT id, ref_name, url, clicks, created_at FROM referral_links ORDER BY id DESC");
?>

<!-- ✅ Page Content -->
<section>
    <h1>Referral Links</h1>
    <p>Manage and track custom referral links.</p>

    <!-- ✅ Show message -->
    <?php if ($message) echo $message; ?>

    <!-- ✅ Referral form -->
    <form method="POST" action="">
        <label for="ref_name">Referral Name:</label>
        <input type="text" name="ref_name" id="ref_name" required>
        <button type="submit">Generate Link</button>
    </form>

    <!-- ✅ Referral list -->
    <h2>Existing Referral Links</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Generated URL</th>
            <th>Clicks</th>
            <th>Created At</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['ref_name']}</td>
                        <td><a href='{$row['url']}' target='_blank'>{$row['url']}</a></td>
                        <td>{$row['clicks']}</td>
                        <td>{$row['created_at']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No referral links created yet.</td></tr>";
        }
        ?>
    </table>
</section>

<?php
$conn->close();

// ✅ Optionally include admin footer (if you have it)
if (file_exists("admin_footer.php")) {
    include "admin_footer.php";
}
?>
