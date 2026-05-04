<?php
/**
 * checkout.php — Delivery Details & Order Summary
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
$error = '';
$success = false;

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM tblUser WHERE userID = ?");
$stmt->bind_param('i', $userID);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deliveryName = sanitizeInput($_POST['deliveryName']);
    $deliveryAddress = sanitizeInput($_POST['deliveryAddress']);
    $deliveryCity = sanitizeInput($_POST['deliveryCity']);
    $postalCode = sanitizeInput($_POST['postalCode']);
    $deliveryType = sanitizeInput($_POST['deliveryType'] ?? 'residential');
    
    if (empty($deliveryName) || empty($deliveryAddress) || empty($deliveryCity) || empty($postalCode)) {
        $error = 'All delivery fields are required.';
    } else {
        try {
            // Process each item in cart
            $totalAmount = 0;
            foreach ($_SESSION['cart'] as $clothingID => $item) {
                $itemTotal = ($item['price'] * $item['qty']) + (15 * $item['qty']);
                $totalAmount += $itemTotal;
                
                // Create order for each item
                $stmt = $conn->prepare("INSERT INTO tblOrder (buyerID, clothingID, deliveryName, deliveryAddress, deliveryCity, postalCode, deliveryType, totalAmount, serviceFee, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                $serviceFee = 15 * $item['qty'];
                // Types: buyerID=int, clothingID=int, deliveryName=string, deliveryAddress=string,
                // deliveryCity=string, postalCode=string, deliveryType=string, totalAmount=double, serviceFee=double
                $stmt->bind_param('iisssssdd', $userID, $clothingID, $deliveryName, $deliveryAddress, $deliveryCity, $postalCode, $deliveryType, $itemTotal, $serviceFee);
                $stmt->execute();
                $orderId = $conn->insert_id;
                $stmt->close();

                // Mirror order to text file
                if (function_exists('appendDataFile')) {
                    appendDataFile('ordersData.txt', [
                        'action' => 'create',
                        'orderID' => (int)$orderId,
                        'buyerID' => (int)$userID,
                        'clothingID' => (int)$clothingID,
                        'deliveryName' => $deliveryName,
                        'deliveryAddress' => $deliveryAddress,
                        'deliveryCity' => $deliveryCity,
                        'postalCode' => $postalCode,
                        'deliveryType' => $deliveryType,
                        'totalAmount' => (float)$itemTotal,
                        'serviceFee' => (float)$serviceFee,
                        'status' => 'pending',
                        'createdAt' => date('c')
                    ]);
                }
                
                // Update clothing status to sold
                $stmt = $conn->prepare("UPDATE tblClothes SET status = 'sold' WHERE clothingID = ?");
                $stmt->bind_param('i', $clothingID);
                $stmt->execute();
                $stmt->close();

                // Mirror clothing status update
                if (function_exists('appendDataFile')) {
                    appendDataFile('clothesData.txt', [
                        'action' => 'update_status',
                        'clothingID' => (int)$clothingID,
                        'status' => 'sold',
                        'updatedAt' => date('c')
                    ]);
                }
            }
            
            // Clear cart
            $_SESSION['cart'] = array();
            $success = true;
            redirect('order-confirmation.php');
        } catch (Exception $e) {
            $error = 'Error processing order: ' . $e->getMessage();
        }
    }
}

// Calculate totals
$subtotal = 0;
$serviceFeesTotal = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $itemTotal = $item['price'] * $item['qty'];
        $subtotal += $itemTotal;
        $serviceFeesTotal += 15 * $item['qty'];
    }
}
$grandTotal = $subtotal + $serviceFeesTotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            margin: 2rem 0;
        }
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: Inter, sans-serif;
            font-size: 0.95rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .order-summary {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        .summary-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .summary-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .summary-item-details {
            flex: 1;
            font-size: 0.85rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .summary-row.total {
            border-top: 2px solid #d1d5db;
            padding-top: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .payment-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #1e40af;
        }
        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem;">Checkout</h1>

        <?php if (!empty($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="checkout-container">
            <div class="checkout-form">
                <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <h2 style="margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-map-marker-alt"></i> Delivery Details
                    </h2>

                    <div class="form-group">
                        <label for="deliveryName">FULL NAME</label>
                        <input type="text" id="deliveryName" name="deliveryName" value="<?php echo htmlspecialchars($user['fullName']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="deliveryAddress">STREET ADDRESS</label>
                        <input type="text" id="deliveryAddress" name="deliveryAddress" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="deliveryCity">CITY</label>
                            <input type="text" id="deliveryCity" name="deliveryCity" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="postalCode">POSTAL CODE</label>
                            <input type="text" id="postalCode" name="postalCode" value="<?php echo htmlspecialchars($user['postalCode'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">PHONE NUMBER (OPTIONAL)</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="deliveryType">DELIVERY TYPE</label>
                        <select id="deliveryType" name="deliveryType">
                            <option value="residential">Residential</option>
                            <option value="work">Work</option>
                        </select>
                    </div>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb; margin-top: 1.5rem;">
                    <h2 style="margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-lock"></i> Payment (Simulated)
                    </h2>
                    <div class="payment-info">
                        <i class="fas fa-shield-alt"></i> No real payment is processed in this prototype. Click "Place Order" to simulate a successful transaction.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1.5rem;">
                    <i class="fas fa-check-circle"></i> Place Order • <?php echo formatPrice($grandTotal); ?>
                </button>
            </div>

            <div class="order-summary">
                <h3 style="margin-top: 0;">Order Summary</h3>
                
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="summary-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="summary-item-image">
                        <div class="summary-item-details">
                            <p style="margin: 0; font-weight: 500;"><?php echo htmlspecialchars($item['title']); ?></p>
                            <p style="margin: 0.25rem 0; color: #6b7280;"><?php echo htmlspecialchars($item['brand']); ?> • Size <?php echo htmlspecialchars($item['size']); ?></p>
                            <p style="margin: 0.25rem 0; font-weight: 600;"><?php echo formatPrice($item['price'] * $item['qty']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Buyer Protection Fee</span>
                    <span><?php echo formatPrice($serviceFeesTotal); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($grandTotal); ?></span>
                </div>
            </div>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
