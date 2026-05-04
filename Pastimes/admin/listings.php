<?php
/**
 * admin/listings.php — Manage Clothing Listings (approve, reject, delete)
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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitizeInput($_POST['action'] ?? '');
    $clothingID = (int)($_POST['clothing_id'] ?? 0);

    if ($action === 'approve') {
        try {
            $stmt = $conn->prepare("UPDATE tblClothes SET status = 'approved' WHERE clothingID = ?");
            $stmt->bind_param('i', $clothingID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', [
                    'action' => 'update_status',
                    'clothingID' => (int)$clothingID,
                    'status' => 'approved',
                    'updatedAt' => date('c')
                ]);
            }
        } catch (Exception $e) {}
    } elseif ($action === 'reject') {
        try {
            $stmt = $conn->prepare("UPDATE tblClothes SET status = 'rejected' WHERE clothingID = ?");
            $stmt->bind_param('i', $clothingID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', [
                    'action' => 'update_status',
                    'clothingID' => (int)$clothingID,
                    'status' => 'rejected',
                    'updatedAt' => date('c')
                ]);
            }
        } catch (Exception $e) {}
    } elseif ($action === 'delete') {
        try {
            $stmt = $conn->prepare("DELETE FROM tblClothes WHERE clothingID = ?");
            $stmt->bind_param('i', $clothingID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', [
                    'action' => 'delete',
                    'clothingID' => (int)$clothingID,
                    'deletedAt' => date('c')
                ]);
            }
        } catch (Exception $e) {}
    }
}

// Get filter
$status = sanitizeInput($_GET['status'] ?? '');
$where = '';
if ($status) {
    $where = "WHERE status = '$status'";
}

// Get listings
$query = "SELECT c.*, u.fullName FROM tblClothes c JOIN tblUser u ON c.sellerID = u.userID $where ORDER BY c.createdAt DESC";
$result = $conn->query($query);
$listings = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'includes/admin-header.php'; ?>

<style>
    .listings-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .listings-table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .listings-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
    }
    .listings-table td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .listings-table tbody tr:last-child td {
        border-bottom: none;
    }
    .status-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-pending {
        background: #fef3c7;
        color: #b45309;
    }
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .status-sold {
        background: #e5e7eb;
        color: #4b5563;
    }
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    .action-buttons button {
        padding: 0.4rem 0.8rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .btn-approve {
        background: #d1fae5;
        color: #065f46;
    }
    .btn-reject {
        background: #fef3c7;
        color: #b45309;
    }
    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .filter-tabs a {
        padding: 0.5rem 1rem;
        border-radius: 4px;
        background: white;
        border: 1px solid #e5e7eb;
        text-decoration: none;
        color: #6b7280;
    }
    .filter-tabs a.active {
        background: #1e40af;
        color: white;
        border-color: #1e40af;
    }
</style>

<h1 style="margin: 0 0 1.5rem 0;">Listing Management</h1>

<div class="filter-tabs">
    <a href="listings.php" class="<?php echo empty($status) ? 'active' : ''; ?>">All</a>
    <a href="listings.php?status=pending" class="<?php echo $status === 'pending' ? 'active' : ''; ?>">Pending</a>
    <a href="listings.php?status=approved" class="<?php echo $status === 'approved' ? 'active' : ''; ?>">Approved</a>
    <a href="listings.php?status=rejected" class="<?php echo $status === 'rejected' ? 'active' : ''; ?>">Rejected</a>
    <a href="listings.php?status=sold" class="<?php echo $status === 'sold' ? 'active' : ''; ?>">Sold</a>
</div>

<table class="listings-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Seller</th>
            <th>Brand</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($listings as $listing): ?>
            <tr>
                <td><?php echo $listing['clothingID']; ?></td>
                <td><?php echo htmlspecialchars($listing['title']); ?></td>
                <td><?php echo htmlspecialchars($listing['fullName']); ?></td>
                <td><?php echo htmlspecialchars($listing['brand']); ?></td>
                <td><?php echo formatPrice($listing['price']); ?></td>
                <td>
                    <?php
                    $statusClass = 'status-' . $listing['status'];
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($listing['status']); ?></span>
                </td>
                <td>
                    <div class="action-buttons">
                        <?php if ($listing['status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="clothing_id" value="<?php echo $listing['clothingID']; ?>">
                                <button type="submit" class="btn-approve">Approve</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="clothing_id" value="<?php echo $listing['clothingID']; ?>">
                                <button type="submit" class="btn-reject">Reject</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="clothing_id" value="<?php echo $listing['clothingID']; ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/admin-footer.php'; ?>
