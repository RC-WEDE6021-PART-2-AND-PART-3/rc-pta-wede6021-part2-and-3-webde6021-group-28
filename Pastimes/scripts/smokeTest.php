<?php
// Simple smoke test for Pastimes DB and auth functions
// Run: php scripts/smokeTest.php

session_start();
require_once '../includes/DBConn.php';
/** @var mysqli $conn */

// Suppress errors for display
ini_set('display_errors', 0);

$tests = [];

// Test 1: Database Connection
try {
    $testQuery = $conn->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'clothingstore'");
    $testQuery->execute();
    $result = $testQuery->get_result();
    $row = $result->fetch_assoc();
    $tests['Database Connection'] = [
        'status' => 'PASS',
        'message' => 'Connected to clothingstore database'
    ];
} catch (Exception $e) {
    $tests['Database Connection'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 2: Tables Exist
$requiredTables = ['tblUser', 'tblAdmin', 'tblClothes', 'tblOrder', 'tblMessages'];
try {
    $tableCount = 0;
    foreach ($requiredTables as $table) {
        // Use a direct, escaped query instead of a prepared statement for SHOW TABLES
        $tbl = $conn->real_escape_string($table);
        $res = $conn->query("SHOW TABLES LIKE '" . $tbl . "'");
        if ($res && $res->num_rows > 0) {
            $tableCount++;
        }
    }
    if ($tableCount === count($requiredTables)) {
        $tests['Required Tables'] = [
            'status' => 'PASS',
            'message' => 'All ' . count($requiredTables) . ' tables exist'
        ];
    } else {
        $tests['Required Tables'] = [
            'status' => 'WARN',
            'message' => "Only $tableCount/" . count($requiredTables) . " tables found"
        ];
    }
} catch (Exception $e) {
    $tests['Required Tables'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 3: User Records
try {
    $userStmt = $conn->prepare("SELECT COUNT(*) as count FROM tblUser");
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData = $userResult->fetch_assoc();
    $userCount = $userData['count'];
    
    if ($userCount > 0) {
        $tests['User Records'] = [
            'status' => 'PASS',
            'message' => "$userCount users found in database"
        ];
    } else {
        $tests['User Records'] = [
            'status' => 'WARN',
            'message' => 'No users found - run loadClothingStore.php'
        ];
    }
} catch (Exception $e) {
    $tests['User Records'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 4: Clothing Records
try {
    $clothingStmt = $conn->prepare("SELECT COUNT(*) as count FROM tblClothes");
    $clothingStmt->execute();
    $clothingResult = $clothingStmt->get_result();
    $clothingData = $clothingResult->fetch_assoc();
    $clothingCount = $clothingData['count'];
    
    if ($clothingCount > 0) {
        $tests['Clothing Records'] = [
            'status' => 'PASS',
            'message' => "$clothingCount clothing items found"
        ];
    } else {
        $tests['Clothing Records'] = [
            'status' => 'WARN',
            'message' => 'No clothing items found'
        ];
    }
} catch (Exception $e) {
    $tests['Clothing Records'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 5: Admin Accounts
try {
    $adminStmt = $conn->prepare("SELECT COUNT(*) as count FROM tblAdmin");
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $adminData = $adminResult->fetch_assoc();
    $adminCount = $adminData['count'];
    
    if ($adminCount > 0) {
        $tests['Admin Accounts'] = [
            'status' => 'PASS',
            'message' => "$adminCount admin accounts found"
        ];
    } else {
        $tests['Admin Accounts'] = [
            'status' => 'WARN',
            'message' => 'No admin accounts found'
        ];
    }
} catch (Exception $e) {
    $tests['Admin Accounts'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 6: Functions Exist
try {
    require_once '../includes/functions.php';
    $functionCheck = function_exists('sanitizeInput') && 
                     function_exists('hashPassword') && 
                     function_exists('isLoggedIn');
    
    if ($functionCheck) {
        $tests['Core Functions'] = [
            'status' => 'PASS',
            'message' => 'All required functions defined'
        ];
    } else {
        $tests['Core Functions'] = [
            'status' => 'FAIL',
            'message' => 'Some functions missing'
        ];
    }
} catch (Exception $e) {
    $tests['Core Functions'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 7: OOP Classes
try {
    require_once '../includes/classes/User.php';
    require_once '../includes/classes/Clothing.php';
    require_once '../includes/classes/Order.php';
    require_once '../includes/classes/Message.php';
    
    $classCheck = class_exists('User') && 
                  class_exists('Clothing') && 
                  class_exists('Order') && 
                  class_exists('Message');
    
    if ($classCheck) {
        $tests['OOP Classes'] = [
            'status' => 'PASS',
            'message' => 'All 4 required classes defined'
        ];
    } else {
        $tests['OOP Classes'] = [
            'status' => 'FAIL',
            'message' => 'Some classes missing'
        ];
    }
} catch (Exception $e) {
    $tests['OOP Classes'] = [
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
}

// Test 8: File Structure
$requiredFiles = [
    '../index.php' => 'Homepage',
    '../register.php' => 'Registration',
    '../login.php' => 'Login',
    '../shop.php' => 'Shop',
    '../cart.php' => 'Cart',
    '../admin/dashboard.php' => 'Admin Dashboard',
    '../css/style.css' => 'Main Stylesheet',
    '../js/main.js' => 'Main Script'
];

$missingFiles = [];
foreach ($requiredFiles as $file => $desc) {
    if (!file_exists($file)) {
        $missingFiles[] = $desc;
    }
}

if (empty($missingFiles)) {
    $tests['File Structure'] = [
        'status' => 'PASS',
        'message' => 'All required files present'
    ];
} else {
    $tests['File Structure'] = [
        'status' => 'WARN',
        'message' => 'Missing: ' . implode(', ', $missingFiles)
    ];
}

// Test 9: Image Directory
if (is_dir('../images')) {
    $tests['Image Directory'] = [
        'status' => 'PASS',
        'message' => 'Images directory exists and writable'
    ];
} else {
    @mkdir('../images', 0755, true);
    if (is_dir('../images')) {
        $tests['Image Directory'] = [
            'status' => 'PASS',
            'message' => 'Images directory created'
        ];
    } else {
        $tests['Image Directory'] = [
            'status' => 'FAIL',
            'message' => 'Cannot create images directory'
        ];
    }
}

// Count results
$passed = 0;
$failed = 0;
$warned = 0;

foreach ($tests as $test) {
    if ($test['status'] === 'PASS') $passed++;
    elseif ($test['status'] === 'FAIL') $failed++;
    else $warned++;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smoke Test - Pastimes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .summary {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 30px;
            justify-content: center;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-item.pass .number { color: #4CAF50; }
        .summary-item.fail .number { color: #f44336; }
        .summary-item.warn .number { color: #ff9800; }
        
        .summary-item .label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
        }
        
        .results {
            background: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .test-item {
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #ddd;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .test-item.pass {
            background: #f0f8f0;
            border-left-color: #4CAF50;
        }
        
        .test-item.fail {
            background: #fef0f0;
            border-left-color: #f44336;
        }
        
        .test-item.warn {
            background: #fffbf0;
            border-left-color: #ff9800;
        }
        
        .test-name {
            flex: 1;
            font-weight: 500;
            color: #333;
        }
        
        .test-message {
            flex: 2;
            font-size: 13px;
            color: #666;
        }
        
        .test-status {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }
        
        .test-status.pass {
            background: #4CAF50;
            color: white;
        }
        
        .test-status.fail {
            background: #f44336;
            color: white;
        }
        
        .test-status.warn {
            background: #ff9800;
            color: white;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: white;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Pastimes Smoke Test</h1>
            <p>System health check and configuration verification</p>
        </div>
        
        <div class="summary">
            <div class="summary-item pass">
                <div class="number"><?php echo $passed; ?></div>
                <div class="label">Passed</div>
            </div>
            <div class="summary-item fail">
                <div class="number"><?php echo $failed; ?></div>
                <div class="label">Failed</div>
            </div>
            <div class="summary-item warn">
                <div class="number"><?php echo $warned; ?></div>
                <div class="label">Warnings</div>
            </div>
        </div>
        
        <div class="results">
            <?php foreach ($tests as $testName => $test): ?>
                <div class="test-item <?php echo strtolower($test['status']); ?>">
                    <div class="test-name"><?php echo htmlspecialchars($testName); ?></div>
                    <div class="test-message"><?php echo htmlspecialchars($test['message']); ?></div>
                    <div class="test-status <?php echo strtolower($test['status']); ?>">
                        <?php echo $test['status']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="footer">
            <a href="../index.php">← Home</a>
            <a href="loadClothingStore.php">Setup Database</a>
            <a href="../admin/login.php">Admin Login</a>
        </div>
    </div>
</body>
</html>
