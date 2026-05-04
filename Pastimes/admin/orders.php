<?php
/**
 * admin/orders.php — Manage Orders (update status, view details)
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderID = (int)($_POST['order_id'] ?? 0);
    $newStatus = sanitizeInput($_POST['status'] ?? '');

    if ($orderID && in_array($newStatus, ['pending', 'dispatched', 'delivered', 'cancelled'])) {
        try {
            $stmt = $conn->prepare("UPDATE tblOrder SET status = ? WHERE orderID = ?");
            $stmt->bind_param('si', $newStatus, $orderID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendDataFile')) {
                appendDataFile('ordersData.txt', [
                    'action' => 'update_status',
                    'orderID' => (int)$orderID,
                    'status' => $newStatus,
                    'updatedAt' => date('c')
                ]);
            }
        } catch (Exception $e) {}
    }
}

// Get orders
$stmt = $conn->prepare("SELECT o.*, c.title, u.fullName FROM tblOrder o JOIN tblClothes c ON o.clothingID = c.clothingID JOIN tblUser u ON o.buyerID = u.userID ORDER BY o.orderDate DESC");
$stmt->execute();
$ordersResult = $stmt->get_result();
$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'includes/admin-header.php'; ?>

<style>
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
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
    .status-dispatched {
        background: #bfdbfe;
        color: #1e40af;
    }
    .status-delivered {
        background: #d1fae5;
        color: #065f46;
    }
    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }
    .status-select {
        padding: 0.4rem;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 0.9rem;
    }
</style>

<h1 style="margin: 0 0 1.5rem 0;">Order Management</h1>

<table class="orders-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Item</th>
            <th>Amount</th>
            <th>Delivery City</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo $order['orderID']; ?></td>
                <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                <td><?php echo htmlspecialchars($order['title']); ?></td>
                <td><?php echo formatPrice($order['totalAmount']); ?></td>
                <td><?php echo htmlspecialchars($order['deliveryCity']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order['orderID']; ?>">
                        <select name="status" class="status-select" onchange="this.form.submit();">
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="dispatched" <?php echo $order['status'] === 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </form>
                </td>
                <td><?php echo date('d M Y', strtotime($order['orderDate'])); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/admin-footer.php'; ?>
