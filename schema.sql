-- =====================================================================
-- ChargeHub.pk - Complete MySQL Database Export for phpMyAdmin
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `chargehub_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `chargehub_db`;

-- ---------------------------------------------------------------------
-- 1. Table structure for `admins`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default login: admin / admin123
INSERT INTO `admins` (`id`, `username`, `password_hash`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$oY7b4/Tj/RrkM62fE1iQJ.N5p7p2P6yE32q0p1n1N5U7E2f0e0y0W', 'support@chargehub.pk', NOW());

-- ---------------------------------------------------------------------
-- 2. Table structure for `categories`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `badge_text` VARCHAR(50) DEFAULT NULL,
  `models_count_text` VARCHAR(50) DEFAULT NULL,
  `bg_color` VARCHAR(50) DEFAULT 'bg-blue-50',
  `description` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cat_sort` (`sort_order`),
  INDEX `idx_cat_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `image_url`, `badge_text`, `models_count_text`, `bg_color`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Party Speakers', 'speakers', '🔊', 'HOT', '18 Models', 'bg-blue-50', 'High-power bluetooth party speakers with punchy bass and dynamic LED rings.', 1, 1),
(2, 'Earbuds & ANC', 'earbuds', '🎧', NULL, '24 Models', 'bg-indigo-50', 'Active Noise Cancelling earbuds, transparency modes and studio audio.', 2, 1),
(3, 'SmartWatches', 'smartwatches', '⌚', NULL, '15 Models', 'bg-emerald-50', 'Next-gen fitness tracking, AMOLED displays and IP68 waterproof smartwatches.', 3, 1),
(4, 'Fast Chargers', 'chargers', '⚡', NULL, '32 Models', 'bg-amber-50', 'Next-generation Gallium Nitride multi-port high wattage wall and desktop chargers.', 4, 1),
(5, 'Cables & Hubs', 'cables', '🔌', NULL, '40+ Models', 'bg-purple-50', 'Military-grade DuPont Kevlar braided 100W-240W cables and multi-port hubs.', 5, 1),
(6, 'Data Storage', 'storage', '💾', NULL, '19 Models', 'bg-rose-50', 'Ultra-fast NVMe portable SSDs, high-speed flash drives and memory cards.', 6, 1);

-- ---------------------------------------------------------------------
-- 3. Table structure for `brands`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_brand_sort` (`sort_order`),
  INDEX `idx_brand_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `brands` (`id`, `name`, `slug`, `logo_url`, `website`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Anker', 'anker', 'images/v7/brands/brand_anker_2x.png', 'https://anker.com', 'World leader in mobile charging and pioneering GaNPrime technology.', 1, 1),
(2, 'A4TECH', 'a4tech', 'images/v7/brands/brand_a4tech_2x.png', 'https://a4tech.com', 'Ergonomic precision peripherals, cables and smart desktop tech.', 2, 1),
(3, 'Baseus', 'baseus', 'images/v7/brands/brand_baseus_2x.png', 'https://baseus.com', 'Base on User design aesthetics with smart digital power displays.', 3, 1),
(4, 'Apple', 'apple', 'images/v7/brands/brand_apple_2x.png', 'https://apple.com', 'Certified 20W to 140W USB-C PD power accessories and MagSafe gear.', 4, 1),
(5, 'BOYA', 'boya', 'images/v7/brands/brand_boya_2x.png', 'https://boya-mic.com', 'Professional studio audio gear, wireless lavalier microphones and adapters.', 5, 1),
(6, 'JisuLife', 'jisulife', 'images/v7/brands/brand_jisulife_2x.png', 'https://jisulife.com', 'Portable handheld turbo fans, personal cooling devices and rechargeable gadgets.', 6, 1),
(7, 'Mcdodo', 'mcdodo', 'images/v7/brands/brand_mcdodo_2x.png', 'https://mcdodo.co', 'Smart auto-disconnect digital wattage display cables and ultra-slim GaN chargers.', 7, 1),
(8, 'PITAKA', 'pitaka', 'images/v7/brands/brand_pitaka_2x.png', 'https://ipitaka.com', 'Aerospace-grade aramid fiber cases, magnetic wireless charging stands and docks.', 8, 1),
(9, 'Samsung', 'samsung', 'images/v7/brands/brand_samsung_2x.png', 'https://samsung.com', 'Super Fast Charging 2.0 45W PPS official adapters and genuine cords.', 9, 1),
(10, 'UGREEN', 'ugreen', 'images/v7/brands/brand_ugreen_2x.png', 'https://ugreen.com', 'Innovative Nexode GaN chargers and premium high-speed interconnects.', 10, 1),
(11, 'JBL', 'jbl', 'images/v7/brands/brand_jbl_2x.png', 'https://jbl.com', 'World-class wireless speakers, Pro Sound earbuds and rugged portable audio.', 11, 1),
(12, 'Xiaomi', 'xiaomi', 'images/v7/brands/brand_xiaomi_2x.png', 'https://mi.com', 'High-capacity fast-charging power banks, compact GaN chargers and smart home power.', 12, 1);

-- ---------------------------------------------------------------------
-- 4. Table structure for `products`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `category_id` INT DEFAULT NULL,
  `brand_id` INT DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `sku` VARCHAR(100) DEFAULT NULL,
  `badge_text` VARCHAR(150) DEFAULT '⚡ PD 3.1 140W PEAK',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `original_price` DECIMAL(10,2) DEFAULT NULL,
  `stock_status` VARCHAR(100) DEFAULT 'In Stock (Lahore Hub)',
  `stock_quantity` INT DEFAULT 50,
  `warranty_text` VARCHAR(255) DEFAULT '18-Month Official Warranty & Swap Guarantee',
  `rating` DECIMAL(2,1) DEFAULT 4.9,
  `reviews_count` INT DEFAULT 348,
  `orders_count` INT DEFAULT 1420,
  `main_image` VARCHAR(500) NOT NULL,
  `gallery_images` TEXT DEFAULT NULL,
  `short_description` TEXT DEFAULT NULL,
  `full_description` LONGTEXT DEFAULT NULL,
  `features` TEXT DEFAULT NULL,
  `specs` TEXT DEFAULT NULL,
  `power_matrix` TEXT DEFAULT NULL,
  `bundle_name` VARCHAR(255) DEFAULT 'Bundle + Anker 140W Bio-Braided Cable (6ft)',
  `bundle_price` DECIMAL(10,2) DEFAULT 24999.00,
  `bundle_original_price` DECIMAL(10,2) DEFAULT 32499.00,
  `is_featured` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_prod_cat` (`category_id`),
  INDEX `idx_prod_brand` (`brand_id`),
  INDEX `idx_prod_featured` (`is_featured`),
  INDEX `idx_prod_active` (`is_active`),
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prod_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (
  `id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `badge_text`,
  `price`, `original_price`, `stock_status`, `stock_quantity`, `warranty_text`,
  `rating`, `reviews_count`, `orders_count`,
  `main_image`, `gallery_images`,
  `short_description`, `full_description`, `features`, `specs`, `power_matrix`,
  `bundle_name`, `bundle_price`, `bundle_original_price`,
  `is_featured`, `is_active`
) VALUES
(
  1, 1, 1,
  'Anker Prime 140W GaN Wall Charger (3-Port 2C1A Fast GaNPrime™)',
  'anker-prime-140w-gan-charger',
  'ANK-PRM-140W-GRY',
  '⚡ PD 3.1 140W PEAK',
  22499.00, 28999.00,
  'In Stock (Lahore Hub)', 45,
  '18-Month Official Warranty & Swap Guarantee',
  4.9, 348, 1420,
  'images/product_detail/gallery_1_hero.jpg',
  '[\"images/product_detail/gallery_1_hero.jpg\", \"images/product_detail/gallery_2_ports.jpg\", \"images/product_detail/gallery_3_speed.jpg\", \"images/product_detail/gallery_4_thermal.jpg\"]',
  'Ultra-compact multi-port power. Fast charge your MacBook Pro, iPhone 16 & Galaxy simultaneously at peak speeds with intelligent dynamic power distribution.',
  '<p>The Anker Prime 140W GaN Wall Charger is the ultimate all-in-one charging solution designed for demanding professionals, content creators, and multi-device tech enthusiasts.</p><p>Featuring cutting-edge GaNPrime™ technology, PowerIQ 4.0 with Dynamic Power Distribution, and ActiveShield 2.0 real-time temperature monitoring, this charger outputs up to 140W single-port charging capable of fueling a 16-inch MacBook Pro from 0 to 50% in only 28 minutes.</p>',
  '[\"140W USB-C PD 3.1 Peak: Charges 16\\\" MacBook Pro from 0 to 50% in only 28 minutes\", \"GaNPrime Multi-Port Architecture: 2x USB-C + 1x USB-A in an ultra-compact footprint 53% smaller than Apple standard 140W brick\", \"ActiveShield 2.0 Safety Shield: Real-time intelligent temperature monitoring takes over 3,000,000 checks per day\", \"Universal Compatibility: Full support for PD 3.1, PPS (Samsung 45W 2.0), QC 4.0+, and Apple 2.4A protocols\"]',
  '{\"Model\":\"A2341 (Anker Prime 737 Series)\",\"Total Output\":\"140W Max (PD 3.1)\",\"Ports\":\"3 Ports (2x USB-C + 1x USB-A)\",\"Plug Type\":\"UK 3-Pin / Foldable US Pins\",\"Laptop Tier\":\"Full Speed 140W (MacBook Pro 16\\\" 0-50% in 28 mins, Dell XPS, ThinkPad)\",\"Phone Speed\":\"Super Fast 45W PPS (Samsung S25/S24 Ultra, iPhone 16 Pro Max 27W+)\",\"Protocols\":\"USB PD 3.1, PPS 2.0 (5A), QC 4.0+, SCP, FCP, Apple 2.4A\",\"GaN Tech\":\"GaNPrime™ Gen 3 Semiconductor (Gallium Nitride)\",\"Safety System\":\"ActiveShield™ 2.0 Real-time Dynamic Temperature Monitoring (3M checks/day)\",\"Input\":\"100-240V ~ 50/60Hz 2.0A (Worldwide Compatible)\",\"Dimensions\":\"67 × 31 × 73 mm\",\"Weight\":\"235g (53% lighter than standard 140W bricks)\",\"Compat Laptops\":\"MacBook Pro 16\\\"/14\\\", MacBook Air M2/M3/M4, Dell XPS 15/16, Lenovo ThinkPad X1, HP Spectre, ASUS ROG Flow\",\"Compat Phones\":\"iPhone 16 / 15 / 14 Series, Samsung Galaxy S25 / S24 / S23 Ultra, Google Pixel 9 / 8 Pro, OnePlus 12\",\"Compat Other\":\"iPad Pro / Air, Steam Deck, ROG Ally, DJI Drones, Nintendo Switch, MagSafe Chargers\"}',
  '{\"single_port\":\"USB-C1: 140W Max (PD 3.1 EPR 28V/5A) | USB-C2: 140W Max | USB-A: 22.5W Max\",\"dual_port\":\"Laptop + Phone: USB-C1 (100W) + USB-C2 (40W) = 140W Peak Simultaneous Fast Charge\",\"multi_port\":\"All Active: USB-C1 (100W Laptop) + USB-C2 (22.5W Phone) + USB-A (17.5W Accessories) = 140W Total Max\",\"allocation_note\":\"PowerIQ 4.0 dynamic sharing automatically recalculates optimal wattage when devices are plugged or unplugged.\"}',
  'Bundle + Anker 140W Bio-Braided Cable (6ft)',
  24999.00, 32499.00,
  1, 1
),
(
  2, 1, 2,
  'Ugreen Nexode 100W 4-Port GaN Desktop Charger',
  'ugreen-nexode-100w-gan-charger',
  'UGR-NEX-100W-4P',
  '🔥 TOP SELLER',
  16999.00, 21999.00,
  'In Stock (Karachi Hub)', 32,
  '12-Month Official Replacement Warranty',
  4.8, 215, 890,
  'images/Spotlight Section/1.png',
  '[\"images/Spotlight Section/1.png\", \"images/Spotlight Section/2.png\"]',
  '4-in-1 desktop charging station with 3x USB-C and 1x USB-A ports powering 4 devices simultaneously.',
  '<p>The Ugreen Nexode 100W GaN charger provides lightning-fast charging for laptops, tablets, phones, and peripherals with intelligent thermal regulation.</p>',
  '[\"100W High Speed USB-C Power Delivery\", \"4-Port Multi-Device Charging simultaneously\", \"Thermal Guard Temperature Regulation\", \"Broad device compatibility\"]',
  '{\"Model\":\"CD226\",\"Total Output\":\"100W Max\",\"Ports\":\"3x USB-C + 1x USB-A\",\"Input\":\"100-240V ~ 50/60Hz 1.8A\",\"Weight\":\"280g\"}',
  '{\"single_port\":{\"usb_c1\":\"100W Max\",\"usb_c2\":\"100W Max\",\"usb_a\":\"22.5W Max\"}}',
  'Bundle with 100W Type-C 2-Meter Cable',
  18499.00, 23999.00,
  1, 1
),
(
  3, 2, 1,
  'ChargeHub 240W Indestructible Kevlar Braided USB-C Cable',
  'chargehub-240w-kevlar-braided-cable',
  'CH-CBL-240W-6FT',
  '⚡ 240W PD 3.1 & E-MARKER',
  3499.00, 4999.00,
  'In Stock (Lahore Hub)', 120,
  'Lifetime Breakage Warranty',
  5.0, 520, 2800,
  'images/v7/chargers/hero_fast_cable_ultra.jpg',
  '[\"images/v7/chargers/hero_fast_cable_ultra.jpg\"]',
  'Military-grade DuPont Kevlar braided cable certified for 50,000+ bends with real-time digital wattage display.',
  '<p>Engineered to outlast your devices. Built with Kevlar fiber cores, zinc alloy housings, and gold-plated terminals.</p>',
  '[\"240W Peak USB-C Power Delivery 3.1\", \"50,000+ Severe Bend Tested\", \"Smart E-Marker 2.0 Chip with Safety Voltage Cutoff\", \"Zinc Alloy Shell with Anti-Fray Strain Relief\"]',
  '{\"Length\":\"6ft (1.8m)\",\"Wattage\":\"240W Max (48V/5A)\",\"Data Speed\":\"480 Mbps USB 2.0\",\"Materials\":\"DuPont Kevlar Braided Nylon\"}',
  '{\"output\":\"Supports 5W to 240W continuous high-current flow without voltage drop\"}',
  'Bundle 2x 240W Cables (Save Rs. 1,000)',
  5999.00, 9999.00,
  1, 1
),
(
  4, 4, 3,
  'Baseus MagPro 3-in-1 Magnetic Qi2 Wireless Charger',
  'baseus-magpro-3-in-1-wireless-dock',
  'BAS-MAG-3IN1-QI2',
  '🧲 SNAP & CHARGE QI2',
  14999.00, 18999.00,
  'In Stock (Islamabad Hub)', 28,
  '12-Month Swap Guarantee',
  4.7, 184, 650,
  'images/v7/chargers/hero_wireless_station.jpg',
  '[\"images/v7/chargers/hero_wireless_station.jpg\"]',
  'Fast Qi2 15W wireless charging for iPhone, Apple Watch rapid dock, and AirPods pad on a heavy weighted aluminum stand.',
  '<p>Streamline your desk or nightstand with 3-in-1 magnetic wireless power. Clean, weighted, and MagSafe certified.</p>',
  '[\"15W True Qi2 Fast Magnetic Charging for iPhone\", \"Apple Watch Official Rapid Charging Module\", \"5W Base Pad for AirPods and Galaxy Buds\", \"Weighted Non-Slip Aluminum Chassis\"]',
  '{\"Total Output\":\"25W Total\",\"Phone Output\":\"15W Qi2\",\"Watch Output\":\"5W\",\"AirPods Output\":\"5W\",\"Chassis\":\"Aviation Aluminum Alloy\"}',
  '{\"matrix\":\"Simultaneous 15W + 5W + 5W full-speed continuous charge\"}',
  'Bundle with 30W GaN Wall Adapter',
  16999.00, 21999.00,
  1, 1
),
(
  5, 3, 1,
  'Anker Prime 20,000mAh Power Bank (200W Output & Smart Display)',
  'anker-prime-20000mah-200w-powerbank',
  'ANK-PRM-20K-200W',
  '🔋 AIRLINE APPROVED 200W',
  28999.00, 34999.00,
  'In Stock (Lahore Hub)', 19,
  '18-Month Official Warranty',
  4.9, 142, 480,
  'images/v7/chargers/hero_powerstation_ultra.jpg',
  '[\"images/v7/chargers/hero_powerstation_ultra.jpg\"]',
  'Supercharged 200W total output with smart digital display showing battery health, cycle count, and real-time wattage per port.',
  '<p>Charge two 100W laptops simultaneously while on the move. Fast recharge the power bank itself in just 1 hour 15 minutes.</p>',
  '[\"200W Total Combined Power Output\", \"Dual 100W USB-C Ports simultaneously\", \"Full Color Smart Digital LCD Display Screen\", \"Airline Compliant 72Wh battery capacity\"]',
  '{\"Capacity\":\"20,000mAh (72Wh)\",\"Max Output\":\"200W Total\",\"Ports\":\"2x USB-C + 1x USB-A\",\"Display\":\"Real-Time TFT LCD\",\"Recharge Time\":\"75 mins at 100W Input\"}',
  '{\"matrix\":\"Dual 100W USB-C output simultaneously or 100W + 65W + 35W\"}',
  'Bundle with 100W Anker GaN Charger',
  39999.00, 48999.00,
  1, 1
);

-- ---------------------------------------------------------------------
-- 5. Table structure for `hero_slides`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `hero_slides`;
CREATE TABLE `hero_slides` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` TEXT DEFAULT NULL,
  `badge_text` VARCHAR(150) DEFAULT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `nav_color` VARCHAR(30) DEFAULT '#0052cc',
  `cta_link` VARCHAR(255) DEFAULT 'explore.php',
  `cta_text` VARCHAR(100) DEFAULT 'Explore Catalog',
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_hero_sort` (`sort_order`),
  INDEX `idx_hero_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hero_slides` (`id`, `title`, `subtitle`, `badge_text`, `image_url`, `nav_color`, `cta_link`, `cta_text`, `sort_order`, `is_active`) VALUES
(1, 'Next-Gen Fast Charging.', 'Ultra-compact multi-port power. Fast charge your MacBook Pro, iPhone 16 & Galaxy simultaneously at peak speeds with intelligent dynamic power distribution.', '⚡ GENUINE POWER TECH • OFFICIAL STORE', 'images/Hero Banner.jpg', '#0052cc', 'explore.php', 'Explore Catalog', 1, 1),
(2, 'Engineered to Endure.', 'Military-grade DuPont Kevlar braided cables with real-time digital wattage display and smart E-Marker chip. Certified for 50,000+ bends without fraying.', '🔌 INDESTRUCTIBLE KEVLAR • 240W PD', 'images/v7/chargers/hero_fast_cable_ultra.jpg', '#0a194f', 'explore.php', 'Shop Genuine Cables', 2, 1),
(3, 'Magnetic Wireless Power.', 'Fast Qi2 15W wireless charging for iPhone, Apple Watch rapid dock, and AirPods pad. Premium weighted aluminum stand for a tidy minimalist setup.', '🧲 SNAP & CHARGE • 3-IN-1 WIRELESS', 'images/v7/chargers/hero_wireless_station.jpg', '#0e3d54', 'product-detail.php?slug=baseus-magpro-3-in-1-wireless-dock', 'Shop Wireless Docks', 3, 1),
(4, 'All-In-One Power Hubs.', 'High-performance charging stations with dual 100W USB-C PD, high-speed USB-A & surge-protected AC outlets to power your complete workspace.', '⚡ DESKTOP HUB • WORKSTATION POWER', 'images/v7/chargers/hero_powerstation_ultra.jpg', '#003d99', 'explore.php', 'Shop Power Stations', 4, 1);

-- ---------------------------------------------------------------------
-- 6. Table structure for `spotlight_items`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `spotlight_items`;
CREATE TABLE `spotlight_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `link_url` VARCHAR(255) DEFAULT 'product-detail.php',
  `badge_text` VARCHAR(100) DEFAULT 'HOT DEAL',
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_spot_sort` (`sort_order`),
  INDEX `idx_spot_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `spotlight_items` (`id`, `title`, `image_url`, `link_url`, `badge_text`, `sort_order`, `is_active`) VALUES
(1, 'ChargeHub Spotlight Feature 1', 'images/Spotlight Section/1.png', 'product-detail.php?slug=anker-prime-140w-gan-charger', 'HOT DEAL', 1, 1),
(2, 'ChargeHub Spotlight Feature 2', 'images/Spotlight Section/2.png', 'product-detail.php?slug=ugreen-nexode-100w-4port-charger', 'FEATURED', 2, 1),
(3, 'ChargeHub Spotlight Feature 3', 'images/Spotlight Section/3.png', 'product-detail.php?slug=chargehub-armor-240w-kevlar-cable', 'POPULAR', 3, 1),
(4, 'ChargeHub Spotlight Feature 4', 'images/Spotlight Section/4.png', 'product-detail.php?slug=baseus-magpro-3-in-1-wireless-dock', 'NEW', 4, 1),
(5, 'ChargeHub Spotlight Feature 5', 'images/Spotlight Section/5.png', 'product-detail.php?slug=anker-prime-20000mah-200w-powerbank', 'EXCLUSIVE', 5, 1);


-- ---------------------------------------------------------------------
-- 7. Table structure for `new_arrival_items`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `new_arrival_items`;
CREATE TABLE `new_arrival_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(100) DEFAULT '',
  `image_url` VARCHAR(500) NOT NULL,
  `link_url` VARCHAR(255) DEFAULT 'product-detail.php',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `original_price` DECIMAL(10,2) NULL,
  `discount_pct` INT DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 0.0,
  `badge_text` VARCHAR(100) DEFAULT 'NEW ARRIVAL',
  `spec_1_title` VARCHAR(100) DEFAULT '',
  `spec_1_sub` VARCHAR(100) DEFAULT '',
  `spec_2_title` VARCHAR(100) DEFAULT '',
  `spec_2_sub` VARCHAR(100) DEFAULT '',
  `spec_3_title` VARCHAR(100) DEFAULT '',
  `spec_3_sub` VARCHAR(100) DEFAULT '',
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_new_sort` (`sort_order`),
  INDEX `idx_new_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `new_arrival_items` (`id`, `title`, `sku`, `image_url`, `link_url`, `price`, `original_price`, `discount_pct`, `rating`, `badge_text`, `spec_1_title`, `spec_1_sub`, `spec_2_title`, `spec_2_sub`, `spec_3_title`, `spec_3_sub`, `sort_order`, `is_active`) VALUES
(1, 'CHARGEHUB PRO | 65W GAN', 'CH-65W', 'images/1000x1000 transparent PNG/1.png', 'product-detail.php?slug=anker-prime-140w-gan-charger', 5511.00, 6599.00, 16, 0.0, 'NEW ARRIVAL', 'Quad Mic', 'ENC Support', 'ANC', 'Support', 'Upto 7 Hrs', 'Playback', 1, 1),
(2, 'VOLTRA PRO | 100W GAN', 'CH-100PRO', 'images/1000x1000 transparent PNG/2.png', 'product-detail.php?slug=ugreen-nexode-100w-4port-charger', 7139.00, 8499.00, 16, 0.0, 'NEW ARRIVAL', '22.5Watt', 'Max Output', 'Attached', 'Cables', '30000mAh', 'Capacity', 2, 1),
(3, 'HYPERSPEED | 45W DUAL', 'CH-45D', 'images/1000x1000 transparent PNG/3.png', 'product-detail.php?slug=ugreen-nexode-100w-4port-charger', 2624.00, 4499.00, 42, 0.0, 'NEW ARRIVAL', '45Watt', 'Max Output', 'GaN', 'Technology', 'PD Port', 'Output', 3, 1),
(4, 'ARMOR CABLE | 240W PD', 'CH-240C', 'images/1000x1000 transparent PNG/4.png', 'product-detail.php?slug=chargehub-armor-240w-kevlar-cable', 5774.00, 6499.00, 11, 0.0, 'NEW ARRIVAL', 'Open Ear', 'Design', '11mm', 'Bass Drivers', 'AI', 'System', 4, 1),
(5, 'NANO CUBE | 30W ULTRA', 'CH-30N', 'images/1000x1000 transparent PNG/5.png', 'product-detail.php?slug=anker-prime-140w-gan-charger', 1899.00, 2999.00, 37, 0.0, 'NEW ARRIVAL', '30W Ultra', 'Pocket Cube', 'Type-C PD', 'Fast iPhone', 'Safety Guard', 'Thermal Safe', 5, 1);


-- ---------------------------------------------------------------------
-- 8. Table structure for `flash_sale_items`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `flash_sale_items`;
CREATE TABLE `flash_sale_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `ribbon_text` VARCHAR(100) DEFAULT '10.10 sale is live',
  `discount_text` VARCHAR(50) DEFAULT '14% Off',
  `image_url` VARCHAR(500) NOT NULL,
  `link_url` VARCHAR(255) DEFAULT 'product-detail.php',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `original_price` DECIMAL(10,2) NULL,
  `rating` DECIMAL(2,1) DEFAULT 0.0,
  `color_swatch` VARCHAR(50) DEFAULT '#000000',
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_flash_sort` (`sort_order`),
  INDEX `idx_flash_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `flash_sale_items` (`id`, `title`, `ribbon_text`, `discount_text`, `image_url`, `link_url`, `price`, `original_price`, `rating`, `color_swatch`, `sort_order`, `is_active`) VALUES
(1, 'L-265 Ronaq | 12 inch Wireless Speaker', '10.10 sale is live', '14% Off', 'images/v7/flash_sale/l265_ronaq.png', 'product-detail.php?slug=l265-ronaq-12inch-speaker', 17859.00, 20999.00, 0.0, '#000000', 1, 1),
(2, 'L-267 Jamster | 30 Watt Wireless Speaker', '10.10 sale is live', '22% Off', 'images/v7/flash_sale/l267_jamster.png', 'product-detail.php?slug=l267-jamster-30w-speaker', 7799.00, 9999.00, 0.0, '#000000', 2, 1),
(3, 'L-268 Jamster Pro | 50 Watt Wireless Speaker', '10.10 sale is live', '18% Off', 'images/v7/flash_sale/l268_jamster_pro.png', 'product-detail.php?slug=l268-jamster-pro-50w-speaker', 11479.00, 13999.00, 0.0, '#000000', 3, 1),
(4, 'L-269 Jamster Prime | 60 Watt Wireless Speaker', '10.10 sale is live', '20% Off', 'images/v7/flash_sale/l269_jamster_prime.png', 'product-detail.php?slug=l269-jamster-prime-60w-speaker', 14399.00, 17999.00, 0.0, '#000000', 4, 1),
(5, 'L-270 Predox | Wireless Soundbar', '10.10 sale is live', '15% Off', 'images/v7/flash_sale/l270_predox.png', 'product-detail.php?slug=l270-predox-wireless-soundbar', 8499.00, 9999.00, 0.0, '#000000', 5, 1);
