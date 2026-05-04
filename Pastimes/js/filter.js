/**
 * filter.js — Pastimes Filter JavaScript
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 * 
 * This file contains JavaScript code for handling the filter functionality on the Pastimes website. 
 * It listens for changes to the filter dropdown and updates the displayed pastimes accordingly. 
 * The filtering is done by showing or hiding pastimes based on their category, which is determined by a data attribute on each pastime element.    
 */

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    initSearchFilter();
    initCategoryFilter();
    initConditionFilter();
    initPriceFilter();
    initSortFilter();
});

// ==================== SEARCH FILTER ====================
function initSearchFilter() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    // Debounced search
    searchInput.addEventListener('input', debounce(function() {
        const searchTerm = this.value.toLowerCase().trim();
        filterProducts();
    }, 300));
}

// ==================== CATEGORY FILTER ====================
function initCategoryFilter() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const categorySelect = document.getElementById('categoryFilter');
    
    // Category buttons (pill-style)
    categoryButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Remove active from all
            categoryButtons.forEach(b => b.classList.remove('active'));
            // Add active to clicked
            this.classList.add('active');
            
            // Update select if exists
            if (categorySelect) {
                categorySelect.value = this.dataset.category || '';
            }
            
            filterProducts();
        });
    });
    
    // Category dropdown select
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterProducts();
        });
    }
    
    // Sidebar category links
    document.querySelectorAll('.filter-category-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active from all
            document.querySelectorAll('.filter-category-link').forEach(l => l.classList.remove('active'));
            // Add active to clicked
            this.classList.add('active');
            
            filterProducts();
        });
    });
}

// ==================== CONDITION FILTER ====================
function initConditionFilter() {
    const conditionSelect = document.getElementById('conditionFilter');
    const conditionLinks = document.querySelectorAll('.filter-condition-link');
    
    if (conditionSelect) {
        conditionSelect.addEventListener('change', function() {
            filterProducts();
        });
    }
    
    conditionLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            conditionLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            filterProducts();
        });
    });
}

// ==================== PRICE FILTER ====================
function initPriceFilter() {
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    const priceRange = document.getElementById('priceRange');
    
    if (minPrice) {
        minPrice.addEventListener('input', debounce(function() {
            filterProducts();
        }, 500));
    }
    
    if (maxPrice) {
        maxPrice.addEventListener('input', debounce(function() {
            filterProducts();
        }, 500));
    }
    
    if (priceRange) {
        priceRange.addEventListener('input', function() {
            const value = this.value;
            const display = document.getElementById('priceRangeValue');
            if (display) {
                display.textContent = 'R' + value;
            }
            debounce(filterProducts, 300)();
        });
    }
}

// ==================== SORT FILTER ====================
function initSortFilter() {
    const sortSelect = document.getElementById('sortFilter');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortProducts(this.value);
        });
    }
}

// ==================== MAIN FILTER FUNCTION ====================
function filterProducts() {
    const products = document.querySelectorAll('.product-card');
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categoryFilter');
    const conditionSelect = document.getElementById('conditionFilter');
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    
    // Get filter values
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const category = categorySelect ? categorySelect.value.toLowerCase() : '';
    const condition = conditionSelect ? conditionSelect.value.toLowerCase() : '';
    const min = minPrice ? parseFloat(minPrice.value) || 0 : 0;
    const max = maxPrice ? parseFloat(maxPrice.value) || Infinity : Infinity;
    
    // Also check active category button
    const activeCategory = document.querySelector('.category-btn.active');
    const activeCategoryValue = activeCategory ? (activeCategory.dataset.category || '').toLowerCase() : '';
    
    // Check active sidebar category
    const activeSidebarCategory = document.querySelector('.filter-category-link.active');
    const sidebarCategoryValue = activeSidebarCategory ? (activeSidebarCategory.dataset.category || '').toLowerCase() : '';
    
    const finalCategory = category || activeCategoryValue || sidebarCategoryValue;
    
    let visibleCount = 0;
    
    products.forEach(function(product) {
        const title = (product.dataset.title || '').toLowerCase();
        const brand = (product.dataset.brand || '').toLowerCase();
        const productCategory = (product.dataset.category || '').toLowerCase();
        const productCondition = (product.dataset.condition || '').toLowerCase();
        const price = parseFloat(product.dataset.price) || 0;
        
        // Check all filters
        const matchesSearch = !searchTerm || 
            title.includes(searchTerm) || 
            brand.includes(searchTerm);
        
        const matchesCategory = !finalCategory || 
            finalCategory === 'all' || 
            productCategory === finalCategory;
        
        const matchesCondition = !condition || 
            condition === 'all' || 
            productCondition.includes(condition);
        
        const matchesPrice = price >= min && price <= max;
        
        // Show/hide product
        if (matchesSearch && matchesCategory && matchesCondition && matchesPrice) {
            product.style.display = '';
            product.style.animation = 'fadeInUp 0.3s ease forwards';
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    // Update results count
    const resultsCount = document.getElementById('resultsCount');
    if (resultsCount) {
        resultsCount.textContent = visibleCount + ' results';
    }
    
    // Show/hide no results message
    const noResults = document.getElementById('noResults');
    if (noResults) {
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// ==================== SORT FUNCTION ====================
function sortProducts(sortBy) {
    const container = document.getElementById('productsGrid');
    if (!container) return;
    
    const products = Array.from(container.querySelectorAll('.product-card'));
    
    products.sort(function(a, b) {
        switch (sortBy) {
            case 'price-low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price-high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'newest':
                return new Date(b.dataset.date) - new Date(a.dataset.date);
            case 'oldest':
                return new Date(a.dataset.date) - new Date(b.dataset.date);
            default:
                return 0;
        }
    });
    
    // Re-append sorted products
    products.forEach(function(product) {
        container.appendChild(product);
    });
}

// ==================== CLEAR FILTERS ====================
function clearFilters() {
    // Reset search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    
    // Reset category
    const categorySelect = document.getElementById('categoryFilter');
    if (categorySelect) categorySelect.value = '';
    
    document.querySelectorAll('.category-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.category === '' || btn.dataset.category === 'all') {
            btn.classList.add('active');
        }
    });
    
    document.querySelectorAll('.filter-category-link').forEach(function(link) {
        link.classList.remove('active');
        if (link.dataset.category === '' || link.dataset.category === 'all') {
            link.classList.add('active');
        }
    });
    
    // Reset condition
    const conditionSelect = document.getElementById('conditionFilter');
    if (conditionSelect) conditionSelect.value = '';
    
    // Reset price
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    if (minPrice) minPrice.value = '';
    if (maxPrice) maxPrice.value = '';
    
    // Reset sort
    const sortSelect = document.getElementById('sortFilter');
    if (sortSelect) sortSelect.value = 'newest';
    
    // Re-filter
    filterProducts();
}

// ==================== DEBOUNCE UTILITY ====================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
