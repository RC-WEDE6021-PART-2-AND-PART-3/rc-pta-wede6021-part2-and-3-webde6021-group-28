<?php
/**
 * admin/upload_images.php — Simple uploader to attach images to clothing items
 * Usage: open /pastimes/admin/upload_images.php in your browser (requires admin login)
 */
session_start();
require_once __DIR__ . '/../includes/DBConn.php';
require_once __DIR__ . '/../includes/functions.php';

// Basic auth check (reuse isAdmin() if available)
if (!isAdmin()) {
    // If not admin, redirect to admin login
    header('Location: login.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process uploaded files for items
    foreach ($_FILES as $field => $file) {
        // expecting fields like image_12 where 12 is clothingID
        if (preg_match('/^image_(\d+)$/', $field, $m)) {
            $id = intval($m[1]);
            if ($file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
                $orig = basename($file['name']);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $safe = preg_replace('/[^a-z0-9_.-]/i', '_', pathinfo($orig, PATHINFO_FILENAME));
                $destName = $safe . '_' . $id . '.' . $ext;
                $destPath = __DIR__ . '/../images/' . $destName;
                if (!is_dir(__DIR__ . '/../images')) mkdir(__DIR__ . '/../images', 0755, true);
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    // update DB
                    $imagePath = 'images/' . $destName;
                    $stmt = $conn->prepare('UPDATE tblClothes SET imagePath = ? WHERE clothingID = ?');
                    $stmt->bind_param('si', $imagePath, $id);
                    if ($stmt->execute()) {
                        $msg .= "Updated item $id with $destName<br>";
                        if (function_exists('appendDataFile')) {
                            appendDataFile('clothesData.txt', [
                                'action' => 'update',
                                'clothingID' => (int)$id,
                                'imagePath' => $imagePath,
                                'updatedAt' => date('c')
                            ]);
                        }
                    } else {
                        $msg .= "DB update failed for item $id<br>";
                    }
                } else {
                    $msg .= "Failed to move uploaded file for item $id<br>";
                }
            }
        }
    }
}

// Load items to display
$items = [];
$res = $conn->query("SELECT clothingID, title, brand, imagePath, status FROM tblClothes ORDER BY createdAt DESC LIMIT 200");
if ($res) {
    while ($row = $res->fetch_assoc()) $items[] = $row;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding:2rem;">
    <h1>Upload Images for Items</h1>
    <?php if (!empty($msg)): ?><div class="alert alert-info"><?php echo $msg; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; padding:8px;">ID</th>
                    <th style="text-align:left; padding:8px;">Title</th>
                    <th style="text-align:left; padding:8px;">Current Image</th>
                    <th style="text-align:left; padding:8px;">Upload New</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td style="padding:8px; vertical-align:middle;"><?php echo $it['clothingID']; ?></td>
                    <td style="padding:8px; vertical-align:middle;"><?php echo htmlspecialchars($it['title']); ?></td>
                    <td style="padding:8px; vertical-align:middle;"><?php if (!empty($it['imagePath'])): ?><img src="<?php echo htmlspecialchars(image_url($it['imagePath'])); ?>" alt="" style="height:80px; object-fit:cover; border:1px solid #ddd; padding:2px;" /><?php else: ?>No image<?php endif; ?></td>
                    <td style="padding:8px; vertical-align:middle;"><input type="file" name="image_<?php echo $it['clothingID']; ?>" accept="image/*"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:12px;">
            <button class="btn btn-primary" type="submit">Upload Selected Images</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
