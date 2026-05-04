<?php
// import_images.php
// Move image files from 'assets_to_import' into 'images/design' and normalize names.
// Usage: php scripts/import_images.php

require_once '../includes/DBConn.php';
/** @var mysqli $conn */

$imageDir = '../images/';

if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Default images - placeholder URLs mapping
$defaultImages = [
    'default-avatar.png' => 'https://via.placeholder.com/150?text=Avatar',
    'default-clothing.jpg' => 'https://via.placeholder.com/400?text=Clothing',
    'hero-bg.jpg' => 'https://via.placeholder.com/1920x600?text=Hero',
    'empty-cart.svg' => 'https://via.placeholder.com/400?text=Empty+Cart'
];

$results = [];
foreach ($defaultImages as $filename => $placeholder) {
    $filepath = $imageDir . $filename;
    if (!file_exists($filepath)) {
        // For development, create a simple text file or use local placeholder
        file_put_contents($filepath, "<!-- Placeholder for $filename -->\n<!-- In production, replace with actual image -->\n");
        $results[$filename] = "Created: $filepath";
    } else {
        $results[$filename] = "Already exists: $filepath";
    }
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Image Import - Pastimes</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 40px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo ".success { color: #4CAF50; padding: 10px; margin: 5px 0; background: #f0f8f0; border-left: 4px solid #4CAF50; }";
echo ".error { color: #f44336; padding: 10px; margin: 5px 0; background: #fef0f0; border-left: 4px solid #f44336; }";
echo "h1 { color: #333; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>Image Import Status</h1>";
echo "<p>Initializing image directory for Pastimes...</p>";

foreach ($results as $filename => $status) {
    echo "<div class='success'>" . htmlspecialchars($status) . "</div>";
}

echo "<h3>Instructions:</h3>";
echo "<ul>";
echo "<li>Place product images in: <code>pastimes/images/</code></li>";
echo "<li>Supported formats: JPG, PNG, WebP, GIF</li>";
echo "<li>Recommended size: 400x400px for products</li>";
echo "<li>Update database imagePath field with filename only (e.g., 'item-123.jpg')</li>";
echo "</ul>";
echo "<p><a href='loadClothingStore.php' style='color: #0066cc;'>← Back to Setup</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
