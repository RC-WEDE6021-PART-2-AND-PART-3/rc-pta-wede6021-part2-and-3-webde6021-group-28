<?php
/**
 * register.php — User Registration Page
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

$pageTitle = 'Register';
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $fullName = sanitizeInput($_POST['fullName'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = sanitizeInput($_POST['role'] ?? 'buyer');
    
    // Validation
    if (empty($fullName)) {
        $errors['fullName'] = 'Full name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Passwords do not match';
    }
    
    if (!in_array($role, ['buyer', 'seller', 'both'])) {
        $errors['role'] = 'Please select a valid role';
    }
    
    // If no errors, attempt registration
    if (empty($errors)) {
        $user = new User($conn);
        $result = $user->register([
            'fullName' => $fullName,
            'email' => strtolower($email),
            'username' => $username,
            'password' => $password,
            'role' => $role
        ]);
        
        if ($result['success']) {
            $success = $result['message'];
            // Clear form data
            $fullName = $email = $username = $role = '';
        } else {
            $errors['general'] = $result['message'];
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
    max-width: 500px;
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

.role-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.role-option {
    position: relative;
}

.role-option input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.role-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--spacing-lg);
    border: 2px solid var(--light-gray);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-base);
}

.role-option input:checked + .role-card {
    border-color: var(--primary-dark);
    background-color: var(--cream);
}

.role-card i {
    font-size: 1.5rem;
    margin-bottom: var(--spacing-sm);
    color: var(--primary-dark);
}

.role-card .role-title {
    font-weight: 600;
    color: var(--primary-dark);
    margin-bottom: var(--spacing-xs);
}

.role-card .role-desc {
    font-size: 0.8125rem;
    color: var(--medium-gray);
    text-align: center;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.auth-note {
    background-color: var(--warning-light);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.auth-note strong {
    color: #92400e;
}

.auth-note p {
    color: #92400e;
    margin: 0;
    font-size: 0.875rem;
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
</style>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">P</div>
            <h1 class="auth-title">Create Your Account</h1>
            <p class="auth-subtitle">Join Pastimes — free to sign up</p>
        </div>
        
        <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error">
            <?php echo $errors['general']; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <!-- Role Selection -->
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="buyer" <?php echo (!isset($role) || $role === 'buyer') ? 'checked' : ''; ?>>
                    <div class="role-card">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="role-title">I want to Buy</span>
                        <span class="role-desc">Browse & purchase clothing</span>
                    </div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="seller" <?php echo (isset($role) && $role === 'seller') ? 'checked' : ''; ?>>
                    <div class="role-card">
                        <i class="fas fa-store"></i>
                        <span class="role-title">I want to Sell</span>
                        <span class="role-desc">List & sell your items</span>
                    </div>
                </label>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fullName">Full Name</label>
                    <input type="text" 
                           id="fullName" 
                           name="fullName" 
                           class="form-input <?php echo isset($errors['fullName']) ? 'error' : ''; ?>" 
                           placeholder="Sheketli Mochaki"
                           value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>"
                           required>
                    <?php if (isset($errors['fullName'])): ?>
                    <div class="form-error"><?php echo $errors['fullName']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-input <?php echo isset($errors['username']) ? 'error' : ''; ?>" 
                           placeholder="sheketli_mochaki"
                           value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                           required>
                    <?php if (isset($errors['username'])): ?>
                    <div class="form-error"><?php echo $errors['username']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                       placeholder="sheketli@example.com"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                       required>
                <?php if (isset($errors['email'])): ?>
                <div class="form-error"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>" 
                           placeholder="Min. 8 characters"
                           minlength="8"
                           required>
                    <i class="fas fa-eye input-icon" onclick="togglePasswordVisibility('password', this)"></i>
                </div>
                <?php if (isset($errors['password'])): ?>
                <div class="form-error"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirmPassword">Confirm Password</label>
                <div class="input-group">
                    <input type="password" 
                           id="confirmPassword" 
                           name="confirmPassword" 
                           class="form-input <?php echo isset($errors['confirmPassword']) ? 'error' : ''; ?>" 
                           placeholder="Re-enter your password"
                           required>
                    <i class="fas fa-eye input-icon" onclick="togglePasswordVisibility('confirmPassword', this)"></i>
                </div>
                <?php if (isset($errors['confirmPassword'])): ?>
                <div class="form-error"><?php echo $errors['confirmPassword']; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full btn-lg">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
            
            <div class="auth-note">
                <p><strong>Note:</strong> New accounts require administrator approval before login is enabled. You'll be notified once approved.</p>
            </div>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>

<script src="/pastimes/js/validate.js"></script>

<?php include 'includes/footer.php'; ?>
