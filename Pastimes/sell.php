<?php
/**
 * sell.php — Seller: List a New Clothing Item
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

session_start();
require_once 'includes/DBConn.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userID = $_SESSION['userID'];
$role = $_SESSION['role'];

// Check if user is allowed to sell
if (!in_array($role, ['seller', 'both'])) {
    $cannotSell = true;
} else {
    // Check if user status is active
    $stmt = $conn->prepare("SELECT status FROM tblUser WHERE userID = ?");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    $cannotSell = ($user['status'] !== 'active');
}

$error = '';
$success = false;
$suggestedPrice = '';

// Handle AJAX price suggestion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'suggest-price') {
    header('Content-Type: application/json');
    $brand = sanitizeInput($_POST['brand'] ?? '');
    $condition = sanitizeInput($_POST['condition'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    
    if ($brand && $condition && $category) {
        $suggested = priceSuggestion($brand, $condition, $category);
        echo json_encode(['price' => $suggested]);
    } else {
        echo json_encode(['price' => 0]);
    }
    exit;
}

// Handle listing submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    if ($cannotSell) {
        $error = 'You must have an active seller account to list items.';
    } else {
        $title = sanitizeInput($_POST['title']);
        $brand = sanitizeInput($_POST['brand']);
        $category = sanitizeInput($_POST['category']);
        $size = sanitizeInput($_POST['size']);
        $condition = sanitizeInput($_POST['condition']);
        $price = (float)($_POST['price'] ?? 0);
        $description = sanitizeInput($_POST['description']);
        
        if (empty($title) || empty($brand) || empty($category) || empty($size) || empty($condition) || $price <= 0) {
            $error = 'All fields are required and price must be greater than 0.';
        } elseif (empty($_FILES['image']['name'])) {
            $error = 'Please upload an image.';
        } else {
            // Handle image upload
            $uploadDir = 'images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = basename($_FILES['image']['name']);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = 'Only JPG, PNG, and WebP images are allowed.';
            } else {
                // Create unique filename
                $uniqueName = uniqid() . '.' . $fileType;
                $uploadPath = $uploadDir . $uniqueName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Calculate CO2 and water saved
                    $co2Saved = 3.00;
                    $waterSaved = 2700;
                    
                    // Insert into database
                    try {
                        $stmt = $conn->prepare("INSERT INTO tblClothes (sellerID, title, brand, category, size, itemCondition, price, description, imagePath, status, co2Saved, waterSaved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
                        $stmt->bind_param('isssssdsdd', $userID, $title, $brand, $category, $size, $condition, $price, $description, $uploadPath, $co2Saved, $waterSaved);
                        $stmt->execute();
                        $newId = $conn->insert_id;
                        $stmt->close();

                        // Mirror to text file
                        if (function_exists('appendDataFile')) {
                            appendDataFile('clothesData.txt', [
                                'action' => 'create',
                                'clothingID' => (int)$newId,
                                'sellerID' => (int)$userID,
                                'title' => $title,
                                'brand' => $brand,
                                'category' => $category,
                                'size' => $size,
                                'itemCondition' => $condition,
                                'price' => $price,
                                'description' => $description,
                                'imagePath' => $uploadPath,
                                'status' => 'pending',
                                'co2Saved' => $co2Saved,
                                'waterSaved' => $waterSaved,
                                'createdAt' => date('c')
                            ]);
                        }

                        $success = true;
                        echo '<script>setTimeout(() => { window.location.href = "dashboard.php"; }, 2000);</script>';
                    } catch (Exception $e) {
                        $error = 'Error creating listing: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Failed to upload image. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Item - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .sell-form {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: Inter, sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .image-upload {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .image-upload:hover {
            border-color: #1e40af;
            background: #f0f9ff;
        }
        .image-upload i {
            font-size: 2.5rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            display: block;
        }
        .image-upload input[type="file"] {
            display: none;
        }
        .price-suggestion {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .success {
            background: #efe;
            border: 1px solid #cfc;
            color: #060;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .cannot-sell {
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem;">List a New Item</h1>

        <?php if ($cannotSell): ?>
            <div class="cannot-sell">
                <h2 style="margin-top: 0;">
                    <i class="fas fa-lock"></i> Seller Account Not Active
                </h2>
                <p>To list items on Pastimes, you need an active seller account. Please contact our admin team to activate your seller privileges.</p>
                <a href="dashboard.php" class="btn btn-secondary">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> Your item has been submitted and is pending admin approval.
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="sell-form">
                <div class="form-group">
                    <label for="image">ITEM PHOTO</label>
                    <div class="image-upload" onclick="document.getElementById('image').click();">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p style="margin: 0.5rem 0; font-weight: 600;">Drag & drop your photo here</p>
                        <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">or</p>
                        <button type="button" style="background: #1e40af; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; margin-top: 0.5rem;">Click to browse files</button>
                        <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.85rem;">JPG, PNG, WebP — max 5MB</p>
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="title">ITEM NAME</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Navy Polo Shirt" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="brand">BRAND</label>
                        <input type="text" id="brand" name="brand" placeholder="e.g. Polo Ralph Lauren" required>
                    </div>
                    <div class="form-group">
                        <label for="category">CATEGORY</label>
                        <select id="category" name="category" required onchange="updatePriceSuggestion()">
                            <option value="">Select a category</option>
                            <option value="tops">Tops</option>
                            <option value="bottoms">Bottoms</option>
                            <option value="dresses">Dresses</option>
                            <option value="outerwear">Outerwear</option>
                            <option value="footwear">Footwear</option>
                            <option value="accessories">Accessories</option>
                            <option value="activewear">Activewear</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="size">SIZE</label>
                        <select id="size" name="size" required>
                            <option value="">Select size</option>
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                            <option value="ONE">One Size</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="condition">CONDITION</label>
                        <select id="condition" name="condition" required onchange="updatePriceSuggestion()">
                            <option value="">Select condition</option>
                            <option value="like new">Like New</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="price">PRICE (R)</label>
                    <input type="number" id="price" name="price" placeholder="e.g. 350" step="0.01" min="0" required>
                    <div id="priceSuggestion"></div>
                </div>

                <div class="form-group">
                    <label for="description">DESCRIPTION</label>
                    <textarea id="description" name="description" rows="5" placeholder="Describe the item — colour, fabric, fit, any flaws, how often it was worn..." required></textarea>
                    <small style="color: #6b7280;">Recommended for faster sales</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                    <i class="fas fa-plus"></i> Submit Listing
                </button>
                <a href="dashboard.php" style="display: block; text-align: center; margin-top: 1rem; color: #6b7280;">Cancel</a>
            </form>
        <?php endif; ?>
    </main>

    <script>
        function updatePriceSuggestion() {
            const brand = document.getElementById('brand').value;
            const condition = document.getElementById('condition').value;
            const category = document.getElementById('category').value;

            if (brand && condition && category) {
                fetch('sell.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=suggest-price&brand=${encodeURIComponent(brand)}&condition=${encodeURIComponent(condition)}&category=${encodeURIComponent(category)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.price > 0) {
                        document.getElementById('priceSuggestion').innerHTML = `<div class="price-suggestion">💡 Suggested price: R ${data.price.toFixed(2)} — you can adjust this.</div>`;
                    }
                });
            }
        }

        document.getElementById('image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            const uploadDiv = document.querySelector('.image-upload');
            uploadDiv.innerHTML = `<i class="fas fa-check-circle" style="color: #10b981;"></i><p style="margin: 0.5rem 0; font-weight: 600;">${fileName}</p><p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Ready to upload</p>`;
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
