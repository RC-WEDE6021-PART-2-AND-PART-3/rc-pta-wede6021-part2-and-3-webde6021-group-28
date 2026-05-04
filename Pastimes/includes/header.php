<?php
/**
 * header.php — Global HTML Head + Navigation Bar
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/functions.php';
// Authentication helpers (session, roles, status messages)
require_once __DIR__ . '/auth.php';

// Get cart count
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pastimes - Your Online Clothing Store. Buy and sell quality second-hand branded clothing.">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | Pastimes' : 'Pastimes - Your Online Clothing Store'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/pastimes/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <a href="/pastimes/index.php" class="nav-logo">
                <span class="logo-icon">P</span>
                <span class="logo-text">Pastimes</span>
            </a>
            
            <!-- Navigation Links -->
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="/pastimes/shop.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'shop.php' ? 'active' : ''; ?>">Shop</a>
                </li>
                <li class="nav-item">
                    <a href="/pastimes/about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : ''; ?>">About</a>
                </li>
                <?php if (isLoggedIn() && canSell()): ?>
                <li class="nav-item">
                    <a href="/pastimes/sell.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'sell.php' ? 'active' : ''; ?>">Sell</a>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Right Side Actions -->
            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <!-- My Account Link -->
                    <a href="/pastimes/dashboard.php" class="nav-action-link">
                        <i class="fas fa-user-circle"></i>
                        <span>My Account</span>
                    </a>
                    
                    <!-- Cart -->
                    <a href="/pastimes/cart.php" class="nav-cart" id="navCart" data-count="<?php echo $cartCount; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Dropdown -->
                    <div class="nav-user-dropdown">
                        <button class="nav-user-btn" id="userDropdownBtn">
                            <span class="user-avatar"><?php echo strtoupper(substr($_SESSION['fullName'], 0, 1)); ?></span>
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['fullName']); ?></span>
                        </button>
                        <div class="dropdown-menu" id="userDropdownMenu">
                            <a href="/pastimes/dashboard.php" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a href="/pastimes/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="/pastimes/my-orders.php" class="dropdown-item">
                                <i class="fas fa-box"></i> My Orders
                            </a>
                            <a href="/pastimes/messages.php" class="dropdown-item">
                                <i class="fas fa-envelope"></i> Messages
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/pastimes/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login/Register -->
                    <a href="/pastimes/login.php" class="nav-link btn-login">Login</a>
                    <a href="/pastimes/register.php" class="btn btn-primary btn-register">Register</a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php
        // Display session messages
        if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['info']; unset($_SESSION['info']); ?>
            </div>
        <?php endif; ?>
        
        <?php echo getUserStatusMessage(); ?>
