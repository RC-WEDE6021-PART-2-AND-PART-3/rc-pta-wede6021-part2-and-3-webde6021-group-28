<?php
/**
 * Clothing.php — OOP Clothing Class
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

class Clothing {
    // Properties
    public $clothingID;
    public $sellerID;
    public $title;
    public $brand;
    public $category;
    public $size;
    public $condition;
    public $price;
    public $description;
    public $imagePath;
    public $status;
    public $suggestedPrice;
    public $co2Saved;
    public $waterSaved;
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
     * Save new clothing item
     * @param array $data Clothing data
     * @return array Result with success status and message
     */
    public function save($data) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO tblClothes (sellerID, title, brand, category, size, itemCondition, price, description, imagePath, status, suggestedPrice, co2Saved, waterSaved) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)"
            );
            
            $co2Saved = isset($data['co2Saved']) ? $data['co2Saved'] : 3.00;
            $waterSaved = isset($data['waterSaved']) ? $data['waterSaved'] : 2700;
            $suggestedPrice = isset($data['suggestedPrice']) ? $data['suggestedPrice'] : $data['price'];
            
            $stmt->bind_param("isssssdssddi", 
                $data['sellerID'], 
                $data['title'], 
                $data['brand'], 
                $data['category'], 
                $data['size'], 
                $data['condition'], 
                $data['price'], 
                $data['description'], 
                $data['imagePath'],
                $suggestedPrice,
                $co2Saved,
                $waterSaved
            );
            
            if ($stmt->execute()) {
                $newId = $this->conn->insert_id;
                if (function_exists('appendDataFile')) {
                    appendDataFile('clothesData.txt', [
                        'action' => 'create',
                        'clothingID' => (int)$newId,
                        'sellerID' => (int)$data['sellerID'],
                        'title' => $data['title'],
                        'brand' => $data['brand'],
                        'category' => $data['category'],
                        'size' => $data['size'],
                        'itemCondition' => $data['condition'],
                        'price' => $data['price'],
                        'description' => $data['description'],
                        'imagePath' => $data['imagePath'],
                        'status' => 'pending',
                        'suggestedPrice' => $suggestedPrice,
                        'co2Saved' => $co2Saved,
                        'waterSaved' => $waterSaved,
                        'createdAt' => date('c')
                    ]);
                }

                return ['success' => true, 'message' => 'Your item has been submitted and is pending admin approval.', 'id' => $newId];
            } else {
                return ['success' => false, 'message' => 'Failed to save item.'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all clothing items with optional filters
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Array of clothing items
     */
    public function getAll($filters = [], $limit = 12, $offset = 0) {
        try {
            $sql = "SELECT c.*, u.fullName as sellerName, u.username as sellerUsername 
                    FROM tblClothes c 
                    LEFT JOIN tblUser u ON c.sellerID = u.userID 
                    WHERE 1=1";
            $params = [];
            $types = "";
            
            // Apply filters
            if (!empty($filters['status'])) {
                $sql .= " AND c.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND c.category = ?";
                $params[] = $filters['category'];
                $types .= "s";
            }
            
            if (!empty($filters['brand'])) {
                $sql .= " AND c.brand LIKE ?";
                $params[] = "%" . $filters['brand'] . "%";
                $types .= "s";
            }
            
            if (!empty($filters['size'])) {
                $sql .= " AND c.size = ?";
                $params[] = $filters['size'];
                $types .= "s";
            }
            
            if (!empty($filters['condition'])) {
                $sql .= " AND c.itemCondition = ?";
                $params[] = $filters['condition'];
                $types .= "s";
            }
            
            if (!empty($filters['minPrice'])) {
                $sql .= " AND c.price >= ?";
                $params[] = $filters['minPrice'];
                $types .= "d";
            }
            
            if (!empty($filters['maxPrice'])) {
                $sql .= " AND c.price <= ?";
                $params[] = $filters['maxPrice'];
                $types .= "d";
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (c.title LIKE ? OR c.brand LIKE ? OR c.description LIKE ?)";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "sss";
            }
            
            if (!empty($filters['sellerID'])) {
                $sql .= " AND c.sellerID = ?";
                $params[] = $filters['sellerID'];
                $types .= "i";
            }
            
                // Explicitly select columns and alias itemCondition back to `condition` for compatibility
                $sql = "SELECT c.clothingID, c.sellerID, c.title, c.brand, c.category, c.size, c.itemCondition AS `condition`, c.price, c.description, c.imagePath, c.status, c.suggestedPrice, c.co2Saved, c.waterSaved, c.createdAt, u.fullName as sellerName, u.username as sellerUsername 
                    FROM tblClothes c 
                    LEFT JOIN tblUser u ON c.sellerID = u.userID 
                    WHERE 1=1" . substr($sql, strpos($sql, ' WHERE 1=1') + strlen(' WHERE 1=1')) . " ORDER BY c.createdAt DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = $this->conn->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return $items;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get clothing item by ID
     * @param int $clothingID Clothing ID
     * @return array|null Clothing data or null
     */
    public function getByID($clothingID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT c.clothingID, c.sellerID, c.title, c.brand, c.category, c.size, c.itemCondition AS `condition`, c.price, c.description, c.imagePath, c.status, c.suggestedPrice, c.co2Saved, c.waterSaved, c.createdAt, u.fullName as sellerName, u.username as sellerUsername, u.profilePic as sellerPic 
                 FROM tblClothes c 
                 LEFT JOIN tblUser u ON c.sellerID = u.userID 
                 WHERE c.clothingID = ?"
            );
            $stmt->bind_param("i", $clothingID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Approve clothing item
     * @param int $clothingID Clothing ID
     * @return bool Success
     */
    public function approve($clothingID) {
        try {
            $stmt = $this->conn->prepare("UPDATE tblClothes SET status = 'approved' WHERE clothingID = ?");
            $stmt->bind_param("i", $clothingID);
            $ok = $stmt->execute();
            if ($ok && function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', ['action'=>'update_status','clothingID'=>(int)$clothingID,'status'=>'approved','updatedAt'=>date('c')]);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Reject clothing item
     * @param int $clothingID Clothing ID
     * @return bool Success
     */
    public function reject($clothingID) {
        try {
            $stmt = $this->conn->prepare("UPDATE tblClothes SET status = 'rejected' WHERE clothingID = ?");
            $stmt->bind_param("i", $clothingID);
            $ok = $stmt->execute();
            if ($ok && function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', ['action'=>'update_status','clothingID'=>(int)$clothingID,'status'=>'rejected','updatedAt'=>date('c')]);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Mark item as sold
     * @param int $clothingID Clothing ID
     * @return bool Success
     */
    public function markSold($clothingID) {
        try {
            $stmt = $this->conn->prepare("UPDATE tblClothes SET status = 'sold' WHERE clothingID = ?");
            $stmt->bind_param("i", $clothingID);
            $ok = $stmt->execute();
            if ($ok && function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', ['action'=>'update_status','clothingID'=>(int)$clothingID,'status'=>'sold','updatedAt'=>date('c')]);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete clothing item
     * @param int $clothingID Clothing ID
     * @return bool Success
     */
    public function delete($clothingID) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM tblClothes WHERE clothingID = ?");
            $stmt->bind_param("i", $clothingID);
            $ok = $stmt->execute();
            if ($ok && function_exists('appendDataFile')) {
                appendDataFile('clothesData.txt', ['action'=>'delete','clothingID'=>(int)$clothingID,'deletedAt'=>date('c')]);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get similar items
     * @param int $clothingID Current item ID
     * @param string $category Category to match
     * @param int $limit Number of items
     * @return array Array of similar items
     */
    public function getSimilar($clothingID, $category, $limit = 4) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT clothingID, sellerID, title, brand, category, size, itemCondition AS `condition`, price, description, imagePath, status, suggestedPrice, co2Saved, waterSaved, createdAt FROM tblClothes 
                 WHERE category = ? AND clothingID != ? AND status = 'approved' 
                 ORDER BY RAND() LIMIT ?"
            );
            $stmt->bind_param("sii", $category, $clothingID, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return $items;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Count items by status
     * @param string $status Status to count
     * @return int Count
     */
    public function countByStatus($status = null) {
        try {
            if ($status) {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM tblClothes WHERE status = ?");
                $stmt->bind_param("s", $status);
            } else {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM tblClothes");
            }
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get items by seller
     * @param int $sellerID Seller ID
     * @return array Array of items
     */
    public function getBySeller($sellerID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT clothingID, sellerID, title, brand, category, size, itemCondition AS `condition`, price, description, imagePath, status, suggestedPrice, co2Saved, waterSaved, createdAt FROM tblClothes WHERE sellerID = ? ORDER BY createdAt DESC"
            );
            $stmt->bind_param("i", $sellerID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return $items;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
