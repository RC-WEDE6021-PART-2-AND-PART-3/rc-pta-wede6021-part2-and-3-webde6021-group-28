/**
 * validate.js - Client-side form validation for Pastimes
 * 
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    initRegisterValidation();
    initLoginValidation();
    initSellFormValidation();
});

// ==================== REGISTER FORM VALIDATION ====================
function initRegisterValidation() {
    const form = document.getElementById('registerForm');
    if (!form) return;
    
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const email = document.getElementById('email');
    
    // Real-time password validation
    if (password) {
        password.addEventListener('input', function() {
            validatePasswordLength(this);
        });
    }
    
    // Real-time confirm password validation
    if (confirmPassword && password) {
        confirmPassword.addEventListener('input', function() {
            validatePasswordMatch(password, this);
        });
    }
    
    // Real-time email validation
    if (email) {
        email.addEventListener('input', function() {
            validateEmail(this);
        });
    }
    
    // Form submit validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate all required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
        
        // Validate email format
        if (email && email.value && !isValidEmail(email.value)) {
            showFieldError(email, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate password length
        if (password && password.value.length < 8) {
            showFieldError(password, 'Password must be at least 8 characters');
            isValid = false;
        }
        
        // Validate password match
        if (password && confirmPassword && password.value !== confirmPassword.value) {
            showFieldError(confirmPassword, 'Passwords do not match');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// ==================== LOGIN FORM VALIDATION ====================
function initLoginValidation() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        
        if (username && !username.value.trim()) {
            showFieldError(username, 'Username is required');
            isValid = false;
        } else if (username) {
            clearFieldError(username);
        }
        
        if (password && !password.value.trim()) {
            showFieldError(password, 'Password is required');
            isValid = false;
        } else if (password) {
            clearFieldError(password);
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// ==================== SELL FORM VALIDATION ====================
function initSellFormValidation() {
    const form = document.getElementById('sellForm');
    if (!form) return;
    
    const priceInput = document.getElementById('price');
    const imageInput = document.getElementById('image');
    
    // Price validation
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (isNaN(value) || value <= 0) {
                showFieldError(this, 'Price must be greater than 0');
            } else {
                clearFieldError(this);
            }
        });
    }
    
    // Image validation
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            validateImageFile(this);
        });
    }
    
    // Form submit validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
        
        // Validate price
        if (priceInput) {
            const price = parseFloat(priceInput.value);
            if (isNaN(price) || price <= 0) {
                showFieldError(priceInput, 'Please enter a valid price greater than 0');
                isValid = false;
            }
        }
        
        // Validate image file type
        if (imageInput && imageInput.files.length > 0) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            const file = imageInput.files[0];
            
            if (!validTypes.includes(file.type)) {
                showFieldError(imageInput, 'Please upload a JPG, PNG, or WebP image');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// ==================== VALIDATION HELPERS ====================
function validatePasswordLength(input) {
    if (input.value.length > 0 && input.value.length < 8) {
        showFieldError(input, 'Password must be at least 8 characters');
        return false;
    } else {
        clearFieldError(input);
        return true;
    }
}

function validatePasswordMatch(password, confirmPassword) {
    if (confirmPassword.value && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        return false;
    } else {
        clearFieldError(confirmPassword);
        return true;
    }
}

function validateEmail(input) {
    if (input.value && !isValidEmail(input.value)) {
        showFieldError(input, 'Please enter a valid email address');
        return false;
    } else {
        clearFieldError(input);
        return true;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateImageFile(input) {
    if (!input.files || input.files.length === 0) return true;
    
    const file = input.files[0];
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!validTypes.includes(file.type)) {
        showFieldError(input, 'Please upload a JPG, PNG, or WebP image');
        return false;
    }
    
    if (file.size > maxSize) {
        showFieldError(input, 'Image must be less than 10MB');
        return false;
    }
    
    clearFieldError(input);
    return true;
}

// ==================== ERROR DISPLAY ====================
function showFieldError(input, message) {
    // Add error class to input
    input.classList.add('error');
    
    // Find or create error message element
    let errorDiv = input.parentElement.querySelector('.form-error');
    
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        input.parentElement.appendChild(errorDiv);
    }
    
    errorDiv.textContent = message;
}

function clearFieldError(input) {
    input.classList.remove('error');
    
    const errorDiv = input.parentElement.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// ==================== DEMO ACCOUNT FILL ====================
function fillDemoAccount(type) {
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    if (!usernameInput || !passwordInput) return;
    
    const accounts = {
        'admin': { username: 'admin', password: 'Admin1234' },
        'seller': { username: 'thabo', password: 'Pass1234' },
        'buyer': { username: 'lerato', password: 'Pass1234' }
    };
    
    if (accounts[type]) {
        usernameInput.value = accounts[type].username;
        passwordInput.value = accounts[type].password;
    }
}
