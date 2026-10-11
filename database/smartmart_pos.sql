-- =======================================================
-- ECO Mart POS - Supermarket Management System Database
-- Target Database: MySQL 5.7+ / MariaDB 10.3+ (XAMPP Standard)
-- Default Currency: Sri Lankan Rupee (LKR / Rs.)
-- =======================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

DROP DATABASE IF EXISTS `eco_mart_pos`;
CREATE DATABASE IF NOT EXISTS `eco_mart_pos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eco_mart_pos`;

-- --------------------------------------------------------
-- Table: roles
-- --------------------------------------------------------
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Administrator', 'Full system access, settings, user management, and sensitive accounting'),
(2, 'Manager', 'Inventory, purchases, returns, reports, cashier oversight'),
(3, 'Cashier', 'POS billing terminal, customer search, shift cash tracking');

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pre-hashed passwords for Admin@123, Manager@123, Cashier@123 ($2y$10$...)
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `phone`, `status`, `last_login`) VALUES
(1, 1, 'Kasun Perera (Admin)', 'admin@smartmart.com', '$2y$10$sM1lHjZgWpTqIe1Z6F5aDu4w7Gk1yU8P1e0F3q7A9n4z2C8u0E7e2', '+94 77 123 4567', 'active', NOW()),
(2, 2, 'Nimali Jayasinghe (Manager)', 'manager@smartmart.com', '$2y$10$sM1lHjZgWpTqIe1Z6F5aDu4w7Gk1yU8P1e0F3q7A9n4z2C8u0E7e2', '+94 71 987 6543', 'active', NOW()),
(3, 3, 'Dinesh Silva (Cashier)', 'cashier@smartmart.com', '$2y$10$sM1lHjZgWpTqIe1Z6F5aDu4w7Gk1yU8P1e0F3q7A9n4z2C8u0E7e2', '+94 76 555 4321', 'active', NOW());

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-shopping-basket',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `status`) VALUES
(1, 'Grocery & Staples', 'grocery', 'fa-seedling', 'active'),
(2, 'Beverages', 'beverages', 'fa-coffee', 'active'),
(3, 'Dairy & Eggs', 'dairy', 'fa-cheese', 'active'),
(4, 'Bakery & Biscuits', 'bakery', 'fa-bread-slice', 'active'),
(5, 'Fruits & Vegetables', 'produce', 'fa-apple-alt', 'active'),
(6, 'Meat & Seafood', 'meat', 'fa-drumstick-bite', 'active'),
(7, 'Personal Care', 'personal-care', 'fa-pump-soap', 'active'),
(8, 'Household & Cleaning', 'household', 'fa-spray-can', 'active'),
(9, 'Snacks & Confectionery', 'snacks', 'fa-cookie', 'active'),
(10, 'Baby Products', 'baby', 'fa-baby', 'active');

-- --------------------------------------------------------
-- Table: brands
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `brands` (`id`, `name`, `status`) VALUES
(1, 'Munchee', 'active'),
(2, 'Maliban', 'active'),
(3, 'Anchor', 'active'),
(4, 'Dilmah', 'active'),
(5, 'Elephant House', 'active'),
(6, 'Sunlight', 'active'),
(7, 'Signal', 'active'),
(8, 'Astra', 'active'),
(9, 'Kotmale', 'active'),
(10, 'Highland', 'active'),
(11, 'Vim', 'active'),
(12, 'Surf Excel', 'active'),
(13, 'Prima', 'active'),
(14, 'Nestle', 'active'),
(15, 'Keells Krest', 'active');

-- --------------------------------------------------------
-- Table: suppliers
-- --------------------------------------------------------
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text DEFAULT NULL,
  `outstanding_balance` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `suppliers` (`id`, `company_name`, `contact_person`, `email`, `phone`, `address`, `outstanding_balance`) VALUES
(1, 'Ceylon Cold Stores PLC', 'Rohan Jayawardena', 'orders@elephanthouse.lk', '+94 11 247 5000', 'No 148, Vauxhall Street, Colombo 02', 45000.00),
(2, 'Fonterra Brands Lanka', 'Dilani Mendis', 'sales@fonterra.lk', '+94 11 482 8888', 'Biyagama Free Trade Zone, Malwana', 120000.00),
(3, 'Ceylon Biscuits Limited (Munchee)', 'Bandula Fernando', 'supply@muncheelk.com', '+94 11 500 0000', 'Pannipitiya, Colombo', 35000.00),
(4, 'Unilever Sri Lanka Ltd', 'Sanjaya Wickramasinghe', 'orders@unilever.lk', '+94 11 218 0000', 'Commercial Centre, Grandpass, Colombo 14', 85000.00),
(5, 'Dilmah Ceylon Tea Co.', 'Anushka Karunaratne', 'distribution@dilmahtea.com', '+94 11 482 2000', '111 Negombo Road, Peliyagoda', 18000.00);

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `barcode` varchar(50) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `tax_percent` decimal(5,2) DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) NOT NULL DEFAULT 10,
  `reorder_level` int(11) NOT NULL DEFAULT 20,
  `unit` varchar(20) DEFAULT 'pcs',
  `expiry_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default_product.svg',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode_unique` (`barcode`),
  UNIQUE KEY `sku_unique` (`sku`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `fk_products_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `barcode`, `sku`, `name`, `category_id`, `brand_id`, `supplier_id`, `cost_price`, `selling_price`, `discount_price`, `tax_percent`, `quantity`, `min_stock_level`, `reorder_level`, `unit`, `expiry_date`, `image`, `status`) VALUES
(1, '8934567890101', 'SM-GRO-001', 'Keeri Samba Rice 5kg', 1, 13, 1, 1150.00, 1350.00, NULL, 0.00, 48, 15, 30, 'bag', '2027-06-30', 'rice.jpg', 'active'),
(2, '8934567890102', 'SM-GRO-002', 'White Sugar 1kg', 1, 13, 1, 230.00, 275.00, NULL, 0.00, 120, 25, 50, 'kg', '2027-12-31', 'sugar.jpg', 'active'),
(3, '8934567890103', 'SM-GRO-003', 'Red Dhal (Mysore) 1kg', 1, 13, 1, 290.00, 340.00, NULL, 0.00, 85, 20, 40, 'kg', '2027-08-15', 'dhal.jpg', 'active'),
(4, '8934567890104', 'SM-DRY-001', 'Anchor Full Cream Milk Powder 400g', 3, 3, 2, 920.00, 1080.00, 1040.00, 0.00, 65, 20, 40, 'pkt', '2027-05-20', 'anchor.jpg', 'active'),
(5, '8934567890105', 'SM-BEV-001', 'Dilmah Premium Ceylon Tea 100g', 2, 4, 5, 310.00, 390.00, NULL, 2.50, 90, 15, 30, 'box', '2028-01-10', 'tea.jpg', 'active'),
(6, '8934567890106', 'SM-BEV-002', 'Nescafe Classic Instant Coffee 50g', 2, 14, 2, 620.00, 750.00, NULL, 2.50, 42, 10, 20, 'jar', '2027-09-30', 'coffee.jpg', 'active'),
(7, '8934567890107', 'SM-BEV-003', 'Elephant House Ginger Beer (EGB) 400ml', 2, 5, 1, 110.00, 140.00, NULL, 0.00, 150, 30, 60, 'btl', '2027-02-28', 'egb.jpg', 'active'),
(8, '8934567890108', 'SM-BAK-001', 'Munchee Super Cream Cracker 490g', 4, 1, 3, 390.00, 460.00, 430.00, 0.00, 75, 15, 30, 'pkt', '2026-11-20', 'cracker.jpg', 'active'),
(9, '8934567890109', 'SM-BAK-002', 'Maliban Lemon Puff 200g', 4, 2, 3, 195.00, 240.00, NULL, 0.00, 80, 20, 35, 'pkt', '2026-12-15', 'lemonpuff.jpg', 'active'),
(10, '8934567890110', 'SM-DRY-002', 'Kotmale Fresh Milk Bottle 1L', 3, 9, 2, 420.00, 490.00, NULL, 0.00, 8, 10, 25, 'btl', '2026-09-05', 'milk.jpg', 'active'),
(11, '8934567890111', 'SM-DRY-003', 'Highland Processed Cheese 200g (8 Portions)', 3, 10, 2, 610.00, 720.00, NULL, 0.00, 28, 10, 20, 'box', '2027-03-15', 'cheese.jpg', 'active'),
(12, '8934567890112', 'SM-DRY-004', 'Astra Margarine Fat Spread 250g', 3, 8, 4, 380.00, 450.00, NULL, 0.00, 52, 12, 25, 'tub', '2027-04-10', 'astra.jpg', 'active'),
(13, '8934567890113', 'SM-DRY-005', 'Farm Fresh Brown Eggs (Pack of 10)', 3, 13, 1, 410.00, 480.00, NULL, 0.00, 35, 15, 30, 'pack', '2026-09-10', 'eggs.jpg', 'active'),
(14, '8934567890114', 'SM-PRD-001', 'Nuwara Eliya Carrots 1kg', 5, 13, 1, 280.00, 360.00, NULL, 0.00, 4, 10, 20, 'kg', '2026-08-25', 'carrots.jpg', 'active'),
(15, '8934567890115', 'SM-PRD-002', 'Red Onions (Lunu) 1kg', 5, 13, 1, 380.00, 460.00, NULL, 0.00, 45, 15, 30, 'kg', '2026-09-15', 'onions.jpg', 'active'),
(16, '8934567890116', 'SM-PRD-003', 'Fresh Cavendish Bananas (Ambul) 1kg', 5, 13, 1, 210.00, 280.00, NULL, 0.00, 30, 10, 20, 'kg', '2026-08-24', 'bananas.jpg', 'active'),
(17, '8934567890117', 'SM-MEA-001', 'Skinless Chicken Breast 1kg', 6, 15, 1, 1150.00, 1380.00, 1290.00, 0.00, 22, 8, 15, 'kg', '2026-08-23', 'chicken.jpg', 'active'),
(18, '8934567890118', 'SM-MEA-002', 'Keells Krest Chicken Sausages 500g', 6, 15, 1, 740.00, 890.00, NULL, 0.00, 34, 10, 20, 'pkt', '2026-10-30', 'sausages.jpg', 'active'),
(19, '8934567890119', 'SM-HOU-001', 'Sunlight Lemon Lime Laundry Soap 115g', 8, 6, 4, 125.00, 150.00, NULL, 0.00, 140, 25, 50, 'bar', '2028-12-31', 'sunlight.jpg', 'active'),
(20, '8934567890120', 'SM-HOU-002', 'Surf Excel Matic Washing Powder 1kg', 8, 12, 4, 760.00, 920.00, 880.00, 0.00, 40, 10, 25, 'pkt', '2028-06-30', 'surfexcel.jpg', 'active'),
(21, '8934567890121', 'SM-HOU-003', 'Vim Lemon Dishwash Bar 200g', 8, 11, 4, 135.00, 165.00, NULL, 0.00, 95, 20, 40, 'bar', '2028-12-31', 'vim.jpg', 'active'),
(22, '8934567890122', 'SM-PER-001', 'Signal Triple Action Toothpaste 120g', 7, 7, 4, 260.00, 320.00, NULL, 0.00, 85, 15, 30, 'box', '2028-04-30', 'signal.jpg', 'active'),
(23, '8934567890123', 'SM-PER-002', 'Lifebuoy Total 10 Antibacterial Soap 100g', 7, 4, 4, 145.00, 180.00, NULL, 0.00, 110, 20, 40, 'bar', '2028-08-31', 'lifebuoy.jpg', 'active'),
(24, '8934567890124', 'SM-SNK-001', 'Elephant House French Vanilla Ice Cream 1L', 9, 5, 1, 620.00, 750.00, NULL, 0.00, 18, 5, 12, 'tub', '2027-01-31', 'icecream.jpg', 'active'),
(25, '8934567890125', 'SM-GRO-004', 'Prima All-Purpose Wheat Flour 1kg', 1, 13, 1, 190.00, 230.00, NULL, 0.00, 60, 15, 30, 'pkt', '2027-02-15', 'flour.jpg', 'active');

-- Normalize product image filenames to the files shipped in asset/images/products.
UPDATE `products` SET `image` = CASE `id`
  WHEN 1 THEN 'samba.png'
  WHEN 2 THEN 'sugar.png'
  WHEN 3 THEN 'dhal.png'
  WHEN 4 THEN 'anchor.png'
  WHEN 5 THEN 'dilmha.png'
  WHEN 6 THEN 'nescafe.png'
  WHEN 7 THEN 'gingerbeer.png'
  WHEN 8 THEN 'creamcaker.png'
  WHEN 9 THEN 'lemonpuff.png'
  WHEN 10 THEN 'milk.png'
  WHEN 11 THEN 'cheese.png'
  WHEN 12 THEN 'astra.png'
  WHEN 13 THEN 'eggs.png'
  WHEN 14 THEN 'carrorts.png'
  WHEN 15 THEN 'onion.png'
  WHEN 16 THEN 'bananas.png'
  WHEN 17 THEN 'chicken.png'
  WHEN 18 THEN 'default_product.svg'
  WHEN 19 THEN 'soap.png'
  WHEN 20 THEN 'surfexcel.png'
  WHEN 21 THEN 'vim.png'
  WHEN 22 THEN 'signal.png'
  WHEN 23 THEN 'lifeboy.png'
  WHEN 24 THEN 'icecream.png'
  WHEN 25 THEN 'default_product.svg'
  ELSE `image`
END
WHERE `id` BETWEEN 1 AND 25;

-- --------------------------------------------------------
-- Table: customers
-- --------------------------------------------------------
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `loyalty_points` int(11) NOT NULL DEFAULT 0,
  `total_spend` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone_unique` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `loyalty_points`, `total_spend`, `status`) VALUES
(1, 'Walk-in Customer', '0000000000', 'walkin@smartmart.lk', 'Store Direct', 0, 0.00, 'active'),
(2, 'Kamal Rajapaksha', '+94 77 345 6789', 'kamal.r@gmail.com', '12/4 Temple Road, Nugegoda', 185, 37000.00, 'active'),
(3, 'Shirani Senanayake', '+94 71 890 1234', 'shirani.s@yahoo.com', '45 Galle Road, Colombo 03', 340, 68000.00, 'active'),
(4, 'Mahesh Abeywickrama', '+94 76 234 5678', 'mahesh.abey@outlook.com', '88 Kandy Road, Kiribathgoda', 90, 18000.00, 'active'),
(5, 'Priyanka De Alwis', '+94 72 678 9012', 'priyanka.alwis@gmail.com', '24 Havelock Road, Colombo 05', 420, 84000.00, 'active');

-- --------------------------------------------------------
-- Table: cashier_shifts
-- --------------------------------------------------------
CREATE TABLE `cashier_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `opening_float` decimal(10,2) NOT NULL DEFAULT 5000.00,
  `cash_sales` decimal(10,2) NOT NULL DEFAULT 0.00,
  `card_sales` decimal(10,2) NOT NULL DEFAULT 0.00,
  `online_sales` decimal(10,2) NOT NULL DEFAULT 0.00,
  `refunds_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expected_cash` decimal(10,2) NOT NULL DEFAULT 5000.00,
  `closing_cash` decimal(10,2) DEFAULT NULL,
  `discrepancy` decimal(10,2) DEFAULT 0.00,
  `opened_at` datetime NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_shifts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cashier_shifts` (`id`, `user_id`, `opening_float`, `cash_sales`, `card_sales`, `online_sales`, `refunds_total`, `expected_cash`, `closing_cash`, `discrepancy`, `opened_at`, `closed_at`, `status`, `notes`) VALUES
(1, 3, 5000.00, 18450.00, 14200.00, 3200.00, 0.00, 23450.00, 23450.00, 0.00, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 18 HOUR), 'closed', 'Morning Shift 1 - Balanced perfectly'),
(2, 3, 5000.00, 9420.00, 6800.00, 1450.00, 0.00, 14420.00, NULL, 0.00, NOW(), NULL, 'open', 'Active Shift Terminal 01');

-- --------------------------------------------------------
-- Table: sales
-- --------------------------------------------------------
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL DEFAULT 1,
  `shift_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','card','online','split') NOT NULL DEFAULT 'cash',
  `payment_status` enum('paid','partial','refunded') DEFAULT 'paid',
  `loyalty_points_earned` int(11) DEFAULT 0,
  `loyalty_points_redeemed` int(11) DEFAULT 0,
  `status` enum('completed','cancelled','held') DEFAULT 'completed',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_unique` (`invoice_number`),
  KEY `user_id` (`user_id`),
  KEY `customer_id` (`customer_id`),
  KEY `shift_id` (`shift_id`),
  CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_sales_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `fk_sales_shift` FOREIGN KEY (`shift_id`) REFERENCES `cashier_shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sales` (`id`, `invoice_number`, `user_id`, `customer_id`, `shift_id`, `subtotal`, `discount_total`, `tax_total`, `grand_total`, `paid_amount`, `change_amount`, `payment_method`, `payment_status`, `loyalty_points_earned`, `loyalty_points_redeemed`, `status`, `created_at`) VALUES
(1, 'INV-20260818-001', 3, 2, 1, 3810.00, 50.00, 19.00, 3779.00, 4000.00, 221.00, 'cash', 'paid', 18, 0, 'completed', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(2, 'INV-20260818-002', 3, 3, 1, 5640.00, 100.00, 25.00, 5565.00, 5565.00, 0.00, 'card', 'paid', 27, 0, 'completed', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(3, 'INV-20260819-001', 3, 4, 2, 2830.00, 0.00, 9.75, 2839.75, 3000.00, 160.25, 'cash', 'paid', 14, 0, 'completed', DATE_SUB(NOW(), INTERVAL 2 HOUR));

-- --------------------------------------------------------
-- Table: sale_items
-- --------------------------------------------------------
CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_saleitems_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saleitems_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `cost_price`, `unit_price`, `discount_amount`, `tax_amount`, `line_total`) VALUES
(1, 1, 1, 1, 1150.00, 1350.00, 0.00, 0.00, 1350.00),
(2, 1, 4, 1, 920.00, 1080.00, 40.00, 0.00, 1040.00),
(3, 1, 5, 2, 310.00, 390.00, 10.00, 19.00, 789.00),
(4, 1, 8, 1, 390.00, 460.00, 0.00, 0.00, 460.00),
(5, 2, 17, 2, 1150.00, 1380.00, 90.00, 0.00, 2670.00),
(6, 2, 20, 1, 760.00, 920.00, 40.00, 0.00, 880.00),
(7, 2, 6, 2, 620.00, 750.00, 0.00, 25.00, 1525.00),
(8, 3, 2, 2, 230.00, 275.00, 0.00, 0.00, 550.00),
(9, 3, 13, 2, 410.00, 480.00, 0.00, 0.00, 960.00),
(10, 3, 7, 4, 110.00, 140.00, 0.00, 0.00, 560.00),
(11, 3, 5, 1, 310.00, 390.00, 0.00, 9.75, 399.75);

-- --------------------------------------------------------
-- Table: stock_movements (Audit of every stock in/out/adjust/sale/return)
-- --------------------------------------------------------
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `type` enum('sale','purchase','return','adjustment','damaged','expired') NOT NULL,
  `quantity` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `reference_id` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_movements_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `stock_movements` (`id`, `product_id`, `type`, `quantity`, `balance_after`, `reference_id`, `user_id`, `notes`) VALUES
(1, 1, 'purchase', 50, 50, 'PO-2026-001', 1, 'Initial Supplier Stock Received'),
(2, 1, 'sale', -1, 49, 'INV-20260818-001', 3, 'POS Checkout Sale'),
(3, 4, 'sale', -1, 65, 'INV-20260818-001', 3, 'POS Checkout Sale'),
(4, 5, 'sale', -2, 90, 'INV-20260818-001', 3, 'POS Checkout Sale');

-- --------------------------------------------------------
-- Table: purchases
-- --------------------------------------------------------
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','received','cancelled') DEFAULT 'pending',
  `received_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_unique` (`po_number`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `fk_purchases_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `purchases` (`id`, `po_number`, `supplier_id`, `user_id`, `total_amount`, `status`, `received_at`, `notes`) VALUES
(1, 'PO-2026-001', 1, 1, 55000.00, 'received', DATE_SUB(NOW(), INTERVAL 3 DAY), 'Stock restock for staples and beverages'),
(2, 'PO-2026-002', 2, 2, 42000.00, 'pending', NULL, 'Fresh dairy delivery scheduled for tomorrow');

-- --------------------------------------------------------
-- Table: purchase_items
-- --------------------------------------------------------
CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_pitems_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pitems_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `quantity`, `unit_cost`, `total_cost`) VALUES
(1, 1, 1, 30, 1150.00, 34500.00),
(2, 1, 7, 100, 110.00, 11000.00),
(3, 1, 17, 8, 1150.00, 9200.00);

-- --------------------------------------------------------
-- Table: returns (Customer returns & refunds)
-- --------------------------------------------------------
CREATE TABLE `returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_number` varchar(50) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_method` enum('cash','card','credit_note') DEFAULT 'cash',
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ret_num_unique` (`return_number`),
  KEY `sale_id` (`sale_id`),
  CONSTRAINT `fk_returns_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: return_items
-- --------------------------------------------------------
CREATE TABLE `return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_refund` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  CONSTRAINT `fk_ritems_return` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: promotions & discounts
-- --------------------------------------------------------
CREATE TABLE `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `type` enum('percentage','fixed','bogo') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `promotions` (`id`, `title`, `type`, `value`, `category_id`, `product_id`, `min_spend`, `start_date`, `end_date`, `status`) VALUES
(1, 'Weekend Bakery Special (10% Off)', 'percentage', 10.00, 4, NULL, 500.00, '2026-08-01', '2026-12-31', 'active'),
(2, 'Surf Excel Super Saver Flat Rs.40 Off', 'fixed', 40.00, NULL, 20, 0.00, '2026-08-01', '2026-12-31', 'active');

-- --------------------------------------------------------
-- Table: expenses
-- --------------------------------------------------------
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `expenses` (`id`, `category`, `amount`, `description`, `user_id`, `expense_date`) VALUES
(1, 'Electricity & Utilities', 24500.00, 'CEB Commercial electricity bill for July/August', 1, '2026-08-15'),
(2, 'Store Packaging & Bags', 8500.00, 'Biodegradable supermarket shopping bags 5000pcs', 2, '2026-08-16'),
(3, 'Chiller Maintenance', 6000.00, 'Beverage refrigerator compressor servicing', 1, '2026-08-17');

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('low_stock','out_of_stock','expiring','purchase_pending','system') NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notifications` (`id`, `type`, `title`, `message`, `is_read`) VALUES
(1, 'low_stock', 'Low Stock Alert: Kotmale Fresh Milk Bottle 1L', 'Stock is currently 8 btl (below threshold of 10 btl). Reorder suggested.', 0),
(2, 'low_stock', 'Low Stock Alert: Nuwara Eliya Carrots 1kg', 'Stock is currently 4 kg (below threshold of 10 kg).', 0),
(3, 'expiring', 'Expiring Product Alert: Skinless Chicken Breast 1kg', 'Batch expires on 2026-08-23. Mark down or rotate stock immediately.', 0),
(4, 'purchase_pending', 'Pending Purchase Order: PO-2026-002', 'Fonterra Brands delivery pending confirmation.', 0);

-- --------------------------------------------------------
-- Table: audit_logs
-- --------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT '127.0.0.1',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`) VALUES
(1, 1, 'SYSTEM_INIT', 'ECO Mart POS database and security initialized', '127.0.0.1'),
(2, 3, 'SHIFT_OPEN', 'Cashier Dinesh Silva opened Shift Terminal 01 with float Rs. 5,000.00', '127.0.0.1'),
(3, 3, 'SALE_COMPLETED', 'Invoice INV-20260819-001 completed (Rs. 2,839.75)', '127.0.0.1');

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'store_name', 'ECO Mart'),
(2, 'store_tagline', 'Freshness & Quality Guaranteed Every Day'),
(3, 'store_address', 'No. 245 Galle Road, Bambalapitiya, Colombo 04, Sri Lanka'),
(4, 'store_phone', '+94 11 258 9900 / +94 77 123 4567'),
(5, 'store_email', 'contact@smartmart.lk'),
(6, 'tax_rate', '0.00'),
(7, 'currency_symbol', 'Rs.'),
(8, 'currency_code', 'LKR'),
(9, 'invoice_prefix', 'INV-'),
(10, 'loyalty_spend_per_point', '200.00'),
(11, 'loyalty_point_value', '1.00'),
(12, 'receipt_header', 'WELCOME TO ECO MART'),
(13, 'receipt_footer', 'Thank you for shopping with us! Please check goods and change before leaving counter. Exchange possible within 3 days with receipt.'),
(14, 'low_stock_default_threshold', '10'),
(15, 'theme_mode', 'light');

COMMIT;
