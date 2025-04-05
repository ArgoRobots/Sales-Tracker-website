<?php
session_start();
require_once '../db_connect.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Function to get all license keys
function get_all_license_keys() {
    $db = get_db_connection();
    $result = $db->query('SELECT * FROM license_keys ORDER BY created_at DESC');
    
    $licenses = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $licenses[] = $row;
    }
    
    return $licenses;
}

// Handle license key generation
$generated_key = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {
    require_once '../license_functions.php';
    
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $generated_key = create_license_key($email);
    }
}

// Get all license keys
$licenses = get_all_license_keys();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../images/argo-logo/A-logo.ico">
    <title>Argo Sales Tracker - Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        h1 {
            color: #1e3a8a;
            margin: 0;
        }
        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .key-field {
            font-family: monospace;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .generated-key {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 16px;
        }
        .logout-btn {
            background: #ef4444;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>License Key Administration</h1>
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
        
        <div class="form-container">
            <h2>Generate New License Key</h2>
            <?php if ($generated_key): ?>
                <div class="generated-key">
                    New key generated: <?php echo htmlspecialchars($generated_key); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="email">Customer Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" name="generate_key" class="btn">Generate License Key</button>
            </form>
        </div>
        
        <div class="table-container">
            <h2>License Keys</h2>
            <?php if (empty($licenses)): ?>
                <p>No license keys found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>License Key</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Activation Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($licenses as $license): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($license['id']); ?></td>
                                <td class="key-field"><?php echo htmlspecialchars($license['license_key']); ?></td>
                                <td><?php echo htmlspecialchars($license['email']); ?></td>
                                <td><?php echo htmlspecialchars($license['created_at']); ?></td>
                                <td>
                                    <?php if ($license['activated']): ?>
                                        <span class="badge badge-success">Activated</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $license['activation_date'] ? htmlspecialchars($license['activation_date']) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>