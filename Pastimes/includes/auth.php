<?php
/**
 * Student Numbers: [Your Student Numbers]
 * Student Names: [Your Student Names]
 * Declaration: This is our own work. We have not copied from any other source.
 * Date: 2024
 * 
 * Authentication Helper Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require user login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/**
 * Require admin login - redirect to admin login if not logged in
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect('admin/login.php');
    }
}

/**
 * Require seller role
 */
function requireSeller() {
    requireLogin();
    if (!in_array($_SESSION['role'], ['seller', 'both'])) {
        $_SESSION['error'] = 'You need seller privileges to access this page.';
        redirect('dashboard.php');
    }
    if ($_SESSION['status'] !== 'active') {
        $_SESSION['error'] = 'Your account must be active to access seller features.';
        redirect('dashboard.php');
    }
}

/**
 * Check if user can sell
 * @return bool True if user can sell
 */
function canSell() {
    return isLoggedIn() 
        && in_array($_SESSION['role'], ['seller', 'both']) 
        && $_SESSION['status'] === 'active';
}

/**
 * Get current user ID
 * @return int|null User ID or null
 */
function getCurrentUserID() {
    return isset($_SESSION['userID']) ? $_SESSION['userID'] : null;
}

/**
 * Get current admin ID
 * @return int|null Admin ID or null
 */
function getCurrentAdminID() {
    return isset($_SESSION['adminID']) ? $_SESSION['adminID'] : null;
}

/**
 * Get user status for display
 * @return string Status description
 */
function getUserStatusMessage() {
    if (!isLoggedIn()) {
        return '';
    }
    
    if ($_SESSION['status'] === 'pending') {
        return '<div class="alert alert-warning">Your account is pending admin verification. Some features may be limited.</div>';
    }
    
    return '';
}
?>
