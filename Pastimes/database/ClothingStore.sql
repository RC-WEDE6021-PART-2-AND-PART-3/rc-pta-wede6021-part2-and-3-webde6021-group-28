-- =============================================
-- Pastimes Online Clothing Store
-- Database: ClothingStore
-- Students: ST10452756 Sheketli Mochaki,
--           ST10442357 Lufuno Makhado,
--           ST10440144 Katlego Joshua
-- Date: 2026-03-25
-- Declaration: This SQL file is our own work.
-- =============================================

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS clothingstore;
CREATE DATABASE clothingstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clothingstore;

-- ============================================================
-- TABLE: tblAdmin
-- ============================================================
DROP TABLE IF EXISTS tblAdmin;
CREATE TABLE tblAdmin (
    adminID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: tblUser
-- ============================================================
DROP TABLE IF EXISTS tblUser;
CREATE TABLE tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    role ENUM('buyer','seller','both') DEFAULT 'buyer',
    status ENUM('pending','active','suspended') DEFAULT 'pending',
    address TEXT,
    city VARCHAR(100),
    postalCode VARCHAR(20),
    phone VARCHAR(20),
    profilePic VARCHAR(255) DEFAULT 'images/default-avatar.png',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: tblClothes
-- ============================================================
DROP TABLE IF EXISTS tblClothes;
CREATE TABLE tblClothes (
    clothingID INT AUTO_INCREMENT PRIMARY KEY,
    sellerID INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    brand VARCHAR(100),
    category ENUM('tops','bottoms','dresses','outerwear','footwear','accessories','activewear') NOT NULL,
    size VARCHAR(20),
    itemCondition ENUM('like new','good','fair') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    imagePath VARCHAR(255) DEFAULT 'images/default-clothing.jpg',
    status ENUM('pending','approved','sold','rejected') DEFAULT 'pending',
    suggestedPrice DECIMAL(10,2),
    co2Saved DECIMAL(5,2) DEFAULT 3.00,
    waterSaved INT DEFAULT 2700,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sellerID) REFERENCES tblUser(userID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: tblOrder
-- ============================================================
DROP TABLE IF EXISTS tblOrder;
CREATE TABLE tblOrder (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    buyerID INT NOT NULL,
    clothingID INT NOT NULL,
    deliveryName VARCHAR(100) NOT NULL,
    deliveryAddress TEXT NOT NULL,
    deliveryCity VARCHAR(100),
    postalCode VARCHAR(20),
    deliveryType ENUM('residential','work') DEFAULT 'residential',
    totalAmount DECIMAL(10,2) NOT NULL,
    serviceFee DECIMAL(10,2) DEFAULT 15.00,
    status ENUM('pending','dispatched','delivered','cancelled') DEFAULT 'pending',
    orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyerID) REFERENCES tblUser(userID) ON DELETE CASCADE,
    FOREIGN KEY (clothingID) REFERENCES tblClothes(clothingID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: tblMessages
-- ============================================================
DROP TABLE IF EXISTS tblMessages;
CREATE TABLE tblMessages (
    messageID INT AUTO_INCREMENT PRIMARY KEY,
    senderID INT NOT NULL,
    receiverID INT NOT NULL,
    clothingID INT,
    subject VARCHAR(200),
    messageBody TEXT NOT NULL,
    isRead TINYINT(1) DEFAULT 0,
    sentAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (senderID) REFERENCES tblUser(userID) ON DELETE CASCADE,
    FOREIGN KEY (receiverID) REFERENCES tblUser(userID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: tblAdmin (3+ entries)
-- Password hash c3c37a23fa3c5a5f41f9d43f85e99edd = md5('Admin1234')
-- ============================================================
INSERT INTO tblAdmin (fullName, email, username, passwordHash) VALUES
('Super Admin', 'admin@pastimes.co.za', 'admin', 'c3c37a23fa3c5a5f41f9d43f85e99edd'),
('Jane Mokoena', 'j.mokoena@pastimes.co.za', 'jmokoena', 'c3c37a23fa3c5a5f41f9d43f85e99edd'),
('Dev Admin', 'dev@pastimes.co.za', 'devadmin', 'c3c37a23fa3c5a5f41f9d43f85e99edd');

-- ============================================================
-- SEED DATA: tblUser (30+ entries)
-- Password hash 29ef52e7563626a96cea74b4085c124a = md5('Pass1234')
-- ============================================================
INSERT INTO tblUser (fullName, email, username, passwordHash, role, status, address, city, postalCode, phone) VALUES
('Sheketli Mochaki', 's.mocha@abc.co.za', 's.mocah', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '12 Main Road', 'Cape Town', '8001', '0821234567'),
('Lufuno Makhado', 'l.makhado@gmail.com', 'l.makhado', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '45 Oak Avenue', 'Johannesburg', '2001', '0839876543'),
('Katlego Joshua', 'm.johnson@outlook.com', 'K.Joshua', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '78 Pine Street', 'Pretoria', '0001', '0845551234'),
('Priya Naidoo', 'p.naidoo@webmail.co.za', 'priya_n', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'pending', '5 Rose Avenue', 'Durban', '4001', '0761234567'),
('Lebo Dlamini', 'lebo.d@ymail.com', 'lebod', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '22 Jacaranda Lane', 'Bloemfontein', '9301', '0829998877'),
('Thabo Nkosi', 'thabo.n@email.com', 'thabo_n', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '15 Nelson Mandela Dr', 'Soweto', '1804', '0831112233'),
('Aisha Patel', 'aisha.p@gmail.com', 'aisha_p', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '8 Sandton Drive', 'Sandton', '2196', '0844445566'),
('Sipho Mokoena', 'sipho.m@outlook.com', 'sipho_m', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '33 Church Street', 'Polokwane', '0700', '0857778899'),
('Zanele Khumalo', 'zanele@example.com', 'zanele_k', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'pending', '19 Freedom Way', 'East London', '5201', '0866661122'),
('Lerato Dlamini', 'lerato.d@mail.co.za', 'lerato_d', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '12 Jacaranda Street', 'Pretoria', '0001', '0823334455'),
('Mandla Sithole', 'mandla.s@email.com', 'mandla_s', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '67 Market Street', 'Johannesburg', '2001', '0834567890'),
('Nomsa Zwane', 'nomsa.z@gmail.com', 'nomsa_z', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '45 Beach Road', 'Durban', '4001', '0847890123'),
('Bongani Mahlangu', 'bongani.m@yahoo.com', 'bongani_m', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '23 Mountain View', 'Cape Town', '8001', '0851234567'),
('Naledi Molefe', 'naledi.m@outlook.com', 'naledi_m', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '89 Sunset Boulevard', 'Pretoria', '0002', '0862345678'),
('Themba Ndlovu', 'themba.n@mail.com', 'themba_n', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'suspended', '12 Railway Street', 'Port Elizabeth', '6001', '0873456789'),
('Palesa Mosia', 'palesa.m@email.co.za', 'palesa_m', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '56 Gold Street', 'Johannesburg', '2001', '0884567890'),
('Kagiso Tau', 'kagiso.t@gmail.com', 'kagiso_t', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '34 Diamond Road', 'Kimberley', '8301', '0895678901'),
('Dineo Radebe', 'dineo.r@yahoo.com', 'dineo_r', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '78 Emerald Lane', 'Bloemfontein', '9301', '0826789012'),
('Siyanda Cele', 'siyanda.c@outlook.com', 'siyanda_c', '29ef52e7563626a96cea74b4085c124a', 'both', 'pending', '90 Ruby Avenue', 'Durban', '4001', '0837890123'),
('Mpho Langa', 'mpho.l@email.com', 'mpho_l', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '11 Sapphire Street', 'Soweto', '1804', '0848901234'),
('Thandeka Ngcobo', 'thandeka.n@gmail.com', 'thandeka_n', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '22 Pearl Road', 'Pietermaritzburg', '3201', '0859012345'),
('Lindani Mthembu', 'lindani.m@mail.co.za', 'lindani_m', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '33 Coral Lane', 'Richards Bay', '3900', '0860123456'),
('Nokuthula Zulu', 'nokuthula.z@yahoo.com', 'nokuthula_z', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '44 Amber Street', 'Newcastle', '2940', '0871234567'),
('Vusi Shabangu', 'vusi.s@outlook.com', 'vusi_s', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '55 Jade Avenue', 'Nelspruit', '1200', '0882345678'),
('Ayanda Mkhize', 'ayanda.m@email.com', 'ayanda_m', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '66 Opal Road', 'Rustenburg', '0299', '0893456789'),
('Sbongile Dube', 'sbongile.d@gmail.com', 'sbongile_d', '29ef52e7563626a96cea74b4085c124a', 'seller', 'pending', '77 Topaz Lane', 'Mahikeng', '2745', '0824567890'),
('Nhlanhla Koza', 'nhlanhla.k@yahoo.com', 'nhlanhla_k', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '88 Crystal Street', 'Upington', '8800', '0835678901'),
('Busisiwe Ndaba', 'busisiwe.n@mail.com', 'busisiwe_n', '29ef52e7563626a96cea74b4085c124a', 'both', 'active', '99 Onyx Avenue', 'George', '6529', '0846789012'),
('Lwazi Gumede', 'lwazi.g@outlook.com', 'lwazi_g', '29ef52e7563626a96cea74b4085c124a', 'seller', 'active', '10 Quartz Road', 'Grahamstown', '6139', '0857890123'),
('Zinhle Maseko', 'zinhle.m@email.co.za', 'zinhle_m', '29ef52e7563626a96cea74b4085c124a', 'buyer', 'active', '21 Granite Lane', 'Worcester', '6850', '0868901234');

-- ============================================================
-- SEED DATA: tblClothes (30+ entries)
-- ============================================================
INSERT INTO tblClothes (sellerID, title, brand, category, size, itemCondition, price, description, imagePath, status, suggestedPrice, co2Saved, waterSaved) VALUES
(2, 'Levi''s 501 Jeans Blue Denim', 'Levi''s', 'bottoms', '32', 'like new', 350.00, 'Classic straight-fit jeans in excellent condition. Authentic Levi''s with original tags.', 'images/levis-jeans.jpg', 'approved', 320.00, 3.50, 2800),
(2, 'Nike Air Zoom Running Shoes', 'Nike', 'footwear', '9', 'good', 420.00, 'Barely worn trainers, perfect for running or casual wear. Size UK 9.', 'images/nike-shoes.jpg', 'approved', 380.00, 4.20, 3200),
(5, 'Zara Floral Wrap Dress', 'Zara', 'dresses', 'M', 'like new', 280.00, 'Perfect summer dress with beautiful floral pattern. Worn once for a wedding.', 'images/zara-dress.jpg', 'approved', 280.00, 2.80, 2500),
(3, 'H&M Olive Trench Coat', 'H&M', 'outerwear', 'L', 'good', 320.00, 'Warm lined coat perfect for autumn/winter. Classic olive green color.', 'images/hm-coat.jpg', 'approved', 350.00, 5.00, 4000),
(2, 'Adidas Hoodie Grey Marl', 'Adidas', 'tops', 'XL', 'fair', 150.00, 'Slight pilling on cuffs but still very wearable. Original Adidas.', 'images/adidas-hoodie.jpg', 'approved', 120.00, 2.50, 2200),
(5, 'Woolworths Striped Linen Top', 'Woolworths', 'tops', 'S', 'like new', 120.00, 'Unworn with tags still attached. Beautiful striped pattern.', 'images/woolies-top.jpg', 'approved', 140.00, 2.00, 1800),
(3, 'Puma Gym Leggings Black', 'Puma', 'activewear', 'M', 'good', 180.00, 'High-waisted, very comfortable for workouts. Moisture-wicking fabric.', 'images/puma-leggings.jpg', 'approved', 180.00, 2.20, 2000),
(2, 'Fossil Leather Watch Brown', 'Fossil', 'accessories', 'ONE', 'like new', 650.00, 'Comes with original box and papers. Classic brown leather strap.', 'images/fossil-watch.jpg', 'approved', 600.00, 1.50, 1200),
(5, 'MRP Denim Mini Skirt', 'MRP', 'bottoms', 'S', 'fair', 90.00, 'Light wash denim, slight fraying at hem adds character.', 'images/mrp-skirt.jpg', 'approved', 80.00, 1.80, 1500),
(3, 'Tommy Hilfiger Polo Shirt', 'Tommy Hilfiger', 'tops', 'M', 'good', 200.00, 'Classic navy polo with signature logo. Great condition.', 'images/tommy-polo.jpg', 'approved', 200.00, 2.30, 2100),
(6, 'Color-Block Sweatshirt', 'Urban Brand', 'tops', 'L', 'like new', 349.00, 'Stylish color-block design with contrast sleeves. Very trendy piece.', 'images/colorblock-sweatshirt.jpg', 'approved', 320.00, 2.80, 2400),
(6, 'Premium League Hoodie', 'Premium League', 'tops', 'XL', 'good', 520.00, 'Dark green Premium League hoodie with bold crest logo on the back. Superior quality heavyweight fleece.', 'images/premium-hoodie.jpg', 'approved', 480.00, 3.20, 2800),
(6, 'Oversized Knit Sweater', '& Other Stories', 'tops', 'S', 'like new', 420.00, 'Cozy oversized knit in charcoal grey. Perfect for layering.', 'images/knit-sweater.jpg', 'approved', 400.00, 3.00, 2600),
(6, 'Quarter-Zip Knit Pullover', 'Banana Republic', 'tops', 'M', 'like new', 480.00, 'Classic quarter-zip in heather grey. Premium quality.', 'images/quarter-zip.jpg', 'approved', 450.00, 2.90, 2500),
(6, 'Short-Sleeve Zip Polo', 'Dickies', 'tops', 'L', 'good', 295.00, 'Casual brown polo with zip detail. Great workwear style.', 'images/dickies-polo.jpg', 'approved', 280.00, 2.40, 2100),
(6, 'Vintage Levi Denim Jacket', 'Levi''s', 'outerwear', 'L', 'like new', 550.00, 'Authentic vintage Levi''s denim jacket. Rare find in excellent condition.', 'images/levis-jacket.jpg', 'pending', 520.00, 4.50, 3800),
(6, 'Embroidered Boilersuit', 'Carhartt', 'bottoms', 'S', 'good', 780.00, 'Unique embroidered boilersuit. Statement piece for fashion-forward individuals.', 'images/boilersuit.jpg', 'approved', 720.00, 5.20, 4200),
(7, 'Los Angeles Red Sweatshirt', 'LA Apparel', 'tops', 'M', 'like new', 310.00, 'Vibrant red sweatshirt with Los Angeles California print. Eye-catching piece.', 'images/la-red-sweatshirt.jpg', 'approved', 290.00, 2.60, 2300),
(7, 'Los Angeles Blue Sweatshirt', 'LA Apparel', 'tops', 'L', 'like new', 310.00, 'Royal blue sweatshirt with Los Angeles California print. Perfect matching pair.', 'images/la-blue-sweatshirt.jpg', 'approved', 290.00, 2.60, 2300),
(7, 'Grey Jogger Pants 87', 'Champion', 'activewear', 'M', 'good', 220.00, 'Comfortable grey joggers with 87 print. Great for casual wear.', 'images/grey-joggers.jpg', 'approved', 200.00, 2.30, 2000),
(11, 'Nike Tech Fleece Joggers', 'Nike', 'activewear', 'L', 'like new', 450.00, 'Premium tech fleece joggers in charcoal. Tapered fit.', 'images/nike-tech.jpg', 'approved', 420.00, 3.10, 2700),
(11, 'Adidas Track Jacket', 'Adidas', 'outerwear', 'M', 'good', 380.00, 'Classic three-stripe design in black with white stripes.', 'images/adidas-track.jpg', 'approved', 350.00, 3.80, 3200),
(12, 'H&M Flowy Maxi Dress', 'H&M', 'dresses', 'S', 'like new', 340.00, 'Elegant flowy maxi dress perfect for summer events.', 'images/hm-maxi.jpg', 'approved', 320.00, 2.90, 2500),
(12, 'Zara Wide Leg Trousers', 'Zara', 'bottoms', 'M', 'good', 290.00, 'High-waisted wide leg trousers in cream. Very elegant.', 'images/zara-trousers.jpg', 'approved', 280.00, 2.70, 2400),
(14, 'Woolworths Cashmere Blend Scarf', 'Woolworths', 'accessories', 'ONE', 'like new', 280.00, 'Luxurious cashmere blend scarf in burgundy. Perfect for winter.', 'images/woolies-scarf.jpg', 'approved', 300.00, 1.20, 1000),
(14, 'Ray-Ban Wayfarers', 'Ray-Ban', 'accessories', 'ONE', 'good', 890.00, 'Classic Wayfarer sunglasses with original case. Minor scratches.', 'images/rayban.jpg', 'approved', 850.00, 0.80, 600),
(17, 'Puma Sports Bra', 'Puma', 'activewear', 'S', 'like new', 180.00, 'High support sports bra in black. Perfect for intense workouts.', 'images/puma-bra.jpg', 'approved', 180.00, 1.50, 1200),
(17, 'Nike Dri-FIT Tank', 'Nike', 'activewear', 'M', 'good', 160.00, 'Breathable Dri-FIT tank top. Great moisture management.', 'images/nike-tank.jpg', 'approved', 150.00, 1.40, 1100),
(20, 'Converse Chuck Taylor High', 'Converse', 'footwear', '8', 'good', 380.00, 'Classic black high-top Chucks. Well-maintained condition.', 'images/converse-high.jpg', 'approved', 350.00, 3.50, 3000),
(20, 'Dr. Martens 1460 Boots', 'Dr. Martens', 'footwear', '7', 'like new', 1200.00, 'Iconic 8-eye boots in cherry red. Barely worn, still stiff.', 'images/docs.jpg', 'approved', 1150.00, 5.80, 4800),
(22, 'Forever New Blazer', 'Forever New', 'outerwear', 'S', 'like new', 450.00, 'Tailored blazer in dusty pink. Perfect for office or events.', 'images/fn-blazer.jpg', 'approved', 420.00, 4.00, 3500),
(22, 'Cotton On Denim Shorts', 'Cotton On', 'bottoms', 'M', 'fair', 120.00, 'Distressed denim shorts. Some natural fading.', 'images/co-shorts.jpg', 'approved', 100.00, 1.60, 1400),
(23, 'Superdry Windbreaker', 'Superdry', 'outerwear', 'L', 'good', 520.00, 'Lightweight windbreaker in navy with orange lining. Water-resistant.', 'images/superdry-wind.jpg', 'approved', 480.00, 3.90, 3300),
(24, 'Guess Logo T-Shirt', 'Guess', 'tops', 'M', 'like new', 220.00, 'Classic Guess logo tee in white. Timeless piece.', 'images/guess-tee.jpg', 'approved', 200.00, 1.80, 1500),
(25, 'Michael Kors Tote Bag', 'Michael Kors', 'accessories', 'ONE', 'good', 1800.00, 'Large tan leather tote with gold hardware. Some wear on handles.', 'images/mk-tote.jpg', 'approved', 1700.00, 2.50, 2000),
(28, 'Diesel Slim Jeans', 'Diesel', 'bottoms', '34', 'like new', 680.00, 'Premium slim fit jeans in dark wash. Italian denim quality.', 'images/diesel-jeans.jpg', 'approved', 650.00, 3.80, 3200),
(29, 'Under Armour Training Tee', 'Under Armour', 'activewear', 'L', 'good', 190.00, 'Performance fabric training tee. Quick-dry technology.', 'images/ua-tee.jpg', 'approved', 180.00, 1.70, 1400),
(30, 'ASOS Midi Dress', 'ASOS', 'dresses', 'M', 'like new', 260.00, 'Elegant midi dress in emerald green. Perfect for special occasions.', 'images/asos-midi.jpg', 'approved', 240.00, 2.60, 2200);

-- ============================================================
-- SEED DATA: tblOrder (30+ entries)
-- ============================================================
INSERT INTO tblOrder (buyerID, clothingID, deliveryName, deliveryAddress, deliveryCity, postalCode, deliveryType, totalAmount, serviceFee, status) VALUES
(1, 3, 'Sheketli Mochaki', '12 Main Road', 'Cape Town', '8001', 'residential', 295.00, 15.00, 'delivered'),
(4, 1, 'Priya Naidoo', '5 Rose Avenue', 'Durban', '4001', 'residential', 365.00, 15.00, 'dispatched'),
(1, 5, 'Sheketli Mochaki', '12 Main Road', 'Cape Town', '8001', 'residential', 165.00, 15.00, 'pending'),
(4, 6, 'Priya Naidoo', '5 Rose Avenue', 'Durban', '4001', 'work', 135.00, 15.00, 'delivered'),
(1, 8, 'Sheketli Mochaki', '12 Main Road', 'Cape Town', '8001', 'residential', 665.00, 15.00, 'pending'),
(8, 2, 'Sipho Mokoena', '33 Church Street', 'Polokwane', '0700', 'residential', 435.00, 15.00, 'delivered'),
(10, 4, 'Lerato Dlamini', '12 Jacaranda Street', 'Pretoria', '0001', 'residential', 335.00, 15.00, 'dispatched'),
(13, 7, 'Bongani Mahlangu', '23 Mountain View', 'Cape Town', '8001', 'work', 195.00, 15.00, 'delivered'),
(10, 9, 'Lerato Dlamini', '12 Jacaranda Street', 'Pretoria', '0001', 'residential', 105.00, 15.00, 'pending'),
(18, 10, 'Dineo Radebe', '78 Emerald Lane', 'Bloemfontein', '9301', 'residential', 215.00, 15.00, 'delivered'),
(21, 11, 'Thandeka Ngcobo', '22 Pearl Road', 'Pietermaritzburg', '3201', 'residential', 364.00, 15.00, 'delivered'),
(24, 12, 'Vusi Shabangu', '55 Jade Avenue', 'Nelspruit', '1200', 'work', 535.00, 15.00, 'dispatched'),
(27, 13, 'Nhlanhla Koza', '88 Crystal Street', 'Upington', '8800', 'residential', 435.00, 15.00, 'pending'),
(10, 14, 'Lerato Dlamini', '12 Jacaranda Street', 'Pretoria', '0001', 'residential', 495.00, 15.00, 'delivered'),
(1, 15, 'Sheketli Mochaki', '12 Main Road', 'Cape Town', '8001', 'residential', 310.00, 15.00, 'delivered'),
(13, 18, 'Bongani Mahlangu', '23 Mountain View', 'Cape Town', '8001', 'residential', 325.00, 15.00, 'dispatched'),
(8, 19, 'Sipho Mokoena', '33 Church Street', 'Polokwane', '0700', 'residential', 325.00, 15.00, 'pending'),
(21, 20, 'Thandeka Ngcobo', '22 Pearl Road', 'Pietermaritzburg', '3201', 'work', 235.00, 15.00, 'delivered'),
(18, 21, 'Dineo Radebe', '78 Emerald Lane', 'Bloemfontein', '9301', 'residential', 465.00, 15.00, 'dispatched'),
(24, 22, 'Vusi Shabangu', '55 Jade Avenue', 'Nelspruit', '1200', 'residential', 395.00, 15.00, 'delivered'),
(27, 23, 'Nhlanhla Koza', '88 Crystal Street', 'Upington', '8800', 'residential', 355.00, 15.00, 'pending'),
(10, 24, 'Lerato Dlamini', '12 Jacaranda Street', 'Pretoria', '0001', 'work', 305.00, 15.00, 'delivered'),
(1, 25, 'Sheketli Mochaki', '12 Main Road', 'Cape Town', '8001', 'residential', 295.00, 15.00, 'dispatched'),
(13, 26, 'Bongani Mahlangu', '23 Mountain View', 'Cape Town', '8001', 'residential', 905.00, 15.00, 'pending'),
(8, 27, 'Sipho Mokoena', '33 Church Street', 'Polokwane', '0700', 'residential', 195.00, 15.00, 'delivered'),
(21, 28, 'Thandeka Ngcobo', '22 Pearl Road', 'Pietermaritzburg', '3201', 'residential', 175.00, 15.00, 'delivered'),
(18, 29, 'Dineo Radebe', '78 Emerald Lane', 'Bloemfontein', '9301', 'work', 395.00, 15.00, 'dispatched'),
(24, 30, 'Vusi Shabangu', '55 Jade Avenue', 'Nelspruit', '1200', 'residential', 1215.00, 15.00, 'pending'),
(27, 31, 'Nhlanhla Koza', '88 Crystal Street', 'Upington', '8800', 'residential', 465.00, 15.00, 'delivered'),
(10, 32, 'Lerato Dlamini', '12 Jacaranda Street', 'Pretoria', '0001', 'residential', 135.00, 15.00, 'delivered'),
(1, 33, 'Sheketli Mochaki', '12 Main Road', 'Cape Town', '8001', 'residential', 535.00, 15.00, 'dispatched'),
(13, 34, 'Bongani Mahlangu', '23 Mountain View', 'Cape Town', '8001', 'work', 235.00, 15.00, 'pending');

-- ============================================================
-- SEED DATA: tblMessages (10+ entries)
-- ============================================================
INSERT INTO tblMessages (senderID, receiverID, clothingID, subject, messageBody, isRead) VALUES
(1, 2, 1, 'Question about Levi''s Jeans', 'Hi! Are these jeans still available? What is the waist measurement?', 1),
(2, 1, 1, 'Re: Question about Levi''s Jeans', 'Yes, still available! Waist is 32 inches, fits true to size.', 1),
(4, 5, 3, 'Interested in the dress', 'Is the dress suitable for a formal event? What color is the floral pattern?', 0),
(8, 3, 4, 'H&M Coat inquiry', 'Does the coat have an inside pocket? Looking for something practical.', 1),
(3, 8, 4, 'Re: H&M Coat inquiry', 'Yes, it has two inside pockets! Very practical for everyday use.', 0),
(10, 6, 12, 'Re: Premium League Hoodie', 'Hi! Is the hoodie still available? Can you provide more photos?', 0),
(6, 10, 12, 'Re: Premium League Hoodie', 'Yes it is! I''ll upload more photos today. It''s in great condition.', 1),
(13, 2, 8, 'Fossil Watch', 'Is the battery still working? Does it come with warranty papers?', 0),
(18, 11, 21, 'Nike Tech Fleece', 'What is the exact color? Is it more charcoal or black?', 1),
(21, 12, 23, 'H&M Maxi Dress', 'What is the length of the dress? I''m 5''6.', 0),
(1, 6, NULL, 'General Question', 'Do you ship nationwide? What are the delivery times?', 1),
(6, 1, NULL, 'Re: General Question', 'Yes we ship nationwide! Usually 3-5 business days for delivery.', 0);

-- ============================================================
-- End of ClothingStore.sql
-- ============================================================
