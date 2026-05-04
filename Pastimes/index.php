<?php
/**
 * index.php — Homepage
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

$pageTitle = 'Home';

// Get featured items (8 most recent approved)
$clothing = new Clothing($conn);
$featuredItems = $clothing->getAll(['status' => 'approved'], 8, 0);

// Categories for display
$categories = [
    ['name' => 'Tops', 'slug' => 'tops', 'image' => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=400'],
    ['name' => 'Dresses', 'slug' => 'dresses', 'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400'],
    ['name' => 'Outerwear', 'slug' => 'outerwear', 'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400'],
    ['name' => 'Bottoms', 'slug' => 'bottoms', 'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=400'],
    ['name' => 'Footwear', 'slug' => 'footwear', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400'],
    ['name' => 'Accessories', 'slug' => 'accessories', 'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=400']
];

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-leaf"></i>
                <span>Sustainable Fashion Marketplace</span>
            </div>
            <h1 class="hero-title">
                Shop Sustainably.<br>
                <span class="accent">Sell Confidently.</span>
            </h1>
            <p class="hero-text">
                Discover quality second-hand branded clothing. Buy, sell, and trade with confidence on South Africa's most trusted clothing marketplace.
            </p>
            <div class="hero-buttons">
                <a href="shop.php" class="btn btn-accent btn-lg">
                    Browse Shop <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?php echo isLoggedIn() && canSell() ? 'sell.php' : 'register.php'; ?>" class="btn btn-secondary btn-lg">
                    Start Selling
                </a>
            </div>
        </div>
        <div class="hero-images">
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=600" alt="Fashion model">
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=400" alt="Designer clothing">
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400" alt="Branded jacket">
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400" alt="Premium hoodie">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <a href="shop.php?category=<?php echo $category['slug']; ?>" class="category-card">
                <img src="<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>">
                <div class="category-overlay">
                    <span class="category-name"><?php echo $category['name']; ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Listings Section -->
<section class="featured-section" style="padding: var(--spacing-3xl) 0;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl);">
            <h2 class="section-title" style="margin: 0;">Featured Listings</h2>
            <a href="shop.php" class="btn btn-secondary">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if (!empty($featuredItems)): ?>
        <div class="products-grid">
            <?php foreach ($featuredItems as $item): ?>
            <article class="product-card" 
                     data-title="<?php echo htmlspecialchars($item['title']); ?>"
                     data-brand="<?php echo htmlspecialchars($item['brand']); ?>"
                     data-category="<?php echo htmlspecialchars($item['category']); ?>"
                     data-price="<?php echo $item['price']; ?>">
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
        <div class="text-center" style="padding: var(--spacing-3xl);">
            <i class="fas fa-tshirt" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: var(--spacing-md);"></i>
            <p>No items available yet. Be the first to list!</p>
            <a href="sell.php" class="btn btn-primary">Start Selling</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Sustainability Banner -->
<section style="background-color: var(--success); padding: var(--spacing-2xl) 0;">
    <div class="container text-center">
        <div style="display: flex; align-items: center; justify-content: center; gap: var(--spacing-lg); flex-wrap: wrap;">
            <i class="fas fa-leaf" style="font-size: 2rem; color: var(--white);"></i>
            <p style="color: var(--white); font-size: 1.25rem; margin: 0; font-weight: 500;">
                Every purchase saves an average of <strong>3kg CO₂</strong> and <strong>2,700L of water</strong>
            </p>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section style="padding: var(--spacing-3xl) 0; background-color: var(--white);">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-xl);">
            <!-- Step 1 -->
            <div class="text-center">
                <div style="width: 80px; height: 80px; background-color: var(--cream); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md);">
                    <i class="fas fa-user-plus" style="font-size: 1.75rem; color: var(--primary-dark);"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: var(--spacing-sm);">1. Register</h3>
                <p style="color: var(--dark-gray); margin: 0;">Create your free account to start buying and selling pre-loved fashion.</p>
            </div>
            <!-- Step 2 -->
            <div class="text-center">
                <div style="width: 80px; height: 80px; background-color: var(--cream); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md);">
                    <i class="fas fa-search" style="font-size: 1.75rem; color: var(--primary-dark);"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: var(--spacing-sm);">2. List or Browse</h3>
                <p style="color: var(--dark-gray); margin: 0;">List your items for sale or browse thousands of quality second-hand pieces.</p>
            </div>
            <!-- Step 3 -->
            <div class="text-center">
                <div style="width: 80px; height: 80px; background-color: var(--cream); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md);">
                    <i class="fas fa-handshake" style="font-size: 1.75rem; color: var(--primary-dark);"></i>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: var(--spacing-sm);">3. Buy or Sell</h3>
                <p style="color: var(--dark-gray); margin: 0;">Complete secure transactions with buyer protection on every purchase.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
