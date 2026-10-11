# ECO Mart Supermarket POS & Inventory Management System

A complete, production-grade Point of Sale (POS) and Inventory Management System designed specifically for modern supermarkets, grocery stores, and retail marts running locally on **XAMPP (Apache + MySQL + PHP 8+)**.

---

## 🚀 Key Features

* **High-Speed Barcode POS Fast-Lane**: Full keyboard shortcut navigation, auto-focus barcode scanner, fast search, line discounts, loyalty points redemption, cash tender with auto change calculation, and split card/online payment.
* **Dual Invoice Printing Modes**: Instant 80mm/58mm thermal receipt printing + Corporate A4 Tax Invoice with print-optimized CSS.
* **Live Inventory & Auto Stock Deduction**: Atomic database transactions (`FOR UPDATE`) ensure zero race conditions. Shelf stock instantly decrements upon checkout.
* **Procurement & Goods Receiving (PO)**: Supplier purchase orders with automatic stock increment and moving average cost tracking.
* **Customer Returns & Stock Restitution**: Process invoice refunds with automated stock replenishment and cashier drawer balancing.
* **Cashier Shift & Drawer Audit**: Open shifts with float, monitor cash/card totals live, and balance closing discrepancies with audit trail.
* **Customer Loyalty Program**: Tiered loyalty point accruals, telephone lookup, and store credit.
* **Supermarket Analytics & Profit Intelligence**: COGS gross margin calculation, category share breakdown, and top-selling SKU charts.
* **Role-Based Access Control (RBAC)**: Admin, Store Manager, and Fast-Lane Cashier roles with password hashing and session management.
* **Immutable Audit Trail**: Security ledger recording logins, sales, voids, returns, and stock adjustments.

---

## 🛠️ Technology Stack
* **Backend**: PHP 8.0+ (Vanilla, Object-Oriented with PDO Prepared Statements)
* **Database**: MySQL 5.7+ / MariaDB 10.4+
* **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Font Awesome 6.5, Chart.js

---

## 📥 Default Demo Credentials

| Role | Email / Username | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@smartmart.com` | `Admin@123` |
| **Store Manager** | `manager@smartmart.com` | `Manager@123` |
| **POS Cashier** | `cashier@smartmart.com` | `Cashier@123` |
