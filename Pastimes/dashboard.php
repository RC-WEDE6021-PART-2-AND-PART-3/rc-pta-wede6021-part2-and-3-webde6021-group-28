<?php
/**
 * dashboard.php — Buyer / Seller Dashboard
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
$role = $_SESSION['role'];
$fullName = $_SESSION['fullName'];

// Get user data
$stmt = $conn->prepare("SELECT * FROM tblUser WHERE userID = ?");
$stmt->bind_param('i', $userID);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

// Get orders count
$stmt = $conn->prepare("SELECT COUNT(*) as totalOrders FROM tblOrder WHERE buyerID = ?");
$stmt->bind_param('i', $userID);
$stmt->execute();
$orderResult = $stmt->get_result()->fetch_assoc();
$totalOrders = $orderResult['totalOrders'];
$stmt->close();

// Get delivered orders
$stmt = $conn->prepare("SELECT COUNT(*) as delivered FROM tblOrder WHERE buyerID = ? AND status = 'delivered'");
$stmt->bind_param('i', $userID);
$stmt->execute();
$deliveredResult = $stmt->get_result()->fetch_assoc();
$delivered = $deliveredResult['delivered'];
$stmt->close();

// Get in transit orders
$stmt = $conn->prepare("SELECT COUNT(*) as inTransit FROM tblOrder WHERE buyerID = ? AND status = 'dispatched'");
$stmt->bind_param('i', $userID);
$stmt->execute();
$transitResult = $stmt->get_result()->fetch_assoc();
$inTransit = $transitResult['inTransit'];
$stmt->close();

// Get unread messages
$stmt = $conn->prepare("SELECT COUNT(*) as unread FROM tblMessages WHERE receiverID = ? AND isRead = 0");
$stmt->bind_param('i', $userID);
$stmt->execute();
$messageResult = $stmt->get_result()->fetch_assoc();
$unreadMessages = $messageResult['unread'];
$stmt->close();

// Get listings count (if seller)
$totalListings = 0;
$pendingListings = 0;
if (in_array($role, ['seller', 'both'])) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tblClothes WHERE sellerID = ? AND status != 'sold'");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $listingResult = $stmt->get_result()->fetch_assoc();
    $totalListings = $listingResult['total'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM tblClothes WHERE sellerID = ? AND status = 'pending'");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $pendingResult = $stmt->get_result()->fetch_assoc();
    $pendingListings = $pendingResult['pending'];
    $stmt->close();
}

// Get recent orders
$stmt = $conn->prepare("SELECT o.*, c.title, c.imagePath FROM tblOrder o JOIN tblClothes c ON o.clothingID = c.clothingID WHERE o.buyerID = ? ORDER BY o.orderDate DESC LIMIT 5");
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
    <title>My Dashboard - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .stat-card i {
            font-size: 2rem;
            color: #1e40af;
            margin-bottom: 0.5rem;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        .stat-card .label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .stat-card .sublabel {
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 0;
        }
        .tab {
            padding: 1rem;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.3s;
        }
        .tab.active {
            color: #1e40af;
            border-bottom-color: #1e40af;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .orders-table thead {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .orders-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
        }
        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .orders-table tbody tr:last-child td {
            border-bottom: none;
        }
        .order-item {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .order-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <div class="dashboard-header">
            <h1 style="margin: 0; font-size: 2rem; color: #ffffff;">Welcome, <?php echo htmlspecialchars($fullName); ?>!</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Manage your orders, listings, and messages</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <i class="fas fa-shopping-bag"></i>
                <div class="number"><?php echo $totalOrders; ?></div>
                <div class="label">Total Orders</div>
                <div class="sublabel"><?php echo $delivered; ?> delivered</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <div class="number"><?php echo $inTransit; ?></div>
                <div class="label">In Transit</div>
                <div class="sublabel">Orders on the way</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <div class="number"><?php echo $unreadMessages; ?></div>
                <div class="label">Unread Messages</div>
                <div class="sublabel">Check your inbox</div>
            </div>
            <?php if (in_array($role, ['seller', 'both'])): ?>
                <div class="stat-card">
                    <i class="fas fa-list"></i>
                    <div class="number"><?php echo $totalListings; ?></div>
                    <div class="label">Active Listings</div>
                    <div class="sublabel"><?php echo $pendingListings; ?> pending</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab(event, 'orders')">
                <i class="fas fa-shopping-bag"></i> My Orders
            </button>
            <button class="tab" onclick="switchTab(event, 'messages')">
                <i class="fas fa-envelope"></i> Messages (<?php echo $unreadMessages; ?>)
            </button>
            <?php if (in_array($role, ['seller', 'both'])): ?>
                <button class="tab" onclick="switchTab(event, 'listings')">
                    <i class="fas fa-list"></i> My Listings
                </button>
            <?php endif; ?>
            <button class="tab" onclick="switchTab(event, 'profile')">
                <i class="fas fa-user"></i> Profile
            </button>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content active">
            <h2 style="margin-top: 0;">Recent Orders</h2>
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 3rem 1rem;">
                    <i class="fas fa-inbox" style="font-size: 2rem; color: #d1d5db; display: block; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">You haven't placed any orders yet.</p>
                    <a href="shop.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Start Shopping</a>
                </div>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <div class="order-item">
                                        <img src="<?php echo htmlspecialchars($order['imagePath']); ?>" alt="<?php echo htmlspecialchars($order['title']); ?>" class="order-image">
                                        <span><?php echo htmlspecialchars($order['title']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo formatPrice($order['totalAmount']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'badge-pending';
                                    if ($order['status'] === 'delivered') $statusClass = 'badge-delivered';
                                    elseif ($order['status'] === 'dispatched') $statusClass = 'badge-dispatched';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['orderDate'])); ?></td>
                                <td>
                                    <a href="product.php?id=<?php echo $order['clothingID']; ?>" style="color: #1e40af; text-decoration: none;">View Item</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="my-orders.php" style="display: block; text-align: center; margin-top: 1.5rem; color: #1e40af;">View all orders →</a>
            <?php endif; ?>
        </div>

        <!-- Messages Tab -->
        <div id="messages" class="tab-content">
            <h2 style="margin-top: 0;">Messages</h2>
            <a href="messages.php" class="btn btn-primary" style="margin-bottom: 1rem;">Go to Messages</a>
            <p style="color: #6b7280;">View and reply to messages from other users.</p>
        </div>

        <!-- Listings Tab -->
        <?php if (in_array($role, ['seller', 'both'])): ?>
            <div id="listings" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="margin: 0;">My Listings</h2>
                    <a href="sell.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Listing
                    </a>
                </div>
                <p style="color: #6b7280; margin-bottom: 1rem;">Manage your active listings and pending reviews.</p>
                <a href="dashboard.php?tab=listings" style="color: #1e40af;">View all listings →</a>
            </div>
        <?php endif; ?>

        <!-- Profile Tab -->
        <div id="profile" class="tab-content">
            <h2 style="margin-top: 0;">Profile Settings</h2>
            <a href="profile.php" class="btn btn-primary" style="display: inline-block; margin-bottom: 1rem;">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-top: 1rem;">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['fullName']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Role:</strong> <span style="text-transform: capitalize;"><?php echo $user['role']; ?></span></p>
                <p><strong>Status:</strong> <span style="text-transform: capitalize;"><?php echo $user['status']; ?></span></p>
            </div>
        </div>
    </main>

    <script>
        function switchTab(event, tabName) {
            event.preventDefault();
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.closest('.tab').classList.add('active');
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
