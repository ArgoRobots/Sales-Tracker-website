// Report functionality
(function() {
    'use strict';

    const modal = document.getElementById('reportModal');
    const form = document.getElementById('reportForm');
    const closeBtn = document.querySelector('.report-modal-close');
    const cancelBtn = document.querySelector('.report-modal-cancel');
    const contentTypeInput = document.getElementById('reportContentType');
    const contentIdInput = document.getElementById('reportContentId');

    // Open modal when report button is clicked
    document.addEventListener('click', function(e) {
        if (e.target.closest('.report-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.report-btn');
            const contentType = btn.getAttribute('data-content-type');
            const contentId = btn.getAttribute('data-content-id');

            // Set hidden form fields
            contentTypeInput.value = contentType;
            contentIdInput.value = contentId;

            // Show modal
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    });

    // Close modal functions
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        form.reset();
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        fetch('report_content.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof showNotification === 'function') {
                    showNotification('Report submitted successfully. Our team will review it shortly.', 'success');
                } else {
                    alert('Report submitted successfully. Our team will review it shortly.');
                }
                closeModal();
            } else {
                // Show error notification
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Failed to submit report. Please try again.', 'error');
                } else {
                    alert(data.message || 'Failed to submit report. Please try again.');
                }
            }
        })
        .catch(error => {
            console.error('Error submitting report:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred. Please try again later.', 'error');
            } else {
                alert('An error occurred. Please try again later.');
            }
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        });
    });

})();
