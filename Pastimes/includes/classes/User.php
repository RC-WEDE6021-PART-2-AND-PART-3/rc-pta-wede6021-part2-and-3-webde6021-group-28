<?php
/**
 * User.php — OOP User Class
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

class User {
    // Properties
    public $userID;
    public $fullName;
    public $email;
    public $username;
    public $role;
    public $status;
    public $address;
    public $city;
    public $postalCode;
    public $phone;
    public $profilePic;
    public $createdAt;
    
    private $conn;
    
    /**
     * Constructor
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Register a new user
     * @param array $data User data
     * @return array Result with success status and message
     */
    public function register($data) {
        try {
            // Check if username exists
            $stmt = $this->conn->prepare("SELECT userID FROM tblUser WHERE username = ?");
            $stmt->bind_param("s", $data['username']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'Username already exists. Please choose another.'];
            }
            $stmt->close();
            
            // Check if email exists
            $stmt = $this->conn->prepare("SELECT userID FROM tblUser WHERE email = ?");
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'Email already registered. Please use another email.'];
            }
            $stmt->close();
            
            // Hash password
            $passwordHash = md5($data['password']);
            
            // Insert new user
            $stmt = $this->conn->prepare(
                "INSERT INTO tblUser (fullName, email, username, passwordHash, role, status) 
                 VALUES (?, ?, ?, ?, ?, 'pending')"
            );
            $stmt->bind_param("sssss", 
                $data['fullName'], 
                $data['email'], 
                $data['username'], 
                $passwordHash, 
                $data['role']
            );
            
            if ($stmt->execute()) {
                $newId = $this->conn->insert_id;
                // Mirror to text database
                if (function_exists('appendPlainDataFile')) {
                    // Format: fullName\temail\tusername\tpasswordHash\trole\tstatus
                    appendPlainDataFile('userData.txt', $data['fullName'] . "\t" . $data['email'] . "\t" . $data['username'] . "\t" . $passwordHash . "\t" . $data['role'] . "\t" . 'pending');
                }

                return ['success' => true, 'message' => 'Registration successful! Your account is pending admin verification. You will be able to log in once approved.'];
            } else {
                return ['success' => false, 'message' => 'Registration failed. Please try again.'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     * @param string $username Username
     * @param string $password Password
     * @return array Result with success status, message, and user data
     */
    public function login($username, $password) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT userID, fullName, email, username, passwordHash, role, status, profilePic 
                 FROM tblUser WHERE username = ?"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'User not found. Would you like to <a href="register.php">register</a>?'];
            }
            
            $user = $result->fetch_assoc();
            
            // Check password
            if (!verifyPassword($password, $user['passwordHash'])) {
                return ['success' => false, 'message' => 'Incorrect password. Please try again.', 'sticky' => true];
            }
            
            // Check status
            if ($user['status'] === 'pending') {
                return ['success' => false, 'message' => 'Your account is pending admin approval. Please check back later.'];
            }
            
            if ($user['status'] === 'suspended') {
                return ['success' => false, 'message' => 'Your account has been suspended. Contact support.'];
            }
            
            // Login successful
            return [
                'success' => true, 
                'message' => 'Login successful!',
                'user' => $user
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update user profile
     * @param int $userID User ID
     * @param array $data Profile data
     * @return array Result with success status and message
     */
    public function updateProfile($userID, $data) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE tblUser SET fullName = ?, email = ?, phone = ?, address = ?, city = ?, postalCode = ? 
                 WHERE userID = ?"
            );
            $stmt->bind_param("ssssssi", 
                $data['fullName'], 
                $data['email'], 
                $data['phone'], 
                $data['address'], 
                $data['city'], 
                $data['postalCode'],
                $userID
            );
            
            if ($stmt->execute()) {
                // Update session
                $_SESSION['fullName'] = $data['fullName'];
                if (function_exists('appendPlainDataFile')) {
                    // Format: update\tuserID\tfullName\temail\tphone\taddress\tcity\tpostalCode\tupdatedAt
                    $line = 'update' . "\t" . (int)$userID . "\t" . $data['fullName'] . "\t" . $data['email'] . "\t" . $data['phone'] . "\t" . $data['address'] . "\t" . $data['city'] . "\t" . $data['postalCode'] . "\t" . date('c');
                    appendPlainDataFile('userData.txt', $line);
                }
                return ['success' => true, 'message' => 'Profile updated successfully!'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile.'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user orders
     * @param int $userID User ID
     * @return array Array of orders
     */
    public function getOrders($userID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT o.*, c.title, c.imagePath, c.brand, c.size 
                 FROM tblOrder o 
                 JOIN tblClothes c ON o.clothingID = c.clothingID 
                 WHERE o.buyerID = ? 
                 ORDER BY o.orderDate DESC"
            );
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            
            return $orders;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get user by ID
     * @param int $userID User ID
     * @return array|null User data or null
     */
    public function getByID($userID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM tblUser WHERE userID = ?"
            );
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all users
     * @param string $status Filter by status (optional)
     * @return array Array of users
     */
    public function getAll($status = null) {
        try {
            if ($status) {
                $stmt = $this->conn->prepare("SELECT * FROM tblUser WHERE status = ? ORDER BY createdAt DESC");
                $stmt->bind_param("s", $status);
            } else {
                $stmt = $this->conn->prepare("SELECT * FROM tblUser ORDER BY createdAt DESC");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            return $users;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update user status
     * @param int $userID User ID
     * @param string $status New status
     * @return bool Success
     */
    public function updateStatus($userID, $status) {
        try {
            $stmt = $this->conn->prepare("UPDATE tblUser SET status = ? WHERE userID = ?");
            $stmt->bind_param("si", $status, $userID);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete user
     * @param int $userID User ID
     * @return bool Success
     */
    public function delete($userID) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM tblUser WHERE userID = ?");
            $stmt->bind_param("i", $userID);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
