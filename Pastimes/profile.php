<?php
/**
 * profile.php — User Profile & Delivery Address Management
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

// Get user data
$stmt = $conn->prepare("SELECT * FROM tblUser WHERE userID = ?");
$stmt->bind_param('i', $userID);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitizeInput($_POST['fullName']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $postalCode = sanitizeInput($_POST['postalCode']);

    if (empty($fullName) || empty($email)) {
        $error = 'Full name and email are required.';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE tblUser SET fullName = ?, email = ?, phone = ?, address = ?, city = ?, postalCode = ? WHERE userID = ?");
            $stmt->bind_param('ssssssi', $fullName, $email, $phone, $address, $city, $postalCode, $userID);
            $stmt->execute();
            $stmt->close();

            // Update session
            $_SESSION['fullName'] = $fullName;

            if (function_exists('appendPlainDataFile')) {
                // Format: update\tuserID\tfullName\temail\tphone\taddress\tcity\tpostalCode\tupdatedAt
                $line = 'update' . "\t" . (int)$userID . "\t" . $fullName . "\t" . $email . "\t" . $phone . "\t" . $address . "\t" . $city . "\t" . $postalCode . "\t" . date('c');
                appendPlainDataFile('userData.txt', $line);
            }

            $success = true;
            $user['fullName'] = $fullName;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['address'] = $address;
            $user['city'] = $city;
            $user['postalCode'] = $postalCode;
        } catch (Exception $e) {
            $error = 'Error updating profile: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
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
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: Inter, sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-avatar img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #1e40af;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin: 0; font-size: 2.5rem;">My Profile</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="profile-container">
            <div class="profile-avatar">
                <img src="<?php echo htmlspecialchars($user['profilePic']); ?>" alt="Profile Picture">
            </div>

            <?php if (!empty($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> Profile updated successfully!
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="fullName">FULL NAME</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['fullName']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">PHONE NUMBER</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">STREET ADDRESS</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">CITY</label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="postalCode">POSTAL CODE</label>
                        <input type="text" id="postalCode" name="postalCode" value="<?php echo htmlspecialchars($user['postalCode'] ?? ''); ?>">
                    </div>
                </div>

                <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                    <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">
                        <strong>Account Status:</strong> <span style="text-transform: capitalize;"><?php echo $user['status']; ?></span>
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
