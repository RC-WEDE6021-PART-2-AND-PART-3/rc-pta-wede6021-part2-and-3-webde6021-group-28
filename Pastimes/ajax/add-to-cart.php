<?php
// ajax/add-to-cart.php — AJAX handler for adding items to cart

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/DBConn.php';
require_once __DIR__ . '/../includes/classes/Clothing.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$clothingID = isset($_POST['clothingID']) ? intval($_POST['clothingID']) : 0;
if ($clothingID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID.']);
    exit;
}

$clothing = new Clothing($conn);
$item = $clothing->getByID($clothingID);
if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found.']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['cart'][$clothingID])) {
    $_SESSION['cart'][$clothingID] = [
        'clothingID' => $clothingID,
        'title' => $item['title'],
        'brand' => $item['brand'],
        'size' => $item['size'],
        'price' => $item['price'],
        'image' => $item['imagePath'],
        'condition' => $item['condition'],
        'qty' => 1
    ];
    $message = 'Item added to cart!';
} else {
    // increment quantity
    $_SESSION['cart'][$clothingID]['qty'] += 1;
    $message = 'Item quantity updated in cart.';
}

$cartCount = getCartCount();

echo json_encode(['success' => true, 'message' => $message, 'cartCount' => $cartCount]);
exit;
