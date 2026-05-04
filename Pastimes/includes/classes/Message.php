<?php
/**
 * Message.php — OOP Message Class
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

class Message {
    // Properties
    public $messageID;
    public $senderID;
    public $receiverID;
    public $clothingID;
    public $subject;
    public $messageBody;
    public $isRead;
    public $sentAt;
    
    private $conn;
    
    /**
     * Constructor
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Send a message
     * @param array $data Message data
     * @return array Result with success status and message
     */
    public function send($data) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO tblMessages (senderID, receiverID, clothingID, subject, messageBody, isRead) 
                 VALUES (?, ?, ?, ?, ?, 0)"
            );
            
            $clothingID = isset($data['clothingID']) ? $data['clothingID'] : null;
            
            $stmt->bind_param("iiiss", 
                $data['senderID'], 
                $data['receiverID'], 
                $clothingID, 
                $data['subject'], 
                $data['messageBody']
            );
            
            if ($stmt->execute()) {
                $newId = $this->conn->insert_id;
                if (function_exists('appendDataFile')) {
                    appendDataFile('messagesData.txt', [
                        'action' => 'create',
                        'messageID' => (int)$newId,
                        'senderID' => (int)$data['senderID'],
                        'receiverID' => (int)$data['receiverID'],
                        'clothingID' => isset($data['clothingID']) ? (int)$data['clothingID'] : null,
                        'subject' => $data['subject'],
                        'messageBody' => $data['messageBody'],
                        'isRead' => 0,
                        'sentAt' => date('c')
                    ]);
                }
                return ['success' => true, 'message' => 'Message sent successfully!'];
            } else {
                return ['success' => false, 'message' => 'Failed to send message.'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get inbox messages for a user
     * @param int $userID User ID
     * @return array Array of messages
     */
    public function getInbox($userID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, 
                        s.fullName as senderName, s.username as senderUsername, s.profilePic as senderPic,
                        c.title as clothingTitle 
                 FROM tblMessages m 
                 LEFT JOIN tblUser s ON m.senderID = s.userID 
                 LEFT JOIN tblClothes c ON m.clothingID = c.clothingID 
                 WHERE m.receiverID = ? 
                 ORDER BY m.sentAt DESC"
            );
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            return $messages;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get sent messages for a user
     * @param int $userID User ID
     * @return array Array of messages
     */
    public function getSent($userID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, 
                        r.fullName as receiverName, r.username as receiverUsername,
                        c.title as clothingTitle 
                 FROM tblMessages m 
                 LEFT JOIN tblUser r ON m.receiverID = r.userID 
                 LEFT JOIN tblClothes c ON m.clothingID = c.clothingID 
                 WHERE m.senderID = ? 
                 ORDER BY m.sentAt DESC"
            );
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            return $messages;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Mark message as read
     * @param int $messageID Message ID
     * @return bool Success
     */
    public function markRead($messageID) {
        try {
            $stmt = $this->conn->prepare("UPDATE tblMessages SET isRead = 1 WHERE messageID = ?");
            $stmt->bind_param("i", $messageID);
            $ok = $stmt->execute();
            if ($ok && function_exists('appendDataFile')) {
                appendDataFile('messagesData.txt', ['action'=>'update','messageID'=>(int)$messageID,'isRead'=>1,'updatedAt'=>date('c')]);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Count unread messages for a user
     * @param int $userID User ID
     * @return int Count
     */
    public function countUnread($userID) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM tblMessages WHERE receiverID = ? AND isRead = 0");
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get conversation thread between two users
     * @param int $userID1 First user ID
     * @param int $userID2 Second user ID
     * @return array Array of messages
     */
    public function getConversation($userID1, $userID2) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, 
                        s.fullName as senderName, s.username as senderUsername, s.profilePic as senderPic 
                 FROM tblMessages m 
                 LEFT JOIN tblUser s ON m.senderID = s.userID 
                 WHERE (m.senderID = ? AND m.receiverID = ?) OR (m.senderID = ? AND m.receiverID = ?) 
                 ORDER BY m.sentAt ASC"
            );
            $stmt->bind_param("iiii", $userID1, $userID2, $userID2, $userID1);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            return $messages;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get all messages (for admin)
     * @return array Array of messages
     */
    public function getAll() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, 
                        s.fullName as senderName, s.username as senderUsername,
                        r.fullName as receiverName, r.username as receiverUsername 
                 FROM tblMessages m 
                 LEFT JOIN tblUser s ON m.senderID = s.userID 
                 LEFT JOIN tblUser r ON m.receiverID = r.userID 
                 ORDER BY m.sentAt DESC"
            );
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            
            return $messages;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Send broadcast message (admin only)
     * @param int $senderID Admin sender ID
     * @param string $recipientType Type of recipients (all, buyers, sellers)
     * @param string $subject Message subject
     * @param string $messageBody Message body
     * @return array Result with success status
     */
    public function sendBroadcast($senderID, $recipientType, $subject, $messageBody) {
        try {
            // Get recipients based on type
            $sql = "SELECT userID FROM tblUser WHERE status = 'active'";
            
            if ($recipientType === 'buyers') {
                $sql .= " AND role IN ('buyer', 'both')";
            } elseif ($recipientType === 'sellers') {
                $sql .= " AND role IN ('seller', 'both')";
            }
            
            $result = $this->conn->query($sql);
            $count = 0;
            
            while ($row = $result->fetch_assoc()) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO tblMessages (senderID, receiverID, subject, messageBody, isRead) 
                     VALUES (?, ?, ?, ?, 0)"
                );
                $stmt->bind_param("iiss", $senderID, $row['userID'], $subject, $messageBody);
                if ($stmt->execute()) {
                    $count++;
                    if (function_exists('appendDataFile')) {
                        appendDataFile('messagesData.txt', [
                            'action' => 'create',
                            'messageID' => $this->conn->insert_id,
                            'senderID' => (int)$senderID,
                            'receiverID' => (int)$row['userID'],
                            'subject' => $subject,
                            'messageBody' => $messageBody,
                            'isRead' => 0,
                            'sentAt' => date('c')
                        ]);
                    }
                }
            }
            
            return ['success' => true, 'message' => "Broadcast sent to $count recipients."];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get message by ID
     * @param int $messageID Message ID
     * @return array|null Message data or null
     */
    public function getByID($messageID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, 
                        s.fullName as senderName, s.username as senderUsername,
                        r.fullName as receiverName, r.username as receiverUsername 
                 FROM tblMessages m 
                 LEFT JOIN tblUser s ON m.senderID = s.userID 
                 LEFT JOIN tblUser r ON m.receiverID = r.userID 
                 WHERE m.messageID = ?"
            );
            $stmt->bind_param("i", $messageID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
