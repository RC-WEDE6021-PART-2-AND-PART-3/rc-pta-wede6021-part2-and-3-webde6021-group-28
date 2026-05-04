<?php
/**
 * scripts/loadClothingStore.php
 * Runs the full ClothingStore.sql file: drops, creates, and seeds all tables.
 * Run ONCE via browser: http://localhost/pastimes/scripts/loadClothingStore.php
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

require_once '../includes/DBConn.php';
/** @var mysqli $conn */

echo "<h1>Pastimes Database Setup</h1>";
echo "<p>Loading ClothingStore database...</p>";

try {
    // Drop all tables in correct FK order
    $conn->query("DROP TABLE IF EXISTS tblMessages");
    $conn->query("DROP TABLE IF EXISTS tblOrder");
    $conn->query("DROP TABLE IF EXISTS tblClothes");
    $conn->query("DROP TABLE IF EXISTS tblUser");
    $conn->query("DROP TABLE IF EXISTS tblAdmin");
    
    echo "<h2>✓ Dropped existing tables</h2>";

    // Create tblAdmin
    $conn->query("CREATE TABLE tblAdmin (
        adminID INT AUTO_INCREMENT PRIMARY KEY,
        fullName VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        passwordHash VARCHAR(255) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create tblUser
    $conn->query("CREATE TABLE tblUser (
        userID INT AUTO_INCREMENT PRIMARY KEY,
        fullName VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        passwordHash VARCHAR(255) NOT NULL,
        role ENUM('buyer','seller','both') DEFAULT 'buyer',
        status ENUM('pending','active','suspended') DEFAULT 'pending',
        address TEXT,
        city VARCHAR(100),
        postalCode VARCHAR(20),
        phone VARCHAR(20),
        profilePic VARCHAR(255) DEFAULT 'images/default-avatar.png',
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create tblClothes
    $conn->query("CREATE TABLE tblClothes (
        clothingID INT AUTO_INCREMENT PRIMARY KEY,
        sellerID INT NOT NULL,
        title VARCHAR(150) NOT NULL,
        brand VARCHAR(100),
        category ENUM('tops','bottoms','dresses','outerwear','footwear','accessories','activewear'),
        size VARCHAR(20),
        itemCondition ENUM('like new','good','fair') NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        description TEXT,
        imagePath VARCHAR(255) DEFAULT 'images/default-clothing.jpg',
        status ENUM('pending','approved','sold','rejected') DEFAULT 'pending',
        suggestedPrice DECIMAL(10,2),
        co2Saved DECIMAL(5,2) DEFAULT 3.00,
        waterSaved INT DEFAULT 2700,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sellerID) REFERENCES tblUser(userID) ON DELETE CASCADE
    )");

    // Create tblOrder
    $conn->query("CREATE TABLE tblOrder (
        orderID INT AUTO_INCREMENT PRIMARY KEY,
        buyerID INT NOT NULL,
        clothingID INT NOT NULL,
        deliveryName VARCHAR(100) NOT NULL,
        deliveryAddress TEXT NOT NULL,
        deliveryCity VARCHAR(100),
        postalCode VARCHAR(20),
        deliveryType ENUM('residential','work') DEFAULT 'residential',
        totalAmount DECIMAL(10,2) NOT NULL,
        serviceFee DECIMAL(10,2) DEFAULT 15.00,
        status ENUM('pending','dispatched','delivered','cancelled') DEFAULT 'pending',
        orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (buyerID) REFERENCES tblUser(userID) ON DELETE CASCADE,
        FOREIGN KEY (clothingID) REFERENCES tblClothes(clothingID) ON DELETE CASCADE
    )");

    // Create tblMessages
    $conn->query("CREATE TABLE tblMessages (
        messageID INT AUTO_INCREMENT PRIMARY KEY,
        senderID INT NOT NULL,
        receiverID INT NOT NULL,
        clothingID INT,
        subject VARCHAR(200),
        messageBody TEXT NOT NULL,
        isRead TINYINT(1) DEFAULT 0,
        sentAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (senderID) REFERENCES tblUser(userID) ON DELETE CASCADE,
        FOREIGN KEY (receiverID) REFERENCES tblUser(userID) ON DELETE CASCADE
    )");

    echo "<h2>✓ Created all tables</h2>";

    // Load admin data
    $file = '../database/adminData.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $data = explode("\t", $line);
            if (count($data) >= 4) {
                $fullName = trim($data[0]);
                $email = trim($data[1]);
                $username = trim($data[2]);
                $passwordHash = trim($data[3]);
                
                $stmt = $conn->prepare("INSERT INTO tblAdmin (fullName, email, username, passwordHash) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssss', $fullName, $email, $username, $passwordHash);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "<p>✓ Loaded admin data</p>";
    }

    // Load user data
    $file = '../database/userData.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $data = explode("\t", $line);
            if (count($data) >= 5) {
                $fullName = trim($data[0]);
                $email = trim($data[1]);
                $username = trim($data[2]);
                $passwordHash = trim($data[3]);
                $role = trim($data[4] ?? 'buyer');
                
                $stmt = $conn->prepare("INSERT INTO tblUser (fullName, email, username, passwordHash, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->bind_param('sssss', $fullName, $email, $username, $passwordHash, $role);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "<p>✓ Loaded user data</p>";
    }

    // Load clothing data
    $file = '../database/clothesData.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $data = explode("\t", $line);
            if (count($data) >= 9) {
                $sellerID = (int)trim($data[0]);
                $title = trim($data[1]);
                $brand = trim($data[2]);
                $category = trim($data[3]);
                $size = trim($data[4]);
                $condition = trim($data[5]);
                $price = (float)trim($data[6]);
                $description = trim($data[7]);
                $status = trim($data[8] ?? 'approved');
                
                $stmt = $conn->prepare("INSERT INTO tblClothes (sellerID, title, brand, category, size, itemCondition, price, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssssdss', $sellerID, $title, $brand, $category, $size, $condition, $price, $description, $status);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "<p>✓ Loaded clothing data</p>";
    }

    // Load order data
    $file = '../database/ordersData.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $data = explode("\t", $line);
            if (count($data) >= 9) {
                $buyerID = (int)trim($data[0]);
                $clothingID = (int)trim($data[1]);
                $deliveryName = trim($data[2]);
                $deliveryAddress = trim($data[3]);
                $deliveryCity = trim($data[4]);
                $postalCode = trim($data[5]);
                $deliveryType = trim($data[6] ?? 'residential');
                $totalAmount = (float)trim($data[7]);
                $serviceFee = (float)trim($data[8] ?? '15.00');
                
                $stmt = $conn->prepare("INSERT INTO tblOrder (buyerID, clothingID, deliveryName, deliveryAddress, deliveryCity, postalCode, deliveryType, totalAmount, serviceFee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('iisssssdd', $buyerID, $clothingID, $deliveryName, $deliveryAddress, $deliveryCity, $postalCode, $deliveryType, $totalAmount, $serviceFee);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "<p>✓ Loaded order data</p>";
    }

    echo "<h2 style='color: green;'>✓ Database setup complete!</h2>";
    echo "<p><a href='../index.php'>← Return to Pastimes</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f5f5f5; }
        h1, h2 { color: #333; }
        p { margin: 0.5rem 0; }
        a { color: #1e40af; }
    </style>
</head>
<body>
</body>
</html>
