<?php
/**
 * ChargeHub.pk - Pure MySQL Database Connection & Initialization
 * 
 * Uses PDO with Prepared Statements to completely eliminate SQL Injection risks.
 * Strictly configured for MySQL / phpMyAdmin. SQLite is completely disabled.
 */

// Optional local configuration override (if db.local.php exists)
if (file_exists(__DIR__ . '/db.local.php')) {
    require_once __DIR__ . '/db.local.php';
}

// MySQL Connection Credentials (Standard phpMyAdmin / XAMPP / MAMP / WAMP)
if (!defined('DB_HOST'))   define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT'))   define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME'))   define('DB_NAME', getenv('DB_NAME') ?: 'chargehub_db');
if (!defined('DB_USER'))   define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS'))   define('DB_PASS', (getenv('DB_PASS') !== false) ? getenv('DB_PASS') : '');
if (!defined('DB_SOCKET')) define('DB_SOCKET', getenv('DB_SOCKET') ?: '');

$pdo = null;
$db_connected_driver = 'mysql';
$db_connection_notice = '';
$connection_errors = [];

// Prepare candidate MySQL connection targets
$candidates = [];

// 1. Explicit socket if defined
if (!empty(DB_SOCKET)) {
    $candidates[] = [
        'type' => 'socket',
        'socket' => DB_SOCKET,
        'label' => 'Configured Unix Socket (' . DB_SOCKET . ')'
    ];
}

// 2. Standard TCP host:port
$candidates[] = [
    'type' => 'tcp',
    'host' => DB_HOST,
    'port' => DB_PORT,
    'label' => 'MySQL TCP (' . DB_HOST . ':' . DB_PORT . ')'
];

// 3. If primary host is local, auto-probe common macOS/Linux sockets for zero-config XAMPP/MAMP/Homebrew support
if (in_array(DB_HOST, ['127.0.0.1', 'localhost', '::1'])) {
    $common_sockets = [
        '/tmp/mysql.sock',                                       // Homebrew macOS standard
        '/Applications/MAMP/tmp/mysql/mysql.sock',               // MAMP macOS
        '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',   // XAMPP macOS
        '/var/run/mysqld/mysqld.sock',                           // Debian/Ubuntu/Linux standard
        '/var/mysql/mysql.sock',                                 // macOS default system socket
    ];

    foreach ($common_sockets as $sock) {
        if (file_exists($sock)) {
            $candidates[] = [
                'type' => 'socket',
                'socket' => $sock,
                'label' => 'Detected Local Socket (' . $sock . ')'
            ];
        }
    }

    // Also check MAMP default port 8889 if 3306 is not the MAMP port
    if (DB_PORT != '8889') {
        $candidates[] = [
            'type' => 'tcp',
            'host' => DB_HOST,
            'port' => '8889',
            'label' => 'MAMP Alternate Port (8889)'
        ];
    }
}

// Attempt connection through candidate targets
$candidate_passes = array_unique([DB_PASS, '', 'root']);

foreach ($candidates as $cand) {
    if ($pdo) break;

    foreach ($candidate_passes as $passAttempt) {
        try {
            if ($cand['type'] === 'socket') {
                $serverDsn = "mysql:unix_socket=" . $cand['socket'] . ";charset=utf8mb4";
                $dbDsn     = "mysql:unix_socket=" . $cand['socket'] . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            } else {
                $serverDsn = "mysql:host=" . $cand['host'] . ";port=" . $cand['port'] . ";charset=utf8mb4";
                $dbDsn     = "mysql:host=" . $cand['host'] . ";port=" . $cand['port'] . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            }

            // 1. Connect to MySQL server and ensure database exists
            $initPdo = new PDO($serverDsn, DB_USER, $passAttempt, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2,
            ]);
            $initPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            unset($initPdo);

            // 2. Connect directly to target database
            $pdo = new PDO($dbDsn, DB_USER, $passAttempt, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Strictly enforce native prepared statements
            ]);

            $db_connected_driver = 'mysql';
            break 2; // Successfully connected!

        } catch (PDOException $ex) {
            $connection_errors[] = [
                'target' => $cand['label'] . ($passAttempt !== '' ? ' (pass: ' . substr($passAttempt, 0, 1) . '***)' : ' (empty pass)'),
                'error'  => $ex->getMessage()
            ];
        }
    }
}

// If MySQL is completely unreachable, display user-friendly setup instructions (NO SQLite fallback)
if (!$pdo) {
    render_mysql_error_screen($connection_errors);
    exit;
}

// Auto-initialize MySQL schema & initial seed data if not yet created
init_chargehub_database($pdo);

/**
 * Ensures pure MySQL schema exists and seeds initial data securely using prepared statements
 */
function init_chargehub_database(PDO $pdo) {
    // 1. Admins Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(150),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(150) UNIQUE NOT NULL,
        image_url VARCHAR(500),
        badge_text VARCHAR(50) DEFAULT NULL,
        models_count_text VARCHAR(50) DEFAULT NULL,
        bg_color VARCHAR(50) DEFAULT 'bg-blue-50',
        description TEXT,
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Brands Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(150) UNIQUE NOT NULL,
        logo_url VARCHAR(500),
        website VARCHAR(255),
        description TEXT,
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 4. Products Table (Complete Product Detail Page Requirements)
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        brand_id INT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        sku VARCHAR(100),
        badge_text VARCHAR(150) DEFAULT '⚡ PD 3.1 140W PEAK',
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        original_price DECIMAL(10,2),
        stock_status VARCHAR(100) DEFAULT 'In Stock (Lahore Hub)',
        stock_quantity INT DEFAULT 50,
        warranty_text VARCHAR(255) DEFAULT '18-Month Official Warranty & Swap Guarantee',
        rating DECIMAL(2,1) DEFAULT 4.9,
        reviews_count INT DEFAULT 348,
        orders_count INT DEFAULT 1420,
        main_image VARCHAR(500) NOT NULL,
        gallery_images TEXT,
        short_description TEXT,
        full_description TEXT,
        features TEXT,
        specs TEXT,
        power_matrix TEXT,
        bundle_name VARCHAR(255) DEFAULT 'Bundle + Anker 140W Bio-Braided Cable (6ft)',
        bundle_price DECIMAL(10,2) DEFAULT 24999.00,
        bundle_original_price DECIMAL(10,2) DEFAULT 32499.00,
        is_featured INT DEFAULT 1,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category_id),
        INDEX idx_brand (brand_id),
        INDEX idx_featured (is_featured),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 5. Hero Slides Table (CMS Controlled)
    $pdo->exec("CREATE TABLE IF NOT EXISTS hero_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subtitle TEXT,
        badge_text VARCHAR(150),
        image_url VARCHAR(500) NOT NULL,
        nav_color VARCHAR(30) DEFAULT '#0052cc',
        cta_link VARCHAR(255) DEFAULT 'explore.php',
        cta_text VARCHAR(100) DEFAULT 'Explore Catalog',
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 6. Spotlight Items Table (CMS Controlled)
    $pdo->exec("CREATE TABLE IF NOT EXISTS spotlight_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        link_url VARCHAR(255) DEFAULT 'product-detail.php',
        badge_text VARCHAR(100) DEFAULT 'HOT DEAL',
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 7. New Arrival Items Table (CMS Controlled)
    $pdo->exec("CREATE TABLE IF NOT EXISTS new_arrival_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NULL,
        title VARCHAR(255) NOT NULL,
        sku VARCHAR(100) DEFAULT '',
        image_url VARCHAR(500) NOT NULL,
        link_url VARCHAR(255) DEFAULT 'product-detail.php',
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        original_price DECIMAL(10,2) NULL,
        discount_pct INT DEFAULT 0,
        rating DECIMAL(2,1) DEFAULT 0.0,
        badge_text VARCHAR(100) DEFAULT 'NEW ARRIVAL',
        spec_1_title VARCHAR(100) DEFAULT '',
        spec_1_sub VARCHAR(100) DEFAULT '',
        spec_2_title VARCHAR(100) DEFAULT '',
        spec_2_sub VARCHAR(100) DEFAULT '',
        spec_3_title VARCHAR(100) DEFAULT '',
        spec_3_sub VARCHAR(100) DEFAULT '',
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 8. Flash Sale Items Table (CMS Controlled)
    $pdo->exec("CREATE TABLE IF NOT EXISTS flash_sale_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NULL,
        title VARCHAR(255) NOT NULL,
        ribbon_text VARCHAR(100) DEFAULT '10.10 sale is live',
        discount_text VARCHAR(50) DEFAULT '14% Off',
        image_url VARCHAR(500) NOT NULL,
        link_url VARCHAR(255) DEFAULT 'product-detail.php',
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        original_price DECIMAL(10,2) NULL,
        rating DECIMAL(2,1) DEFAULT 0.0,
        color_swatch VARCHAR(50) DEFAULT '#000000',
        sort_order INT DEFAULT 0,
        is_active INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 9. Orders Table (Checkout & Store Orders)
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(150) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_email VARCHAR(150) NULL,
        customer_address TEXT NOT NULL,
        customer_city VARCHAR(100) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        order_items_json TEXT NOT NULL,
        notes TEXT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 10. Site Settings Table (Store & WhatsApp Configuration)
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Seed default WhatsApp settings if none exist
    try {
        $stmtWaS = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'whatsapp_number'");
        $stmtWaS->execute();
        if ($stmtWaS->fetchColumn() == 0) {
            $insSetting = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            $insSetting->execute(['whatsapp_number', '+92 300 1234567']);
            $insSetting->execute(['whatsapp_enabled', '1']);
            $insSetting->execute(['whatsapp_default_msg', 'Hi ChargeHub, I have a question about your products.']);
        }
    } catch (Exception $e) {}

    // Seed default admin if none exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insertAdmin = $pdo->prepare("INSERT INTO admins (username, password_hash, email) VALUES (:u, :p, :e)");
        $insertAdmin->execute([
            ':u' => 'admin',
            ':p' => password_hash('admin123', PASSWORD_DEFAULT),
            ':e' => 'support@chargehub.pk'
        ]);
    }

    // Seed Categories if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insCat = $pdo->prepare("INSERT INTO categories (name, slug, image_url, badge_text, models_count_text, bg_color, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $cats = [
            ['Party Speakers', 'speakers', '🔊', 'HOT', '18 Models', 'bg-blue-50', 'High-power bluetooth party speakers with punchy bass and dynamic LED rings.', 1, 1],
            ['Earbuds & ANC', 'earbuds', '🎧', '', '24 Models', 'bg-indigo-50', 'Active Noise Cancelling earbuds, transparency modes and studio audio.', 2, 1],
            ['SmartWatches', 'smartwatches', '⌚', '', '15 Models', 'bg-emerald-50', 'Next-gen fitness tracking, AMOLED displays and IP68 waterproof smartwatches.', 3, 1],
            ['Fast Chargers', 'chargers', '⚡', '', '32 Models', 'bg-amber-50', 'Next-generation Gallium Nitride multi-port high wattage wall and desktop chargers.', 4, 1],
            ['Cables & Hubs', 'cables', '🔌', '', '40+ Models', 'bg-purple-50', 'Military-grade DuPont Kevlar braided 100W-240W cables and multi-port hubs.', 5, 1],
            ['Data Storage', 'storage', '💾', '', '19 Models', 'bg-rose-50', 'Ultra-fast NVMe portable SSDs, high-speed flash drives and memory cards.', 6, 1],
        ];
        foreach ($cats as $c) {
            $insCat->execute($c);
        }
    }

    // Seed Brands if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM brands");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insBrand = $pdo->prepare("INSERT INTO brands (name, slug, logo_url, website, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $brands = [
            ['Anker', 'anker', 'images/v7/brands/brand_anker_2x.png', 'https://anker.com', 'World leader in mobile charging and pioneering GaNPrime technology.', 1, 1],
            ['A4TECH', 'a4tech', 'images/v7/brands/brand_a4tech_2x.png', 'https://a4tech.com', 'Ergonomic precision peripherals, cables and smart desktop tech.', 2, 1],
            ['Baseus', 'baseus', 'images/v7/brands/brand_baseus_2x.png', 'https://baseus.com', 'Base on User design aesthetics with smart digital power displays.', 3, 1],
            ['Apple', 'apple', 'images/v7/brands/brand_apple_2x.png', 'https://apple.com', 'Certified 20W to 140W USB-C PD power accessories and MagSafe gear.', 4, 1],
            ['BOYA', 'boya', 'images/v7/brands/brand_boya_2x.png', 'https://boya-mic.com', 'Professional studio audio gear, wireless lavalier microphones and adapters.', 5, 1],
            ['JisuLife', 'jisulife', 'images/v7/brands/brand_jisulife_2x.png', 'https://jisulife.com', 'Portable handheld turbo fans, personal cooling devices and rechargeable gadgets.', 6, 1],
            ['Mcdodo', 'mcdodo', 'images/v7/brands/brand_mcdodo_2x.png', 'https://mcdodo.co', 'Smart auto-disconnect digital wattage display cables and ultra-slim GaN chargers.', 7, 1],
            ['PITAKA', 'pitaka', 'images/v7/brands/brand_pitaka_2x.png', 'https://ipitaka.com', 'Aerospace-grade aramid fiber cases, magnetic wireless charging stands and docks.', 8, 1],
            ['Samsung', 'samsung', 'images/v7/brands/brand_samsung_2x.png', 'https://samsung.com', 'Super Fast Charging 2.0 45W PPS official adapters and genuine cords.', 9, 1],
            ['UGREEN', 'ugreen', 'images/v7/brands/brand_ugreen_2x.png', 'https://ugreen.com', 'Innovative Nexode GaN chargers and premium high-speed interconnects.', 10, 1],
            ['JBL', 'jbl', 'images/v7/brands/brand_jbl_2x.png', 'https://jbl.com', 'World-class wireless speakers, Pro Sound earbuds and rugged portable audio.', 11, 1],
            ['Xiaomi', 'xiaomi', 'images/v7/brands/brand_xiaomi_2x.png', 'https://mi.com', 'High-capacity fast-charging power banks, compact GaN chargers and smart home power.', 12, 1],
        ];
        foreach ($brands as $b) {
            $insBrand->execute($b);
        }
    }

    // Seed Products if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insProd = $pdo->prepare("INSERT INTO products (
            category_id, brand_id, name, slug, sku, badge_text,
            price, original_price, stock_status, stock_quantity, warranty_text,
            rating, reviews_count, orders_count,
            main_image, gallery_images, short_description, full_description,
            features, specs, power_matrix, bundle_name, bundle_price, bundle_original_price,
            is_featured, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $gallery = json_encode([
            "images/product_detail/gallery_1_hero.jpg",
            "images/product_detail/gallery_2_ports.jpg",
            "images/product_detail/gallery_3_speed.jpg",
            "images/product_detail/gallery_4_thermal.jpg"
        ]);

        $features = json_encode([
            '140W USB-C PD 3.1 Peak: Charges 16" MacBook Pro from 0 to 50% in only 28 minutes',
            'ActiveShield™ 2.0: Real-time intelligent temperature monitoring exceeding 3,000,000 times per day',
            'GaNPrime™ Multi-Device Architecture: Dynamic power distribution across 2 USB-C and 1 USB-A ports',
            'Foldable US Pins + High-Conductivity Interchangeable UK / EU Travel Adapter Kit Included',
            'Official 18-Month Direct Swap Replacement Guarantee across Pakistan'
        ]);

        $specs = json_encode([
            'Model' => 'A2341 (GaNPrime 737 Series)',
            'Total Output' => '140W Max Dynamic Allocation',
            'Ports' => '2x USB-C (PD 3.1), 1x USB-A (PowerIQ 2.0)',
            'Input' => '100-240V ~ 2.2A 50-60Hz (Worldwide Universal)',
            'Dimensions' => '67.5 x 31 x 73 mm (Compact GaN Architecture)',
            'Weight' => '235g (Ultra-portable density)',
            'Protocols' => 'PD 3.1 / PD 3.0 / PPS / QC 4.0+ / Apple 2.4A / Samsung SFC 2.0',
            'Compatibility' => 'MacBook Pro 16/14/13, iPad Pro, iPhone 16/15/14, Galaxy S25/S24 Ultra, Dell XPS, Steam Deck, ROG Ally'
        ]);

        $powerMatrix = json_encode([
            'single_port' => [
                'usb_c1' => '140W Max (PD 3.1)',
                'usb_c2' => '140W Max (PD 3.1)',
                'usb_a'  => '22.5W Max'
            ],
            'dual_port' => [
                'c1_plus_c2' => 'Dynamic PowerShare (Up to 140W Total)',
                'c1_plus_a'  => 'Dynamic (Up to 140W Total)'
            ],
            'triple_port' => 'USB-C1 (100W) + USB-C2 (22.5W) + USB-A (17.5W) = 140W Total Max'
        ]);

        $insProd->execute([
            1, 1,
            'Anker Prime 140W GaN Wall Charger (3-Port 2C1A Fast GaNPrime™)',
            'anker-prime-140w',
            'ANK-A2341-BLK',
            '⚡ PD 3.1 140W PEAK',
            19999.00,
            24999.00,
            'In Stock (Lahore Hub)',
            85,
            '18-Month Official Warranty & Swap Guarantee',
            4.9,
            348,
            1420,
            'images/product_detail/gallery_1_hero.jpg',
            $gallery,
            'Flagship GaN 140W multi-device wall charger capable of full-throttle fast charging high-demand laptops and phones simultaneously.',
            '<p class="mb-4 text-app-muted">Experience peak desktop charging performance shrunk down to pocketable dimensions. The <strong>Anker Prime 140W GaN Charger</strong> harnesses cutting-edge Gallium Nitride (GaN) semiconductors and proprietary PowerIQ 4.0 dynamic distribution.</p><p class="text-app-muted">Engineered with dual high-output USB-C ports delivering up to 140W PD 3.1 single-port output alongside a legacy USB-A port for accessories.</p>',
            $features,
            $specs,
            $powerMatrix,
            'Bundle + Anker 140W Bio-Braided Cable (6ft)',
            24999.00,
            32499.00,
            1, 1
        ]);
    }

    // Seed Hero Slides if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM hero_slides");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insHero = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, badge_text, image_url, nav_color, cta_link, cta_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $heroSlides = [
            [
                'Next-Gen GaN Fast Chargers',
                'Hyper-speed Gallium Nitride charging for laptops, phones, and wearables. Zero clutter, peak thermal efficiency.',
                '⚡ OFFICIAL DISTRIBUTOR IN PAKISTAN',
                'images/Hero Section/hero banner.jpg',
                '#0052cc',
                'explore.php',
                'Explore GaN Chargers',
                1, 1
            ],
            [
                'Military-Grade Kevlar Cables',
                'Engineered to withstand 35,000+ bends with smart E-Marker chips delivering verified 100W-240W sustained throughput.',
                '🛡️ INDESTRUCTIBLE BUILD',
                'images/v7/chargers/hero_fast_cable_ultra.jpg',
                '#10b981',
                'explore.php?category=cables',
                'Shop Heavy-Duty Cables',
                2, 1
            ],
            [
                'High-Capacity Desktop Power Stations',
                'Massive battery capacity with high-wattage AC/DC output ports. Keep your whole workstation powered through load-shedding.',
                '🔋 UNINTERRUPTED POWER',
                'images/v7/chargers/hero_powerstation_ultra.jpg',
                '#f59e0b',
                'explore.php?category=powerbanks',
                'View Power Stations',
                3, 1
            ],
            [
                'Qi2 MagSafe Wireless Stands',
                'Snap and charge at maximum 15W MagSafe speeds with active cooling fans and aerospace zinc-alloy construction.',
                '🧲 SNAP & CHARGE',
                'images/v7/chargers/hero_wireless_station.jpg',
                '#8b5cf6',
                'explore.php?category=wireless',
                'Explore Wireless Tech',
                4, 1
            ]
        ];
        foreach ($heroSlides as $slide) {
            $insHero->execute($slide);
        }
    }

    // Seed Spotlight Items if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM spotlight_items");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insSpot = $pdo->prepare("INSERT INTO spotlight_items (title, image_url, link_url, badge_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $spotlights = [
            ['Anker Prime 140W GaN', 'images/Spotlight Section/1.png', 'product-detail.php?slug=anker-prime-140w-gan-charger', 'HOT DEAL', 1, 1],
            ['Ugreen Nexode 100W 4-Port', 'images/Spotlight Section/2.png', 'product-detail.php?slug=ugreen-nexode-100w-4port-charger', 'POPULAR', 2, 1],
            ['ChargeHub Armor 240W Kevlar Cable', 'images/Spotlight Section/3.png', 'product-detail.php?slug=chargehub-armor-240w-kevlar-cable', 'TOP RATED', 3, 1],
            ['Baseus MagPro 3-in-1 Magnetic Dock', 'images/Spotlight Section/4.png', 'product-detail.php?slug=baseus-magpro-3-in-1-wireless-dock', 'OFFICIAL', 4, 1],
            ['Anker Prime 200W Power Bank', 'images/Spotlight Section/5.png', 'product-detail.php?slug=anker-prime-20000mah-200w-powerbank', 'SPECIAL', 5, 1],
        ];
        foreach ($spotlights as $item) {
            $insSpot->execute($item);
        }
    }

    // Seed New Arrival Items if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM new_arrival_items");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insNew = $pdo->prepare("INSERT INTO new_arrival_items (title, sku, image_url, link_url, price, original_price, discount_pct, rating, badge_text, spec_1_title, spec_1_sub, spec_2_title, spec_2_sub, spec_3_title, spec_3_sub, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $arrivals = [
            ['CHARGEHUB PRO | 65W GAN', 'CH-65W', 'images/1000x1000 transparent PNG/1.png', 'product-detail.php?slug=anker-prime-140w-gan-charger', 5511, 6599, 16, 0.0, 'NEW ARRIVAL', 'Quad Mic', 'ENC Support', 'ANC', 'Support', 'Upto 7 Hrs', 'Playback', 1, 1],
            ['VOLTRA PRO | 100W GAN', 'CH-100PRO', 'images/1000x1000 transparent PNG/2.png', 'product-detail.php?slug=ugreen-nexode-100w-4port-charger', 7139, 8499, 16, 0.0, 'NEW ARRIVAL', '22.5Watt', 'Max Output', 'Attached', 'Cables', '30000mAh', 'Capacity', 2, 1],
            ['HYPERSPEED | 45W DUAL', 'CH-45D', 'images/1000x1000 transparent PNG/3.png', 'product-detail.php?slug=ugreen-nexode-100w-4port-charger', 2624, 4499, 42, 0.0, 'NEW ARRIVAL', '45Watt', 'Max Output', 'GaN', 'Technology', 'PD Port', 'Output', 3, 1],
            ['ARMOR CABLE | 240W PD', 'CH-240C', 'images/1000x1000 transparent PNG/4.png', 'product-detail.php?slug=chargehub-armor-240w-kevlar-cable', 5774, 6499, 11, 0.0, 'NEW ARRIVAL', 'Open Ear', 'Design', '11mm', 'Bass Drivers', 'AI', 'System', 4, 1],
            ['NANO CUBE | 30W ULTRA', 'CH-30N', 'images/1000x1000 transparent PNG/5.png', 'product-detail.php?slug=anker-prime-140w-gan-charger', 1899, 2999, 37, 0.0, 'NEW ARRIVAL', '30W Ultra', 'Pocket Cube', 'Type-C PD', 'Fast iPhone', 'Safety Guard', 'Thermal Safe', 5, 1],
        ];
        foreach ($arrivals as $arr) {
            $insNew->execute($arr);
        }
    }

    // Seed Flash Sale Items if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM flash_sale_items");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insFlash = $pdo->prepare("INSERT INTO flash_sale_items (title, ribbon_text, discount_text, image_url, link_url, price, original_price, rating, color_swatch, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $flashDeals = [
            ['L-265 Ronaq | 12 inch Wireless Speaker', '10.10 sale is live', '14% Off', 'images/v7/flash_sale/l265_ronaq.png', 'product-detail.php?slug=l265-ronaq-12inch-speaker', 17859.00, 20999.00, 0.0, '#000000', 1, 1],
            ['L-267 Jamster | 30 Watt Wireless Speaker', '10.10 sale is live', '22% Off', 'images/v7/flash_sale/l267_jamster.png', 'product-detail.php?slug=l267-jamster-30w-speaker', 7799.00, 9999.00, 0.0, '#000000', 2, 1],
            ['L-268 Jamster Pro | 50 Watt Wireless Speaker', '10.10 sale is live', '18% Off', 'images/v7/flash_sale/l268_jamster_pro.png', 'product-detail.php?slug=l268-jamster-pro-50w-speaker', 11479.00, 13999.00, 0.0, '#000000', 3, 1],
            ['L-269 Jamster Prime | 60 Watt Wireless Speaker', '10.10 sale is live', '20% Off', 'images/v7/flash_sale/l269_jamster_prime.png', 'product-detail.php?slug=l269-jamster-prime-60w-speaker', 14399.00, 17999.00, 0.0, '#000000', 4, 1],
            ['L-270 Predox | Wireless Soundbar', '10.10 sale is live', '15% Off', 'images/v7/flash_sale/l270_predox.png', 'product-detail.php?slug=l270-predox-wireless-soundbar', 8499.00, 9999.00, 0.0, '#000000', 5, 1],
        ];
        foreach ($flashDeals as $fd) {
            $insFlash->execute($fd);
        }
    }

    // Auto-seed complete products catalog if catalog has less than 5 items
    try {
        $stmtCount = $pdo->query("SELECT COUNT(*) FROM products");
        if ($stmtCount && $stmtCount->fetchColumn() < 5) {
            $catMap = $pdo->query("SELECT slug, id FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
            $brandMap = $pdo->query("SELECT slug, id FROM brands")->fetchAll(PDO::FETCH_KEY_PAIR);

            $insCatalog = $pdo->prepare("INSERT INTO products (
                category_id, brand_id, name, slug, sku, badge_text,
                price, original_price, stock_status, stock_quantity, warranty_text,
                rating, reviews_count, orders_count,
                main_image, gallery_images, short_description, full_description,
                features, specs, is_featured, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE name=VALUES(name)");

            $catalogProducts = [
                [
                    $catMap['cables'] ?? 5, $brandMap['mcdodo'] ?? 7,
                    'ChargeHub 240W Kevlar Digital Wattage Cable',
                    'chargehub-240w-kevlar-cable',
                    'CH-240C-OLED',
                    'STAFF PICK',
                    3499.00, 4999.00, 'In Stock', 120, '1-Year Official Warranty',
                    4.8, 192, 850,
                    'images/v7/featured/card_fast_cable.jpg',
                    json_encode(['images/v7/featured/card_fast_cable.jpg']),
                    'DuPont Kevlar braided 240W Type-C cord with real-time OLED wattage and voltage display.',
                    '<p>Military-grade high wattage USB-C to USB-C charging cable with integrated smart OLED real-time power display and E-Marker silicon chip.</p>',
                    json_encode(['240W Power Delivery 3.1 Extended Power Range', 'Real-Time Digital Wattage OLED Display', 'DuPont Kevlar Braided Core - 35,000+ Bend Tested', 'Dual E-Marker Chip for Peak Efficiency']),
                    json_encode(['Power Rating' => '240W Max (48V/5A)', 'Length' => '1.2m / 2.0m', 'Data Transfer' => '480 Mbps', 'Connector' => 'Type-C to Type-C Zinc Alloy']),
                    1, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['pitaka'] ?? 8,
                    'ChargeHub 3-in-1 Foldable MagSafe Stand',
                    'chargehub-3in1-magsafe-stand',
                    'CH-3IN1-MAG',
                    'HOT',
                    8999.00, 11999.00, 'In Stock', 45, '1-Year Official Warranty',
                    4.9, 127, 430,
                    'images/v7/featured/card_wireless_dock.jpg',
                    json_encode(['images/v7/featured/card_wireless_dock.jpg']),
                    'Qi2 certified 15W magnetic rapid charging station for iPhone, Apple Watch and AirPods.',
                    '<p>Space-saving aerospace aluminum foldable wireless dock engineered for Apple ecosystem. Delivers certified 15W MagSafe speeds with active cooling.</p>',
                    json_encode(['Qi2 Certified 15W Fast Magnetic Wireless', 'Simultaneous 3-Device Power Station', 'Foldable Travel Friendly Form Factor', 'Foreign Object Detection & Overheat Protection']),
                    json_encode(['Phone Output' => '15W Max MagSafe', 'Watch Output' => '5W Fast Apple Watch', 'Earbuds Output' => '5W Qi Pad', 'Input' => 'USB-C 30W+ Recommended']),
                    1, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['anker'] ?? 1,
                    'Anker 737 PowerCore 24,000mAh 140W Display',
                    'anker-737-powercore-24000mah',
                    'ANK-737-24K',
                    'FLAGSHIP',
                    28999.00, 34999.00, 'In Stock', 30, '18-Month Official Warranty',
                    5.0, 512, 1100,
                    'images/v7/featured/card_anker_powerbank.jpg',
                    json_encode(['images/v7/featured/card_anker_powerbank.jpg']),
                    'High-capacity 24,000mAh power bank with bidirectional 140W fast recharging and smart color screen.',
                    '<p>The ultimate high-performance portable battery. Equips ultra-powerful two-way charging using Power Delivery 3.1 and bi-directional technology to quickly recharge the portable charger.</p>',
                    json_encode(['Ultra-Powerful Two-Way 140W Charging', 'Smart Digital Color Display with Battery Health', '24,000mAh Giant Capacity (Charges iPhone 15 4.9 times)', 'ActiveShield 2.0 Real-Time Thermals']),
                    json_encode(['Capacity' => '24,000mAh / 86.4Wh', 'Total Output' => '140W Max', 'Ports' => '2x USB-C, 1x USB-A', 'Recharge Time' => '52 minutes to 100% (140W Input)']),
                    1, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['ugreen'] ?? 10,
                    'Ugreen Nexode 100W 4-Port GaN Fast Charger',
                    'ugreen-nexode-100w-4port-charger',
                    'UG-100W-NEX',
                    'POPULAR',
                    14999.00, 18499.00, 'In Stock', 60, '1-Year Official Warranty',
                    4.8, 164, 520,
                    'images/Spotlight Section/2.png',
                    json_encode(['images/Spotlight Section/2.png']),
                    'Quad-port Nexode GaN desktop and travel fast charger delivering up to 100W PD.',
                    '<p>Charge up to 4 devices simultaneously with smart power distribution. Features 3 USB-C ports and 1 USB-A port with GaN II efficiency.</p>',
                    json_encode(['100W High Speed Single Port Output', '4 Ports (3 USB-C + 1 USB-A)', 'GaN II Semiconductor Architecture', 'Universal Fast Charge Protocol Support']),
                    json_encode(['Output' => '100W Max', 'Dimensions' => '69 x 69 x 33 mm', 'Weight' => '235g', 'Protocols' => 'PD 3.0, QC 4.0+, PPS']),
                    0, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['baseus'] ?? 3,
                    'Baseus MagPro 3-in-1 Magnetic Wireless Dock',
                    'baseus-magpro-3-in-1-wireless-dock',
                    'BS-MAGPRO-3IN1',
                    'OFFICIAL',
                    9499.00, 11999.00, 'In Stock', 40, '1-Year Official Warranty',
                    4.7, 88, 310,
                    'images/Spotlight Section/4.png',
                    json_encode(['images/Spotlight Section/4.png']),
                    'Baseus MagPro retractable desktop magnetic charging hub for phone, watch, and buds.',
                    '<p>Clean minimalist desktop aesthetic with hidden watch charger and pop-up magnetic ring head for landscape and portrait viewing.</p>',
                    json_encode(['15W Peak Magnetic Wireless', 'Retractable Apple Watch Charger', 'Dual Indicator LED Lighting', 'Anti-Slip Weighted Base']),
                    json_encode(['Input' => '9V/3A, 12V/3A', 'Phone' => '15W Max', 'Watch' => '3W Max', 'Buds' => '5W Max']),
                    0, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['anker'] ?? 1,
                    'Anker Prime 20,000mAh Power Bank (200W Output)',
                    'anker-prime-20000mah-200w-powerbank',
                    'ANK-A1336-200W',
                    'SPECIAL',
                    21999.00, 26999.00, 'In Stock', 55, '18-Month Official Warranty',
                    4.9, 210, 780,
                    'images/Spotlight Section/5.png',
                    json_encode(['images/Spotlight Section/5.png']),
                    '200W total output power bank with dual 100W USB-C ports and full-color display screen.',
                    '<p>Pocket-sized powerhouse with two high-powered USB-C ports and one USB-A port to charge two laptops simultaneously at 100W each.</p>',
                    json_encode(['200W Total Dynamic Output', 'Dual 100W USB-C Ports', 'Smart Interactive Color Screen', 'Compact Soda-Can Sizing']),
                    json_encode(['Capacity' => '20,000mAh', 'Input' => '100W Max', 'Weight' => '540g', 'Warranty' => '18 Months']),
                    0, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['anker'] ?? 1,
                    'CHARGEHUB PRO | 65W GaN Fast Charger',
                    'chargehub-pro-65w-gan',
                    'CH-65W',
                    'NEW ARRIVAL',
                    5511.00, 6599.00, 'In Stock', 70, '1-Year Official Warranty',
                    4.8, 142, 600,
                    'images/1000x1000 transparent PNG/1.png',
                    json_encode(['images/1000x1000 transparent PNG/1.png']),
                    'High-speed 65W GaN dual-port travel wall charger with PPS fast charging.',
                    '<p>Ultra-compact 65W Gallium Nitride wall adapter engineered for laptops, tablets and smartphones with dual fast charging.</p>',
                    json_encode(['65W Max Single Port Output', 'Dynamic Dual Port Power Distribution', 'Miniature GaN Architecture', 'Thermal Guard Active Protection']),
                    json_encode(['Input' => '100-240V 50/60Hz', 'Outputs' => 'USB-C1 + USB-C2', 'Size' => '42 x 36 x 32 mm', 'Weight' => '98g']),
                    0, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['ugreen'] ?? 10,
                    'VOLTRA PRO | 100W GaN Quad-Port Charger',
                    'voltra-pro-100w-gan',
                    'CH-100PRO',
                    'NEW ARRIVAL',
                    7139.00, 8499.00, 'In Stock', 65, '1-Year Official Warranty',
                    4.9, 98, 410,
                    'images/1000x1000 transparent PNG/2.png',
                    json_encode(['images/1000x1000 transparent PNG/2.png']),
                    'Flagship 100W multi-device fast charger with foldable AC prongs.',
                    '<p>Compact 100W GaN power station equipped with smart power distribution to run power-hungry laptops, tablets, and phones simultaneously.</p>',
                    json_encode(['100W Peak Type-C PD Output', 'Simultaneous 4-Device Charging', 'Foldable Travel Prongs', 'Advanced Heat Dissipation']),
                    json_encode(['Input' => '100-240V', 'Total Output' => '100W Max', 'Ports' => '3x USB-C, 1x USB-A']),
                    0, 1
                ],
                [
                    $catMap['chargers'] ?? 4, $brandMap['anker'] ?? 1,
                    'HYPERSPEED | 45W Dual USB-C Fast Adapter',
                    'hyperspeed-45w-dual',
                    'CH-45D',
                    'NEW ARRIVAL',
                    2624.00, 4499.00, 'In Stock', 90, '1-Year Official Warranty',
                    4.7, 76, 320,
                    'images/1000x1000 transparent PNG/3.png',
                    json_encode(['images/1000x1000 transparent PNG/3.png']),
                    'Dual-port 45W ultra-thin fast charger designed for Samsung SFC 2.0 and iPhone 16.',
                    '<p>Efficient, cool-running 45W GaN adapter with dual Type-C outputs for seamless phone and tablet dual fast charging.</p>',
                    json_encode(['Samsung 45W Super Fast Charging 2.0', 'Dual USB-C Output', 'Ultra Slim Pocket Design', 'Over-Voltage & Over-Current Safety']),
                    json_encode(['Output' => '45W Max', 'Ports' => '2x USB-C', 'Dimensions' => '40 x 30 x 30 mm']),
                    0, 1
                ],
                [
                    $catMap['speakers'] ?? 1, $brandMap['jbl'] ?? 11,
                    'L-265 Ronaq | 12 inch Wireless Party Speaker',
                    'l265-ronaq-12inch-speaker',
                    'SPK-L265',
                    '14% OFF',
                    17859.00, 20999.00, 'In Stock', 35, '1-Year Official Warranty',
                    4.9, 115, 390,
                    'images/v7/flash_sale/l265_ronaq.png',
                    json_encode(['images/v7/flash_sale/l265_ronaq.png']),
                    '12-inch high-output wireless trolley party speaker with RGB ring lights and dual mic inputs.',
                    '<p>Fill the entire room or outdoor yard with thunderous bass and crystal highs. Equipped with heavy-duty woofer, DJ sound effects, and wireless karaoke mic support.</p>',
                    json_encode(['12-Inch Heavy Bass Woofer', 'Dynamic Beat-Synced RGB Lighting', 'Wireless Microphone Support Included', 'Upto 10 Hours High-Capacity Battery']),
                    json_encode(['Driver' => '12-inch Subwoofer + 3-inch Tweeter', 'Power' => '120W RMS Peak', 'Battery' => '7.4V 4500mAh', 'Connectivity' => 'Bluetooth 5.3, USB, TF, AUX, Mic-In']),
                    0, 1
                ],
                [
                    $catMap['speakers'] ?? 1, $brandMap['jbl'] ?? 11,
                    'L-267 Jamster | 30 Watt Wireless Speaker',
                    'l267-jamster-30w-speaker',
                    'SPK-L267',
                    '22% OFF',
                    7799.00, 9999.00, 'In Stock', 50, '1-Year Official Warranty',
                    4.8, 89, 290,
                    'images/v7/flash_sale/l267_jamster.png',
                    json_encode(['images/v7/flash_sale/l267_jamster.png']),
                    '30W portable cylinder bluetooth speaker with dual passive bass radiators.',
                    '<p>Rugged, splash-proof portable speaker built for outdoor road trips, parties, and desktop audio with rich low-end punch.</p>',
                    json_encode(['30W Room-Filling Acoustic Output', 'Dual Passive Bass Radiators', 'IPX6 Water-Resistant Casing', 'TWS Stereo Pairing Supported']),
                    json_encode(['Power' => '30W RMS', 'Battery Life' => 'Upto 12 Hours', 'Bluetooth' => 'v5.3 Low Latency', 'Weight' => '680g']),
                    0, 1
                ],
                [
                    $catMap['speakers'] ?? 1, $brandMap['jbl'] ?? 11,
                    'L-268 Jamster Pro | 50 Watt Wireless Speaker',
                    'l268-jamster-pro-50w-speaker',
                    'SPK-L268',
                    '18% OFF',
                    11479.00, 13999.00, 'In Stock', 40, '1-Year Official Warranty',
                    4.9, 64, 210,
                    'images/v7/flash_sale/l268_jamster_pro.png',
                    json_encode(['images/v7/flash_sale/l268_jamster_pro.png']),
                    '50W high-power portable boombox with EQ bass boost and shoulder strap.',
                    '<p>Booming clarity and chest-thumping bass designed for enthusiasts. Features durable ballistic weave fabric and USB powerbank function to charge your phone.</p>',
                    json_encode(['50W Peak Dynamic Stereo Audio', 'Integrated Powerbank Function for Phones', 'Dedicated Indoor/Outdoor EQ Modes', 'Ergonomic Carry Handle & Strap']),
                    json_encode(['Power' => '50W RMS', 'Playtime' => '15 Hours', 'Battery' => '6000mAh', 'Weight' => '1.2 kg']),
                    0, 1
                ],
                [
                    $catMap['speakers'] ?? 1, $brandMap['jbl'] ?? 11,
                    'L-269 Jamster Prime | 60 Watt Wireless Speaker',
                    'l269-jamster-prime-60w-speaker',
                    'SPK-L269',
                    '20% OFF',
                    14399.00, 17999.00, 'In Stock', 30, '1-Year Official Warranty',
                    4.9, 78, 250,
                    'images/v7/flash_sale/l269_jamster_prime.png',
                    json_encode(['images/v7/flash_sale/l269_jamster_prime.png']),
                    '60W flagship portable party audio system with pulsating side radiators and metallic accents.',
                    '<p>Engineered for maximum acoustic dispersion with dual silk-dome tweeters and high-excursion woofers.</p>',
                    json_encode(['60W Quad-Driver Acoustic Architecture', 'Side Bass Strobes with Synchronized Illumination', 'Lossless Bluetooth 5.3 + Aux Hi-Res', 'Fast Type-C Quick Charge Recharging']),
                    json_encode(['Power' => '60W RMS', 'Playtime' => '16 Hours', 'Charging' => 'Type-C 18W Fast In', 'Dimensions' => '280 x 115 x 110 mm']),
                    0, 1
                ],
                [
                    $catMap['speakers'] ?? 1, $brandMap['jbl'] ?? 11,
                    'L-270 Predox | Wireless Soundbar',
                    'l270-predox-wireless-soundbar',
                    'SND-L270',
                    '15% OFF',
                    8499.00, 9999.00, 'In Stock', 45, '1-Year Official Warranty',
                    4.8, 92, 340,
                    'images/v7/flash_sale/l270_predox.png',
                    json_encode(['images/v7/flash_sale/l270_predox.png']),
                    'Sleek desktop and TV wireless soundbar with optical, HDMI ARC and Bluetooth inputs.',
                    '<p>Elevate your TV, gaming monitor, or desktop setup with wide 3D spatial surround sound and dialogue enhancement algorithms.</p>',
                    json_encode(['Dual Acoustic Cones + Dual Bass Reflex Tubes', 'HDMI ARC, Optical, Aux and Bluetooth 5.3', 'Cinematic Movie and Dialogue Clarity Modes', 'Slim Low-Profile Metallic Mesh Enclosure']),
                    json_encode(['Power' => '40W RMS', 'Inputs' => 'HDMI ARC, Optical, Bluetooth, Aux', 'Length' => '60 cm', 'Mounting' => 'Wall-mount or Desktop']),
                    0, 1
                ]
            ];

            foreach ($catalogProducts as $cp) {
                $insCatalog->execute($cp);
            }
        }
    } catch (PDOException $e) {
        // Safe to ignore
    }

    // Auto-migrate any legacy ID links to clean slug links in spotlight_items, new_arrival_items, flash_sale_items and hero_slides
    try {
        $pdo->exec("UPDATE spotlight_items s 
                    JOIN products p ON s.link_url = CONCAT('product-detail.php?id=', p.id)
                    SET s.link_url = CONCAT('product-detail.php?slug=', p.slug)");
        $pdo->exec("UPDATE new_arrival_items n 
                    JOIN products p ON n.link_url = CONCAT('product-detail.php?id=', p.id)
                    SET n.link_url = CONCAT('product-detail.php?slug=', p.slug)");
        $pdo->exec("UPDATE flash_sale_items f 
                    JOIN products p ON f.link_url = CONCAT('product-detail.php?id=', p.id)
                    SET f.link_url = CONCAT('product-detail.php?slug=', p.slug)");
        $pdo->exec("UPDATE hero_slides h 
                    JOIN products p ON h.cta_link = CONCAT('product-detail.php?id=', p.id)
                    SET h.cta_link = CONCAT('product-detail.php?slug=', p.slug)");
    } catch (PDOException $e) {
        // Safe to ignore if tables not yet populated
    }

    // Auto-migrate new columns for categories if missing
    try {
        $catCols = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('badge_text', $catCols)) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN badge_text VARCHAR(50) DEFAULT NULL AFTER image_url");
        }
        if (!in_array('models_count_text', $catCols)) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN models_count_text VARCHAR(50) DEFAULT NULL AFTER badge_text");
        }
        if (!in_array('bg_color', $catCols)) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN bg_color VARCHAR(50) DEFAULT 'bg-blue-50' AFTER models_count_text");
        }
    } catch (PDOException $e) {
        // Safe to ignore
    }

    // Auto-sync the 6 official explore categories matching screenshot
    try {
        $officialCategoriesList = [
            ['Party Speakers', 'speakers', '🔊', 'HOT', '18 Models', 'bg-blue-50', 'High-power bluetooth party speakers with punchy bass and dynamic LED rings.', 1],
            ['Earbuds & ANC', 'earbuds', '🎧', '', '24 Models', 'bg-indigo-50', 'Active Noise Cancelling earbuds, transparency modes and studio audio.', 2],
            ['SmartWatches', 'smartwatches', '⌚', '', '15 Models', 'bg-emerald-50', 'Next-gen fitness tracking, AMOLED displays and IP68 waterproof smartwatches.', 3],
            ['Fast Chargers', 'chargers', '⚡', '', '32 Models', 'bg-amber-50', 'Next-generation Gallium Nitride multi-port high wattage wall and desktop chargers.', 4],
            ['Cables & Hubs', 'cables', '🔌', '', '40+ Models', 'bg-purple-50', 'Military-grade DuPont Kevlar braided 100W-240W cables and multi-port hubs.', 5],
            ['Data Storage', 'storage', '💾', '', '19 Models', 'bg-rose-50', 'Ultra-fast NVMe portable SSDs, high-speed flash drives and memory cards.', 6],
        ];

        $checkCatStmt = $pdo->prepare("SELECT id, badge_text, models_count_text, bg_color, image_url FROM categories WHERE slug = ?");
        $updateCatStmt = $pdo->prepare("UPDATE categories SET name = ?, image_url = ?, badge_text = ?, models_count_text = ?, bg_color = ?, sort_order = ? WHERE id = ?");
        $insertCatStmt = $pdo->prepare("INSERT INTO categories (name, slug, image_url, badge_text, models_count_text, bg_color, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");

        foreach ($officialCategoriesList as $oc) {
            $checkCatStmt->execute([$oc[1]]);
            $existingCat = $checkCatStmt->fetch(PDO::FETCH_ASSOC);
            if ($existingCat) {
                // If missing badge or models count or background, or image is spotlight image, sync them
                $curImg = $existingCat['image_url'] ?? '';
                $newImg = (empty($curImg) || strpos($curImg, 'Spotlight') !== false) ? $oc[2] : $curImg;
                $updateCatStmt->execute([$oc[0], $newImg, $oc[3], $oc[4], $oc[5], $oc[7], $existingCat['id']]);
            } else {
                $insertCatStmt->execute([$oc[0], $oc[1], $oc[2], $oc[3], $oc[4], $oc[5], $oc[6], $oc[7]]);
            }
        }
    } catch (PDOException $e) {
        // Safe to ignore
    }

    // Auto-sync the 12 official partner brands so logo paths and entries are always complete
    try {
        $officialBrandsList = [
            ['Anker', 'anker', 'images/v7/brands/brand_anker_2x.png', 'https://anker.com', 'World leader in mobile charging and pioneering GaNPrime technology.', 1],
            ['A4TECH', 'a4tech', 'images/v7/brands/brand_a4tech_2x.png', 'https://a4tech.com', 'Ergonomic precision peripherals, cables and smart desktop tech.', 2],
            ['Baseus', 'baseus', 'images/v7/brands/brand_baseus_2x.png', 'https://baseus.com', 'Base on User design aesthetics with smart digital power displays.', 3],
            ['Apple', 'apple', 'images/v7/brands/brand_apple_2x.png', 'https://apple.com', 'Certified 20W to 140W USB-C PD power accessories and MagSafe gear.', 4],
            ['BOYA', 'boya', 'images/v7/brands/brand_boya_2x.png', 'https://boya-mic.com', 'Professional studio audio gear, wireless lavalier microphones and adapters.', 5],
            ['JisuLife', 'jisulife', 'images/v7/brands/brand_jisulife_2x.png', 'https://jisulife.com', 'Portable handheld turbo fans, personal cooling devices and rechargeable gadgets.', 6],
            ['Mcdodo', 'mcdodo', 'images/v7/brands/brand_mcdodo_2x.png', 'https://mcdodo.co', 'Smart auto-disconnect digital wattage display cables and ultra-slim GaN chargers.', 7],
            ['PITAKA', 'pitaka', 'images/v7/brands/brand_pitaka_2x.png', 'https://ipitaka.com', 'Aerospace-grade aramid fiber cases, magnetic wireless charging stands and docks.', 8],
            ['Samsung', 'samsung', 'images/v7/brands/brand_samsung_2x.png', 'https://samsung.com', 'Super Fast Charging 2.0 45W PPS official adapters and genuine cords.', 9],
            ['UGREEN', 'ugreen', 'images/v7/brands/brand_ugreen_2x.png', 'https://ugreen.com', 'Innovative Nexode GaN chargers and premium high-speed interconnects.', 10],
            ['JBL', 'jbl', 'images/v7/brands/brand_jbl_2x.png', 'https://jbl.com', 'World-class wireless speakers, Pro Sound earbuds and rugged portable audio.', 11],
            ['Xiaomi', 'xiaomi', 'images/v7/brands/brand_xiaomi_2x.png', 'https://mi.com', 'High-capacity fast-charging power banks, compact GaN chargers and smart home power.', 12],
        ];

        $checkBrandStmt = $pdo->prepare("SELECT id, logo_url FROM brands WHERE slug = ?");
        $updateBrandStmt = $pdo->prepare("UPDATE brands SET logo_url = ?, sort_order = ? WHERE id = ?");
        $insertBrandStmt = $pdo->prepare("INSERT INTO brands (name, slug, logo_url, website, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");

        foreach ($officialBrandsList as $ob) {
            $checkBrandStmt->execute([$ob[1]]);
            $existing = $checkBrandStmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                // If logo is a placeholder (Spotlight or horizontal), upgrade it to official high-res logo
                if (empty($existing['logo_url']) || strpos($existing['logo_url'], 'Spotlight') !== false || strpos($existing['logo_url'], 'horizontal') !== false) {
                    $updateBrandStmt->execute([$ob[2], $ob[5], $existing['id']]);
                }
            } else {
                $insertBrandStmt->execute([$ob[0], $ob[1], $ob[2], $ob[3], $ob[4], $ob[5]]);
            }
        }
    } catch (PDOException $e) {
        // Safe to ignore
    }
}

/**
 * Renders a clean, informative error screen when MySQL is not running.
 * SQLite fallback is intentionally NOT provided.
 */
function render_mysql_error_screen(array $errors) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MySQL Connection Required - ChargeHub.pk</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { background: #0b0f19; color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-xl w-full bg-[#131b2e] border border-red-500/30 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex items-center space-x-3 text-red-500">
                <span class="text-3xl">⚠️</span>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-white">MySQL Database Connection Required</h1>
                    <p class="text-xs text-red-400">ChargeHub is configured for pure MySQL. SQLite is disabled.</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-xs text-slate-300 space-y-2">
                <div class="font-bold text-red-400">Connection Attempts &amp; Errors:</div>
                <ul class="list-disc pl-5 space-y-1 font-mono text-[11px] text-red-300">
                    <?php foreach ($errors as $err): ?>
                    <li><strong><?= htmlspecialchars($err['target']) ?>:</strong> <?= htmlspecialchars($err['error']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="space-y-3 text-xs text-slate-300">
                <div class="font-bold text-white text-sm">Quick Solutions:</div>
                <ol class="list-decimal pl-5 space-y-2 text-slate-400">
                    <li>
                        <strong class="text-slate-200">Start your MySQL Server:</strong><br>
                        • If using <strong>XAMPP</strong> or <strong>MAMP</strong>, open its control panel and click <strong>Start MySQL</strong>.<br>
                        • If using <strong>Homebrew</strong> on Mac, run in Terminal: <code class="px-1.5 py-0.5 rounded bg-black/40 text-emerald-400 font-mono">brew services start mysql</code>
                    </li>
                    <li>
                        <strong class="text-slate-200">phpMyAdmin Database Import:</strong><br>
                        • Open phpMyAdmin at <a href="http://localhost/phpmyadmin" target="_blank" class="text-blue-400 underline font-mono">http://localhost/phpmyadmin</a><br>
                        • Click the <strong>Import</strong> tab and import the file <code class="px-1.5 py-0.5 rounded bg-black/40 text-amber-300 font-mono">v7/chargehub_db.sql</code>.
                    </li>
                    <li>
                        <strong class="text-slate-200">Verify MySQL Credentials:</strong><br>
                        • Host: <code class="font-mono text-white"><?= htmlspecialchars(DB_HOST) ?></code> | Port: <code class="font-mono text-white"><?= htmlspecialchars(DB_PORT) ?></code><br>
                        • User: <code class="font-mono text-white"><?= htmlspecialchars(DB_USER) ?></code> | Database: <code class="font-mono text-white"><?= htmlspecialchars(DB_NAME) ?></code><br>
                        • If your MySQL password is not blank, edit <code class="px-1.5 py-0.5 rounded bg-black/40 text-cyan-300 font-mono">v7/config/db.php</code>.
                    </li>
                </ol>
            </div>

            <div class="pt-2 flex items-center justify-between border-t border-slate-700/60">
                <a href="http://localhost/phpmyadmin" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition-colors">
                    Open phpMyAdmin ↗
                </a>
                <button onclick="window.location.reload()" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-bold text-white shadow-lg transition-all">
                    🔄 Retry Connection
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Site Settings & WhatsApp Configuration Helpers
 */
if (!function_exists('get_site_setting')) {
    function get_site_setting($key, $default = '') {
        global $pdo;
        if (!$pdo) return $default;
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null) ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('set_site_setting')) {
    function set_site_setting($key, $value) {
        global $pdo;
        if (!$pdo) return false;
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            return $stmt->execute([$key, $value]);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('clean_whatsapp_number')) {
    function clean_whatsapp_number($phone) {
        $cleaned = preg_replace('/[^0-9]/', '', (string)$phone);
        if (str_starts_with($cleaned, '03') && strlen($cleaned) === 11) {
            $cleaned = '92' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '0092')) {
            $cleaned = substr($cleaned, 2);
        } elseif (!str_starts_with($cleaned, '92') && strlen($cleaned) === 10 && str_starts_with($cleaned, '3')) {
            $cleaned = '92' . $cleaned;
        }
        return $cleaned ?: '923001234567';
    }
}

if (!function_exists('get_whatsapp_config')) {
    function get_whatsapp_config() {
        $rawNumber = get_site_setting('whatsapp_number', '+92 300 1234567');
        $enabled = (int)get_site_setting('whatsapp_enabled', '1');
        $defaultMsg = get_site_setting('whatsapp_default_msg', 'Hi ChargeHub, I have a question about your products.');
        
        return [
            'number_raw' => $rawNumber ?: '+92 300 1234567',
            'number_clean' => clean_whatsapp_number($rawNumber),
            'enabled' => $enabled === 1,
            'default_msg' => $defaultMsg ?: 'Hi ChargeHub, I have a question about your products.',
        ];
    }
}
