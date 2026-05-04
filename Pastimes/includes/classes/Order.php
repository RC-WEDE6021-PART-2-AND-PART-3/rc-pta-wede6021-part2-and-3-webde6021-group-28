<?php
/**
 * Order.php — OOP Order Class
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

class Order {
    // Properties
    public $orderID;
    public $buyerID;
    public $clothingID;
    public $deliveryName;
    public $deliveryAddress;
    public $deliveryCity;
    public $postalCode;
    public $deliveryType;
    public $totalAmount;
    public $serviceFee;
    public $status;
    public $orderDate;
    
    private $conn;
    
    /**
     * Constructor
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create new order
     * @param array $data Order data
     * @return array Result with success status and message
     */
    public function create($data) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO tblOrder (buyerID, clothingID, deliveryName, deliveryAddress, deliveryCity, postalCode, deliveryType, totalAmount, serviceFee, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            
            $serviceFee = isset($data['serviceFee']) ? $data['serviceFee'] : 15.00;
            
            $stmt->bind_param("iisssssdd", 
                $data['buyerID'], 
                $data['clothingID'], 
                $data['deliveryName'], 
                $data['deliveryAddress'], 
                $data['deliveryCity'], 
                $data['postalCode'], 
                $data['deliveryType'], 
                $data['totalAmount'],
                $serviceFee
            );
            
            if ($stmt->execute()) {
                $newId = $this->conn->insert_id;
                if (function_exists('appendDataFile')) {
                    appendDataFile('ordersData.txt', [
                        'action' => 'create',
                        'orderID' => (int)$newId,
                        'buyerID' => (int)$data['buyerID'],
                        'clothingID' => (int)$data['clothingID'],
                        'deliveryName' => $data['deliveryName'],
                        'deliveryAddress' => $data['deliveryAddress'],
                        'deliveryCity' => $data['deliveryCity'],
                        'postalCode' => $data['postalCode'],
                        'deliveryType' => $data['deliveryType'],
                        'totalAmount' => (float)$data['totalAmount'],
                        'serviceFee' => (float)$serviceFee,
                        'status' => 'pending',
                        'createdAt' => date('c')
                    ]);
                }

                return ['success' => true, 'message' => 'Order placed successfully!', 'orderID' => $newId];
            } else {
                return ['success' => false, 'message' => 'Failed to place order.'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get orders by buyer
     * @param int $buyerID Buyer ID
     * @return array Array of orders
     */
    public function getByBuyer($buyerID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT o.*, c.title, c.imagePath, c.brand, c.size, c.price as itemPrice 
                 FROM tblOrder o 
                 JOIN tblClothes c ON o.clothingID = c.clothingID 
                 WHERE o.buyerID = ? 
                 ORDER BY o.orderDate DESC"
            );
            $stmt->bind_param("i", $buyerID);
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
     * Update order status
     * @param int $orderID Order ID
     * @param string $status New status
     * @return bool Success
     */
    public function updateStatus($orderID, $status) {
        try {
            $stmt = $this->conn->prepare("UPDATE tblOrder SET status = ? WHERE orderID = ?");
            $stmt->bind_param("si", $status, $orderID);
            $ok = $stmt->execute();
            if ($ok && function_exists('appendDataFile')) {
                appendDataFile('ordersData.txt', ['action'=>'update_status','orderID'=>(int)$orderID,'status'=>$status,'updatedAt'=>date('c')]);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all orders with optional filters
     * @param array $filters Optional filters
     * @return array Array of orders
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT o.*, c.title, c.imagePath, c.brand, u.fullName as buyerName 
                    FROM tblOrder o 
                    JOIN tblClothes c ON o.clothingID = c.clothingID 
                    JOIN tblUser u ON o.buyerID = u.userID 
                    WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($filters['status'])) {
                $sql .= " AND o.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (!empty($filters['dateFrom'])) {
                $sql .= " AND DATE(o.orderDate) >= ?";
                $params[] = $filters['dateFrom'];
                $types .= "s";
            }
            
            if (!empty($filters['dateTo'])) {
                $sql .= " AND DATE(o.orderDate) <= ?";
                $params[] = $filters['dateTo'];
                $types .= "s";
            }
            
            $sql .= " ORDER BY o.orderDate DESC";
            
            $stmt = $this->conn->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
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
     * Get order by ID
     * @param int $orderID Order ID
     * @return array|null Order data or null
     */
    public function getByID($orderID) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT o.*, c.title, c.imagePath, c.brand, c.size, c.co2Saved, c.waterSaved, u.fullName as buyerName 
                 FROM tblOrder o 
                 JOIN tblClothes c ON o.clothingID = c.clothingID 
                 JOIN tblUser u ON o.buyerID = u.userID 
                 WHERE o.orderID = ?"
            );
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Count orders by status
     * @param string $status Status to count
     * @return int Count
     */
    public function countByStatus($status = null) {
        try {
            if ($status) {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM tblOrder WHERE status = ?");
                $stmt->bind_param("s", $status);
            } else {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM tblOrder");
            }
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count orders for today
     * @return int Count
     */
    public function countToday() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM tblOrder WHERE DATE(orderDate) = CURDATE()");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get orders grouped by status for charts
     * @return array Status counts
     */
    public function getStatusCounts() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT status, COUNT(*) as count FROM tblOrder GROUP BY status"
            );
            $stmt->execute();
            $result = $stmt->get_result();
            
            $counts = [];
            while ($row = $result->fetch_assoc()) {
                $counts[$row['status']] = $row['count'];
            }
            
            return $counts;
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
