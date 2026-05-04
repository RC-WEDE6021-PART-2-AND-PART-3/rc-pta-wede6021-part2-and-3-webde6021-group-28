<?php
/**
 * scripts/import_sql.php
 * Imports database/ClothingStore.sql into MySQL using mysqli multi_query.
 * Run via browser: http://localhost/Pastimes/scripts/import_sql.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$pass = '';
$sqlFile = __DIR__ . '/../database/ClothingStore.sql';

?><!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Import ClothingStore.sql</title>
<style>body{font-family:Arial,Helvetica,sans-serif;margin:2rem;background:#f7fafc;color:#111}pre{background:#fff;padding:1rem;border:1px solid #e6e6e6;border-radius:6px}</style>
</head><body>
<h1>Import ClothingStore.sql</h1>
<?php
if (!file_exists($sqlFile)) {
    echo "<p style='color:red;'>SQL file not found: $sqlFile</p>";
    echo "<p>Make sure the file exists at <strong>database/ClothingStore.sql</strong>.</p>";
    exit;
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "<p style='color:red;'>Failed to read SQL file.</p>";
    exit;
}

// Provide a mysqli object and annotate for static analysis
$mysqli = new mysqli($host, $user, $pass);
/** @var mysqli $mysqli */
if ($mysqli->connect_error) {
    echo "<p style='color:red;'>Connection failed: " . htmlspecialchars($mysqli->connect_error) . "</p>";
    exit;
}

$mysqli->set_charset('utf8mb4');

echo "<p>Starting import (this may take a few seconds)...</p>";

if ($mysqli->multi_query($sql)) {
    $step = 0;
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
        $step++;
    } while ($mysqli->more_results() && $mysqli->next_result());

    if ($mysqli->errno) {
        echo "<p style='color:red;'>Import finished with warnings/errors: " . htmlspecialchars($mysqli->error) . "</p>";
    } else {
        echo "<p style='color:green;'>Import completed successfully.</p>";
    }
} else {
    echo "<p style='color:red;'>Import failed: " . htmlspecialchars($mysqli->error) . "</p>";
}

$mysqli->close();

echo '<p><a href="../scripts/verify_connection.php">Run verification</a> — <a href="../scripts/createTable.php">Run createTable.php</a></p>';
?>
</body></html>
