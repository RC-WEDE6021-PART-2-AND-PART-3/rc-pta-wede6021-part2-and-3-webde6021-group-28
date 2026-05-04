<?php
/**
 * admin/users.php — Manage Users (view, activate, suspend, change role)
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
    $userID = (int)($_POST['user_id'] ?? 0);

    if ($action === 'verify') {
        try {
            $stmt = $conn->prepare("UPDATE tblUser SET status = 'active' WHERE userID = ?");
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendPlainDataFile')) {
                // Format: update_status\tuserID\tstatus\tupdatedAt
                appendPlainDataFile('userData.txt', 'update_status' . "\t" . (int)$userID . "\t" . 'active' . "\t" . date('c'));
            }
        } catch (Exception $e) {}
    } elseif ($action === 'suspend') {
        try {
            $stmt = $conn->prepare("UPDATE tblUser SET status = 'suspended' WHERE userID = ?");
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendPlainDataFile')) {
                // Format: update_status\tuserID\tstatus\tupdatedAt
                appendPlainDataFile('userData.txt', 'update_status' . "\t" . (int)$userID . "\t" . 'suspended' . "\t" . date('c'));
            }
        } catch (Exception $e) {}
    } elseif ($action === 'delete') {
        try {
            $stmt = $conn->prepare("DELETE FROM tblUser WHERE userID = ?");
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->close();
            if (function_exists('appendPlainDataFile')) {
                // Format: delete\tuserID\tdeletedAt
                appendPlainDataFile('userData.txt', 'delete' . "\t" . (int)$userID . "\t" . date('c'));
            }
        } catch (Exception $e) {}
    }
}

// Get all users
$stmt = $conn->prepare("SELECT * FROM tblUser ORDER BY createdAt DESC");
$stmt->execute();
$usersResult = $stmt->get_result();
$users = $usersResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'includes/admin-header.php'; ?>

<style>
    .users-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .users-table thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .users-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
    }
    .users-table td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .users-table tbody tr:last-child td {
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
    .status-active {
        background: #d1fae5;
        color: #065f46;
    }
    .status-suspended {
        background: #fee2e2;
        color: #991b1b;
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
    .btn-verify {
        background: #d1fae5;
        color: #065f46;
    }
    .btn-suspend {
        background: #fef3c7;
        color: #b45309;
    }
    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }
</style>

<h1 style="margin: 0 0 1.5rem 0;">User Management</h1>

<table class="users-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['userID']; ?></td>
                <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td style="text-transform: capitalize;"><?php echo $user['role']; ?></td>
                <td>
                    <?php
                    $statusClass = 'status-' . $user['status'];
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($user['status']); ?></span>
                </td>
                <td>
                    <div class="action-buttons">
                        <?php if ($user['status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="verify">
                                <input type="hidden" name="user_id" value="<?php echo $user['userID']; ?>">
                                <button type="submit" class="btn-verify">Verify</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($user['status'] !== 'suspended'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="suspend">
                                <input type="hidden" name="user_id" value="<?php echo $user['userID']; ?>">
                                <button type="submit" class="btn-suspend">Suspend</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo $user['userID']; ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/admin-footer.php'; ?>
