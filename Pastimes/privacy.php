<?php
/**
 * privacy.php — Privacy Policy & Terms of Service
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */

session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .policy-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        h2 {
            font-size: 1.3rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        p {
            color: #6b7280;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        ul {
            color: #6b7280;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <div class="policy-container">
            <h1>Privacy Policy</h1>
            <p><strong>Last Updated:</strong> May 2026</p>

            <h2>1. Introduction</h2>
            <p>
                Pastimes ("we," "us," "our") is committed to protecting your privacy. This Privacy Policy explains 
                how we collect, use, disclose, and otherwise handle your information when you use our website and services.
            </p>

            <h2>2. Information We Collect</h2>
            <p>We collect information you provide directly, such as:</p>
            <ul>
                <li>Account registration information (name, email, username, password)</li>
                <li>Profile information (address, phone number, profile picture)</li>
                <li>Listing information from sellers (product details, images, descriptions)</li>
                <li>Order and transaction information</li>
                <li>Messages and communications between users</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Provide, maintain, and improve our services</li>
                <li>Process transactions and send related information</li>
                <li>Send promotional communications (with your consent)</li>
                <li>Detect and prevent fraud and abuse</li>
                <li>Comply with legal obligations</li>
            </ul>

            <h2>4. Data Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal information 
                against unauthorized access, alteration, disclosure, or destruction.
            </p>

            <h2>5. User Responsibilities</h2>
            <p>
                You are responsible for maintaining the confidentiality of your account credentials and for all 
                activities that occur under your account.
            </p>

            <h2>6. Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. We will notify you of any material changes 
                by posting the new Privacy Policy on our website.
            </p>

            <h2>7. Contact Us</h2>
            <p>
                If you have any questions about this Privacy Policy, please contact us at: 
                <strong>privacy@pastimes.co.za</strong>
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
