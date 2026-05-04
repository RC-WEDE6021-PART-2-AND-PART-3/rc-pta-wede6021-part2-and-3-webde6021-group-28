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

session_start();

// Suppress errors for clean output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Verification - Pastimes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .status-group {
            margin-bottom: 30px;
        }

        .status-group h2 {
            color: #667eea;
            font-size: 16px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-left: 4px solid #ddd;
            border-radius: 4px;
        }

        .status-item.success {
            background: #f0fdf4;
            border-left-color: #22c55e;
        }

        .status-item.error {
            background: #fef2f2;
            border-left-color: #ef4444;
        }

        .status-item.warning {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }

        .status-icon {
            font-size: 20px;
            margin-right: 12px;
            min-width: 24px;
        }

        .status-content {
            flex: 1;
        }

        .status-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .status-detail {
            font-size: 13px;
            color: #666;
        }

        .connection-info {
            background: #f0f4ff;
            border: 1px solid #d4dff0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .connection-info h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e7ff;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #333;
        }

        .info-value {
            color: #667eea;
            font-family: 'Courier New', monospace;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .table-card {
            background: white;
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .table-card.exists {
            background: #f0fdf4;
            border-color: #22c55e;
        }

        .table-card.missing {
            background: #fef2f2;
            border-color: #ef4444;
        }

        .table-name {
            font-weight: 600;
            color: #333;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .table-count {
            font-size: 12px;
            color: #666;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            color: #999;
            font-size: 12px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #667eea;
        }

        .btn-secondary:hover {
            background: #c7d2fe;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Database Connection Verification</h1>
        <p class="subtitle">Pastimes E-Commerce Platform - Connection Status</p>

        <?php
        // Test 1: PHP Version and Extensions
        echo '<div class="status-group">';
        echo '<h2>System Information</h2>';
        
        $php_version = phpversion();
        echo '<div class="status-item success">';
        echo '<div class="status-icon">✓</div>';
        echo '<div class="status-content">';
        echo '<div class="status-label">PHP Version</div>';
        echo '<div class="status-detail">' . $php_version . '</div>';
        echo '</div></div>';

        $mysqli_loaded = extension_loaded('mysqli');
        $status_class = $mysqli_loaded ? 'success' : 'error';
        $status_icon = $mysqli_loaded ? '✓' : '✗';
        $mysqli_status = $mysqli_loaded ? 'Loaded' : 'NOT LOADED';
        
        echo '<div class="status-item ' . $status_class . '">';
        echo '<div class="status-icon">' . $status_icon . '</div>';
        echo '<div class="status-content">';
        echo '<div class="status-label">MySQLi Extension</div>';
        echo '<div class="status-detail">' . $mysqli_status . '</div>';
        echo '</div></div>';
        echo '</div>';

        // Test 2: MySQL Connection
        echo '<div class="status-group">';
        echo '<h2>MySQL Connection</h2>';

        $conn = @new mysqli('localhost', 'root', '', 'clothingstore');
        
        if ($conn->connect_error) {
            echo '<div class="status-item error">';
            echo '<div class="status-icon">✗</div>';
            echo '<div class="status-content">';
            echo '<div class="status-label">Connection Failed</div>';
            echo '<div class="status-detail">' . $conn->connect_error . ' (Error: ' . $conn->connect_errno . ')</div>';
            echo '</div></div>';
            echo '<div class="connection-info">';
            echo '<h3>Troubleshooting</h3>';
            echo '<p style="font-size: 13px; color: #666;">1. Start MySQL from XAMPP Control Panel<br>';
            echo '2. Wait 5-10 seconds for MySQL to start<br>';
            echo '3. Verify the hostname, username, password, and database name<br>';
            echo '4. Check if port 3306 is not in use</p>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="status-item success">';
            echo '<div class="status-icon">✓</div>';
            echo '<div class="status-content">';
            echo '<div class="status-label">Connection Successful</div>';
            echo '<div class="status-detail">Successfully connected to MySQL Database</div>';
            echo '</div></div>';

            echo '<div class="connection-info">';
            echo '<h3>Connection Details</h3>';
            echo '<div class="info-row">';
            echo '<span class="info-label">Host:</span>';
            echo '<span class="info-value">localhost</span>';
            echo '</div>';
            echo '<div class="info-row">';
            echo '<span class="info-label">Port:</span>';
            echo '<span class="info-value">3306</span>';
            echo '</div>';
            echo '<div class="info-row">';
            echo '<span class="info-label">Username:</span>';
            echo '<span class="info-value">root</span>';
            echo '</div>';
            echo '<div class="info-row">';
            echo '<span class="info-label">Database:</span>';
            echo '<span class="info-value">clothingstore</span>';
            echo '</div>';
            echo '<div class="info-row">';
            echo '<span class="info-label">Server Version:</span>';
            echo '<span class="info-value">' . $conn->server_info . '</span>';
            echo '</div>';
            echo '</div>';

            // Test 3: Database Tables
            echo '<div class="status-group">';
            echo '<h2>Database Tables</h2>';

            $tables = ['tblUser', 'tblAdmin', 'tblClothes', 'tblOrder', 'tblMessages'];
            $result = $conn->query("SHOW TABLES");
            $existing_tables = [];

            if ($result) {
                while ($row = $result->fetch_row()) {
                    $existing_tables[] = $row[0];
                }
            }

            $found_count = count($existing_tables);
            $expected_count = count($tables);

            echo '<div class="status-item ' . ($found_count == $expected_count ? 'success' : 'warning') . '">';
            echo '<div class="status-icon">' . ($found_count == $expected_count ? '✓' : '⚠') . '</div>';
            echo '<div class="status-content">';
            echo '<div class="status-label">Tables Found: ' . $found_count . ' / ' . $expected_count . '</div>';
            echo '<div class="status-detail">' . ($found_count == $expected_count ? 'All required tables present' : 'Some tables are missing') . '</div>';
            echo '</div></div>';

            echo '<div class="tables-grid">';
            foreach ($tables as $table) {
                $exists = in_array($table, $existing_tables);
                $card_class = $exists ? 'exists' : 'missing';
                $icon = $exists ? '✓' : '✗';

                echo '<div>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';

            // Test 4: Sample Data Query
            echo '<div class="status-group">';
            echo '<h2>Sample Data</h2>';

            $user_count = $conn->query("SELECT COUNT(*) as cnt FROM tblUser")->fetch_assoc()['cnt'];
            $product_count = $conn->query("SELECT COUNT(*) as cnt FROM tblClothes")->fetch_assoc()['cnt'];
            $order_count = $conn->query("SELECT COUNT(*) as cnt FROM tblOrder")->fetch_assoc()['cnt'];

            echo '<div class="status-item success">';
            echo '<div class="status-icon">📊</div>';
            echo '<div class="status-content">';
            echo '<div class="status-label">Users in Database: ' . $user_count . '</div>';
            echo '<div class="status-detail">Registered user accounts</div>';
            echo '</div></div>';

            echo '<div class="status-item success">';
            echo '<div class="status-icon">👕</div>';
            echo '<div class="status-content">';
            echo '<div class="status-label">Products Listed: ' . $product_count . '</div>';
            echo '<div class="status-detail">Clothing items in catalog</div>';
            echo '</div></div>';

            echo '<div class="status-item success">';
            echo '<div class="status-icon">📦</div>';
            echo '<div class="status-content">';
            echo '<div class="status-label">Orders Placed: ' . $order_count . '</div>';
            echo '<div class="status-detail">Total transactions</div>';
            echo '</div></div>';
            echo '</div>';

            $conn->close();
        }
        ?>

        <div class="action-buttons">
            <a href="../scripts/smokeTest.php" class="btn btn-primary">Run Full Diagnostic</a>
            <a href="http://localhost/phpmyadmin/" class="btn btn-secondary">Open phpMyAdmin</a>
        </div>

        <div class="footer">
            <p>Last checked: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>For more information, see DATABASE_CONNECTION_GUIDE.md</p>
        </div>
    </div>
</body>
</html>
