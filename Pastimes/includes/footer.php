<?php
/**
 * footer.php — Global Site Footer
 *
 * ST10452756 Sheketli Mochaki
 * ST10442357 Lufuno Makhado
 * ST10440144 Katlego Joshua
 *
 * Declaration: This code is our own work except where referenced.
 * Date: 2026-03-25
 */
?>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <a href="/pastimes/index.php" class="footer-logo">
                        <span class="logo-icon">P</span>
                        <span class="logo-text">Pastimes</span>
                    </a>
                    <p class="footer-tagline">South Africa's trusted marketplace for quality second-hand branded clothing.</p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <!-- Shop Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">SHOP</h4>
                    <ul class="footer-links">
                        <li><a href="/pastimes/shop.php">Browse All</a></li>
                        <li><a href="/pastimes/shop.php?category=tops">Tops</a></li>
                        <li><a href="/pastimes/shop.php?category=outerwear">Outerwear</a></li>
                        <li><a href="/pastimes/shop.php?category=dresses">Dresses</a></li>
                        <li><a href="/pastimes/shop.php?category=bottoms">Bottoms</a></li>
                    </ul>
                </div>
                
                <!-- Sell Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">SELL</h4>
                    <ul class="footer-links">
                        <li><a href="/pastimes/about.php#how-it-works">How It Works</a></li>
                        <li><a href="/pastimes/sell.php">List an Item</a></li>
                        <li><a href="/pastimes/about.php#seller-tips">Seller Tips</a></li>
                        <li><a href="/pastimes/about.php#shipping">Shipping Guide</a></li>
                    </ul>
                </div>
                
                <!-- Info Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">INFO</h4>
                    <ul class="footer-links">
                        <li><a href="/pastimes/about.php">About Us</a></li>
                        <li><a href="/pastimes/about.php#contact">Contact</a></li>
                        <li><a href="/pastimes/about.php#faq">FAQ</a></li>
                        <li><a href="/pastimes/privacy.php">Privacy Policy</a></li>
                        <li><a href="/pastimes/privacy.php#terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-eco">
                    <i class="fas fa-leaf"></i>
                    <span>Every purchase saves clothing from landfill</span>
                </div>
                <p class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> Pastimes. All rights reserved. Prototype for testing purposes only.
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>
    
    <!-- JavaScript -->
    <script src="/pastimes/js/main.js"></script>
    <?php if (isset($pageScript)): ?>
    <script src="/pastimes/js/<?php echo $pageScript; ?>"></script>
    <?php endif; ?>
</body>
</html>
