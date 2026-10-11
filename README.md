<div align="center">
  <img src="asset/images/eco-mart-logo.svg" alt="ECO Mart" width="110">
  <h1>ECO Mart POS</h1>
  <p><strong>A PHP and MySQL point-of-sale and store-management app for local XAMPP development.</strong></p>
  <p>
    <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white" alt="PHP 8 or newer">
    <img src="https://img.shields.io/badge/Database-MySQL%20%7C%20MariaDB-4479A1?logo=mysql&logoColor=white" alt="MySQL or MariaDB">
    <img src="https://img.shields.io/badge/UI-Bootstrap%205-7952B3?logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  </p>
</div>

ECO Mart POS is a browser-based supermarket checkout and management demo. It brings product lookup, checkout, inventory, customers, purchasing, returns, cashier shifts, and reports together in one application.

> **Project status:** Intended for local learning, demonstrations, and development with XAMPP. Review and harden the application before exposing it to the internet or using it for real payments or business data.

## Features

- **Checkout:** Find products by barcode, SKU, name, or category; manage cart quantities; calculate discounts and tax; and complete cash, card, or online-marked sales.
- **Receipts:** Display completed-sale receipts and print through the browser; includes thermal-style and A4 invoice pages.
- **Products and stock:** Maintain the product catalog, adjust stock, track stock movements, and view inventory values.
- **Purchasing:** Create supplier purchase orders and receive stock into inventory.
- **Customers:** Maintain customer records, look up loyalty balances, and award points on eligible sales.
- **Returns and expenses:** Record returns and store expenses.
- **Cashier shifts:** Open and close shifts, record the opening float, and review drawer totals.
- **Store operations:** Promotions, notifications, user roles, audit history, and sales reporting.
- **ECO Mart branding:** Forest-green and cream interface, product artwork, and a responsive sign-in screen.

## Technology

- PHP 8+ with PDO
- MySQL or MariaDB
- HTML, CSS, and JavaScript
- Bootstrap 5.3, Font Awesome 6, and Chart.js

Bootstrap, Font Awesome, Google Fonts, and Chart.js are loaded from CDNs, so the pages need an internet connection for those resources.

## Run locally with XAMPP

### Requirements

- XAMPP with Apache, MySQL/MariaDB, PHP 8+, and the PDO MySQL extension
- A modern web browser
- Internet access for the CDN-hosted UI resources

### Setup

1. Copy or clone the project into `C:\xampp\htdocs\smartmart-pos`. For Git:

   ```text
   git clone https://github.com/NethuNavo/ECO-Mart-POS-.git C:\xampp\htdocs\smartmart-pos
   ```

2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin/` and import `database/smartmart_pos.sql`. The script creates the `eco_mart_pos` database and its demo tables and records.
4. Check `config/database.php`. The defaults are for a local XAMPP setup (`root`, blank password, database `eco_mart_pos`). Update the connection settings if your local MySQL configuration differs. If you install the app in a different folder, update `BASE_URL` in that file to match.
5. Visit `http://localhost/smartmart-pos/` and sign in with one of the demo accounts below.

> **Database warning:** `database/smartmart_pos.sql` contains `DROP DATABASE IF EXISTS eco_mart_pos`. Importing it deletes and recreates that database. Back up any existing data first, and do not import this demo seed file over a live store database.

For a longer installation walkthrough, see [INSTALLATION.md](INSTALLATION.md).

## Demo accounts

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@smartmart.com` | `Admin@123` |
| Store Manager | `manager@smartmart.com` | `Manager@123` |
| POS Cashier | `cashier@smartmart.com` | `Cashier@123` |

> These are public demo credentials, not secure production credentials. Change or remove them before any deployment beyond a private local demo. Card and online checkout options record a sale in the demo application; they do **not** connect to a real payment processor.

## Repository map

```text
asset/       ECO Mart artwork and product images
config/      Database connection and application configuration
database/    Demo schema/seed and product-image migration
includes/    Shared layout, navigation, and UI helpers
pos/         Checkout screen and sale processing
products/    Product catalog management
inventory/   Inventory views and stock adjustments
purchases/   Purchase orders and receiving
customers/   Customer management
sales/       Sales history and receipts
returns/     Return processing
reports/     Sales reports
shifts/      Cashier shift management
settings/    Store configuration
```

## Database image migration

For an existing installation whose product rows still contain the original `.jpg` image names, run `database/link_product_images.sql` once in phpMyAdmin against the app database. New installs do not need this migration.

## Troubleshooting

- **Database connection error:** Confirm MySQL is running and the values in `config/database.php` match your XAMPP setup.
- **Page not found:** Confirm the project folder is under `htdocs` and `BASE_URL` matches that folder.
- **Styles or icons missing:** Check the internet connection because the UI libraries are loaded from CDNs.
- **No active cashier shift:** Open a shift before starting checkout when prompted.
