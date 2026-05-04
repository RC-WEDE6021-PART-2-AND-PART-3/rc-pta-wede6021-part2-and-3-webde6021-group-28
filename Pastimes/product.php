<?php
/**
 * product.php — Single Product Detail Page
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
require_once 'includes/classes/Message.php';

// Get product ID
$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productID <= 0) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect('shop.php');
}

// Get product details
$clothing = new Clothing($conn);
$product = $clothing->getByID($productID);

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    redirect('shop.php');
}

$pageTitle = $product['title'];

// Handle message submission
$messageSuccess = '';
$messageError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendMessage'])) {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login to message the seller.';
        redirect('login.php');
    }
    
    $messageBody = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($messageBody)) {
        $messageError = 'Please enter a message.';
    } else {
        $message = new Message($conn);
        $result = $message->send([
            'senderID' => $_SESSION['userID'],
            'receiverID' => $product['sellerID'],
            'clothingID' => $productID,
            'subject' => 'Re: ' . $product['title'],
            'messageBody' => $messageBody
        ]);
        
        if ($result['success']) {
            $messageSuccess = 'Message sent successfully!';
        } else {
            $messageError = $result['message'];
        }
    }
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addToCart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $clothingID = $productID;
    
    if (!isset($_SESSION['cart'][$clothingID])) {
        $_SESSION['cart'][$clothingID] = [
            'clothingID' => $clothingID,
            'title' => $product['title'],
            'brand' => $product['brand'],
            'size' => $product['size'],
            'price' => $product['price'],
            'image' => $product['imagePath'],
            'condition' => $product['condition'],
            'qty' => 1
        ];
        $_SESSION['success'] = 'Item added to cart!';
    } else {
        $_SESSION['info'] = 'Item is already in your cart.';
    }
    
    redirect('cart.php');
}

// Get similar items
$similarItems = $clothing->getSimilar($productID, $product['category'], 4);

// Service fee
$serviceFee = 15.00;
$totalPrice = $product['price'] + $serviceFee;

include 'includes/header.php';
?>

<style>
.breadcrumb {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-md) var(--spacing-lg);
    font-size: 0.875rem;
    color: var(--medium-gray);
}

.breadcrumb a {
    color: var(--dark-gray);
}

.breadcrumb a:hover {
    color: var(--primary-dark);
}

.product-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-2xl);
}

.product-gallery {
    position: sticky;
    top: 90px;
}

.product-main-image {
    width: 100%;
    aspect-ratio: 3/4;
    border-radius: var(--radius-lg);
    overflow: hidden;
    background-color: var(--cream);
}

.product-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    padding: var(--spacing-lg) 0;
}

.product-brand-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--accent-orange);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: var(--spacing-sm);
}

.product-title-large {
    font-family: var(--font-heading);
    font-size: 2rem;
    color: var(--primary-dark);
    margin-bottom: var(--spacing-md);
}

.product-price-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.product-price-large {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-dark);
}

.product-specs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.spec-card {
    background-color: var(--cream);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    text-align: center;
}

.spec-label {
    font-size: 0.75rem;
    color: var(--medium-gray);
    margin-bottom: var(--spacing-xs);
}

.spec-value {
    font-weight: 600;
    color: var(--primary-dark);
}

.product-description {
    margin-bottom: var(--spacing-lg);
}

.product-description h3 {
    font-size: 1rem;
    margin-bottom: var(--spacing-sm);
}

.product-description p {
    color: var(--dark-gray);
    line-height: 1.6;
}

.seller-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background-color: var(--cream);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-lg);
}

.seller-avatar {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-full);
    background-color: var(--primary-dark);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.seller-info {
    flex: 1;
}

.seller-name {
    font-weight: 600;
    color: var(--primary-dark);
}

.seller-meta {
    font-size: 0.875rem;
    color: var(--medium-gray);
}

.seller-badge {
    font-size: 0.75rem;
    color: var(--success);
    font-weight: 500;
}

.add-to-cart-btn-large {
    width: 100%;
    margin-bottom: var(--spacing-md);
}

.trust-badges {
    display: flex;
    gap: var(--spacing-lg);
    justify-content: center;
    margin-bottom: var(--spacing-lg);
    font-size: 0.8125rem;
    color: var(--dark-gray);
}

.trust-badges span {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.trust-badges i {
    color: var(--success);
}

.message-seller-card {
    background-color: var(--white);
    border: 1px solid var(--light-gray);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
}

.message-seller-card h3 {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 1rem;
    margin-bottom: var(--spacing-md);
}

.similar-section {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-2xl) var(--spacing-lg);
}

.similar-section h2 {
    font-family: var(--font-heading);
    font-size: 1.75rem;
    margin-bottom: var(--spacing-lg);
}

@media (max-width: 1024px) {
    .product-container {
        grid-template-columns: 1fr;
    }
    
    .product-gallery {
        position: static;
    }
}
</style>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <a href="index.php">Home</a> / 
    <a href="shop.php">Shop</a> / 
    <strong><?php echo htmlspecialchars($product['title']); ?></strong>
</nav>

<!-- Product Details -->
<div class="product-container">
    <!-- Product Gallery -->
    <div class="product-gallery">
        <div class="product-main-image">
              <img src="<?php echo htmlspecialchars(image_url(!empty($product['imagePath']) ? $product['imagePath'] : '')); ?>" 
                  alt="<?php echo htmlspecialchars($product['title']); ?>" class="user-image">
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="product-details">
        <span class="product-brand-label"><?php echo htmlspecialchars($product['brand']); ?></span>
        <h1 class="product-title-large"><?php echo htmlspecialchars($product['title']); ?></h1>
        
        <div class="product-price-row">
            <span class="product-price-large"><?php echo formatPrice($product['price']); ?></span>
            <?php echo getConditionBadge($product['condition']); ?>
        </div>
        
        <!-- Specs -->
        <div class="product-specs">
            <div class="spec-card">
                <div class="spec-label">Size</div>
                <div class="spec-value"><?php echo htmlspecialchars($product['size']); ?></div>
            </div>
            <div class="spec-card">
                <div class="spec-label">Category</div>
                <div class="spec-value"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></div>
            </div>
            <div class="spec-card">
                <div class="spec-label">Brand</div>
                <div class="spec-value"><?php echo htmlspecialchars($product['brand']); ?></div>
            </div>
        </div>
        
        <!-- Description -->
        <div class="product-description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description provided.')); ?></p>
        </div>
        
        <!-- Seller Card -->
        <div class="seller-card">
            <div class="seller-avatar">
                <?php echo strtoupper(substr($product['sellerName'] ?? 'S', 0, 1)); ?>
            </div>
            <div class="seller-info">
                <div class="seller-name"><?php echo htmlspecialchars($product['sellerName'] ?? 'Seller'); ?></div>
                <div class="seller-meta">
                    <i class="fas fa-star" style="color: var(--warning);"></i> 4.8 rating • 12 sales
                </div>
            </div>
            <span class="seller-badge">Verified Seller</span>
        </div>
        
        <!-- Add to Cart -->
        <?php if ($product['status'] === 'approved'): ?>
        <form method="POST" action="">
            <button type="submit" name="addToCart" class="btn btn-primary btn-lg add-to-cart-btn-large">
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
        </form>
        <?php else: ?>
        <button class="btn btn-secondary btn-lg add-to-cart-btn-large" disabled>
            <?php echo $product['status'] === 'sold' ? 'Sold' : 'Not Available'; ?>
        </button>
        <?php endif; ?>
        
        <!-- Trust Badges -->
        <div class="trust-badges">
            <span><i class="fas fa-shield-alt"></i> Buyer Protection</span>
            <span><i class="fas fa-leaf"></i> Eco-friendly purchase</span>
            <span><i class="fas fa-check-circle"></i> Admin-verified listing</span>
        </div>
        
        <!-- Sustainability Impact -->
        <div style="background-color: var(--success-light); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg);">
            <p style="color: #065f46; margin: 0; font-size: 0.9375rem;">
                <i class="fas fa-leaf"></i> 
                <strong>Sustainability Impact:</strong> Buying this item saves approximately 
                <strong><?php echo number_format($product['co2Saved'] ?? 3, 1); ?> kg CO₂</strong> and 
                <strong><?php echo number_format($product['waterSaved'] ?? 2700); ?> litres of water</strong>.
            </p>
        </div>
        
        <!-- Message Seller -->
        <div class="message-seller-card">
            <h3><i class="fas fa-comment-dots"></i> Message the Seller</h3>
            
            <?php if (!empty($messageSuccess)): ?>
            <div class="alert alert-success"><?php echo $messageSuccess; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($messageError)): ?>
            <div class="alert alert-error"><?php echo $messageError; ?></div>
            <?php endif; ?>
            
            <?php if (isLoggedIn()): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <textarea name="message" 
                              class="form-textarea" 
                              placeholder="Ask about size, condition, shipping..."
                              rows="3"
                              required></textarea>
                </div>
                <button type="submit" name="sendMessage" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
            <?php else: ?>
            <p style="color: var(--medium-gray);">
                <a href="login.php" style="color: var(--accent-orange);">Login</a> to message the seller.
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Similar Items -->
<?php if (!empty($similarItems)): ?>
<section class="similar-section">
    <h2>Similar Items</h2>
    <div class="products-grid">
        <?php foreach ($similarItems as $item): ?>
        <article class="product-card">
            <div class="product-image">
                <?php echo getConditionBadge($item['condition']); ?>
                <a href="product.php?id=<?php echo $item['clothingID']; ?>">
                    <img src="<?php echo htmlspecialchars(image_url(!empty($item['imagePath']) ? $item['imagePath'] : '')); ?>" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>">
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
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
