<?php
/**
 * functions.php — Utility / Helper Functions
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

// ── Input / Output Sanitisation ───────────────────────────────────────────────

/**
 * Sanitize user input
 * @param string $data The input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hash password using MD5 (for compatibility with seed data)
 * @param string $password Plain text password
 * @return string MD5 hashed password
 */
function hashPassword($password) {
    return md5($password);
}

/**
 * Verify password against hash
 * @param string $input Plain text password input
 * @param string $hash Stored password hash
 * @return bool True if password matches
 */
function verifyPassword($input, $hash) {
    return md5($input) === $hash;
}

/**
 * Check if user is logged in
 * @return bool True if user session exists
 */
function isLoggedIn() {
    return isset($_SESSION['userID']) && !empty($_SESSION['userID']);
}

/**
 * Check if admin is logged in
 * @return bool True if admin session exists
 */
function isAdmin() {
    return isset($_SESSION['adminID']) && !empty($_SESSION['adminID']);
}

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Format price in South African Rand
 * @param float $amount The amount to format
 * @return string Formatted price string
 */
function formatPrice($amount) {
    return 'R ' . number_format($amount, 2);
}

/**
 * Calculate suggested price based on brand, condition, and category
 * @param string $brand Item brand
 * @param string $condition Item condition
 * @param string $category Item category
 * @return float Suggested price
 */
function priceSuggestion($brand, $condition, $category) {
    // Base prices by category
    $basePrices = [
        'outerwear' => 350,
        'footwear' => 300,
        'dresses' => 200,
        'activewear' => 180,
        'bottoms' => 160,
        'accessories' => 250,
        'tops' => 120
    ];
    
    // Condition multipliers
    $conditionMultipliers = [
        'like new' => 1.0,
        'good' => 0.7,
        'fair' => 0.4
    ];
    
    // Premium brands that get a bonus
    $premiumBrands = [
        'levi\'s', 'levis', 'nike', 'adidas', 'zara', 'h&m', 'hm',
        'puma', 'woolworths', 'woolies', 'tommy hilfiger', 'tommy', 'fossil'
    ];
    
    // Get base price (default to tops if category not found)
    $basePrice = isset($basePrices[strtolower($category)]) 
        ? $basePrices[strtolower($category)] 
        : 120;
    
    // Get condition multiplier (default to 0.7 if not found)
    $multiplier = isset($conditionMultipliers[strtolower($condition)]) 
        ? $conditionMultipliers[strtolower($condition)] 
        : 0.7;
    
    // Calculate price with condition
    $price = $basePrice * $multiplier;
    
    // Add premium brand bonus
    if (in_array(strtolower($brand), $premiumBrands)) {
        $price += 80;
    }
    
    return round($price, 2);
}

/**
 * Get cart item count
 * @return int Number of items in cart
 */
function getCartCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += isset($item['qty']) ? $item['qty'] : 1;
    }
    return $count;
}

/**
 * Get cart total
 * @return float Total cart value
 */
function getCartTotal() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $qty = isset($item['qty']) ? $item['qty'] : 1;
        $total += $item['price'] * $qty;
    }
    return $total;
}

/**
 * Add search term to search history
 * @param string $term Search term to add
 */
function addSearchHistory($term) {
    if (empty($term)) return;
    
    if (!isset($_SESSION['searchHistory'])) {
        $_SESSION['searchHistory'] = [];
    }
    
    // Remove if exists (to move to front)
    $key = array_search($term, $_SESSION['searchHistory']);
    if ($key !== false) {
        unset($_SESSION['searchHistory'][$key]);
    }
    
    // Add to front
    array_unshift($_SESSION['searchHistory'], $term);
    
    // Keep only last 5
    $_SESSION['searchHistory'] = array_slice($_SESSION['searchHistory'], 0, 5);
}

/**
 * Generate a random order ID
 * @return string Order ID
 */
function generateOrderID() {
    return 'order-' . time() . rand(100, 999);
}

/**
 * Get status badge HTML
 * @param string $status Status value
 * @return string HTML badge
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-pending">Pending</span>',
        'approved' => '<span class="badge badge-approved">Approved</span>',
        'active' => '<span class="badge badge-approved">Active</span>',
        'dispatched' => '<span class="badge badge-dispatched">Dispatched</span>',
        'delivered' => '<span class="badge badge-delivered">Delivered</span>',
        'cancelled' => '<span class="badge badge-rejected">Cancelled</span>',
        'rejected' => '<span class="badge badge-rejected">Rejected</span>',
        'sold' => '<span class="badge badge-sold">Sold</span>',
        'suspended' => '<span class="badge badge-rejected">Suspended</span>'
    ];
    
    return isset($badges[strtolower($status)]) 
        ? $badges[strtolower($status)] 
        : '<span class="badge">' . htmlspecialchars($status) . '</span>';
}

/**
 * Get condition badge HTML
 * @param string $condition Condition value
 * @return string HTML badge
 */
function getConditionBadge($condition) {
    $badges = [
        'like new' => '<span class="condition-badge condition-like-new">Like new</span>',
        'new with tags' => '<span class="condition-badge condition-new-tags">New with tags</span>',
        'good' => '<span class="condition-badge condition-good">Good</span>',
        'fair' => '<span class="condition-badge condition-fair">Fair</span>'
    ];
    
    return isset($badges[strtolower($condition)]) 
        ? $badges[strtolower($condition)] 
        : '<span class="condition-badge">' . htmlspecialchars($condition) . '</span>';
}

/**
 * Build a safe image URL for product images.
 * If empty -> default image. If full URL -> return as-is.
 */
function image_url($filename) {
    $filename = trim($filename);
    

    if (preg_match('#^https?://#i', $filename)) {
        return $filename;
    }

    // Normalize backslashes to slashes
    $filename = str_replace('\\', '/', $filename);

    // If path already contains images/ or image/ (with or without leading slash)
    if (stripos($filename, 'images/') !== false || stripos($filename, 'image/') !== false) {
        // Normalize to the subpath starting at the images or image folder
        $posImages = stripos($filename, 'images/');
        $posImage = stripos($filename, 'image/');
        $pos = ($posImages !== false) ? $posImages : $posImage;
        $sub = substr($filename, $pos);
        $sub = '/' . ltrim($sub, '/');
        // If it already starts with /pastimes/images or /pastimes/image, keep it
        if (stripos($sub, '/pastimes/images/') === 0 || stripos($sub, '/pastimes/image/') === 0) {
            return $sub;
        }
        // Prefer /pastimes/images/ as canonical folder; if original used singular "image/" keep it
        if (stripos($sub, '/image/') === 0 && stripos($sub, '/images/') === false) {
            return '/pastimes' . $sub;
        }
        // Otherwise ensure /pastimes/images/ prefix
        if (stripos($sub, '/images/') === 0) {
            return '/pastimes' . $sub;
        }
        // Fallback
        return '/pastimes' . $sub;
    }

    // Otherwise assume it's a filename in images/ under the app root
    return '/pastimes/images/' . ltrim($filename, '/');
}

/**
 * Get path to data file in the database/ folder
 * @param string $filename
 * @return string Full filesystem path
 */
function dataFilePath($filename) {
    return realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $filename;
}

/**
 * Append an associative array as a JSON line to a data file under /database
 * Creates the file if it doesn't exist. Uses exclusive lock while writing.
 * @param string $filename
 * @param array $data
 * @return bool True on success
 */
function appendDataFile($filename, $data) {
    $path = dataFilePath($filename);
    if (!$path) {
        // directory may not resolve via realpath if not created; build manually
        $path = __DIR__ . '/../database/' . $filename;
    }

    // Ensure directory exists
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $line = json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    $fp = fopen($path, 'a');
    if ($fp === false) return false;
    $ok = flock($fp, LOCK_EX);
    if ($ok) {
        fwrite($fp, $line);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $ok;
}

/**
 * Append a plain text line to a data file under /database
 * Creates the file if it doesn't exist. Uses exclusive lock while writing.
 * @param string $filename
 * @param string $line  The exact line (without newline) to append
 * @return bool True on success
 */
function appendPlainDataFile($filename, $line) {
    $path = dataFilePath($filename);
    if (!$path) {
        $path = __DIR__ . '/../database/' . $filename;
    }

    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $fp = fopen($path, 'a');
    if ($fp === false) return false;
    $ok = flock($fp, LOCK_EX);
    if ($ok) {
        fwrite($fp, $line . PHP_EOL);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $ok;
}
?>