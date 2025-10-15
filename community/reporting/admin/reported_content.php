<?php
session_start();
require_once '../../db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: ../../users/login.php');
    exit;
}

function isAdmin($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM community_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user && $user['role'] === 'admin';
}

// Get pending reports
$stmt = $pdo->prepare("
    SELECT r.*, 
           reporter.username as reporter_name,
           reported.username as reported_name,
           reported.email as reported_email
    FROM community_reports r
    LEFT JOIN community_users reporter ON r.reporter_user_id = reporter.id
    LEFT JOIN community_users reported ON r.reported_user_id = reported.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
");
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reported Content - Admin</title>
    <style>
        .reports-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            }

            .report-item { 
                border: 1px solid #e5e7eb; 
                border-radius: 8px; 
                padding: 16px; 
                margin-bottom: 16px; 
                background: white;
            }

            .report-header { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                margin-bottom: 12px; 
                padding-bottom: 12px;
                border-bottom: 1px solid #f3f4f6;
            }

            .report-content { 
                background: #f9fafb; 
                padding: 12px; 
                border-radius: 4px; 
                margin: 12px 0; 
                border-left: 4px solid #dc2626;
            }

            .report-actions { 
                display: flex; 
                gap: 8px; 
                margin-top: 12px; 
            }

            .btn { 
                padding: 8px 16px; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer; 
                font-size: 14px;
                transition: all 0.2s ease;
            }

            .btn-dismiss { 
                background: #6b7280; 
                color: white; 
            }
            .btn-dismiss:hover {
                background: #4b5563;
            }

            .btn-remove { 
                background: #dc2626; 
                color: white; 
            }
            .btn-remove:hover {
                background: #b91c1c;
            }

            .btn-ban { 
                background: #f59e0b; 
                color: white; 
            }
            .btn-ban:hover {
                background: #d97706;
            }

            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1000;
            }

            .modal-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 24px;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
            }

            .modal-actions {
                display: flex;
                gap: 12px;
                justify-content: flex-end;
                margin-top: 20px;
            }

            .modal-actions button {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            .modal-actions button[type="button"] {
                background: #6b7280;
                color: white;
            }

            .modal-actions button[type="submit"] {
                background: #dc2626;
                color: white;
            }

            form div {
                margin-bottom: 16px;
            }

            form label {
                display: block;
                margin-bottom: 4px;
                font-weight: 600;
            }

            form input, form select, form textarea {
                width: 100%;
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                box-sizing: border-box;
            }

            form textarea {
                resize: vertical;
                min-height: 80px;
            }
    </style>
</head>
<body>
        <div class="reports-container">
        <h1>Reported Content</h1>
        
        <?php if (empty($reports)): ?>
            <div style="text-align: center; padding: 40px; color: #6b7280;">
                <h3>No pending reports</h3>
                <p>There are no reports waiting for review.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reports as $report): ?>
            <div class="report-item">
                <div class="report-header">
                    <div>
                        <strong>Reported by:</strong> <?php echo htmlspecialchars($report['reporter_name'] ?? 'Unknown'); ?>
                        | <strong>Against:</strong> <?php echo htmlspecialchars($report['reported_name'] ?? 'Unknown'); ?>
                        | <strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $report['content_type'])); ?>
                    </div>
                    <small><?php echo date('M j, Y g:i A', strtotime($report['created_at'])); ?></small>
                </div>
                
                <div>
                    <strong>Reason:</strong> <?php echo ucfirst(str_replace('_', ' ', $report['reason'])); ?>
                </div>
                
                <?php if ($report['details']): ?>
                <div>
                    <strong>Additional Details:</strong> <?php echo htmlspecialchars($report['details']); ?>
                </div>
                <?php endif; ?>
                
                <div class="report-content">
                    <strong>Reported Content:</strong><br>
                    <?php echo getReportedContent($report['content_type'], $report['content_id']); ?>
                </div>
                
                <div class="report-actions">
                    <button class="btn btn-dismiss" onclick="handleReport(<?php echo $report['id']; ?>, 'dismiss')">
                        Dismiss Report
                    </button>
                    <button class="btn btn-remove" onclick="handleReport(<?php echo $report['id']; ?>, 'remove_content')">
                        Remove Content
                    </button>
                    <button class="btn btn-ban" onclick="openBanModal(<?php echo $report['id']; ?>, <?php echo $report['reported_user_id']; ?>, '<?php echo htmlspecialchars($report['reported_name']); ?>', '<?php echo htmlspecialchars($report['reported_email']); ?>')">
                        Ban User
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Ban Modal -->
    <div id="banModal" class="modal">
        <div class="modal-content">
            <h3>Ban User</h3>
            <form id="banForm">
                <input type="hidden" id="banReportId" name="report_id">
                <input type="hidden" id="banUserId" name="user_id">
                
                <div>
                    <label>User:</label>
                    <span id="banUserName"></span>
                </div>
                
                <div>
                    <label>Ban Duration:</label>
                    <select name="ban_duration" required>
                        <option value="30_days">30 Days</option>
                        <option value="1_year">1 Year</option>
                        <option value="permanent">Permanent</option>
                    </select>
                </div>
                
                <div>
                    <label>Reason (will be sent to user):</label>
                    <textarea name="ban_reason" rows="4" required placeholder="Explain why this user is being banned..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeBanModal()">Cancel</button>
                    <button type="submit">Confirm Ban</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function handleReport(reportId, action) {
            if (!confirm('Are you sure you want to ' + action.replace('_', ' ') + '?')) {
                return;
            }
            
            fetch('handle_report.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({report_id: reportId, action: action})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing request. Please try again.');
            });
        }

        function openBanModal(reportId, userId, userName, userEmail) {
            document.getElementById('banReportId').value = reportId;
            document.getElementById('banUserId').value = userId;
            document.getElementById('banUserName').textContent = userName + ' (' + userEmail + ')';
            document.getElementById('banModal').style.display = 'block';
        }

        function closeBanModal() {
            document.getElementById('banModal').style.display = 'none';
            document.getElementById('banForm').reset();
        }

        document.getElementById('banForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to ban this user? They will receive an email notification.')) {
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('ban_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeBanModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error banning user. Please try again.');
            });
        });

        // Close modal when clicking outside
        document.getElementById('banModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBanModal();
            }
        });
    </script>
</body>
</html>
