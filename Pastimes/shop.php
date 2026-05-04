<?php
/**
 * shop.php — Browse / Search Clothing Page
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
require_once 'includes/classes/Clothing.php';

$pageTitle = 'Shop';
$pageScript = 'filter.js';

// Get filter parameters
$filters = [
    'status' => 'approved',
    'category' => sanitizeInput($_GET['category'] ?? ''),
    'brand' => sanitizeInput($_GET['brand'] ?? ''),
    'size' => sanitizeInput($_GET['size'] ?? ''),
    'condition' => sanitizeInput($_GET['condition'] ?? ''),
    'minPrice' => isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : null,
    'maxPrice' => isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : null,
    'search' => sanitizeInput($_GET['search'] ?? '')
];

// Add search to history
if (!empty($filters['search'])) {
    addSearchHistory($filters['search']);
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get items
$clothing = new Clothing($conn);
$items = $clothing->getAll($filters, $perPage, $offset);

// Get total count for pagination
$totalItems = $clothing->countByStatus('approved');

// Categories
$categories = ['tops', 'bottoms', 'outerwear', 'dresses', 'activewear', 'accessories'];

// Conditions
$conditions = ['like new', 'new with tags', 'good', 'fair'];

// Search history
$searchHistory = isset($_SESSION['searchHistory']) ? $_SESSION['searchHistory'] : [];

include 'includes/header.php';
?>

<style>
.shop-hero {
    background-color: var(--primary-dark);
    padding: var(--spacing-2xl) 0;
    margin-top: -10px;
}

.shop-hero-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 var(--spacing-lg);
}

.shop-hero h1 {
    font-family: var(--font-heading);
    font-size: 2.5rem;
    color: var(--white);
    margin-bottom: var(--spacing-sm);
}

.shop-hero p {
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: var(--spacing-lg);
}

.search-box {
    max-width: 600px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: var(--spacing-md) var(--spacing-lg);
    padding-left: 50px;
    font-size: 1rem;
    border: none;
    border-radius: var(--radius-full);
    background-color: var(--white);
}

.search-box i {
    position: absolute;
    left: var(--spacing-lg);
    top: 50%;
    transform: translateY(-50%);
    color: var(--medium-gray);
}

.shop-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-lg);
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: var(--spacing-xl);
}

.shop-sidebar {
    background-color: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--light-gray);
    padding: var(--spacing-lg);
    height: fit-content;
    position: sticky;
    top: 90px;
}

.filter-section {
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-lg);
    border-bottom: 1px solid var(--light-gray);
}

.filter-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.filter-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--dark-gray);
    margin-bottom: var(--spacing-md);
}

.filter-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.filter-category-link {
    display: block;
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md);
    color: var(--dark-gray);
    font-size: 0.9375rem;
    transition: all var(--transition-base);
    cursor: pointer;
}

.filter-category-link:hover,
.filter-category-link.active {
    background-color: var(--primary-dark);
    color: var(--white);
}

.shop-main {
    min-height: 600px;
}

.shop-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.category-chips {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.category-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-base);
    background-color: var(--white);
    border: 1px solid var(--light-gray);
    color: var(--dark-gray);
}

.category-btn:hover,
.category-btn.active {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
    color: var(--white);
}

.shop-controls {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.results-count {
    color: var(--medium-gray);
    font-size: 0.9375rem;
}

.sort-select {
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--light-gray);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    color: var(--dark-gray);
    background-color: var(--white);
    cursor: pointer;
}

.no-results {
    text-align: center;
    padding: var(--spacing-3xl);
    background-color: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--light-gray);
}

.no-results i {
    font-size: 4rem;
    color: var(--light-gray);
    margin-bottom: var(--spacing-md);
}

.search-chips {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.search-chip {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-sm);
    background-color: var(--cream);
    border-radius: var(--radius-full);
    font-size: 0.8125rem;
    color: var(--dark-gray);
}

@media (max-width: 1024px) {
    .shop-container {
        grid-template-columns: 1fr;
    }
    
    .shop-sidebar {
        position: static;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }
    
    .filter-section {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
}
</style>

<!-- Shop Hero -->
<section class="shop-hero">
    <div class="shop-hero-content">
        <h1>Shop All Items</h1>
        <p>Discover <?php echo $totalItems; ?> quality second-hand branded clothing items</p>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" 
                   id="searchInput" 
                   placeholder="Search by name, brand, or description..."
                   value="<?php echo htmlspecialchars($filters['search']); ?>">
        </div>
        
        <?php if (!empty($searchHistory)): ?>
        <div style="margin-top: var(--spacing-md);">
            <span style="color: rgba(255,255,255,0.5); font-size: 0.875rem;">Recent searches:</span>
            <div class="search-chips" style="margin-top: var(--spacing-xs);">
                <?php foreach ($searchHistory as $term): ?>
                <a href="shop.php?search=<?php echo urlencode($term); ?>" class="search-chip">
                    <?php echo htmlspecialchars($term); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="shop-container">
    <!-- Sidebar Filters -->
    <aside class="shop-sidebar">
        <div class="filter-section">
            <h3 class="filter-title">Category</h3>
            <div class="filter-list">
                <a class="filter-category-link <?php echo empty($filters['category']) ? 'active' : ''; ?>" 
                   data-category="all"
                   href="shop.php">All</a>
                <?php foreach ($categories as $cat): ?>
                <a class="filter-category-link <?php echo $filters['category'] === $cat ? 'active' : ''; ?>" 
                   data-category="<?php echo $cat; ?>"
                   href="shop.php?category=<?php echo $cat; ?>">
                    <?php echo ucfirst($cat); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="filter-section">
            <h3 class="filter-title">Condition</h3>
            <div class="filter-list">
                <a class="filter-category-link <?php echo empty($filters['condition']) ? 'active' : ''; ?>" 
                   data-condition="all">All</a>
                <?php foreach ($conditions as $cond): ?>
                <a class="filter-category-link <?php echo $filters['condition'] === $cond ? 'active' : ''; ?>" 
                   data-condition="<?php echo $cond; ?>">
                    <?php echo ucfirst($cond); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="filter-section">
            <h3 class="filter-title">Price Range</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm);">
                <input type="number" 
                       id="minPrice" 
                       class="form-input" 
                       placeholder="Min" 
                       value="<?php echo $filters['minPrice']; ?>">
                <input type="number" 
                       id="maxPrice" 
                       class="form-input" 
                       placeholder="Max" 
                       value="<?php echo $filters['maxPrice']; ?>">
            </div>
        </div>
        
        <button class="btn btn-secondary btn-full" onclick="clearFilters()">
            <i class="fas fa-times"></i> Clear Filters
        </button>
    </aside>
    
    <!-- Main Content -->
    <main class="shop-main">
        <div class="shop-header">
            <div class="category-chips">
                <button class="category-btn <?php echo empty($filters['category']) ? 'active' : ''; ?>" data-category="all">All</button>
                <?php foreach ($categories as $cat): ?>
                <button class="category-btn <?php echo $filters['category'] === $cat ? 'active' : ''; ?>" 
                        data-category="<?php echo $cat; ?>">
                    <?php echo ucfirst($cat); ?>
                </button>
                <?php endforeach; ?>
            </div>
            
            <div class="shop-controls">
                <span class="results-count" id="resultsCount"><?php echo count($items); ?> results</span>
                <select class="sort-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                </select>
            </div>
        </div>
        
        <?php if (!empty($items)): ?>
        <div class="products-grid" id="productsGrid">
            <?php foreach ($items as $item): ?>
            <article class="product-card" 
                     data-title="<?php echo htmlspecialchars($item['title']); ?>"
                     data-brand="<?php echo htmlspecialchars($item['brand']); ?>"
                     data-category="<?php echo htmlspecialchars($item['category']); ?>"
                     data-condition="<?php echo htmlspecialchars($item['condition']); ?>"
                     data-price="<?php echo $item['price']; ?>"
                     data-date="<?php echo $item['createdAt']; ?>">
                <div class="product-image">
                    <?php echo getConditionBadge($item['condition']); ?>
                    <a href="product.php?id=<?php echo $item['clothingID']; ?>">
                            <img src="<?php echo htmlspecialchars(image_url(!empty($item['imagePath']) ? $item['imagePath'] : '')); ?>"
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" class="user-image">
                    </a>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?php echo htmlspecialchars(strtoupper($item['brand'])); ?></span>
                    <h3 class="product-title">
                        <a href="product.php?id=<?php echo $item['clothingID']; ?>">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </h3>
                    <div class="product-meta">
                        <span>Size: <?php echo htmlspecialchars($item['size']); ?></span>
                        <span><?php echo htmlspecialchars(ucfirst($item['category'])); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="product-price"><?php echo formatPrice($item['price']); ?></span>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $item['clothingID']; ?>" class="btn btn-icon btn-secondary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-icon btn-primary add-to-cart-btn" data-id="<?php echo $item['clothingID']; ?>" title="Add to Cart">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-results" id="noResults">
            <i class="fas fa-search"></i>
            <h3>No items found</h3>
            <p>Try adjusting your filters or search terms</p>
            <button class="btn btn-primary" onclick="clearFilters()">Clear Filters</button>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
