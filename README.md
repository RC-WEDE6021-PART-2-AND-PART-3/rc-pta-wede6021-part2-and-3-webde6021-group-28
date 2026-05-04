# rc-pta-wede6021-part2-and-3-webde6021-group-28
Pastimes Online Clothing Store

<div align="center">

# Pastimes

### Your Online Clothing Store

A full-stack e-commerce web application for trading second-hand branded clothing online.

[![PHP Version](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

[Features](#features) | [Quick Start](#quick-start)

</div>

---

## Overview

**Pastimes** is a production-quality PHP web application for trading second-hand branded clothing online. Inspired by platforms like [ThredUp](https://www.thredup.com) and [Vinted](https://www.vinted.com), it enables registered users to buy and sell pre-loved clothing in a secure, sustainable environment.

The platform promotes circular fashion by tracking the environmental impact of each purchase, showing users how much CO2 and water they save compared to buying new clothing.

### Why Pastimes?

- **Sustainable Fashion** - Every purchase saves an average of 3kg CO2 and 2,700L of water
- **Secure Transactions** - Built with security best practices and input validation
- **Role-Based Access** - Separate experiences for buyers, sellers, and administrators
- **Modern UI/UX** - Responsive design that works on all devices
- **Complete E-Commerce** - Full shopping cart, checkout, and order management

---

## Features

### For Buyers

| Feature | Description |
|---------|-------------|
| **Product Browsing** | Browse products by category, brand, size, condition, and price range |
| **Advanced Search** | Real-time search with live filtering and recent search history |
| **Shopping Cart** | Add items, adjust quantities, and manage cart before checkout |
| **Secure Checkout** | Complete purchases with delivery address management |
| **Order Tracking** | View order status and history |
| **Messaging** | Contact sellers directly about products |
| **Profile Management** | Manage personal information and saved addresses |

### For Sellers

| Feature | Description |
|---------|-------------|
| **Product Listing** | List items with photos, descriptions, and pricing |
| **AI Price Suggestions** | Get intelligent price recommendations based on brand, category, and condition |
| **Seller Dashboard** | Track listings, sales, and revenue statistics |
| **Listing Management** | Edit, update, or remove your listings |
| **Message Center** | Respond to buyer inquiries |

### For Administrators

| Feature | Description |
|---------|-------------|
| **User Verification** | Approve or reject new user registrations |
| **Listing Approval** | Review and approve product listings before they go live |
| **Order Management** | Update order statuses and track fulfillment |
| **Broadcast Messaging** | Send announcements to all users, sellers, or buyers |
| **Analytics Dashboard** | View platform statistics with Chart.js visualizations |
| **User Management** | Full CRUD operations on user accounts |

### Sustainability Tracking

- Environmental impact displayed on order confirmation
- CO2 savings calculated per item
- Water savings displayed in liters
- Promotes awareness of sustainable fashion choices

---

## Technology Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| **Backend** | PHP 8.x | Server-side scripting with OOP |
| **Database** | MySQL 8 | Relational data storage |
| **Frontend** | HTML5, CSS3 | Semantic markup and styling |
| **JavaScript** | ES6+ | DOM manipulation, AJAX, validation |
| **Server** | Apache (XAMPP) | Local development server |
| **Fonts** | Google Fonts | Playfair Display + Inter |
| **Icons** | Font Awesome 6 | UI iconography |
| **Charts** | Chart.js | Admin dashboard visualizations |

---

## Quick Start

Get up and running in under 5 minutes:

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- Web browser (Chrome, Firefox, Safari, Edge)
- Code editor (VS Code recommended)

### Database Setup

1. Open your browser and navigate to:
   ```
   http://localhost/pastimes/scripts/loadClothingStore.php
   ```

2. Click **"Load All Data"** to automatically:
   - Create the `ClothingStore` database
   - Create all 5 tables with proper relationships
   - Load seed data (users, admins, products, orders)

### Access the Application

| Page | URL |
|------|-----|
| **Homepage** | `http://localhost/pastimes/` |
| **Admin Panel** | `http://localhost/pastimes/admin/login.php` |
| **Shop** | `http://localhost/pastimes/shop.php` |

---

## Project Structure

```
pastimes/
├── admin/                          # Admin panel
│   ├── includes/
│   │   ├── admin-header.php       # Admin navigation
│   │   └── admin-footer.php       # Admin footer
│   ├── dashboard.php              # Admin dashboard with charts
│   ├── users.php                  # User management (CRUD)
│   ├── listings.php               # Product approval workflow
│   ├── orders.php                 # Order status management
│   ├── messages.php               # Broadcast messaging
│   ├── login.php                  # Admin authentication
│   └── logout.php                 # Admin session termination
│
├── css/                           # Stylesheets
│   ├── style.css                  # Main application styles
│   ├── auth.css                   # Login/register styles
│   └── admin.css                  # Admin panel styles
│
├── database/                      # Database files
│   ├── ClothingStore.sql          # Complete database schema + data
│   ├── userData.txt               # User seed data
│   ├── adminData.txt              # Admin seed data
│   ├── clothesData.txt            # Product seed data
│   └── ordersData.txt             # Order seed data
│
├── images/                        # Image assets
│   ├── README.md                  # Image guidelines
│   └── README.txt                 # Detailed image instructions
│
├── includes/                      # Shared PHP components
│   ├── classes/                   # OOP classes
│   │   ├── User.php              # User model & methods
│   │   ├── Clothing.php          # Product model & methods
│   │   ├── Order.php             # Order model & methods
│   │   └── Message.php           # Message model & methods
│   ├── DBConn.php                # Database connection (MySQLi)
│   ├── functions.php             # Utility functions
│   ├── auth.php                  # Authentication helpers
│   ├── header.php                # Site header/navigation
│   └── footer.php                # Site footer
│
├── js/                           # JavaScript modules
│   ├── main.js                   # Core functionality
│   ├── validate.js               # Form validation
│   ├── filter.js                 # Shop filtering
│   ├── sell.js                   # Seller listing form
│   ├── messages.js               # Messaging functionality
│   └── admin.js                  # Admin panel interactions
│
├── scripts/                      # Setup & maintenance scripts
│   ├── createTable.php           # Create tblUser table
│   ├── loadClothingStore.php     # Full database setup
│   ├── import_images.php         # Bulk image import
│   └── smokeTest.php             # System verification
│
├── index.php                     # Homepage
├── register.php                  # User registration
├── login.php                     # User authentication
├── logout.php                    # Session termination
├── shop.php                      # Product catalog
├── product.php                   # Product detail page
├── cart.php                      # Shopping cart
├── checkout.php                  # Checkout process
├── order-confirmation.php        # Order success page
├── sell.php                      # Create product listing
├── dashboard.php                 # User dashboard
├── my-orders.php                 # Order history
├── messages.php                  # User messaging
├── profile.php                   # Profile management
├── about.php                     # About page
├── privacy.php                   # Privacy policy
│
├── README.md                     # This file
├── SETUP_GUIDE.md               # Detailed setup instructions
├── CHECKLIST.md                 # Implementation checklist
├── QUICK_START.txt              # Quick reference guide
└── FILE_INDEX.txt               # Complete file listing
```

---

## Database Schema

### Table Descriptions

| Table | Purpose | Key Fields |
|-------|---------|------------|
| **tblUser** | User accounts | userID, email, username, role (buyer/seller/both), status |
| **tblAdmin** | Admin accounts | adminID, username, passwordHash |
| **tblClothes** | Product listings | clothingID, sellerID, title, brand, price, status |
| **tblOrder** | Purchase records | orderID, buyerID, clothingID, totalAmount, status |
| **tblMessages** | User messaging | messageID, senderID, receiverID, messageBody |

### Database Configuration

```php
// includes/DBConn.php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'ClothingStore';
```

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## Acknowledgments

- Inspired by [ThredUp](https://www.thredup.com) and [Vinted](https://www.vinted.com)
- Icons by [Font Awesome](https://fontawesome.com)
- Fonts by [Google Fonts](https://fonts.google.com)
- Charts by [Chart.js](https://www.chartjs.org)

---

## A YouTube video presentation showcasing the website application of Pastimes Online Clothing Store

https://youtu.be/eXVb8InFZ7A

---

<div align="center">

**Built with purpose. Shop sustainably.**

[Back to Top](#pastimes)

</div>
