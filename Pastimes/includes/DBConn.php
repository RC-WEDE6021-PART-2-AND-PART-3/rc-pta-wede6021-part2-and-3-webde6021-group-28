<?php
/**
 * DBConn.php — Database Connection
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 *
 * Provides a MySQLi object-oriented connection to the ClothingStore database.
 * Include this file in every PHP page using require_once.
 */

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'clothingstore');

try {
    // Create MySQLi connection (OOP approach)
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    // Ensure required tables exist; if missing, attempt to import the provided SQL file.
    try {
        $check = $conn->query("SHOW TABLES LIKE 'tblClothes'");
        if ($check === false || $check->num_rows === 0) {
            $sqlFile = __DIR__ . '/../database/ClothingStore.sql';
            if (file_exists($sqlFile) && is_readable($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if ($sql !== false) {
                    // Attempt to import using multi_query
                    if ($conn->multi_query($sql)) {
                        // flush all results
                        do {
                            if ($res = $conn->store_result()) {
                                $res->free();
                            }
                        } while ($conn->more_results() && $conn->next_result());
                    }
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        // If automatic import fails, continue — queries later will show clear guidance.
    }
} catch (mysqli_sql_exception $e) {
    // If DB is missing (MySQL error 1049), attempt to import the SQL file by connecting without a default database.
    if ($e->getCode() === 1049) {
        $sqlFile = __DIR__ . '/../database/ClothingStore.sql';
        if (file_exists($sqlFile) && is_readable($sqlFile)) {
            try {
                $tmp = new mysqli(DB_HOST, DB_USER, DB_PASS);
                $tmp->set_charset('utf8mb4');
                $sql = file_get_contents($sqlFile);
                if ($sql !== false && $tmp->multi_query($sql)) {
                    do {
                        if ($res = $tmp->store_result()) { $res->free(); }
                    } while ($tmp->more_results() && $tmp->next_result());
                }
                $tmp->close();

                // Try reconnecting to the newly created database
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $conn->set_charset('utf8mb4');
                // success — $conn available for includes
                return;
            } catch (mysqli_sql_exception $impEx) {
                // fall through to show original error below
                $e = $impEx;
            }
        }
    }

    // Fallback: show a helpful error message
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background: #fee; border: 1px solid #c00; border-radius: 5px; margin: 20px;'>
        <h3 style='color: #c00; margin: 0 0 10px 0;'>Database Connection Error</h3>
        <p>Unable to connect to the database. Please ensure:</p>
        <ul>
            <li>XAMPP Apache and MySQL services are running</li>
            <li>The database &#8216;clothingstore&#8217; exists or the SQL file is present at <code>database/ClothingStore.sql</code></li>
            <li>Database credentials are correct</li>
        </ul>
        <p><small>Error: " . htmlspecialchars($e->getMessage()) . "</small></p>
    </div>");
}
?>
