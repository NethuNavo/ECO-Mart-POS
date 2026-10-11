# ECO Mart POS

A PHP and MySQL point-of-sale and inventory system for local use with XAMPP.

## Features

- Barcode-based checkout, product search, and receipts
- Product, stock, supplier, and purchase management
- Customers, returns, and cashier shifts
- Sales reports, user roles, and audit logs

## Requirements

- XAMPP with PHP 8+ and MySQL/MariaDB
- Apache and MySQL enabled in the XAMPP Control Panel
- PDO MySQL extension and an internet connection for CDN-loaded fonts, icons, and UI libraries

## Quick start

1. Clone `https://github.com/NethuNavo/ECO-Mart-POS-.git` into `C:\xampp\htdocs\smartmart-pos` (or copy the project there).
2. Start **Apache** and **MySQL** in XAMPP.
3. In phpMyAdmin, import [`database/smartmart_pos.sql`](database/smartmart_pos.sql). **This SQL script drops and recreates `eco_mart_pos`**; back up existing data before importing.
4. Check [`config/database.php`](config/database.php) for MySQL connection settings and `BASE_URL`; update them if your local setup differs.
5. Open [http://localhost/smartmart-pos/](http://localhost/smartmart-pos/) and sign in with a demo account listed in [`INSTALLATION.md`](INSTALLATION.md).

For full setup notes, see [`INSTALLATION.md`](INSTALLATION.md).

> **Demo only:** The seeded accounts are public demo credentials; change them before using real data. Card and online checkout are recorded by the demo app but are not connected to a payment provider.
