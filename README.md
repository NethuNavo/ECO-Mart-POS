# 🛒 ECO Mart POS
### Supermarket Point of Sale & Inventory Management System

A PHP and MySQL-based POS and Inventory Management System designed for supermarkets, grocery stores, and retail shops. Built for local development using **XAMPP**, ECO Mart POS helps simplify daily sales operations, stock management, and store administration.

<p align="center">
  <img src="asset/images/eco-mart-logo.svg" alt="ECO Mart Logo" width="130">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8+">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/XAMPP-Local%20Server-FB7A24?style=for-the-badge&logo=xampp&logoColor=white" alt="XAMPP">
  <img src="https://img.shields.io/badge/Platform-Windows-0078D4?style=for-the-badge&logo=windows&logoColor=white" alt="Windows">
</p>

---

## 📋 Table of Contents

- [✨ Features](#-features)
- [🛠️ Technologies](#️-technologies)
- [📌 Requirements](#-requirements)
- [🚀 Installation](#-installation)
- [⚙️ Database Configuration](#️-database-configuration)
- [🔐 Demo Access](#-demo-access)
- [📂 Project Structure](#-project-structure)
- [⚠️ Important Notes](#️-important-notes)
- [👩‍💻 Developer](#-developer)

---

## ✨ Features

### 🧾 Point of Sale
- Barcode-based product checkout
- Quick product search
- Sales processing and receipt generation
- Customer and transaction management

### 📦 Inventory Management
- Product and category management
- Stock level tracking
- Supplier management
- Purchase management

### 💰 Sales & Cashier Operations
- Cashier shift management
- Cash transaction tracking
- Product returns management
- Sales reports and transaction history

### 👥 User & System Management
- User roles and access control
- Audit logs for relevant system activities
- Centralized product, sales, and inventory records

---

## 🛠️ Technologies

| Technology | Purpose |
|---|---|
| PHP 8+ | Server-side application logic |
| MySQL / MariaDB | Database management |
| Apache | Local web server |
| HTML, CSS & JavaScript | User interface |
| XAMPP | Local development environment |

---

## 📌 Requirements

Before installation, ensure you have:

- Windows operating system
- [XAMPP](https://www.apachefriends.org/) with PHP 8+
- MySQL or MariaDB
- Apache and MySQL services enabled
- PDO MySQL extension
- Internet access for externally hosted fonts, icons, and UI libraries, if required

---

## 🚀 Installation

### Step 1: Clone the Repository

Open your terminal and run:

```bash
git clone https://github.com/NethuNavo/ECO-Mart-POS-.git smartmart-pos
```

Alternatively, download the repository as a ZIP file and extract it.

Place the project inside:

```text
C:\xampp\htdocs\smartmart-pos
```

### Step 2: Start XAMPP

1. Open the XAMPP Control Panel.
2. Start **Apache**.
3. Start **MySQL**.

Make sure both services are running.

### Step 3: Import the Database

1. Open [phpMyAdmin](http://localhost/phpmyadmin/).
2. Select the **Import** tab.
3. Choose the following SQL file from your project:

   `database/smartmart_pos.sql`

4. Click **Import** or **Go** to execute the SQL script.

> ⚠️ **Warning:** The supplied SQL script drops and recreates the `eco_mart_pos` database. Back up existing data before importing.

### Step 4: Configure the Application

Open the database configuration file:

```text
config/database.php
```

Verify the following settings:

- Database host
- Database name: `eco_mart_pos`
- MySQL username and password
- Application `BASE_URL`, if configured in this file

For a standard local XAMPP setup, the database host is commonly `localhost` and the username is often `root`. Use the password configured on your computer.

### Step 5: Launch ECO Mart POS

Open your browser and visit:

**http://localhost/smartmart-pos/**

Log in using the demonstration credentials documented in `INSTALLATION.md`.

🎉 **ECO Mart POS is ready to explore!**

---

## ⚙️ Database Configuration

The application uses MySQL or MariaDB to store its operational data.

| Setting | Expected value |
|---|---|
| Database name | `eco_mart_pos` |
| Database type | MySQL / MariaDB |
| SQL import file | `database/smartmart_pos.sql` |
| Configuration file | `config/database.php` |

Actual connection credentials depend on your local XAMPP configuration.

---

## 🔐 Demo Access

Demo account details are provided in `INSTALLATION.md`.

**Security recommendations:**
- Change demonstration passwords before using real business data.
- Never publish production credentials in a public repository.
- Do not use publicly seeded accounts for sensitive operations.
- Configure proper access controls before any production deployment.

**Payment limitation:** Card and online checkout transactions are recorded by the demo application but are not connected to a real payment gateway.

---

## 📂 Project Structure

The main project files are organized as follows:

```text
smartmart-pos/
├── asset/
│   └── images/
│       └── eco-mart-logo.svg
├── config/
│   └── database.php
├── database/
│   └── smartmart_pos.sql
├── INSTALLATION.md
└── README.md
```

*Note: This is an overview of the main files referenced by the installation instructions. Your actual project may contain additional directories and files.*

---

## ⚠️ Important Notes

- This README describes a local XAMPP installation.
- Confirm the project folder name matches the configured `BASE_URL`.
- Back up your database before importing the supplied SQL file.
- Use HTTPS, secure credentials, and appropriate application security controls if deploying beyond your local development environment.

---

## 👩‍💻 Developer

**Nethmini Jayamani**

Software Engineering Graduate | Web Development

