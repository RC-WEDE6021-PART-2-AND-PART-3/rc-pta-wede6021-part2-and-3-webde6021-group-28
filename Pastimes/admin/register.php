<?php
/**
 * admin/register.php — Create new administrator account
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

session_start();
require_once '../includes/DBConn.php';
require_once '../includes/functions.php';
require_once '../includes/classes/User.php';

// Check if user is already logged in as admin
if (isset($_SESSION['adminID'])) {
    redirect('../admin/dashboard.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $fullName = sanitizeInput($_POST['fullName'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate all fields are filled
    if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    // Validate password length
    elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    }
    // Validate passwords match
    elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match. Please try again.';
    }
    // Validate username is unique
    else {
        try {
            // Check if username already exists in tblAdmin
            $stmt = $conn->prepare("SELECT adminID FROM tblAdmin WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = 'Username already exists. Please choose a different username.';
            } else {
                // Check if email already exists in tblAdmin
                $stmt = $conn->prepare("SELECT adminID FROM tblAdmin WHERE email = ?");
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error = 'Email address already in use. Please use a different email.';
                } else {
                    // Hash the password using MD5
                    $passwordHash = hashPassword($password);

                    // Insert new admin into database
                    $stmt = $conn->prepare("INSERT INTO tblAdmin (fullName, email, username, passwordHash) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $fullName, $email, $username, $passwordHash);

                    if ($stmt->execute()) {
                        $success = 'Admin account created successfully! Redirecting to login...';
                        if (function_exists('appendPlainDataFile')) {
                            // Format: fullName\temail\tusername\tpasswordHash
                            appendPlainDataFile('adminData.txt', $fullName . "\t" . $email . "\t" . $username . "\t" . $passwordHash);
                        }
                        // Clear form fields
                        $fullName = '';
                        $email = '';
                        $username = '';
                        $password = '';
                        $confirmPassword = '';

                        // Redirect after 2 seconds
                        header('refresh:2;url=login.php');
                    } else {
                        $error = 'Failed to create admin account. Please try again.';
                    }
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | Pastimes</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Main Styles -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .admin-register-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-top: 4px solid #667eea;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 40px;
        }

        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2d2d2d;
            margin: 0 0 10px;
        }

        .admin-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d2d2d;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .password-strength {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #2e7d32;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .form-validation {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-validation i {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .form-validation.invalid {
            color: #c62828;
        }

        .form-validation.valid {
            color: #2e7d32;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: #764ba2;
        }

        .info-box {
            background: #f5f7fa;
            border-left: 4px solid #667eea;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        .info-box strong {
            color: #2d2d2d;
        }

        @media (max-width: 480px) {
            .admin-register-container {
                padding: 30px 20px;
                margin: 20px;
            }

            .admin-header h1 {
                font-size: 24px;
            }

            .admin-icon {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-register-container">
        <!-- Admin Icon and Header -->
        <div class="admin-header">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Create Admin Account</h1>
            <p>Register a new administrator for Pastimes</p>
        </div>

        <!-- Information Box -->
        <div class="info-box">
            <strong><i class="fas fa-info-circle"></i> Note:</strong> Admin accounts have full system access. Only authorized administrators should create new admin accounts.
        </div>

        <!-- Error Message Display -->
        <?php if (!empty($error)): ?>
            <div class="error-message show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Success Message Display -->
        <?php if (!empty($success)): ?>
            <div class="success-message show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="" id="adminRegisterForm" novalidate>
            <!-- Full Name Field -->
            <div class="form-group">
                <label for="fullName">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <input 
                    type="text" 
                    id="fullName" 
                    name="fullName" 
                    placeholder="e.g., John Smith"
                    required
                    value="<?php echo htmlspecialchars($fullName ?? ''); ?>"
                    maxlength="100"
                >
            </div>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="admin@pastimes.co.za"
                    required
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    maxlength="150"
                >
                <div class="form-validation" id="emailValidation"></div>
            </div>

            <!-- Username Field -->
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-at"></i> Username
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="admin_username"
                    required
                    value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    minlength="4"
                    maxlength="50"
                >
                <div class="form-validation" id="usernameValidation"></div>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Min. 8 characters"
                    required
                    minlength="8"
                    maxlength="255"
                >
                <div class="password-strength" id="passwordStrength">Password strength: <span id="strengthText">Weak</span></div>
                <div class="form-validation" id="passwordValidation"></div>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="confirmPassword">
                    <i class="fas fa-check-circle"></i> Confirm Password
                </label>
                <input 
                    type="password" 
                    id="confirmPassword" 
                    name="confirmPassword" 
                    placeholder="Re-enter your password"
                    required
                    minlength="8"
                    maxlength="255"
                >
                <div class="form-validation" id="confirmValidation"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">
                <i class="fas fa-user-plus"></i> Create Admin Account
            </button>
        </form>

        <!-- Footer Links -->
        <div class="form-footer">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>

    <!-- JavaScript Validation and Interaction -->
    <script>
        // Form elements
        const form = document.getElementById('adminRegisterForm');
        const fullNameInput = document.getElementById('fullName');
        const emailInput = document.getElementById('email');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const submitBtn = document.querySelector('.submit-btn');

        // Validation elements
        const emailValidation = document.getElementById('emailValidation');
        const usernameValidation = document.getElementById('usernameValidation');
        const passwordValidation = document.getElementById('passwordValidation');
        const confirmValidation = document.getElementById('confirmValidation');
        const strengthText = document.getElementById('strengthText');

        /**
         * Validate email format
         */
        function validateEmail() {
            const email = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === '') {
                emailValidation.innerHTML = '';
                emailValidation.classList.remove('valid', 'invalid');
            } else if (emailRegex.test(email)) {
                emailValidation.innerHTML = '<i class="fas fa-check"></i> Valid email format';
                emailValidation.classList.remove('invalid');
                emailValidation.classList.add('valid');
                return true;
            } else {
                emailValidation.innerHTML = '<i class="fas fa-times"></i> Invalid email format';
                emailValidation.classList.remove('valid');
                emailValidation.classList.add('invalid');
                return false;
            }
            return false;
        }

        /**
         * Validate username (minimum 4 characters, alphanumeric + underscore)
         */
        function validateUsername() {
            const username = usernameInput.value.trim();
            const usernameRegex = /^[a-zA-Z0-9_]{4,}$/;

            if (username === '') {
                usernameValidation.innerHTML = '';
                usernameValidation.classList.remove('valid', 'invalid');
            } else if (usernameRegex.test(username)) {
                usernameValidation.innerHTML = '<i class="fas fa-check"></i> Valid username';
                usernameValidation.classList.remove('invalid');
                usernameValidation.classList.add('valid');
                return true;
            } else {
                usernameValidation.innerHTML = '<i class="fas fa-times"></i> Username must be 4+ characters (letters, numbers, underscore)';
                usernameValidation.classList.remove('valid');
                usernameValidation.classList.add('invalid');
                return false;
            }
            return false;
        }

        /**
         * Validate password strength
         */
        function validatePassword() {
            const password = passwordInput.value;
            let strength = 'Weak';
            let score = 0;

            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;

            if (score <= 1) strength = 'Weak';
            else if (score <= 2) strength = 'Fair';
            else if (score <= 3) strength = 'Good';
            else strength = 'Strong';

            strengthText.textContent = strength;

            if (password.length < 8) {
                passwordValidation.innerHTML = '<i class="fas fa-times"></i> Password must be at least 8 characters';
                passwordValidation.classList.remove('valid');
                passwordValidation.classList.add('invalid');
                return false;
            } else {
                passwordValidation.innerHTML = '<i class="fas fa-check"></i> Password meets requirements';
                passwordValidation.classList.remove('invalid');
                passwordValidation.classList.add('valid');
                return true;
            }
        }

        /**
         * Validate password confirmation
         */
        function validateConfirmPassword() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword === '') {
                confirmValidation.innerHTML = '';
                confirmValidation.classList.remove('valid', 'invalid');
            } else if (password === confirmPassword && password.length >= 8) {
                confirmValidation.innerHTML = '<i class="fas fa-check"></i> Passwords match';
                confirmValidation.classList.remove('invalid');
                confirmValidation.classList.add('valid');
                return true;
            } else {
                confirmValidation.innerHTML = '<i class="fas fa-times"></i> Passwords do not match';
                confirmValidation.classList.remove('valid');
                confirmValidation.classList.add('invalid');
                return false;
            }
            return false;
        }

        /**
         * Check if all validations pass
         */
        function canSubmit() {
            const fullName = fullNameInput.value.trim().length > 0;
            const email = validateEmail();
            const username = validateUsername();
            const password = validatePassword();
            const confirmPassword = validateConfirmPassword();

            return fullName && email && username && password && confirmPassword;
        }

        // Add event listeners for real-time validation
        emailInput.addEventListener('blur', validateEmail);
        usernameInput.addEventListener('blur', validateUsername);
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);

        // Prevent form submission if validation fails
        form.addEventListener('submit', function(e) {
            if (!canSubmit()) {
                e.preventDefault();
                
                // Validate all fields to show errors
                validateEmail();
                validateUsername();
                validatePassword();
                validateConfirmPassword();

                // Show alert
                alert('Please fix the errors in the form before submitting.');
            }
        });

        // Auto-validate on input
        passwordInput.addEventListener('input', validateConfirmPassword);
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    </script>
</body>
</html>
