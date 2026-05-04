<?php
/**
 * admin/messages.php — View all user messages / inbox monitor
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

$adminID = $_SESSION['adminID'];

// Get admin user ID (which is the sender)
$stmt = $conn->prepare("SELECT adminID FROM tblAdmin WHERE adminID = ?");
$stmt->bind_param('i', $adminID);
$stmt->execute();
$adminResult = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle broadcast
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = sanitizeInput($_POST['recipient'] ?? '');
    $subject = sanitizeInput($_POST['subject']);
    $messageBody = sanitizeInput($_POST['message']);

    if ($subject && $messageBody) {
        $recipients = [];
        
        if ($recipient === 'all') {
            $stmt = $conn->prepare("SELECT userID FROM tblUser");
            $stmt->execute();
            $results = $stmt->get_result();
            while ($row = $results->fetch_assoc()) {
                $recipients[] = $row['userID'];
            }
            $stmt->close();
        } elseif ($recipient === 'sellers') {
            $stmt = $conn->prepare("SELECT userID FROM tblUser WHERE role IN ('seller', 'both')");
            $stmt->execute();
            $results = $stmt->get_result();
            while ($row = $results->fetch_assoc()) {
                $recipients[] = $row['userID'];
            }
            $stmt->close();
        } elseif ($recipient === 'buyers') {
            $stmt = $conn->prepare("SELECT userID FROM tblUser WHERE role IN ('buyer', 'both')");
            $stmt->execute();
            $results = $stmt->get_result();
            while ($row = $results->fetch_assoc()) {
                $recipients[] = $row['userID'];
            }
            $stmt->close();
        } else {
            $recipientID = (int)$recipient;
            $recipients = [$recipientID];
        }

        // Send messages
        foreach ($recipients as $receiverID) {
            try {
                $stmt = $conn->prepare("INSERT INTO tblMessages (senderID, receiverID, subject, messageBody, isRead, sentAt) VALUES (?, ?, ?, ?, 0, NOW())");
                // Use a dummy sender ID for admin messages (or create an admin user record)
                $dummySenderID = 0;
                $stmt->bind_param('iiss', $dummySenderID, $receiverID, $subject, $messageBody);
                if ($stmt->execute()) {
                    $inserted = $conn->insert_id;
                    if (function_exists('appendDataFile')) {
                        appendDataFile('messagesData.txt', [
                            'action' => 'create',
                            'messageID' => (int)$inserted,
                            'senderID' => (int)$dummySenderID,
                            'receiverID' => (int)$receiverID,
                            'subject' => $subject,
                            'messageBody' => $messageBody,
                            'isRead' => 0,
                            'sentAt' => date('c')
                        ]);
                    }
                }
                $stmt->close();
            } catch (Exception $e) {}
        }
    }
}

// Get recent messages
$stmt = $conn->prepare("SELECT m.*, u.fullName FROM tblMessages m JOIN tblUser u ON m.senderID = u.userID ORDER BY m.sentAt DESC LIMIT 20");
$stmt->execute();
$messagesResult = $stmt->get_result();
$messages = $messagesResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get users for dropdown
$stmt = $conn->prepare("SELECT userID, fullName FROM tblUser ORDER BY fullName");
$stmt->execute();
$usersResult = $stmt->get_result();
$users = $usersResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'includes/admin-header.php'; ?>

<style>
    .message-section {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #374151;
    }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-family: Inter, sans-serif;
        font-size: 0.95rem;
        box-sizing: border-box;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }
    .messages-list {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .message-item {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .message-item:last-child {
        border-bottom: none;
    }
    .message-info h4 {
        margin: 0 0 0.25rem 0;
    }
    .message-info p {
        margin: 0.25rem 0;
        color: #6b7280;
        font-size: 0.9rem;
    }
    .message-time {
        color: #9ca3af;
        font-size: 0.85rem;
    }
</style>

<h1 style="margin: 0 0 1.5rem 0;">Message Broadcast</h1>

<div class="message-section">
    <h2 style="margin: 0 0 1rem 0;">Send Broadcast Message</h2>
    
    <form method="POST">
        <div class="form-group">
            <label for="recipient">SEND TO</label>
            <select id="recipient" name="recipient" required>
                <option value="">Select recipient group</option>
                <option value="all">All Users</option>
                <option value="sellers">All Sellers</option>
                <option value="buyers">All Buyers</option>
                <optgroup label="Individual Users">
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['userID']; ?>"><?php echo htmlspecialchars($user['fullName']); ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>

        <div class="form-group">
            <label for="subject">SUBJECT</label>
            <input type="text" id="subject" name="subject" placeholder="Message subject..." required>
        </div>

        <div class="form-group">
            <label for="message">MESSAGE</label>
            <textarea id="message" name="message" placeholder="Write your message..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
            <i class="fas fa-paper-plane"></i> Send Message
        </button>
    </form>
</div>

<h2 style="margin: 1.5rem 0;">Recent Messages</h2>

<div class="messages-list">
    <?php if (empty($messages)): ?>
        <div style="padding: 2rem; text-align: center; color: #9ca3af;">
            <p>No messages yet</p>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message-item">
                <div class="message-info">
                    <h4><?php echo htmlspecialchars($msg['subject']); ?></h4>
                    <p><?php echo htmlspecialchars(substr($msg['messageBody'], 0, 80)); ?>...</p>
                </div>
                <div class="message-time"><?php echo date('d M Y H:i', strtotime($msg['sentAt'])); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
