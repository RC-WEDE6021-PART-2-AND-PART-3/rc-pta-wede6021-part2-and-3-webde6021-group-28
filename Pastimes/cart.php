<?php
/**
 * cart.php — Shopping Cart
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

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitizeInput($_POST['action'] ?? '');
    
    if ($action === 'remove') {
        $clothingID = (int)$_POST['clothing_id'];
        if (array_key_exists($clothingID, $_SESSION['cart'])) {
            unset($_SESSION['cart'][$clothingID]);
        }
    } elseif ($action === 'update') {
        $clothingID = (int)$_POST['clothing_id'];
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0 && array_key_exists($clothingID, $_SESSION['cart'])) {
            $_SESSION['cart'][$clothingID]['qty'] = $quantity;
        }
    }
}

// Calculate totals
$subtotal = 0;
$serviceFeesTotal = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $itemTotal = $item['price'] * $item['qty'];
        $subtotal += $itemTotal;
        $serviceFeesTotal += 15 * $item['qty'];
    }
}
$grandTotal = $subtotal + $serviceFeesTotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin: 2rem 0;
        }
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
        }
        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .cart-item-details {
            flex: 1;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: fit-content;
            margin: 0.5rem 0;
        }
        .qty-control button {
            width: 32px;
            height: 32px;
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        .qty-control input {
            width: 50px;
            text-align: center;
            border: 1px solid #d1d5db;
            padding: 0.25rem;
        }
        .remove-btn {
            background: none;
            border: none;
            color: #dc2626;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .order-summary {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .summary-row.total {
            border-top: 2px solid #e5e7eb;
            padding-top: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
        }
        .empty-cart i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem;">Your Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p style="color: #6b7280; margin: 1rem 0;">Browse our shop to find amazing second-hand clothing.</p>
                <a href="shop.php" class="btn btn-primary" style="display: inline-block;">Browse Shop →</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $clothingID => $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="cart-item-image">
                            <div class="cart-item-details" style="flex: 1;">
                                <h3 style="margin: 0 0 0.25rem 0;"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p style="color: #6b7280; font-size: 0.9rem; margin: 0.25rem 0;"><?php echo htmlspecialchars($item['brand']); ?> • Size <?php echo htmlspecialchars($item['size']); ?> • <?php echo htmlspecialchars($item['condition']); ?></p>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="clothing_id" value="<?php echo $clothingID; ?>">
                                    <div class="qty-control">
                                        <button type="button" onclick="decreaseQty(this)">−</button>
                                        <input type="number" name="quantity" value="<?php echo $item['qty']; ?>" min="1" max="10" onchange="this.form.submit()">
                                        <button type="button" onclick="increaseQty(this)">+</button>
                                    </div>
                                </form>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-weight: 600; font-size: 1.1rem; margin: 0 0 1rem 0;"><?php echo formatPrice($item['price'] * $item['qty']); ?></p>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="clothing_id" value="<?php echo $clothingID; ?>">
                                    <button type="submit" class="remove-btn" title="Remove item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <h3 style="margin-top: 0;">Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal (<?php echo count($_SESSION['cart']); ?> items)</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Buyer Protection Fee</span>
                        <span><?php echo formatPrice($serviceFeesTotal); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?php echo formatPrice($grandTotal); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; margin-top: 1.5rem;">Proceed to Checkout →</a>
                    <a href="shop.php" style="display: block; text-align: center; margin-top: 1rem; color: #6b7280;">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function increaseQty(btn) {
            const input = btn.previousElementSibling;
            input.value = Math.min(parseInt(input.value) + 1, 10);
            input.form.submit();
        }
        function decreaseQty(btn) {
            const input = btn.nextElementSibling;
            input.value = Math.max(parseInt(input.value) - 1, 1);
            input.form.submit();
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
