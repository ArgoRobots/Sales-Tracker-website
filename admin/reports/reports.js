// Modal handling
let banModal = document.getElementById('banModal');

function showBanModal(reportId, userId, username) {
    document.getElementById('banReportId').value = reportId;
    document.getElementById('banUserId').value = userId;
    document.getElementById('banUsername').textContent = username;
    document.getElementById('banReason').value = '';
    banModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBanModal() {
    banModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
banModal.addEventListener('click', function (e) {
    if (e.target === banModal) {
        closeBanModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && banModal.style.display === 'flex') {
        closeBanModal();
    }
});

// Handle report actions (delete content, dismiss)
function handleReport(reportId, action, contentType = null, contentId = null) {
    const actionText = action === 'delete' ? 'delete this content' : 'dismiss this report';
    if (!confirm(`Are you sure you want to ${actionText}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('action', action);
    if (contentType) formData.append('content_type', contentType);
    if (contentId) formData.append('content_id', contentId);

    fetch('handle_report.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Action failed', 'error');
                } else {
                    alert(data.message || 'Action failed');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
        });
}

// Handle user report actions (reset username, clear bio)
function handleUserReport(reportId, action, userId, username) {
    let actionText = '';
    let confirmText = '';

    if (action === 'reset_username') {
        actionText = 'reset the username';
        confirmText = `Are you sure you want to reset the username for "${username}"? This will replace their username with a random string.`;
    } else if (action === 'clear_bio') {
        actionText = 'clear the bio';
        confirmText = `Are you sure you want to clear the bio for "${username}"?`;
    }

    if (!confirm(confirmText)) {
        return;
    }

    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('action', action);
    formData.append('user_id', userId);

    fetch('handle_report.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Action failed', 'error');
                } else {
                    alert(data.message || 'Action failed');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
        });
}

// Submit ban
function submitBan() {
    const reportId = document.getElementById('banReportId').value;
    const userId = document.getElementById('banUserId').value;
    const violationType = document.getElementById('banViolationType').value;
    const additionalDetails = document.getElementById('banReason').value.trim();
    const duration = document.getElementById('banDuration').value;

    if (!violationType) {
        alert('Please select a reason for the ban');
        return;
    }

    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('action', 'ban');
    formData.append('user_id', userId);
    formData.append('violation_type', violationType);
    formData.append('additional_details', additionalDetails);
    formData.append('ban_duration', duration);

    // Disable button
    const submitBtn = event.target;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Banning...';

    fetch('handle_report.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                closeBanModal();
                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Failed to ban user', 'error');
                } else {
                    alert(data.message || 'Failed to ban user');
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Ban User';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Ban User';
        });
}

// Reset Username Modal handling
let resetUsernameModal = document.getElementById('resetUsernameModal');

function showResetUsernameModal(reportId, userId, username) {
    document.getElementById('resetUsernameReportId').value = reportId;
    document.getElementById('resetUsernameUserId').value = userId;
    document.getElementById('resetUsername').textContent = username;
    document.getElementById('resetUsernameViolationType').value = '';
    document.getElementById('resetUsernameDetails').value = '';
    resetUsernameModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeResetUsernameModal() {
    resetUsernameModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
resetUsernameModal.addEventListener('click', function (e) {
    if (e.target === resetUsernameModal) {
        closeResetUsernameModal();
    }
});

function submitResetUsername() {
    const reportId = document.getElementById('resetUsernameReportId').value;
    const userId = document.getElementById('resetUsernameUserId').value;
    const violationType = document.getElementById('resetUsernameViolationType').value;
    const additionalDetails = document.getElementById('resetUsernameDetails').value.trim();

    if (!violationType) {
        alert('Please select a reason for resetting the username');
        return;
    }

    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('action', 'reset_username');
    formData.append('user_id', userId);
    formData.append('violation_type', violationType);
    formData.append('additional_details', additionalDetails);

    // Disable button
    const submitBtn = event.target;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Resetting...';

    fetch('handle_report.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                closeResetUsernameModal();
                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Failed to reset username', 'error');
                } else {
                    alert(data.message || 'Failed to reset username');
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Reset Username';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Reset Username';
        });
}

// Clear Bio Modal handling
let clearBioModal = document.getElementById('clearBioModal');

function showClearBioModal(reportId, userId, username) {
    document.getElementById('clearBioReportId').value = reportId;
    document.getElementById('clearBioUserId').value = userId;
    document.getElementById('clearBioUsername').textContent = username;
    document.getElementById('clearBioViolationType').value = '';
    document.getElementById('clearBioDetails').value = '';
    clearBioModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeClearBioModal() {
    clearBioModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
clearBioModal.addEventListener('click', function (e) {
    if (e.target === clearBioModal) {
        closeClearBioModal();
    }
});

function submitClearBio() {
    const reportId = document.getElementById('clearBioReportId').value;
    const userId = document.getElementById('clearBioUserId').value;
    const violationType = document.getElementById('clearBioViolationType').value;
    const additionalDetails = document.getElementById('clearBioDetails').value.trim();

    if (!violationType) {
        alert('Please select a reason for clearing the bio');
        return;
    }

    const formData = new FormData();
    formData.append('report_id', reportId);
    formData.append('action', 'clear_bio');
    formData.append('user_id', userId);
    formData.append('violation_type', violationType);
    formData.append('additional_details', additionalDetails);

    // Disable button
    const submitBtn = event.target;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Clearing...';

    fetch('handle_report.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                closeClearBioModal();
                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Failed to clear bio', 'error');
                } else {
                    alert(data.message || 'Failed to clear bio');
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Clear Bio';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred', 'error');
            } else {
                alert('An error occurred');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Clear Bio';
        });
}

// Close modals on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (resetUsernameModal.style.display === 'flex') {
            closeResetUsernameModal();
        }
        if (clearBioModal.style.display === 'flex') {
            closeClearBioModal();
        }
    }
});
