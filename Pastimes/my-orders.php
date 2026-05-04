<?php
/**
 * my-orders.php — Buyer Order History
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

if (!isLoggedIn()) {
    redirect('login.php');
}

$userID = $_SESSION['userID'];

// Get all user orders
$stmt = $conn->prepare("SELECT o.*, c.title, c.imagePath FROM tblOrder o JOIN tblClothes c ON o.clothingID = c.clothingID WHERE o.buyerID = ? ORDER BY o.orderDate DESC");
$stmt->bind_param('i', $userID);
$stmt->execute();
$ordersResult = $stmt->get_result();
$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .order-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }
        @media (max-width: 768px) {
            .order-card {
                grid-template-columns: 1fr;
            }
        }
        .order-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .order-details h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }
        .order-details p {
            margin: 0.25rem 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .order-status {
            text-align: right;
        }
        .order-status .price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-pending {
            background: #fef3c7;
            color: #b45309;
        }
        .badge-dispatched {
            background: #bfdbfe;
            color: #1e40af;
        }
        .badge-delivered {
            background: #d1fae5;
            color: #065f46;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1000px; margin: 0 auto; padding: 2rem 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin: 0; font-size: 2.5rem;">My Orders</h1>
            <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>No orders yet</h2>
                <p style="color: #6b7280;">Start shopping to place your first order.</p>
                <a href="shop.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Browse Shop</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <img src="<?php echo htmlspecialchars($order['imagePath']); ?>" alt="<?php echo htmlspecialchars($order['title']); ?>" class="order-image">
                    
                    <div class="order-details">
                        <h3><?php echo htmlspecialchars($order['title']); ?></h3>
                        <p><strong>Order ID:</strong> #<?php echo $order['orderID']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['orderDate'])); ?></p>
                        <p><strong>Delivery:</strong> <?php echo htmlspecialchars($order['deliveryCity']); ?></p>
                    </div>

                    <div class="order-status">
                        <div class="price"><?php echo formatPrice($order['totalAmount']); ?></div>
                        <?php
                        $statusClass = 'badge-pending';
                        $statusText = 'Pending';
                        if ($order['status'] === 'delivered') {
                            $statusClass = 'badge-delivered';
                            $statusText = 'Delivered';
                        } elseif ($order['status'] === 'dispatched') {
                            $statusClass = 'badge-dispatched';
                            $statusText = 'In Transit';
                        }
                        ?>
                        <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        <a href="product.php?id=<?php echo $order['clothingID']; ?>" style="display: block; margin-top: 0.75rem; color: #1e40af; font-size: 0.9rem;">View item →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
