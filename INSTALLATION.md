# 📋 Step-by-Step XAMPP Setup & Installation Guide

Follow these 6 simple steps to run ECO Mart POS locally on your computer:

---

### Step 1: Install & Start XAMPP
1. Download and install [XAMPP for Windows/Mac/Linux](https://www.apachefriends.org/) (ensure PHP 8+ is included).
2. Open the **XAMPP Control Panel**.
3. Click **Start** for both **Apache** and **MySQL**.

---

### Step 2: Extract Project Files to `htdocs`
1. Extract the downloaded `SmartMart-POS-System.zip` or copy the `smartmart-pos` folder directly into:
   ```
   C:\xampp\htdocs\smartmart-pos
   ```
2. Verify that `C:\xampp\htdocs\smartmart-pos\index.php` exists.

---

### Step 3: Import Database in phpMyAdmin
1. Open your browser and navigate to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click **New** in the left sidebar to create a new database.
3. The included SQL script creates the `eco_mart_pos` database automatically. If you create it manually first, name it:
   ```
   eco_mart_pos
   ```
   Collation: `utf8mb4_unicode_ci` (or default) and click **Create**.
4. Select `eco_mart_pos`, then click the **Import** tab in the top navigation bar.
5. Click **Choose File** and browse to:
   ```
   C:\xampp\htdocs\smartmart-pos\database\smartmart_pos.sql
   ```
6. Scroll down and click **Import** (or **Go**).
7. You should see a green success message with the tables and demo data created.

> **Important:** `database/smartmart_pos.sql` drops and recreates `eco_mart_pos`. Export/backup any existing data before importing it; do not import over a live database unless you intend to replace it with the demo database.

---

### Step 4: Verify Database Configuration (Optional)
Check `config/database.php` if your local MySQL has a custom password:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // default in XAMPP is empty
define('DB_NAME', 'eco_mart_pos');
```

---

### Step 5: Launch the Application
Open your web browser and go to:
```
http://localhost/smartmart-pos/
```

---

### Step 6: Log In with Demo Credentials

* **System Admin**:
  * Email: `admin@smartmart.com`
  * Password: `Admin@123`
* **Store Manager**:
  * Email: `manager@smartmart.com`
  * Password: `Manager@123`
* **Lane Cashier**:
  * Email: `cashier@smartmart.com`
  * Password: `Cashier@123`

---

## ⚡ Keyboard Shortcuts in POS Terminal
* `F1` : Focus Barcode Scanner / Search Input
* `F2` : Toggle Payment Tender Modal
* `F4` : Clear / Void Active Shopping Cart
* `Enter` : Add Scanned Item / Complete Payment
* `Esc` : Close Modals / Dialogs
