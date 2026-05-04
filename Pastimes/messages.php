<?php
/**
 * messages.php — User Inbox & Send Message
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
$selectedUserID = isset($_GET['user']) ? (int)$_GET['user'] : null;

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    
    if ($action === 'send') {
        $receiverID = (int)$_POST['receiver_id'];
        $subject = sanitizeInput($_POST['subject']);
        $messageBody = sanitizeInput($_POST['message']);
        $clothingID = isset($_POST['clothing_id']) ? (int)$_POST['clothing_id'] : null;
        
        if ($receiverID && $messageBody) {
            try {
                $stmt = $conn->prepare("INSERT INTO tblMessages (senderID, receiverID, clothingID, subject, messageBody, isRead, sentAt) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                $stmt->bind_param('iisis', $userID, $receiverID, $clothingID, $subject, $messageBody);
                $stmt->execute();
                $msgId = $conn->insert_id;
                $stmt->close();

                if (function_exists('appendDataFile')) {
                    appendDataFile('messagesData.txt', [
                        'action' => 'create',
                        'messageID' => (int)$msgId,
                        'senderID' => (int)$userID,
                        'receiverID' => (int)$receiverID,
                        'clothingID' => $clothingID ? (int)$clothingID : null,
                        'subject' => $subject,
                        'messageBody' => $messageBody,
                        'isRead' => 0,
                        'sentAt' => date('c')
                    ]);
                }
            } catch (Exception $e) {
                // Handle error silently
            }
        }
    } elseif ($action === 'mark-read') {
        $messageID = (int)$_POST['message_id'];
        try {
            $stmt = $conn->prepare("UPDATE tblMessages SET isRead = 1 WHERE messageID = ?");
            $stmt->bind_param('i', $messageID);
            $stmt->execute();
            $stmt->close();

            if (function_exists('appendDataFile')) {
                appendDataFile('messagesData.txt', [
                    'action' => 'update',
                    'messageID' => (int)$messageID,
                    'isRead' => 1,
                    'updatedAt' => date('c')
                ]);
            }
        } catch (Exception $e) {
            // Handle error silently
        }
    }
}

// Get conversation list (unique senders/receivers)
$stmt = $conn->prepare("
    SELECT DISTINCT 
        CASE WHEN senderID = ? THEN receiverID ELSE senderID END as otherUserID,
        u.fullName, u.username,
        MAX(sentAt) as lastMessage
    FROM tblMessages
    JOIN tblUser u ON u.userID = CASE WHEN senderID = ? THEN receiverID ELSE senderID END
    WHERE senderID = ? OR receiverID = ?
    GROUP BY otherUserID, u.fullName, u.username
    ORDER BY lastMessage DESC
");
$stmt->bind_param('iiii', $userID, $userID, $userID, $userID);
$stmt->execute();
$conversationsResult = $stmt->get_result();
$conversations = $conversationsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get selected conversation messages
$messages = [];
if ($selectedUserID) {
    $stmt = $conn->prepare("
        SELECT m.*, u.fullName, u.username 
        FROM tblMessages m
        JOIN tblUser u ON m.senderID = u.userID
        WHERE (senderID = ? AND receiverID = ?) OR (senderID = ? AND receiverID = ?)
        ORDER BY sentAt ASC
    ");
    $stmt->bind_param('iiii', $userID, $selectedUserID, $selectedUserID, $userID);
    $stmt->execute();
    $messagesResult = $stmt->get_result();
    $messages = $messagesResult->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Mark messages as read
    $stmt = $conn->prepare("UPDATE tblMessages SET isRead = 1 WHERE receiverID = ? AND senderID = ?");
    $stmt->bind_param('ii', $userID, $selectedUserID);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1.5rem;
            height: 600px;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .messages-container {
                grid-template-columns: 1fr;
                height: auto;
            }
        }
        .conversation-list {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow-y: auto;
        }
        .conversation-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .conversation-item:hover, .conversation-item.active {
            background: #f3f4f6;
        }
        .conversation-item.active {
            background: #eff6ff;
            border-left: 3px solid #1e40af;
        }
        .conversation-name {
            font-weight: 600;
            color: #1f2937;
        }
        .conversation-time {
            font-size: 0.8rem;
            color: #9ca3af;
        }
        .messages-window {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .messages-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .messages-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .message {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.75rem;
        }
        .message.sent {
            flex-direction: row-reverse;
        }
        .message-bubble {
            max-width: 75%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            word-wrap: break-word;
        }
        .message.received .message-bubble {
            background: #e5e7eb;
            color: #1f2937;
        }
        .message.sent .message-bubble {
            background: #1e40af;
            color: white;
        }
        .message-time {
            font-size: 0.75rem;
            color: #9ca3af;
            align-self: flex-end;
        }
        .messages-footer {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .message-form {
            display: flex;
            gap: 0.5rem;
        }
        .message-form input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: Inter, sans-serif;
        }
        .message-form button {
            background: #1e40af;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .empty-messages {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <h1 style="margin: 0 0 0.5rem 0;">Messages</h1>
        <p style="color: #6b7280; margin: 0;">Chat with sellers and buyers about items</p>

        <div class="messages-container">
            <div class="conversation-list">
                <?php if (empty($conversations)): ?>
                    <div style="padding: 2rem 1rem; text-align: center; color: #9ca3af;">
                        <p>No conversations yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <a href="messages.php?user=<?php echo $conv['otherUserID']; ?>" class="conversation-item <?php echo ($selectedUserID === $conv['otherUserID']) ? 'active' : ''; ?>">
                            <div class="conversation-name"><?php echo htmlspecialchars($conv['fullName']); ?></div>
                            <div class="conversation-time"><?php echo date('M d', strtotime($conv['lastMessage'])); ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="messages-window">
                <?php if ($selectedUserID): ?>
                    <div class="messages-header">
                        <?php
                        $stmt = $conn->prepare("SELECT fullName FROM tblUser WHERE userID = ?");
                        $stmt->bind_param('i', $selectedUserID);
                        $stmt->execute();
                        $userResult = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        ?>
                        <h2 style="margin: 0;"><?php echo htmlspecialchars($userResult['fullName']); ?></h2>
                    </div>

                    <div class="messages-body">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo ($msg['senderID'] === $userID) ? 'sent' : 'received'; ?>">
                                <div class="message-bubble"><?php echo htmlspecialchars($msg['messageBody']); ?></div>
                                <div class="message-time"><?php echo date('H:i', strtotime($msg['sentAt'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="messages-footer">
                        <form method="POST" class="message-form">
                            <input type="hidden" name="action" value="send">
                            <input type="hidden" name="receiver_id" value="<?php echo $selectedUserID; ?>">
                            <input type="hidden" name="subject" value="Message">
                            <textarea name="message" placeholder="Type a message..." style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-family: Inter, sans-serif; resize: none; height: 45px;" required></textarea>
                            <button type="submit" style="background: #1e40af; color: white; border: none; padding: 0.75rem 1rem; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-messages">
                        <i class="fas fa-comment-dots" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>Select a conversation to continue chatting</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
