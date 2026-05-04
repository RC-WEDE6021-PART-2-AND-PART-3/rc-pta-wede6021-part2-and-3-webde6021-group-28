/**
 * main.js — Pastimes Front-End JavaScript
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 *
 * This file contains all the JavaScript functionality for the Pastimes website, including:
 * - Navbar behavior (scroll effects, dropdowns, hamburger menu)
 * - Cart functionality (AJAX add to cart, badge updates)
 * - Toast notifications for user feedback
 * - Alert auto-dismissal
 * - Form utilities (password toggle, confirmation dialogs)
 * - Utility functions (price formatting, loading states, image preview)
 *
 * We have implemented this code using vanilla JavaScript to ensure compatibility and performance across all browsers.
 * All functions are modular and well-documented for maintainability and scalability as the project grows.
 * We have also included error handling for AJAX requests and user interactions to enhance the user experience and provide clear feedback in case of issues.
 * This code is designed to work seamlessly with the corresponding HTML and CSS files to create a cohesive and interactive user interface for the Pastimes website.
 */

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    initNavbar();
    initUserDropdown();
    initHamburgerMenu();
    initCartFunctionality();
    initAlertDismiss();
});

// ==================== NAVBAR ====================
function initNavbar() {
    const navbar = document.getElementById('navbar');
    
    if (!navbar) return;
    
    // Add scrolled class on scroll
    window.addEventListener('scroll', function() {
        if (window.scrollY > 80) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// ==================== USER DROPDOWN ====================
function initUserDropdown() {
    const dropdownBtn = document.getElementById('userDropdownBtn');
    const dropdownMenu = document.getElementById('userDropdownMenu');
    
    if (!dropdownBtn || !dropdownMenu) return;
    
    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('active');
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
            dropdownMenu.classList.remove('active');
        }
    });
}

// ==================== HAMBURGER MENU ====================
function initHamburgerMenu() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    if (!hamburger || !navMenu) return;
    
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
    
    // Close menu on link click
    navMenu.querySelectorAll('.nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
}

// ==================== CART FUNCTIONALITY ====================
function initCartFunctionality() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const clothingID = this.dataset.id;
            addToCart(clothingID);
        });
    });
}

function addToCart(clothingID) {
    fetch('/pastimes/ajax/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'clothingID=' + encodeURIComponent(clothingID)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cartCount);
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function updateCartBadge(count) {
    const navCart = document.getElementById('navCart');
    if (!navCart) return;
    
    let badge = navCart.querySelector('.cart-badge');
    
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            navCart.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
    
    navCart.dataset.count = count;
}

// ==================== TOAST NOTIFICATIONS ====================
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    
    container.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

// ==================== ALERT DISMISS ====================
function initAlertDismiss() {
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

// ==================== FORM UTILITIES ====================
function togglePasswordVisibility(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}

// ==================== CONFIRM DIALOGS ====================
function confirmAction(message) {
    return confirm(message);
}

function confirmDelete(itemName) {
    return confirm('Are you sure you want to delete "' + itemName + '"? This action cannot be undone.');
}

// ==================== FORMATTING ====================
function formatPrice(amount) {
    return 'R ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ==================== LOADING STATE ====================
function setLoading(element, isLoading) {
    if (isLoading) {
        element.disabled = true;
        element.dataset.originalText = element.innerHTML;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    } else {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText || element.innerHTML;
    }
}

// ==================== IMAGE PREVIEW ====================
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}

// ==================== DEBOUNCE ====================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add slideOut animation to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);
