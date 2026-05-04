<?php
/**
 * login.php — User Login Page
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

session_start();
require_once 'includes/DBConn.php';
require_once 'includes/functions.php';
require_once 'includes/classes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$pageTitle = 'Login';
$error = '';
$stickyUsername = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
        $stickyUsername = $username;
    } else {
        $user = new User($conn);
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            // Set session variables
            $_SESSION['userID'] = $result['user']['userID'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['fullName'] = $result['user']['fullName'];
            $_SESSION['role'] = $result['user']['role'];
            $_SESSION['email'] = $result['user']['email'];
            $_SESSION['status'] = $result['user']['status'];
            $_SESSION['profilePic'] = $result['user']['profilePic'] ?? 'images/default-avatar.png';
            
            // Display login banner and redirect
            $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($result['user']['fullName']) . '!';
            
            // Redirect to intended page or dashboard
            $redirectTo = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'dashboard.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirectTo);
        } else {
            $error = $result['message'];
            if (isset($result['sticky']) && $result['sticky']) {
                $stickyUsername = $username;
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
.auth-page {
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-xl);
    background-color: var(--off-white);
}

.auth-card {
    width: 100%;
    max-width: 420px;
    background-color: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    box-shadow: var(--shadow-lg);
}

.auth-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.auth-logo {
    width: 60px;
    height: 60px;
    background-color: var(--primary-dark);
    color: var(--white);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-heading);
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto var(--spacing-md);
}

.auth-title {
    font-family: var(--font-heading);
    font-size: 1.75rem;
    color: var(--primary-dark);
    margin-bottom: var(--spacing-xs);
}

.auth-subtitle {
    color: var(--medium-gray);
    margin: 0;
}

.demo-accounts {
    background-color: var(--cream);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.demo-accounts p {
    font-size: 0.875rem;
    color: var(--dark-gray);
    margin-bottom: var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.demo-buttons {
    display: flex;
    gap: var(--spacing-sm);
}

.demo-btn {
    padding: var(--spacing-xs) var(--spacing-md);
    font-size: 0.8125rem;
    font-weight: 600;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all var(--transition-base);
}

.demo-btn.admin {
    background-color: var(--info-light);
    color: var(--info);
    border: 1px solid var(--info);
}

.demo-btn.seller {
    background-color: var(--success-light);
    color: var(--success);
    border: 1px solid var(--success);
}

.demo-btn.buyer {
    background-color: var(--warning-light);
    color: #92400e;
    border: 1px solid var(--warning);
}

.demo-btn:hover {
    transform: translateY(-1px);
}

.auth-footer {
    text-align: center;
    margin-top: var(--spacing-lg);
    color: var(--dark-gray);
}

.auth-footer a {
    color: var(--accent-orange);
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.prototype-note {
    text-align: center;
    margin-top: var(--spacing-lg);
    color: var(--medium-gray);
    font-size: 0.8125rem;
}
</style>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">P</div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to your Pastimes account</p>
        </div>
        
        <!-- Demo Accounts -->
        <div class="demo-accounts">
            <p><i class="fas fa-info-circle"></i> Demo accounts (click to fill)</p>
            <div class="demo-buttons">
                <button type="button" class="demo-btn admin" onclick="fillDemoAccount('admin')">Admin</button>
                <button type="button" class="demo-btn seller" onclick="fillDemoAccount('seller')">Seller</button>
                <button type="button" class="demo-btn buyer" onclick="fillDemoAccount('buyer')">Buyer</button>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-input" 
                       placeholder="Enter your username"
                       value="<?php echo htmlspecialchars($stickyUsername); ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Enter your password"
                           required>
                    <i class="fas fa-eye input-icon" onclick="togglePasswordVisibility('password', this)"></i>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full btn-lg">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
        <p class="prototype-note">
            This is a prototype. No real credentials are stored or validated.
        </p>
    </div>
</div>

<script src="/pastimes/js/validate.js"></script>

<?php include 'includes/footer.php'; ?>
