document.addEventListener('DOMContentLoaded', function () {
    // License Keys Chart initialization
    const labels = chartData.map(item => item.date);
    const counts = chartData.map(item => item.count);

    const ctx = document.getElementById('keysChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'License Keys Generated',
                data: counts,
                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(37, 99, 235, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    ticks: {
                        padding: 10
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'top',
                }
            },
        }
    });

    // AI Subscriptions Chart initialization
    const subLabels = subscriptionChartData.map(item => item.date);
    const subTotals = subscriptionChartData.map(item => parseInt(item.total));
    const subActive = subscriptionChartData.map(item => parseInt(item.active));

    const subCtx = document.getElementById('subscriptionsChart').getContext('2d');
    new Chart(subCtx, {
        type: 'line',
        data: {
            labels: subLabels,
            datasets: [
                {
                    label: 'Total Subscriptions',
                    data: subTotals,
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(139, 92, 246, 1)'
                },
                {
                    label: 'Active Subscriptions',
                    data: subActive,
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    ticks: {
                        padding: 10
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'top',
                }
            },
        }
    });

    // Date preset select handling
    const datePresetSelect = document.getElementById('date_preset');
    const customDateRange = document.getElementById('custom_date_range');

    datePresetSelect.addEventListener('change', function () {
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
        } else {
            customDateRange.style.display = 'none';
        }
    });

    // If user clicks on date inputs, select custom option
    const dateInputs = customDateRange.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('focus', function () {
            datePresetSelect.value = 'custom';
            customDateRange.style.display = 'flex';
        });
    });

    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkButtons = document.querySelectorAll('.btn-bulk');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkForm = document.getElementById('bulk-form');
    const bulkActionInput = document.getElementById('bulk_action_input');

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCountSpan.textContent = count;

        // Enable/disable buttons
        bulkButtons.forEach(btn => {
            btn.disabled = count === 0;
        });

        // Update select-all checkbox state
        if (count === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (count === rowCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function () {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Individual checkbox changes
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Bulk action buttons
    bulkButtons.forEach(button => {
        button.addEventListener('click', function () {
            const action = this.dataset.action;
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkedBoxes.length;

            if (count === 0) return;

            let actionText = '';
            let confirmMessage = '';

            switch (action) {
                case 'resend_email':
                    actionText = 'send email to';
                    confirmMessage = `Are you sure you want to send emails for ${count} license key${count > 1 ? 's' : ''}?`;
                    break;
                case 'activate':
                    actionText = 'activate';
                    confirmMessage = `Are you sure you want to activate ${count} license key${count > 1 ? 's' : ''}?`;
                    break;
                case 'deactivate':
                    actionText = 'deactivate';
                    confirmMessage = `Are you sure you want to deactivate ${count} license key${count > 1 ? 's' : ''}?`;
                    break;
                case 'delete':
                    actionText = 'delete';
                    confirmMessage = `Are you sure you want to delete ${count} license key${count > 1 ? 's' : ''}? This action cannot be undone.`;
                    break;
            }

            if (confirm(confirmMessage)) {
                bulkActionInput.value = action;
                sessionStorage.setItem('scrollPosition', window.scrollY);
                bulkForm.submit();
            }
        });
    });

    // Initial count
    updateSelectedCount();

    // Save scroll position when filter form is submitted
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function () {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });
    }

    // Auto-clear search when textbox is emptied
    const searchInput = document.querySelector('#search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            if (this.value.trim() === '') {
                const datePreset = document.getElementById('date_preset').value;

                if (!datePreset) {
                    sessionStorage.setItem('scrollPosition', window.scrollY);
                    window.location.href = 'index.php';
                }
            }
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                sessionStorage.setItem('scrollPosition', window.scrollY);
                this.value = '';
                const datePreset = document.getElementById('date_preset').value;

                if (!datePreset) {
                    window.location.href = 'index.php';
                }
            }
        });
    }
});

// Restore scroll position after page fully loads
window.addEventListener('load', function () {
    if (sessionStorage.getItem('scrollPosition')) {
        const scrollPos = sessionStorage.getItem('scrollPosition');
        sessionStorage.removeItem('scrollPosition');

        // Use requestAnimationFrame to ensure page is fully rendered
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                window.scrollTo(0, parseInt(scrollPos));
            });
        });
    }
});