/**
 * sell.js — Sell page JS (dropzone preview, remove image)
 * 
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 * 
 * Selling functionality including:
 * - Price suggestion algorithm
 * - Image upload preview
 * - Form validation
 * - Drag and drop support
 */

'use strict';

/**
 * Initialize sell page functionality when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    initializePriceSuggestion();
    initializeImageUpload();
    initializeFormValidation();
    initializeCharacterCounter();
});

/**
 * Price suggestion algorithm parameters
 */
const PRICE_CONFIG = {
    basePrices: {
        'outerwear': 350,
        'footwear': 300,
        'dresses': 200,
        'activewear': 180,
        'bottoms': 160,
        'accessories': 250,
        'tops': 120
    },
    conditionMultipliers: {
        'like new': 1.0,
        'good': 0.7,
        'fair': 0.4
    },
    premiumBrands: [
        'levi\'s', 'levis', 'nike', 'adidas', 'zara', 'h&m', 'hm',
        'puma', 'woolworths', 'woolies', 'tommy hilfiger', 'tommy',
        'fossil', 'ray-ban', 'rayban', 'converse', 'dr. martens',
        'dr martens', 'michael kors', 'guess', 'diesel', 'calvin klein',
        'ralph lauren', 'polo', 'lacoste', 'under armour', 'new balance'
    ],
    premiumBonus: 80
};

/**
 * Initialize price suggestion functionality
 */
function initializePriceSuggestion() {
    const brandInput = document.getElementById('brand');
    const categorySelect = document.getElementById('category');
    const conditionSelect = document.getElementById('condition');
    const priceInput = document.getElementById('price');
    const suggestionContainer = document.getElementById('priceSuggestion');
    
    if (!brandInput || !categorySelect || !conditionSelect) return;
    
    // Calculate suggestion when inputs change
    const calculateSuggestion = () => {
        const brand = brandInput.value.trim();
        const category = categorySelect.value;
        const condition = conditionSelect.value;
        
        if (brand && category && condition) {
            const suggestedPrice = calculatePriceSuggestion(brand, category, condition);
            displayPriceSuggestion(suggestedPrice, suggestionContainer, priceInput);
        } else {
            hidePriceSuggestion(suggestionContainer);
        }
    };
    
    brandInput.addEventListener('input', debounce(calculateSuggestion, 500));
    categorySelect.addEventListener('change', calculateSuggestion);
    conditionSelect.addEventListener('change', calculateSuggestion);
    
    // Auto-fill button
    const autoFillBtn = document.getElementById('usesuggstedPrice');
    if (autoFillBtn) {
        autoFillBtn.addEventListener('click', function() {
            const suggestedPrice = this.dataset.price;
            if (suggestedPrice && priceInput) {
                priceInput.value = suggestedPrice;
                priceInput.classList.add('price-filled');
                setTimeout(() => priceInput.classList.remove('price-filled'), 500);
            }
        });
    }
}

/**
 * Calculate price suggestion based on brand, category, and condition
 * @param {string} brand - Brand name
 * @param {string} category - Category name
 * @param {string} condition - Condition value
 * @returns {number} Suggested price
 */
function calculatePriceSuggestion(brand, category, condition) {
    // Get base price for category
    const basePrice = PRICE_CONFIG.basePrices[category.toLowerCase()] || 150;
    
    // Get condition multiplier
    const conditionMultiplier = PRICE_CONFIG.conditionMultipliers[condition.toLowerCase()] || 0.7;
    
    // Check if premium brand
    const brandLower = brand.toLowerCase();
    const isPremium = PRICE_CONFIG.premiumBrands.some(b => brandLower.includes(b));
    
    // Calculate final price
    let suggestedPrice = basePrice * conditionMultiplier;
    if (isPremium) {
        suggestedPrice += PRICE_CONFIG.premiumBonus;
    }
    
    // Round to nearest 5
    return Math.round(suggestedPrice / 5) * 5;
}

/**
 * Display price suggestion to user
 * @param {number} price - Suggested price
 * @param {HTMLElement} container - Container element
 * @param {HTMLElement} priceInput - Price input element
 */
function displayPriceSuggestion(price, container, priceInput) {
    if (!container) return;
    
    container.innerHTML = `
        <div class="price-suggestion-card">
            <div class="suggestion-icon">💡</div>
            <div class="suggestion-content">
                <span class="suggestion-label">Suggested price:</span>
                <span class="suggestion-price">R ${price.toFixed(2)}</span>
            </div>
            <button type="button" class="btn-use-suggestion" id="usesuggstedPrice" data-price="${price.toFixed(2)}">
                Use this price
            </button>
        </div>
        <p class="suggestion-note">Based on similar items. You can adjust as needed.</p>
    `;
    
    container.style.display = 'block';
    container.classList.add('fade-in');
    
    // Re-attach click handler
    const useBtn = container.querySelector('#usesuggstedPrice');
    if (useBtn && priceInput) {
        useBtn.addEventListener('click', function() {
            priceInput.value = this.dataset.price;
            priceInput.classList.add('price-filled');
            priceInput.focus();
            setTimeout(() => priceInput.classList.remove('price-filled'), 500);
        });
    }
}

/**
 * Hide price suggestion
 * @param {HTMLElement} container - Container element
 */
function hidePriceSuggestion(container) {
    if (container) {
        container.style.display = 'none';
        container.innerHTML = '';
    }
}

/**
 * Initialize image upload functionality
 */
function initializeImageUpload() {
    const dropZone = document.getElementById('imageDropZone');
    const fileInput = document.getElementById('itemImage');
    const previewContainer = document.getElementById('imagePreview');
    const browseBtn = document.getElementById('browseFiles');
    
    if (!dropZone || !fileInput) return;
    
    // Click to browse
    if (browseBtn) {
        browseBtn.addEventListener('click', () => fileInput.click());
    }
    
    dropZone.addEventListener('click', (e) => {
        if (e.target === dropZone || e.target.closest('.drop-zone-content')) {
            fileInput.click();
        }
    });
    
    // Drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('drag-over');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('drag-over');
        });
    });
    
    // Handle dropped files
    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        handleFiles(files, fileInput, previewContainer, dropZone);
    });
    
    // Handle selected files
    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files, fileInput, previewContainer, dropZone);
    });
}

/**
 * Prevent default drag behaviors
 * @param {Event} e - Event object
 */
function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

/**
 * Handle uploaded files
 * @param {FileList} files - List of files
 * @param {HTMLElement} fileInput - File input element
 * @param {HTMLElement} previewContainer - Preview container
 * @param {HTMLElement} dropZone - Drop zone element
 */
function handleFiles(files, fileInput, previewContainer, dropZone) {
    if (files.length === 0) return;
    
    const file = files[0];
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showError('Please upload a JPG, PNG, WEBP, or GIF image.');
        return;
    }
    
    // Validate file size (max 10MB)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        showError('Image size must be less than 10MB.');
        return;
    }
    
    // Update file input
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    
    // Show preview
    displayImagePreview(file, previewContainer, dropZone);
}

/**
 * Display image preview
 * @param {File} file - Image file
 * @param {HTMLElement} previewContainer - Preview container
 * @param {HTMLElement} dropZone - Drop zone element
 */
function displayImagePreview(file, previewContainer, dropZone) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
        if (previewContainer) {
            previewContainer.innerHTML = `
                <div class="image-preview-item">
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeImage()">&times;</button>
                    <div class="image-info">
                        <span class="image-name">${file.name}</span>
                        <span class="image-size">${formatFileSize(file.size)}</span>
                    </div>
                </div>
            `;
            previewContainer.style.display = 'block';
        }
        
        // Hide drop zone content
        if (dropZone) {
            const dropContent = dropZone.querySelector('.drop-zone-content');
            if (dropContent) {
                dropContent.style.display = 'none';
            }
        }
    };
    
    reader.readAsDataURL(file);
}

/**
 * Remove uploaded image
 */
function removeImage() {
    const fileInput = document.getElementById('itemImage');
    const previewContainer = document.getElementById('imagePreview');
    const dropZone = document.getElementById('imageDropZone');
    
    if (fileInput) {
        fileInput.value = '';
    }
    
    if (previewContainer) {
        previewContainer.innerHTML = '';
        previewContainer.style.display = 'none';
    }
    
    if (dropZone) {
        const dropContent = dropZone.querySelector('.drop-zone-content');
        if (dropContent) {
            dropContent.style.display = 'flex';
        }
    }
}

/**
 * Format file size for display
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted size string
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const sellForm = document.getElementById('sellForm');
    if (!sellForm) return;
    
    sellForm.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Validate title
        const title = document.getElementById('title');
        if (title && title.value.trim().length < 5) {
            isValid = false;
            errors.push('Title must be at least 5 characters');
            showFieldError(title, 'Title must be at least 5 characters');
        } else if (title) {
            clearFieldError(title);
        }
        
        // Validate brand
        const brand = document.getElementById('brand');
        if (brand && brand.value.trim().length < 2) {
            isValid = false;
            errors.push('Please enter a valid brand name');
            showFieldError(brand, 'Please enter a valid brand name');
        } else if (brand) {
            clearFieldError(brand);
        }
        
        // Validate category
        const category = document.getElementById('category');
        if (category && !category.value) {
            isValid = false;
            errors.push('Please select a category');
            showFieldError(category, 'Please select a category');
        } else if (category) {
            clearFieldError(category);
        }
        
        // Validate size
        const size = document.getElementById('size');
        if (size && !size.value) {
            isValid = false;
            errors.push('Please select a size');
            showFieldError(size, 'Please select a size');
        } else if (size) {
            clearFieldError(size);
        }
        
        // Validate condition
        const condition = document.getElementById('condition');
        if (condition && !condition.value) {
            isValid = false;
            errors.push('Please select condition');
            showFieldError(condition, 'Please select condition');
        } else if (condition) {
            clearFieldError(condition);
        }
        
        // Validate price
        const price = document.getElementById('price');
        if (price) {
            const priceValue = parseFloat(price.value);
            if (isNaN(priceValue) || priceValue <= 0) {
                isValid = false;
                errors.push('Please enter a valid price greater than 0');
                showFieldError(price, 'Please enter a valid price greater than 0');
            } else if (priceValue > 50000) {
                isValid = false;
                errors.push('Price cannot exceed R50,000');
                showFieldError(price, 'Price cannot exceed R50,000');
            } else {
                clearFieldError(price);
            }
        }
        
        // Validate image
        const imageInput = document.getElementById('itemImage');
        if (imageInput && imageInput.files.length === 0) {
            isValid = false;
            errors.push('Please upload an image of your item');
            const dropZone = document.getElementById('imageDropZone');
            if (dropZone) {
                dropZone.classList.add('error');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Show error summary
            showErrorSummary(errors);
            
            // Scroll to first error
            const firstError = document.querySelector('.form-group.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Clear errors on input
    const inputs = sellForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', () => clearFieldError(input));
        input.addEventListener('change', () => clearFieldError(input));
    });
}

/**
 * Show error on specific field
 * @param {HTMLElement} field - Form field element
 * @param {string} message - Error message
 */
function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    if (formGroup) {
        formGroup.classList.add('error');
        
        // Remove existing error message
        const existingError = formGroup.querySelector('.field-error');
        if (existingError) existingError.remove();
        
        // Add error message
        const errorEl = document.createElement('span');
        errorEl.className = 'field-error';
        errorEl.textContent = message;
        formGroup.appendChild(errorEl);
    }
    
    field.classList.add('input-error');
}

/**
 * Clear error from specific field
 * @param {HTMLElement} field - Form field element
 */
function clearFieldError(field) {
    const formGroup = field.closest('.form-group');
    if (formGroup) {
        formGroup.classList.remove('error');
        const errorEl = formGroup.querySelector('.field-error');
        if (errorEl) errorEl.remove();
    }
    
    field.classList.remove('input-error');
    
    // Clear drop zone error
    const dropZone = document.getElementById('imageDropZone');
    if (dropZone) {
        dropZone.classList.remove('error');
    }
}

/**
 * Show error summary at top of form
 * @param {array} errors - Array of error messages
 */
function showErrorSummary(errors) {
    // Remove existing summary
    const existingSummary = document.querySelector('.error-summary');
    if (existingSummary) existingSummary.remove();
    
    const summaryHtml = `
        <div class="error-summary">
            <strong>Please fix the following errors:</strong>
            <ul>
                ${errors.map(e => `<li>${e}</li>`).join('')}
            </ul>
        </div>
    `;
    
    const form = document.getElementById('sellForm');
    if (form) {
        form.insertAdjacentHTML('afterbegin', summaryHtml);
    }
}

/**
 * Initialize character counter for description
 */
function initializeCharacterCounter() {
    const description = document.getElementById('description');
    const counter = document.getElementById('descriptionCounter');
    const maxLength = 1000;
    
    if (!description || !counter) return;
    
    const updateCounter = () => {
        const length = description.value.length;
        counter.textContent = `${length}/${maxLength}`;
        
        if (length > maxLength * 0.9) {
            counter.classList.add('warning');
        } else {
            counter.classList.remove('warning');
        }
        
        if (length >= maxLength) {
            counter.classList.add('limit');
        } else {
            counter.classList.remove('limit');
        }
    };
    
    description.addEventListener('input', updateCounter);
    updateCounter();
}

/**
 * Show error notification
 * @param {string} message - Error message
 */
function showError(message) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-error';
    toast.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Debounce function to limit rapid calls
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {function} Debounced function
 */
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

// Make removeImage available globally
window.removeImage = removeImage;
