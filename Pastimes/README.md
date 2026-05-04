# Pastimes — Local Development

This project is a PHP/MySQL web app for a second-hand clothing marketplace.

Prerequisites
- XAMPP (Apache + MySQL + PHP) or similar LAMP/WAMP stack.
- PHP 8.x recommended.

Setup
1. Place the `Pastimes` folder inside your web server document root (e.g., `C:\xampp\htdocs\Pastimes`).
2. Import the database schema: open phpMyAdmin or use the MySQL CLI to import `database/ClothingStore.sql`.

   Using MySQL CLI (example):

```powershell
mysql -u root -p < database\ClothingStore.sql
```

3. Ensure file permissions allow Apache/PHP to read files.
4. Start Apache and MySQL services (via XAMPP control panel).
5. Open the app in your browser:

```
http://localhost/Pastimes/
```

- Notes
- Replace placeholder images in `images/design/` with your provided screenshots. Recommended filenames (place into `images/design/`):
   - `hero-1.jpg`, `hero-2.jpg`, `hero-3.jpg`, `hero-4.jpg` (homepage gallery)
   - `card-1.jpg`, `card-2.jpg`, `card-3.jpg` (product listing thumbnails)
   - `profile-default.jpg` (default avatar)
   If you place images with those names they will replace SVG placeholders automatically.
If you prefer to drop files in a staging folder and let the app import them, place your images into `assets_to_import/` (create in project root) and run:

```powershell
php scripts\import_images.php
```

The script will copy matching files into `images/design/` and print results.
- If you use a subdirectory other than `/Pastimes`, adjust absolute paths in `includes/header.php` and `includes/footer.php` or set up a virtual host.

Testing
- Manual: browse pages, create a test account, list an item via `sell.php`, add to cart, and test messages.
- Smoke test (CLI): a small smoke test is provided at `scripts/smokeTest.php` to verify DB connectivity and seeded accounts. Run from the project root with PHP:

```powershell
php scripts\smokeTest.php
```

- Automated: PHPUnit is not configured. If you want automated tests, I can add a basic PHPUnit scaffold.

Need help importing data or wiring the images? Tell me which images you want added and I will place them into `images/design/` with the correct names.
