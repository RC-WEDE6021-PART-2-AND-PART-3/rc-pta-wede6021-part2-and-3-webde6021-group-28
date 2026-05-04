<?php
/**
 * order-confirmation.php — Post-Purchase Confirmation Screen
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

// Get latest order for this user
$stmt = $conn->prepare("SELECT o.*, c.title, c.imagePath, c.co2Saved, c.waterSaved FROM tblOrder o JOIN tblClothes c ON o.clothingID = c.clothingID WHERE o.buyerID = ? ORDER BY o.orderDate DESC LIMIT 1");
$stmt->bind_param('i', $userID);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();
$stmt->close();

if (!$order) {
    redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .confirmation-icon {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .confirmation-icon i {
            font-size: 2.5rem;
            color: #059669;
        }
        .order-details {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            color: #6b7280;
        }
        .detail-value {
            font-weight: 600;
            color: #1f2937;
        }
        .item-preview {
            display: flex;
            gap: 1rem;
            align-items: center;
            background: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .item-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .sustainability {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .sustainability-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        .sustainability-item:last-child {
            margin-bottom: 0;
        }
        .sustainability-item i {
            font-size: 1.5rem;
            color: #059669;
        }
        .cta-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .cta-buttons a {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <div class="confirmation-container">
            <div class="confirmation-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">Order Confirmed!</h1>
            <p style="color: #6b7280; margin: 0;">Thank you for your purchase</p>

            <div class="item-preview">
                <img src="<?php echo htmlspecialchars($order['imagePath']); ?>" alt="<?php echo htmlspecialchars($order['title']); ?>">
                <div style="text-align: left; flex: 1;">
                    <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($order['title']); ?></p>
                    <p style="margin: 0.25rem 0; color: #6b7280; font-size: 0.9rem;">Order #<?php echo $order['orderID']; ?></p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 1.2rem; font-weight: 700; color: #1e40af;"><?php echo formatPrice($order['totalAmount']); ?></p>
                </div>
            </div>

            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order ID</span>
                    <span class="detail-value">#<?php echo $order['orderID']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value"><?php echo date('d M Y', strtotime($order['orderDate'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery Address</span>
                    <span class="detail-value" style="text-align: right;"><?php echo htmlspecialchars($order['deliveryCity']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color: #f59e0b;">Pending</span>
                </div>
            </div>

            <div class="sustainability">
                <h3 style="margin: 0 0 1rem 0; color: #059669;">🌿 Sustainability Impact</h3>
                <p style="margin: 0 0 1rem 0; color: #047857; font-size: 0.95rem;">By buying this item, you saved approximately:</p>
                <div class="sustainability-item">
                    <i class="fas fa-cloud"></i>
                    <span style="color: #047857;"><strong><?php echo $order['co2Saved']; ?> kg of CO₂</strong> vs buying new</span>
                </div>
                <div class="sustainability-item">
                    <i class="fas fa-droplet"></i>
                    <span style="color: #047857;"><strong><?php echo $order['waterSaved']; ?> litres of water</strong> saved</span>
                </div>
            </div>

            <p style="color: #6b7280; font-size: 0.95rem; margin: 1.5rem 0;">A confirmation email has been sent to your email address. Track your order in <a href="my-orders.php" style="color: #1e40af;">My Orders</a>.</p>

            <div class="cta-buttons">
                <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
                <a href="my-orders.php" class="btn btn-primary">View Order</a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
