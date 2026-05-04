<?php
/**
 * about.php — About Pastimes
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .about-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        .about-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .about-section {
            padding: 3rem 1rem;
            max-width: 900px;
            margin: 0 auto;
        }
        .about-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        .about-section p {
            color: #6b7280;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .value-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .value-card i {
            font-size: 2.5rem;
            color: #1e40af;
            margin-bottom: 1rem;
        }
        .value-card h3 {
            margin: 0.5rem 0;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="about-hero">
        <h1>About Pastimes</h1>
        <p>South Africa's trusted marketplace for quality second-hand branded clothing</p>
    </div>

    <main class="about-section">
        <h2>Our Mission</h2>
        <p>
            Pastimes is dedicated to creating a sustainable future by making quality second-hand branded clothing 
            accessible to everyone. We believe in the power of circular fashion to reduce waste, save resources, and 
            give clothing a second life.
        </p>

        <h2>Our Story</h2>
        <p>
            Founded with the vision of transforming how South Africans buy and sell pre-loved fashion, Pastimes 
            combines trust, quality, and sustainability. Every purchase on Pastimes saves CO₂ emissions and water 
            that would be used in manufacturing new clothing.
        </p>

        <h2>Our Values</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-leaf"></i>
                <h3>Sustainability</h3>
                <p>Reducing fashion waste and environmental impact through circular commerce</p>
            </div>
            <div class="value-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Trust</h3>
                <p>Verified listings and secure transactions for peace of mind shopping</p>
            </div>
            <div class="value-card">
                <i class="fas fa-handshake"></i>
                <h3>Community</h3>
                <p>Connecting buyers and sellers in a vibrant community of conscious consumers</p>
            </div>
            <div class="value-card">
                <i class="fas fa-gem"></i>
                <h3>Quality</h3>
                <p>Only premium branded items that meet our strict quality standards</p>
            </div>
        </div>

        <h2>Impact</h2>
        <p>
            Every transaction on Pastimes contributes to environmental conservation. On average, buying second-hand 
            instead of new saves:
        </p>
        <ul style="color: #6b7280; margin: 1rem 0;">
            <li>3 kg of CO₂ emissions</li>
            <li>2,700 litres of water</li>
            <li>Reducing textile waste in landfills</li>
        </ul>

        <h2>Get Started</h2>
        <p>
            Join Pastimes today to shop sustainably, sell your pre-loved items, and be part of the fashion revolution. 
            Every purchase is a step towards a more sustainable future.
        </p>
        <div style="text-align: center; margin: 2rem 0;">
            <a href="shop.php" class="btn btn-primary" style="display: inline-block; margin-right: 1rem;">Browse Shop</a>
            <a href="register.php" class="btn btn-secondary" style="display: inline-block;">Join Now</a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
