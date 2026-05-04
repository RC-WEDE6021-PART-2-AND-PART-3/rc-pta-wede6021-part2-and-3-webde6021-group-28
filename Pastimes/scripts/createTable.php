<?php
/**
 * scripts/createTable.php
 * Creates all Pastimes database tables (without seed data).
 * Run once via browser: http://localhost/pastimes/scripts/createTable.php
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
// Ensure $conn is defined for static analysis and runtime fallback
if (!isset($conn) || !($conn instanceof mysqli)) {
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_NAME')) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<p style="color:red;">Database connection failed: '.htmlspecialchars($conn->connect_error).'</p>');
        }
        $conn->set_charset('utf8mb4');
    } else {
        die('<p style="color:red;">Database configuration missing. Check includes/DBConn.php</p>');
    }
}

echo "<h2>Creating tblUser...</h2>";

try {
    // Temporarily disable foreign key checks to avoid drop/create conflicts
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Drop if exists
    $conn->query("DROP TABLE IF EXISTS tblUser");
    
    // Create table
    $createSQL = "CREATE TABLE tblUser (
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
    )";
    
    $conn->query($createSQL);
    echo "<p style='color: green;'>✓ tblUser created successfully</p>";
    
    // Read and load data
    $file = '../database/userData.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        
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
                
                if ($stmt->execute()) {
                    $count++;
                    echo "<p style='color: green;'>✓ Inserted: $fullName</p>";
                } else {
                    echo "<p style='color: red;'>✗ Error inserting $fullName: " . $stmt->error . "</p>";
                }
                $stmt->close();
            }
        }
        echo "<p><strong>Total records inserted: $count</strong></p>";
        // Re-enable foreign key checks now that schema changes and seed inserts are complete
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    } else {
        echo "<p style='color: red;'>userData.txt file not found</p>";
    }
} catch (Exception $e) {
    // Ensure FK checks are re-enabled on error
    if ($conn instanceof mysqli) {
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    }
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f5f5f5; }
        h2 { color: #333; }
        p { margin: 0.5rem 0; }
    </style>
</head>
<body>
    <h1>Pastimes Database Setup</h1>
    <?php echo "<!-- Setup complete -->"; ?>
</body>
</html>
