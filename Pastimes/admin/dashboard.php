<?php
/**
 * admin/dashboard.php — Admin Overview Dashboard
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

session_start();
require_once '../includes/DBConn.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['adminID'])) {
    redirect('login.php');
}

// Get stats
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM tblUser");
$stmt->execute();
$userCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM tblUser WHERE status = 'pending'");
$stmt->execute();
$pendingCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM tblClothes WHERE status = 'approved'");
$stmt->execute();
$listingCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM tblOrder");
$stmt->execute();
$orderCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM tblMessages WHERE isRead = 0");
$stmt->execute();
$messageCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Pending verifications
$stmt = $conn->prepare("SELECT userID, fullName, email, role FROM tblUser WHERE status = 'pending' ORDER BY createdAt DESC LIMIT 5");
$stmt->execute();
$pendingUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Pending listings
$stmt = $conn->prepare("SELECT c.*, u.fullName FROM tblClothes c JOIN tblUser u ON c.sellerID = u.userID WHERE c.status = 'pending' ORDER BY c.createdAt DESC LIMIT 5");
$stmt->execute();
$pendingListings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'includes/admin-header.php'; ?>

<style>
    .dashboard-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .stats-grid {
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
    }
    .stat-card i {
        font-size: 2rem;
        color: #1e40af;
        margin-bottom: 0.5rem;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0.5rem 0;
    }
    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
    }
    .stat-sublabel {
        color: #9ca3af;
        font-size: 0.8rem;
    }
    .pending-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    .pending-item {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pending-item:last-child {
        border-bottom: none;
    }
    .pending-info h4 {
        margin: 0 0 0.25rem 0;
    }
    .pending-info p {
        margin: 0.25rem 0;
        color: #6b7280;
        font-size: 0.9rem;
    }
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    .action-buttons button {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .btn-approve {
        background: #d1fae5;
        color: #065f46;
    }
    .btn-reject {
        background: #fee2e2;
        color: #991b1b;
    }
</style>

<div class="dashboard-title">
    <div>
        <h1 style="margin: 0; font-size: 2rem;">Admin Dashboard</h1>
        <p style="color: #6b7280; margin: 0.5rem 0 0 0;">System Status: <span style="color: #10b981; font-weight: 600;">Active</span></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-number"><?php echo $userCount; ?></div>
        <div class="stat-label">Total Users</div>
        <div class="stat-sublabel"><?php echo $pendingCount; ?> pending</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-box"></i>
        <div class="stat-number"><?php echo $listingCount; ?></div>
        <div class="stat-label">Active Listings</div>
        <div class="stat-sublabel">All approved</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-shopping-bag"></i>
        <div class="stat-number"><?php echo $orderCount; ?></div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-sublabel">All time</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-envelope"></i>
        <div class="stat-number"><?php echo $messageCount; ?></div>
        <div class="stat-label">Messages</div>
        <div class="stat-sublabel"><?php echo $messageCount; ?> unread</div>
    </div>
</div>

<div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
    <a href="users.php" class="btn btn-secondary" style="display: inline-block;">Manage Users</a>
    <a href="listings.php" class="btn btn-secondary" style="display: inline-block;">Review Listings</a>
    <a href="orders.php" class="btn btn-secondary" style="display: inline-block;">View Orders</a>
</div>

<?php if (!empty($pendingUsers)): ?>
    <div class="pending-section">
        <h2 style="margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-hourglass-half" style="color: #f59e0b;"></i> Pending User Verifications (<?php echo count($pendingUsers); ?>)
        </h2>
        <?php foreach ($pendingUsers as $user): ?>
            <div class="pending-item">
                <div class="pending-info">
                    <h4><?php echo htmlspecialchars($user['fullName']); ?></h4>
                    <p><?php echo htmlspecialchars($user['email']); ?> • <?php echo ucfirst($user['role']); ?></p>
                </div>
                <div class="action-buttons">
                    <form method="POST" action="users.php" style="display: inline;">
                        <input type="hidden" name="action" value="verify">
                        <input type="hidden" name="user_id" value="<?php echo $user['userID']; ?>">
                        <button type="submit" class="btn-approve">Approve</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($pendingListings)): ?>
    <div class="pending-section">
        <h2 style="margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-hourglass-half" style="color: #f59e0b;"></i> Pending Listings (<?php echo count($pendingListings); ?>)
        </h2>
        <?php foreach ($pendingListings as $listing): ?>
            <div class="pending-item">
                <div class="pending-info">
                    <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                    <p>By <?php echo htmlspecialchars($listing['fullName']); ?> • <?php echo formatPrice($listing['price']); ?></p>
                </div>
                <div class="action-buttons">
                    <form method="POST" action="listings.php" style="display: inline;">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="clothing_id" value="<?php echo $listing['clothingID']; ?>">
                        <button type="submit" class="btn-approve">Approve</button>
                    </form>
                    <form method="POST" action="listings.php" style="display: inline;">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="clothing_id" value="<?php echo $listing['clothingID']; ?>">
                        <button type="submit" class="btn-reject">Reject</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/admin-footer.php'; ?>
