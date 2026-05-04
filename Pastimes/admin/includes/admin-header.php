<?php
/**
 * admin/includes/admin-header.php — Admin Panel Shared Layout
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['adminID'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <nav class="admin-navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <a href="../index.php" style="text-decoration: none; color: white; font-weight: 700; font-size: 1.3rem;">
                    <i class="fas fa-home"></i> Pastimes
                </a>
            </div>
            <div class="navbar-right">
                <span style="color: white; margin-right: 1rem;">
                    <i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($_SESSION['adminName']); ?>
                </span>
                <a href="logout.php" style="color: white; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="listings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'listings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Listings
                </a>
                <a href="orders.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
                <a href="messages.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Messages
                </a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
