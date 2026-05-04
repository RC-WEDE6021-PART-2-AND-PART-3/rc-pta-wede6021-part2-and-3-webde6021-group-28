/**
 * admin.js — Pastimes Admin Panel JavaScript
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 *
 * Admin dashboard functionality including:
 * - Chart.js integration for statistics
 * - User management actions
 * - Listing approval workflow
 * - Order status updates
 * - Confirmation modals
 */

'use strict';

/**
 * Initialize admin dashboard when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeModals();
    initializeStatusUpdates();
    initializeSearchFilters();
    initializeBulkActions();
});

/**
 * Initialize Chart.js charts on dashboard
 */
function initializeCharts() {
    // Orders by Status - Bar Chart
    const ordersChartCanvas = document.getElementById('ordersChart');
    if (ordersChartCanvas) {
        const ordersCtx = ordersChartCanvas.getContext('2d');
        
        // Get data from data attributes or defaults
        const pendingOrders = parseInt(ordersChartCanvas.dataset.pending || 0);
        const dispatchedOrders = parseInt(ordersChartCanvas.dataset.dispatched || 0);
        const deliveredOrders = parseInt(ordersChartCanvas.dataset.delivered || 0);
        const cancelledOrders = parseInt(ordersChartCanvas.dataset.cancelled || 0);
        
        new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Dispatched', 'Delivered', 'Cancelled'],
                datasets: [{
                    label: 'Orders',
                    data: [pendingOrders, dispatchedOrders, deliveredOrders, cancelledOrders],
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Orders by Status',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    // Clothing by Category - Pie Chart
    const clothingChartCanvas = document.getElementById('clothingChart');
    if (clothingChartCanvas) {
        const clothingCtx = clothingChartCanvas.getContext('2d');
        
        // Get data from data attributes or defaults
        const tops = parseInt(clothingChartCanvas.dataset.tops || 0);
        const bottoms = parseInt(clothingChartCanvas.dataset.bottoms || 0);
        const dresses = parseInt(clothingChartCanvas.dataset.dresses || 0);
        const outerwear = parseInt(clothingChartCanvas.dataset.outerwear || 0);
        const footwear = parseInt(clothingChartCanvas.dataset.footwear || 0);
        const accessories = parseInt(clothingChartCanvas.dataset.accessories || 0);
        const activewear = parseInt(clothingChartCanvas.dataset.activewear || 0);
        
        new Chart(clothingCtx, {
            type: 'pie',
            data: {
                labels: ['Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Footwear', 'Accessories', 'Activewear'],
                datasets: [{
                    data: [tops, bottoms, dresses, outerwear, footwear, accessories, activewear],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(249, 115, 22, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    title: {
                        display: true,
                        text: 'Listings by Category',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    }
}

/**
 * Initialize confirmation modals for destructive actions
 */
function initializeModals() {
    // Delete confirmation modal
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const itemType = this.dataset.type || 'item';
            const itemName = this.dataset.name || 'this item';
            const confirmUrl = this.dataset.url || this.href;
            
            showConfirmModal(
                `Delete ${itemType}`,
                `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,
                confirmUrl,
                'Delete',
                'btn-danger'
            );
        });
    });
    
    // Suspend user confirmation
    const suspendButtons = document.querySelectorAll('[data-action="suspend"]');
    suspendButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userName = this.dataset.name || 'this user';
            const confirmUrl = this.dataset.url || this.href;
            
            showConfirmModal(
                'Suspend User',
                `Are you sure you want to suspend "${userName}"? They will no longer be able to access their account.`,
                confirmUrl,
                'Suspend',
                'btn-warning'
            );
        });
    });
}

/**
 * Show confirmation modal
 * @param {string} title - Modal title
 * @param {string} message - Confirmation message
 * @param {string} confirmUrl - URL to navigate to on confirm
 * @param {string} confirmText - Text for confirm button
 * @param {string} confirmClass - CSS class for confirm button
 */
function showConfirmModal(title, message, confirmUrl, confirmText, confirmClass) {
    // Remove existing modal if any
    const existingModal = document.getElementById('confirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal HTML
    const modalHtml = `
        <div id="confirmModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button type="button" class="modal-close" onclick="closeConfirmModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                    <a href="${confirmUrl}" class="btn ${confirmClass}">${confirmText}</a>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal with animation
    setTimeout(() => {
        document.getElementById('confirmModal').classList.add('active');
    }, 10);
    
    // Close on overlay click
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeConfirmModal();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    });
}

/**
 * Close confirmation modal
 */
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

/**
 * Initialize order status dropdown updates
 */
function initializeStatusUpdates() {
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            
            // Show loading state
            this.disabled = true;
            const originalValue = this.dataset.originalValue;
            
            // Make AJAX request to update status
            fetch('orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=updateStatus&orderID=${orderId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Order status updated successfully', 'success');
                    this.dataset.originalValue = newStatus;
                    
                    // Update badge color
                    const row = this.closest('tr');
                    if (row) {
                        updateStatusBadge(row, newStatus);
                    }
                } else {
                    showToast(data.message || 'Failed to update status', 'error');
                    this.value = originalValue;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                this.value = originalValue;
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
}

/**
 * Update status badge color based on status
 * @param {HTMLElement} row - Table row element
 * @param {string} status - New status value
 */
function updateStatusBadge(row, status) {
    const badge = row.querySelector('.status-badge');
    if (badge) {
        badge.className = 'status-badge';
        badge.classList.add(`badge-${status}`);
        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }
}

/**
 * Initialize search and filter functionality
 */
function initializeSearchFilters() {
    const searchInput = document.getElementById('adminSearch');
    const filterSelect = document.getElementById('statusFilter');
    
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterTable(this.value, filterSelect?.value);
            }, 300);
        });
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterTable(searchInput?.value, this.value);
        });
    }
}

/**
 * Filter table rows based on search term and status
 * @param {string} searchTerm - Search term to filter by
 * @param {string} status - Status to filter by
 */
function filterTable(searchTerm = '', status = 'all') {
    const table = document.querySelector('.admin-table tbody');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowStatus = row.dataset.status || '';
        
        const matchesSearch = !term || text.includes(term);
        const matchesStatus = status === 'all' || rowStatus === status;
        
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
    
    // Show "no results" message if all rows are hidden
    const visibleRows = table.querySelectorAll('tr:not([style*="display: none"])');
    const noResultsRow = table.querySelector('.no-results-row');
    
    if (visibleRows.length === 0 && !noResultsRow) {
        const colspan = rows[0]?.querySelectorAll('td').length || 5;
        const noResults = document.createElement('tr');
        noResults.className = 'no-results-row';
        noResults.innerHTML = `<td colspan="${colspan}" style="text-align: center; padding: 2rem;">No results found</td>`;
        table.appendChild(noResults);
    } else if (visibleRows.length > 0 && noResultsRow) {
        noResultsRow.remove();
    }
}

/**
 * Initialize bulk action checkboxes
 */
function initializeBulkActions() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionSelect = document.getElementById('bulkAction');
    const bulkActionBtn = document.getElementById('applyBulkAction');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionState();
        });
    }
    
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionState);
    });
    
    if (bulkActionBtn) {
        bulkActionBtn.addEventListener('click', function() {
            const action = bulkActionSelect?.value;
            if (!action) {
                showToast('Please select an action', 'warning');
                return;
            }
            
            const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                showToast('Please select at least one item', 'warning');
                return;
            }
            
            applyBulkAction(action, selectedIds);
        });
    }
}

/**
 * Update bulk action button state based on selection
 */
function updateBulkActionState() {
    const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
    const bulkActionBtn = document.getElementById('applyBulkAction');
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    
    if (bulkActionBtn) {
        bulkActionBtn.disabled = checkedCount === 0;
        bulkActionBtn.textContent = checkedCount > 0 
            ? `Apply (${checkedCount} selected)` 
            : 'Apply';
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedCount === itemCheckboxes.length && checkedCount > 0;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < itemCheckboxes.length;
    }
}

/**
 * Apply bulk action to selected items
 * @param {string} action - Action to apply
 * @param {array} ids - Array of item IDs
 */
function applyBulkAction(action, ids) {
    const confirmMessages = {
        'approve': 'Are you sure you want to approve the selected items?',
        'reject': 'Are you sure you want to reject the selected items?',
        'delete': 'Are you sure you want to delete the selected items? This cannot be undone.',
        'suspend': 'Are you sure you want to suspend the selected users?',
        'activate': 'Are you sure you want to activate the selected users?'
    };
    
    if (!confirm(confirmMessages[action] || 'Are you sure you want to perform this action?')) {
        return;
    }
    
    // Submit bulk action form
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'bulkAction';
    actionInput.value = action;
    form.appendChild(actionInput);
    
    ids.forEach(id => {
        const input = document.createElement('input');
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

/**
 * Show toast notification
 * @param {string} message - Message to display
 * @param {string} type - Toast type (success, error, warning, info)
 */
function showToast(message, type = 'info') {
    // Remove existing toast if any
    const existingToast = document.querySelector('.admin-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    
    const toast = document.createElement('div');
    toast.className = `admin-toast toast-${type}`;
    toast.innerHTML = `
        ${icons[type] || icons.info}
        <span>${message}</span>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/**
 * Quick approve/reject actions for listings
 * @param {number} clothingId - Clothing item ID
 * @param {string} action - 'approve' or 'reject'
 */
function quickListingAction(clothingId, action) {
    fetch('listings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&clothingID=${clothingId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Listing ${action}d successfully`, 'success');
            // Refresh the page or update the row
            const row = document.querySelector(`tr[data-id="${clothingId}"]`);
            if (row) {
                if (action === 'approve') {
                    row.dataset.status = 'approved';
                    row.querySelector('.status-badge').className = 'status-badge badge-approved';
                    row.querySelector('.status-badge').textContent = 'Approved';
                } else {
                    row.dataset.status = 'rejected';
                    row.querySelector('.status-badge').className = 'status-badge badge-rejected';
                    row.querySelector('.status-badge').textContent = 'Rejected';
                }
            }
        } else {
            showToast(data.message || 'Action failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

/**
 * Quick verify/suspend user actions
 * @param {number} userId - User ID
 * @param {string} action - 'verify' or 'suspend'
 */
function quickUserAction(userId, action) {
    fetch('users.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&userID=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const actionText = action === 'verify' ? 'activated' : 'suspended';
            showToast(`User ${actionText} successfully`, 'success');
            // Refresh the page
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Action failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

/**
 * Export table data to CSV
 * @param {string} tableId - ID of the table to export
 * @param {string} filename - Name of the exported file
 */
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            // Skip action columns
            if (!col.classList.contains('actions')) {
                let text = col.textContent.trim().replace(/"/g, '""');
                rowData.push(`"${text}"`);
            }
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

/**
 * Print admin report
 */
function printReport() {
    window.print();
}

// Make functions available globally
window.closeConfirmModal = closeConfirmModal;
window.quickListingAction = quickListingAction;
window.quickUserAction = quickUserAction;
window.exportToCSV = exportToCSV;
window.printReport = printReport;
