<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// 1. Fetch all active products dynamically from database with category and brand info
$stmtProducts = $pdo->prepare("
    SELECT p.*, 
           c.name AS category_name, c.slug AS category_slug,
           b.name AS brand_name, b.slug AS brand_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1
    ORDER BY p.is_featured DESC, p.id DESC
");
$stmtProducts->execute();
$products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

// Fallback products if DB is empty to ensure design integrity
if (empty($products)) {
    $products = [
        [
            'id' => 1,
            'name' => 'ChargeHub Pro 65W GaN Fast Charger',
            'slug' => 'chargehub-pro-65w-gan',
            'sku' => 'CH-65W',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'ChargeHub',
            'brand_slug' => 'chargehub',
            'badge_text' => 'NEW ARRIVAL',
            'price' => 5511.00,
            'original_price' => 6599.00,
            'rating' => 4.8,
            'reviews_count' => 124,
            'stock_quantity' => 50,
            'stock_status' => 'In Stock',
            'main_image' => 'images/1000x1000 transparent PNG/1.png',
            'specs' => json_encode(['Max Output' => '65W Max', 'Architecture' => 'GaN III', 'Speed' => 'Laptop Fast'])
        ],
        [
            'id' => 2,
            'name' => 'Glazor Voltra Pro 100W GaN 4-Port',
            'slug' => 'glazor-voltra-pro-100w-gan',
            'sku' => 'GL-100W',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'Glazor',
            'brand_slug' => 'glazor',
            'badge_text' => 'HOT DEAL',
            'price' => 7999.00,
            'original_price' => 9999.00,
            'rating' => 4.9,
            'reviews_count' => 88,
            'stock_quantity' => 30,
            'stock_status' => 'In Stock',
            'main_image' => 'images/1000x1000 transparent PNG/2.png',
            'specs' => json_encode(['Max Output' => '100W PD 3.0', 'Ports' => '3x C + 1x A', 'Compatibility' => 'MacBook Pro'])
        ],
        [
            'id' => 3,
            'name' => 'Cerus Hyperspeed 45W Dual USB-C',
            'slug' => 'cerus-hyperspeed-45w-dual',
            'sku' => 'CR-45W',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'Cerus',
            'brand_slug' => 'cerus',
            'badge_text' => 'LIMITED',
            'price' => 4850.00,
            'original_price' => 5800.00,
            'rating' => 4.7,
            'reviews_count' => 65,
            'stock_quantity' => 20,
            'stock_status' => 'In Stock',
            'main_image' => 'images/1000x1000 transparent PNG/3.png',
            'specs' => json_encode(['Dual Output' => '45W Shared', 'Standard' => 'PPS Fast', 'Size' => 'Pocket Size'])
        ],
        [
            'id' => 4,
            'name' => 'Ambeo Armor 240W DuPont Kevlar Cable',
            'slug' => 'ambeo-armor-240w-cable',
            'sku' => 'AM-240C',
            'category_name' => 'Cables & Hubs',
            'category_slug' => 'cables',
            'brand_name' => 'Ambeo',
            'brand_slug' => 'ambeo',
            'badge_text' => 'STAFF PICK',
            'price' => 2999.00,
            'original_price' => 3999.00,
            'rating' => 5.0,
            'reviews_count' => 210,
            'stock_quantity' => 100,
            'stock_status' => 'In Stock',
            'main_image' => 'images/1000x1000 transparent PNG/4.png',
            'specs' => json_encode(['Throughput' => '240W Extended', 'Durability' => 'Kevlar Braid', 'Chip' => 'E-Marker 2.0'])
        ],
        [
            'id' => 5,
            'name' => 'Elan Nano Cube 30W Fast Wall Charger',
            'slug' => 'elan-nano-cube-30w',
            'sku' => 'EL-30W',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'Elan',
            'brand_slug' => 'elan',
            'badge_text' => 'POPULAR',
            'price' => 3299.00,
            'original_price' => 4199.00,
            'rating' => 4.6,
            'reviews_count' => 49,
            'stock_quantity' => 45,
            'stock_status' => 'In Stock',
            'main_image' => 'images/Spotlight Section/1.png',
            'specs' => json_encode(['Output' => '30W Max', 'Form' => 'Ultra-Small', 'Safety' => 'SafeCharge'])
        ],
        [
            'id' => 6,
            'name' => 'ChargeHub Titan 140W Desktop Fast Charging Station',
            'slug' => 'titan-140w-desktop-station',
            'sku' => 'TT-140D',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'ChargeHub',
            'brand_slug' => 'chargehub',
            'badge_text' => 'FLAGSHIP',
            'price' => 14499.00,
            'original_price' => 16999.00,
            'rating' => 5.0,
            'reviews_count' => 320,
            'stock_quantity' => 15,
            'stock_status' => 'In Stock',
            'main_image' => 'images/Spotlight Section/5.png',
            'specs' => json_encode(['Output' => '140W Peak', 'Ports' => '3C + 1A Hub', 'Tech' => 'GaNPrime'])
        ],
        [
            'id' => 7,
            'name' => 'ChargeHub SlimTravel 20W Pocket PD Charger',
            'slug' => 'chargehub-slimtravel-20w',
            'sku' => 'CH-20PK',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'ChargeHub',
            'brand_slug' => 'chargehub',
            'badge_text' => 'HOT DEAL',
            'price' => 3499.00,
            'original_price' => 4699.00,
            'rating' => 5.0,
            'reviews_count' => 93,
            'stock_quantity' => 60,
            'stock_status' => 'In Stock',
            'main_image' => 'images/Spotlight Section/2.png',
            'specs' => json_encode(['Power' => '20W Peak', 'Profile' => 'Ultra-Flat', 'Protection' => 'SafeTemp'])
        ],
        [
            'id' => 8,
            'name' => 'MagStand 15W Qi2 Magnetic Wireless Dock',
            'slug' => 'magstand-15w-qi2-dock',
            'sku' => 'CH-QI2-15',
            'category_name' => 'Fast Chargers',
            'category_slug' => 'chargers',
            'brand_name' => 'ChargeHub',
            'brand_slug' => 'chargehub',
            'badge_text' => 'NEW ARRIVAL',
            'price' => 4599.00,
            'original_price' => 5599.00,
            'rating' => 5.0,
            'reviews_count' => 110,
            'stock_quantity' => 25,
            'stock_status' => 'In Stock',
            'main_image' => 'images/Spotlight Section/4.png',
            'specs' => json_encode(['Wireless' => '15W Qi2', 'Chassis' => 'All-Metal', 'Design' => 'Tilt Adjust'])
        ]
    ];
}

// 2. Fetch categories with dynamic product count
$stmtCategories = $pdo->prepare("
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1 
    WHERE c.is_active = 1 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
");
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    $categories = [
        ['name' => 'Fast Chargers', 'slug' => 'chargers', 'product_count' => 6],
        ['name' => 'Cables & Hubs', 'slug' => 'cables', 'product_count' => 1],
        ['name' => 'Earbuds & ANC', 'slug' => 'earbuds', 'product_count' => 0],
        ['name' => 'Data Storage', 'slug' => 'storage', 'product_count' => 0]
    ];
}

// 3. Compute dynamic price boundaries
$prices = array_map(function($p) { return (float)$p['price']; }, $products);
$minPriceVal = !empty($prices) ? min($prices) : 1000;
$maxPriceVal = !empty($prices) ? max($prices) : 35000;
$minSliderVal = max(500, floor($minPriceVal / 500) * 500);
$maxSliderVal = ceil($maxPriceVal / 1000) * 1000;
if ($maxSliderVal <= $minSliderVal) $maxSliderVal = $minSliderVal + 10000;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog &ndash; ChargeHub.pk | Genuine Earbuds, Chargers &amp; Cables</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-blue': '#0066ff',
                        'brand-orange': '#ff5500',
                        'brand-gold': '#ffaa00',
                        'brand-dark': '#0f172a',
                        'brand-navy': '#001f5c',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f4f9;
            color: #1a1a1a;
            -webkit-font-smoothing: antialiased;
        }
        .brand-font {
            font-family: 'Outfit', sans-serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Signature Nav scoop transition curve shadow */
        .scoop-tab {
            filter: drop-shadow(0 4px 12px rgba(10, 6, 90, 0.35));
        }
        @keyframes ripple {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 0; }
            100% { transform: scale(1.3); opacity: 0; }
        }
        .whatsapp-ripple::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            border: 2px solid #25d366;
            animation: ripple 2s infinite cubic-bezier(0, 0.2, 0.8, 1);
        }
    </style>
</head>
<body class="selection:bg-brand-blue selection:text-white relative min-h-screen flex flex-col justify-between">

    <!-- ================================================================= -->
    <!-- FLOATING STICKY CAPSULE NAVBAR (Active on Scroll, Exact Reference) -->
    <!-- ================================================================= -->
    <nav id="sticky-navbar" class="fixed top-2.5 sm:top-3.5 left-1/2 -translate-x-1/2 w-[95%] max-w-[1360px] z-50 transition-all duration-300 transform -translate-y-24 opacity-0 pointer-events-none">
        <div class="w-full bg-white/95 backdrop-blur-md rounded-full shadow-[0_8px_30px_rgba(0,0,0,0.12)] border border-gray-200/90 px-4 sm:px-6 lg:px-8 h-[48px] sm:h-[52px] flex items-center justify-between">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-2 group flex-shrink-0" title="ChargeHub.pk - Genuine Chargers, Genuine Cables">
                <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-7 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105">
            </a>
            <!-- Links -->
            <div class="hidden md:flex items-center space-x-4 lg:space-x-6 text-[12.5px] lg:text-[13.5px] font-semibold text-gray-800 tracking-normal">
                <a href="index.php" class="hover:text-brand-blue transition-colors whitespace-nowrap">Home</a>
                <a href="explore.php" class="text-brand-blue font-bold whitespace-nowrap">Catalog</a>
                <a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="hover:text-brand-blue transition-colors whitespace-nowrap">Prime 140W</a>
                <a href="index.php#charging" class="hover:text-brand-blue transition-colors whitespace-nowrap">Fast Chargers</a>
                <a href="index.php#charging" class="hover:text-brand-blue transition-colors whitespace-nowrap">Genuine Cables</a>
                <a href="index.php#charging" class="hover:text-brand-blue transition-colors whitespace-nowrap">Power Banks</a>
                <a href="index.php#sale" class="font-bold text-brand-orange hover:text-brand-gold flex items-center space-x-1 whitespace-nowrap transition-transform hover:scale-105">
                    <span>10.10 SALE</span>
                </a>
            </div>
            <!-- Actions -->
            <div class="flex items-center space-x-2.5 sm:space-x-3.5 flex-shrink-0">
                <!-- Search Button -->
                <a href="explore.php" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all hover:scale-105" title="Search catalog">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </a>

                <!-- Shopping Bag Button -->
                <button type="button" onclick="openCartDrawer()" class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all hover:scale-105 cursor-pointer" title="View Cart">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="cart-counter-badge absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-orange text-[10px] font-bold text-white flex items-center justify-center shadow-sm shadow-orange-500/30">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- ================================================================= -->
    <!-- TOP HERO BANNER WITH THE SIGNATURE CURVED SCOOP NAVBAR -->
    <!-- ================================================================= -->
    <section class="w-full px-1.5 sm:px-2 md:px-2.5 pt-0.5 sm:pt-1 pb-2">
        <div class="relative w-full rounded-[18px] sm:rounded-[24px] md:rounded-[30px] overflow-hidden shadow-2xl bg-gradient-to-r from-[#070b19] via-[#001f5c] to-[#070b19] pt-2 pb-8 sm:pb-10 px-4 sm:px-8 border border-blue-900/40">
            
            <!-- THE CURVED NAV NOTCH / SCOOP HEADER (THE EXACT SIGNATURE COMPONENT) -->
            <header class="relative w-full h-[52px] sm:h-[58px] flex items-start justify-between z-40 mb-4 pointer-events-none">
                
                <!-- LEFT WING: CHARGEHUB LOGO -->
                <div class="pointer-events-auto flex items-center pt-2 sm:pt-2.5 z-50">
                    <a href="index.php" class="flex items-center space-x-2 group" title="ChargeHub.pk - Genuine Chargers, Genuine Cables">
                        <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-8 sm:h-9 lg:h-10 w-auto object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,0.6)] transition-transform group-hover:scale-105">
                    </a>
                </div>

                <!-- CENTRAL DIPPING TAB (1.25x Scaled Navbar Tab with Smooth Bézier S-Curves) -->
                <div id="scoop-nav-container" class="pointer-events-auto hidden md:flex items-center absolute top-0 left-1/2 -translate-x-1/2 scoop-tab z-50 transition-all duration-700">
                    <!-- LEFT S-CURVE TRANSITION WING -->
                    <svg class="h-[42px] sm:h-[48px] lg:h-[50px] w-[58px] sm:w-[65px] lg:w-[70px] -mr-[0.5px] flex-shrink-0 pointer-events-none transition-all duration-700" viewBox="0 0 52 33" fill="none" preserveAspectRatio="none">
                        <path id="scoop-left-path" d="M0 0 H52 V33 C30 33 22 0 0 0 Z" fill="#0052cc" class="transition-colors duration-700"/>
                    </svg>

                    <!-- CENTRAL NAVIGATION LINKS BODY -->
                    <nav id="scoop-nav-center" class="h-[42px] sm:h-[48px] lg:h-[50px] px-8 sm:px-10 lg:px-12 flex items-center space-x-6 sm:space-x-8 lg:space-x-10 text-[13.5px] sm:text-[15px] lg:text-[15.5px] font-semibold text-white/95 tracking-normal transition-colors duration-700 bg-[#0052cc]">
                        <a href="index.php" class="hover:text-white transition-colors whitespace-nowrap">Home</a>
                        <a href="explore.php" class="text-white font-black underline decoration-brand-orange underline-offset-4 whitespace-nowrap">Catalog</a>
                        <a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="hover:text-white transition-colors whitespace-nowrap">Prime 140W</a>
                        <a href="index.php#charging" class="hover:text-white transition-colors whitespace-nowrap">Fast Chargers</a>
                        <a href="index.php#charging" class="hover:text-white transition-colors whitespace-nowrap">Genuine Cables</a>
                        <a href="index.php#charging" class="hover:text-white transition-colors whitespace-nowrap">Power Banks</a>
                        <a href="index.php#sale" class="font-bold text-[#ffd700] hover:text-yellow-300 flex items-center space-x-1 whitespace-nowrap transition-transform hover:scale-105">
                            <span class="tracking-wider">10.10 SALE</span>
                        </a>
                    </nav>

                    <!-- RIGHT S-CURVE TRANSITION WING -->
                    <svg class="h-[42px] sm:h-[48px] lg:h-[50px] w-[58px] sm:w-[65px] lg:w-[70px] -ml-[0.5px] flex-shrink-0 pointer-events-none transition-all duration-700" viewBox="0 0 52 33" fill="none" preserveAspectRatio="none">
                        <path id="scoop-right-path" d="M0 0 H52 C30 0 22 33 0 33 V0 Z" fill="#0052cc" class="transition-colors duration-700"/>
                    </svg>
                </div>

                <!-- RIGHT WING: SEARCH & CART ICONS -->
                <div class="pointer-events-auto flex items-center space-x-3 sm:space-x-4 pt-2 sm:pt-2.5 z-50">
                    <!-- Search Link -->
                    <a href="explore.php" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-white/80 hover:text-white transition-all hover:scale-105" title="Search catalog">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>

                    <!-- Shopping Bag Button -->
                    <button type="button" onclick="openCartDrawer()" class="relative w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-white/80 hover:text-white transition-all hover:scale-105 cursor-pointer" title="View Cart">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="cart-counter-badge absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-orange text-[10px] font-bold text-white flex items-center justify-center shadow-sm shadow-orange-500/30">0</span>
                    </button>
                    <!-- Mobile Hamburger -->
                    <button id="mobile-menu-trigger" class="md:hidden w-8 h-8 flex items-center justify-center text-white/80 hover:text-white transition-all" title="Open Navigation Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </div>
            </header>

            <!-- MOBILE DROPDOWN MENU -->
            <div id="mobile-menu" class="hidden md:hidden absolute top-[56px] inset-x-4 z-50 bg-[#1e2026]/95 backdrop-blur-xl rounded-2xl p-5 border border-white/20 shadow-2xl space-y-3">
                <a href="index.php" class="block text-white font-medium hover:text-brand-gold py-1">🏠 Home</a>
                <a href="explore.php" class="block text-brand-gold font-bold py-1">📂 Catalog / All Products</a>
                <a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="block text-white font-medium hover:text-brand-gold py-1">⚡ Anker Prime 140W Detail</a>
                <a href="index.php#charging" class="block text-white font-medium hover:text-brand-gold py-1">🔌 Fast Chargers</a>
                <a href="index.php#charging" class="block text-white font-medium hover:text-brand-gold py-1">⚡ Genuine Cables</a>
                <a href="index.php#charging" class="block text-white font-medium hover:text-brand-gold py-1">🔋 Power Banks</a>
                <a href="index.php#sale" class="block text-brand-orange font-bold py-1">🔥 10.10 SALE Specials</a>
            </div>

            <!-- BREADCRUMBS IN HERO -->
            <div class="max-w-7xl mx-auto pt-2 flex items-center space-x-2 text-xs text-blue-200/80">
                <a href="index.php" class="hover:text-white transition-colors">Home</a>
                <span>/</span>
                <span class="text-white font-bold">Product Catalog</span>
                <span>/</span>
                <span class="text-brand-gold font-bold">All Products &amp; Accessories</span>
            </div>

        </div>
    </section>

    <!-- ================================================================= -->
    <!-- MAIN PRODUCT CATALOG: 2-COLUMN LAYOUT WITH FILTERS & 3-CARD ROWS -->
    <!-- ================================================================= -->
    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 flex-1 w-full">
        
        <!-- CATALOG TOOLBAR & CONTROLS -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-8 border-b border-gray-100 gap-4">
            <div>
                <div class="flex items-center space-x-2.5">
                    <span class="inline-block w-4 sm:w-5 h-0.5 bg-brand-orange rounded-full"></span>
                    <h1 class="brand-font text-xl sm:text-2xl md:text-3xl font-extrabold text-gray-950 tracking-tight">
                        Product Catalog
                    </h1>
                    <span id="catalog-count-badge" class="ml-2 px-2.5 py-0.5 text-xs font-bold text-brand-blue bg-blue-50 rounded-full border border-blue-100"><?= count($products) ?> Product<?= count($products) === 1 ? "" : "s" ?></span>
                </div>
                <p class="text-xs text-gray-500 mt-1 pl-6">
                    Pakistan's genuine GaN chargers, indestructible Kevlar cables, and premium audio gear.
                </p>
            </div>

            <!-- CONTROLS: MOBILE FILTER TRIGGER & SORT DROPDOWN -->
            <div class="flex items-center space-x-3">
                <!-- Mobile Filter Toggle Button (Visible only on < lg screens) -->
                <button id="mobile-filter-toggle" class="lg:hidden flex items-center space-x-2 px-4 py-2 bg-white rounded-xl border border-gray-200 shadow-sm text-xs font-bold text-gray-800 hover:bg-gray-50 active:scale-95 transition-all">
                    <svg class="w-4 h-4 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filters</span>
                    <span id="mobile-filter-badge" class="w-5 h-5 bg-brand-orange text-white rounded-full text-[10px] flex items-center justify-center font-bold">0</span>
                </button>

                <!-- Sort By Dropdown -->
                <div class="flex items-center space-x-2 text-xs">
                    <span class="text-gray-400 font-medium hidden sm:inline">Sort:</span>
                    <select id="catalog-sort" class="bg-white border border-gray-200 text-gray-800 font-semibold text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue cursor-pointer shadow-sm">
                        <option value="featured">Featured First</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="discount">Highest Discount</option>
                        <option value="rating">Top Rated</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- TWO-COLUMN LAYOUT: LEFT FILTER SIDEBAR + RIGHT 3-COLUMN PRODUCT GRID -->
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10 items-start">

            <!-- ========================================================= -->
            <!-- LEFT FILTER SIDEBAR: MAINTAINS CHARGEHUB DESIGN & THEME -->
            <!-- ========================================================= -->
            <aside id="filter-sidebar" class="hidden lg:block w-full lg:w-72 xl:w-80 shrink-0 lg:sticky lg:top-24 z-30 transition-all duration-300">
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-6">
                    
                    <!-- SIDEBAR HEADER: FILTERS TITLE + RESET BUTTON -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Filters</h3>
                        </div>
                        <button type="button" id="reset-filters-btn" class="text-xs font-bold text-brand-orange hover:text-brand-gold transition-colors flex items-center space-x-1">
                            <span>Reset All</span>
                        </button>
                    </div>

                    <!-- SEARCH WITHIN CATALOG -->
                    <div class="relative">
                        <input type="text" id="filter-search-input" placeholder="Search in catalog..." class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-blue/30 focus:border-brand-blue text-gray-800 placeholder-gray-400 transition-all">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- FILTER SECTION 1: CATEGORIES -->
                    <div class="space-y-3 pt-2">
                        <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider flex items-center justify-between">
                            <span>Category</span>
                            <span class="w-2 h-2 rounded-full bg-brand-blue"></span>
                        </h4>
                        <div class="space-y-2 text-xs">
                            <?php foreach ($categories as $cat): ?>
                            <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors group">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" value="<?= htmlspecialchars($cat['slug']) ?>" class="category-checkbox w-4 h-4 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                                    <span class="font-medium text-gray-700 group-hover:text-gray-950"><?= htmlspecialchars($cat['name']) ?></span>
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 bg-gray-100 group-hover:bg-blue-50 group-hover:text-brand-blue px-2 py-0.5 rounded-full transition-colors">
                                    <?= (int)($cat['product_count'] ?? 0) ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- FILTER SECTION 2: WATTAGE / POWER SPEC -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider flex items-center justify-between">
                            <span>Wattage / Power</span>
                            <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                        </h4>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 hover:bg-blue-50/60 cursor-pointer transition-colors">
                                <input type="checkbox" value="30w" class="wattage-checkbox w-3.5 h-3.5 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                                <span class="text-[11.5px] font-semibold text-gray-700">30W Nano</span>
                            </label>
                            <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 hover:bg-blue-50/60 cursor-pointer transition-colors">
                                <input type="checkbox" value="45w" class="wattage-checkbox w-3.5 h-3.5 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                                <span class="text-[11.5px] font-semibold text-gray-700">45W Dual</span>
                            </label>
                            <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 hover:bg-blue-50/60 cursor-pointer transition-colors">
                                <input type="checkbox" value="65w" class="wattage-checkbox w-3.5 h-3.5 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                                <span class="text-[11.5px] font-semibold text-gray-700">65W GaN</span>
                            </label>
                            <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 hover:bg-blue-50/60 cursor-pointer transition-colors">
                                <input type="checkbox" value="100w" class="wattage-checkbox w-3.5 h-3.5 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                                <span class="text-[11.5px] font-semibold text-gray-700">100W Pro</span>
                            </label>
                            <label class="col-span-2 flex items-center space-x-2 p-2 rounded-xl bg-slate-50 hover:bg-blue-50/60 cursor-pointer transition-colors">
                                <input type="checkbox" value="240w" class="wattage-checkbox w-3.5 h-3.5 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                                <span class="text-[11.5px] font-semibold text-gray-700">240W Indestructible PD</span>
                            </label>
                        </div>
                    </div>

                    <!-- FILTER SECTION 3: MAX PRICE RANGE -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Max Price</h4>
                            <span id="price-slider-label" class="text-xs font-black text-brand-blue bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-md">Rs. <?= number_format($maxSliderVal) ?></span>
                        </div>
                        <input type="range" id="price-slider" min="<?= $minSliderVal ?>" max="<?= $maxSliderVal ?>" step="500" value="<?= $maxSliderVal ?>" class="w-full accent-brand-blue cursor-pointer">
                        <div class="flex justify-between text-[10px] font-semibold text-gray-400">
                            <span>Rs. <?= number_format($minSliderVal) ?></span>
                            <span>Rs. <?= number_format($maxSliderVal) ?></span>
                        </div>
                    </div>

                    <!-- FILTER SECTION 4: AVAILABILITY & SALE STATUS -->
                    <div class="space-y-2.5 pt-3 border-t border-slate-100">
                        <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Availability &amp; Deals</h4>
                        <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors">
                            <span class="text-xs font-medium text-gray-700">In Stock Only</span>
                            <input type="checkbox" id="instock-toggle" class="w-4 h-4 rounded text-brand-blue focus:ring-brand-blue border-gray-300">
                        </label>
                        <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors">
                            <span class="text-xs font-medium text-brand-orange font-bold flex items-center space-x-1">
                                <span>🔥 10.10 SALE Specials</span>
                            </span>
                            <input type="checkbox" id="sale-toggle" class="w-4 h-4 rounded text-brand-orange focus:ring-brand-orange border-gray-300">
                        </label>
                    </div>

                    <!-- CHARGEHUB TRUST MINI-BADGE -->
                    <div class="pt-4 border-t border-slate-100">
                        <div class="p-3.5 rounded-2xl bg-gradient-to-br from-blue-50/70 via-slate-50 to-orange-50/40 border border-blue-100/60 text-center space-y-1.5">
                            <div class="text-[11px] font-black text-brand-dark uppercase tracking-wider flex items-center justify-center space-x-1.5">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>100% Genuine Seals</span>
                            </div>
                            <p class="text-[10.5px] text-gray-500 leading-tight">
                                QR scannable authenticity &amp; 18-month direct replacement claim.
                            </p>
                        </div>
                    </div>

                </div>
            </aside>

            <!-- ========================================================= -->
            <!-- RIGHT COLUMN: 3 POP-OUT PRODUCT CARDS PER ROW -->
            <!-- ========================================================= -->
            <div class="flex-1 min-w-0 w-full">
                <!-- 3 POP-OUT PRODUCT CARDS PER ROW (SCULPTED S-CURVE, OUTSIDE-THE-BOX FLOATING) -->
                <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7 gap-y-16 sm:gap-y-20 pt-10 sm:pt-14">
            
                    <!-- ========================================================= -->
                    <?php foreach ($products as $prod): 
                        $discPct = 0;
                        if (!empty($prod['original_price']) && $prod['original_price'] > $prod['price']) {
                            $discPct = round((($prod['original_price'] - $prod['price']) / $prod['original_price']) * 100);
                        }
                        $wattageSlug = '';
                        if (preg_match('/(\d{2,3})\s*W\b/i', $prod['name'] . ' ' . ($prod['badge_text'] ?? '') . ' ' . ($prod['sku'] ?? ''), $wMatch)) {
                            $wattageSlug = strtolower($wMatch[1]) . 'w';
                        }
                        $inStock = ($prod['stock_quantity'] > 0 || stripos($prod['stock_status'] ?? '', 'in stock') !== false || empty($prod['stock_status']));
                        $onSale = ($discPct > 0);
                        
                        $specsList = [];
                        if (!empty($prod['specs'])) {
                            $dec = json_decode($prod['specs'], true);
                            if (is_array($dec)) {
                                foreach ($dec as $k => $v) {
                                    if (is_string($v) && count($specsList) < 3) {
                                        $specsList[] = ['title' => $k, 'sub' => $v];
                                    }
                                }
                            }
                        }
                        if (empty($specsList)) {
                            $specsList = [
                                ['title' => (!empty($prod['category_name']) ? $prod['category_name'] : 'Fast Power'), 'sub' => (!empty($prod['brand_name']) ? $prod['brand_name'] : 'Official')],
                                ['title' => 'Warranty', 'sub' => '1-Year Claim'],
                                ['title' => 'Stock Status', 'sub' => (!empty($prod['stock_status']) ? $prod['stock_status'] : 'In Stock')]
                            ];
                        }
                    ?>
                    <!-- DYNAMIC PRODUCT CARD -->
                    <div class="catalog-card group relative flex flex-col justify-between p-5 sm:p-6 transition-all duration-300 hover:-translate-y-1 filter drop-shadow-[0_8px_25px_rgba(0,0,0,0.06)] hover:drop-shadow-[0_16px_35px_rgba(0,0,0,0.12)]" 
                         data-name="<?= htmlspecialchars($prod['name']) ?>" 
                         data-category="<?= htmlspecialchars($prod['category_slug'] ?? '') ?>" 
                         data-brand="<?= htmlspecialchars($prod['brand_slug'] ?? '') ?>"
                         data-wattage="<?= htmlspecialchars($wattageSlug) ?>" 
                         data-price="<?= (int)$prod['price'] ?>" 
                         data-rating="<?= number_format($prod['rating'] ?? 5.0, 1) ?>" 
                         data-discount="<?= (int)$discPct ?>" 
                         data-instock="<?= $inStock ? 'true' : 'false' ?>" 
                         data-sale="<?= $onSale ? 'true' : 'false' ?>">
                        
                        <!-- SCULPTED CARD SVG BACKGROUND WITH ORGANIC S-CURVE RIGHT SCOOP -->
                        <svg class="absolute inset-0 w-full h-full -z-10 pointer-events-none" viewBox="0 0 280 440" preserveAspectRatio="none" fill="none">
                            <path d="M 28 0 H 252 C 268 0 280 12 280 28 V 135 C 280 168 244 172 244 202 V 265 C 244 295 280 298 280 330 V 412 C 280 428 268 440 252 440 H 28 C 12 440 0 428 0 412 V 28 C 0 12 12 0 28 0 Z" fill="#ffffff" stroke="#f1f5f9" stroke-width="1.2"/>
                        </svg>

                        <!-- VERTICAL RIBBON PILL -->
                        <?php if (!empty($prod['badge_text'])): ?>
                        <div class="absolute right-0 top-[35%] -translate-y-1/2 z-20 flex items-center justify-center">
                            <div class="w-6 sm:w-6.5 h-28 sm:h-32 rounded-l-2xl bg-gradient-to-b from-[#0036b8] via-[#a21caf] to-[#ff6b00] shadow-md flex items-center justify-center p-1">
                                <span class="text-[8px] sm:text-[8.5px] font-black text-white uppercase tracking-widest whitespace-nowrap rotate-90 select-none">
                                    <?= htmlspecialchars($prod['badge_text']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- TOP FLOATING PRODUCT IMAGE (BURSTS OUTSIDE THE CARD BOX) -->
                        <div class="relative -mt-14 sm:-mt-18 mb-3 flex items-center justify-center h-48 sm:h-52 overflow-visible">
                            <a href="product-detail.php?slug=<?= htmlspecialchars($prod['slug']) ?>" class="w-full h-full flex items-center justify-center">
                                <img src="<?= htmlspecialchars($prod['main_image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="max-h-full max-w-[90%] object-contain transition-transform duration-500 group-hover:scale-108 group-hover:-translate-y-1.5" onerror="this.src='images/Hero Banner.jpg'">
                            </a>
                        </div>

                        <!-- PRODUCT DETAILS -->
                        <div>
                            <h3 class="font-black text-sm sm:text-base text-gray-900 tracking-tight uppercase group-hover:text-brand-blue transition-colors line-clamp-1" title="<?= htmlspecialchars($prod['name']) ?>">
                                <a href="product-detail.php?slug=<?= htmlspecialchars($prod['slug']) ?>"><?= htmlspecialchars($prod['name']) ?></a>
                            </h3>
                            <div class="text-[11px] sm:text-xs text-gray-400 font-medium tracking-wide mt-0.5">
                                <?= htmlspecialchars($prod['sku'] ?? (!empty($prod['brand_name']) ? ($prod['brand_name'] . ' • ' . $prod['slug']) : $prod['slug'])) ?>
                            </div>

                            <!-- STAR RATING -->
                            <div class="flex items-center space-x-1 my-2">
                                <span class="text-amber-400 text-xs sm:text-sm tracking-tighter">
                                    <?= str_repeat('★', (int)round($prod['rating'] ?? 5)) ?><?= str_repeat('☆', 5 - (int)round($prod['rating'] ?? 5)) ?>
                                </span>
                                <span class="text-[11px] sm:text-xs text-gray-500 font-semibold ml-1"><?= number_format($prod['rating'] ?? 5.0, 1) ?></span>
                                <?php if (!empty($prod['reviews_count'])): ?>
                                <span class="text-[10px] text-gray-400 font-normal">(<?= (int)$prod['reviews_count'] ?>)</span>
                                <?php endif; ?>
                            </div>

                            <!-- PRICE & COLOR SWATCHES -->
                            <div class="flex items-end justify-between my-2">
                                <div>
                                    <?php if ($discPct > 0): ?>
                                    <span class="text-[11px] sm:text-xs font-bold text-red-500 block leading-tight"><?= $discPct ?>% Off</span>
                                    <?php endif; ?>
                                    <div class="flex items-baseline space-x-1.5 mt-0.5">
                                        <span class="font-black text-base sm:text-lg text-gray-950 tracking-tight">Rs.<?= number_format($prod['price']) ?></span>
                                        <?php if (!empty($prod['original_price']) && $prod['original_price'] > $prod['price']): ?>
                                        <span class="text-xs text-gray-400 line-through font-normal">Rs.<?= number_format($prod['original_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Swatches -->
                                <div class="flex items-center space-x-1.5 mb-1 mr-4">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#1e293b] ring-1 ring-gray-200 inline-block"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#38bdf8] ring-1 ring-gray-200 inline-block"></span>
                                </div>
                            </div>

                            <!-- BOTTOM SPECS & GLOWING BUY NOW BUTTON -->
                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2 mt-2">
                                <div class="grid grid-cols-3 gap-1.5 flex-grow text-left">
                                    <?php foreach ($specsList as $sp): ?>
                                    <div>
                                        <div class="text-[9.5px] font-bold text-gray-900 leading-tight line-clamp-1"><?= htmlspecialchars($sp['title']) ?></div>
                                        <div class="text-[8px] text-gray-400 leading-tight line-clamp-1"><?= htmlspecialchars($sp['sub']) ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="flex-shrink-0 relative group/btn p-[2px] rounded-full bg-gradient-to-r from-[#00d2ff] via-[#a855f7] to-[#ec4899] shadow-[0_3px_14px_rgba(236,72,153,0.30)] hover:shadow-[0_4px_20px_rgba(0,210,255,0.45)] transition-all active:scale-95 add-cart-btn cursor-pointer" data-product="<?= htmlspecialchars($prod['name']) ?>" data-slug="<?= htmlspecialchars($prod['slug']) ?>" data-price="<?= (float)$prod['price'] ?>" data-image="<?= htmlspecialchars($prod['main_image']) ?>">
                                    <div class="px-3 py-1.5 rounded-full bg-[#0a0b0e] hover:bg-[#15171e] flex items-center space-x-1.5 transition-colors">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span class="text-[10px] font-extrabold text-white tracking-wide whitespace-nowrap">Buy Now</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; ?>

                    <!-- NO PRODUCTS FOUND EMPTY STATE (HIDDEN BY DEFAULT) -->
            <div id="no-products-msg" class="hidden col-span-full py-16 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 text-brand-blue flex items-center justify-center text-2xl font-bold">🔍</div>
                <h3 class="text-base font-extrabold text-gray-900 mb-1">No matching products found</h3>
                <p class="text-xs text-gray-500 mb-4">Try relaxing your category or price filters to see more genuine products.</p>
                <button type="button" id="empty-reset-btn" class="px-5 py-2 rounded-full bg-brand-blue text-white text-xs font-bold hover:bg-blue-600 transition-colors shadow-md">Reset All Filters</button>
            </div>

        </div><!-- /#product-grid -->
    </div><!-- /.flex-1 -->
</div><!-- /.flex-col.lg:flex-row -->

    </main>

    <!-- TRUST FEATURES BAR -->
    <section class="bg-white border-y border-gray-200/80 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-blue-50 text-brand-blue flex items-center justify-center flex-shrink-0 text-xl font-black">🛡️</div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">18-Month Warranty</h4>
                    <p class="text-[11px] text-gray-500">Replacement claim guarantee</p>
                </div>
            </div>
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-orange-50 text-brand-orange flex items-center justify-center flex-shrink-0 text-xl font-black">🚀</div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">Overnight Express</h4>
                    <p class="text-[11px] text-gray-500">Lahore, Karachi &amp; Islamabad</p>
                </div>
            </div>
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xl font-black">✓</div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">100% Genuine Seals</h4>
                    <p class="text-[11px] text-gray-500">Scannable QR verification</p>
                </div>
            </div>
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-brand-gold flex items-center justify-center flex-shrink-0 text-xl font-black">💵</div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">Cash on Delivery</h4>
                    <p class="text-[11px] text-gray-500">Pay upon parcel inspection</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-brand-dark text-slate-300 pt-14 pb-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Brand Info -->
                <div class="space-y-4">
                    <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-8 w-auto brightness-200 invert object-contain">
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pakistan's premier destination for 100% authentic GaN fast chargers, indestructible Kevlar cables, and high-capacity portable power stations.
                    </p>
                    <div class="text-xs font-semibold text-slate-400">
                        <span>WhatsApp Helpline: </span>
                        <a href="https://wa.me/923001234567" class="text-brand-orange hover:text-brand-gold font-bold">+92 300 1234567</a>
                    </div>
                </div>

                <!-- Col 2: Fast Chargers -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">Fast Chargers</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="hover:text-brand-blue transition-colors">140W GaN Prime Wall Chargers</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">100W GaN Desktop Stations</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">67W Dual Type-C Mini Adapters</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">45W Samsung Super Fast 2.0</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">Apple 20W USB-C PD</a></li>
                    </ul>
                </div>

                <!-- Col 3: Indestructible Cables -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">Cables &amp; Docks</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">240W Digital Display Kevlar</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">100W Heavy-Duty Braided Type-C</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">Apple MFi Certified Lightning</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">3-in-1 Foldable MagSafe Qi2</a></li>
                    </ul>
                </div>

                <!-- Col 4: Customer Care -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">Customer Support</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#" class="hover:text-brand-blue transition-colors">Track Your Order (TCS/Trax)</a></li>
                        <li><a href="#" class="hover:text-brand-blue transition-colors">1-Year Warranty Claim</a></li>
                        <li><a href="#" class="hover:text-brand-blue transition-colors">7-Day Replacement Policy</a></li>
                        <li><a href="#" class="hover:text-brand-blue transition-colors">Corporate &amp; Wholesale Inquiries</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; 2026 ChargeHub.pk. All Rights Reserved. Genuine Chargers. Genuine Cables.</p>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="hover:text-slate-300">Home</a>
                    <a href="explore.php" class="text-slate-300 font-bold">Catalog</a>
                    <a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="hover:text-slate-300">Product Detail</a>
                    <a href="#" class="hover:text-slate-300">Privacy Policy</a>
                    <a href="#" class="hover:text-slate-300">Terms of Service</a>
                    <a href="admin/login.php" class="text-brand-orange hover:text-brand-gold font-bold flex items-center space-x-1"><span>🛡️ Admin Portal</span></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- FLOATING WHATSAPP BUTTON -->
    <a href="https://wa.me/923001234567" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 bg-[#25d366] text-white rounded-full shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 whatsapp-ripple group" title="Chat with ChargeHub Support on WhatsApp">
        <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
    </a>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mobile Menu Toggle
            const mobileBtn = document.getElementById('mobile-menu-trigger');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Sticky Navbar Scroll Listener (Identical to index.php)
            const stickyNav = document.getElementById('sticky-navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 80) {
                    stickyNav.classList.remove('-translate-y-24', 'opacity-0', 'pointer-events-none');
                    stickyNav.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
                } else {
                    stickyNav.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
                    stickyNav.classList.add('-translate-y-24', 'opacity-0', 'pointer-events-none');
                }
            }, { passive: true });

            // =========================================================
            // CATALOG FILTER & SORT LOGIC
            // =========================================================
            const cards = Array.from(document.querySelectorAll('.catalog-card'));
            const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
            const wattageCheckboxes = document.querySelectorAll('.wattage-checkbox');
            const priceSlider = document.getElementById('price-slider');
            const priceSliderLabel = document.getElementById('price-slider-label');
            const instockToggle = document.getElementById('instock-toggle');
            const saleToggle = document.getElementById('sale-toggle');
            const searchInput = document.getElementById('filter-search-input');
            const sortSelect = document.getElementById('catalog-sort');
            const resetBtn = document.getElementById('reset-filters-btn');
            const emptyResetBtn = document.getElementById('empty-reset-btn');
            const countBadge = document.getElementById('catalog-count-badge');
            const mobileFilterBadge = document.getElementById('mobile-filter-badge');
            const noProductsMsg = document.getElementById('no-products-msg');
            const productGrid = document.getElementById('product-grid');
            const mobileFilterBtn = document.getElementById('mobile-filter-toggle');
            const filterSidebar = document.getElementById('filter-sidebar');

            // Mobile Filter Drawer Toggle
            if (mobileFilterBtn && filterSidebar) {
                mobileFilterBtn.addEventListener('click', () => {
                    filterSidebar.classList.toggle('hidden');
                });
            }

            function applyFiltersAndSort() {
                const selectedCats = Array.from(categoryCheckboxes).filter(c => c.checked).map(c => c.value);
                const selectedWatts = Array.from(wattageCheckboxes).filter(c => c.checked).map(c => c.value);
                const maxPrice = priceSlider ? parseInt(priceSlider.value) : <?= $maxSliderVal ?>;
                const inStockOnly = instockToggle ? instockToggle.checked : false;
                const saleOnly = saleToggle ? saleToggle.checked : false;
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

                if (priceSliderLabel) {
                    priceSliderLabel.textContent = `Rs. ${maxPrice.toLocaleString()}`;
                }

                // Active filter count
                const activeCount = selectedCats.length + selectedWatts.length + (inStockOnly ? 1 : 0) + (saleOnly ? 1 : 0) + (maxPrice < <?= $maxSliderVal ?> ? 1 : 0) + (query ? 1 : 0);
                if (mobileFilterBadge) {
                    mobileFilterBadge.textContent = activeCount;
                }

                let visibleCount = 0;
                cards.forEach(card => {
                    const cat = (card.dataset.category || '').toLowerCase();
                    const wat = (card.dataset.wattage || '').toLowerCase();
                    const brand = (card.dataset.brand || '').toLowerCase();
                    const price = parseInt(card.dataset.price) || 0;
                    const instock = card.dataset.instock === 'true';
                    const sale = card.dataset.sale === 'true';
                    const name = (card.dataset.name || '').toLowerCase();

                    const matchCat = selectedCats.length === 0 || selectedCats.some(sc => cat.includes(sc.toLowerCase()));
                    const matchWat = selectedWatts.length === 0 || selectedWatts.some(sw => wat.includes(sw.toLowerCase()));
                    const matchPrice = price <= maxPrice;
                    const matchStock = !inStockOnly || instock;
                    const matchSale = !saleOnly || sale;
                    const matchSearch = !query || name.includes(query) || brand.includes(query) || cat.includes(query);

                    if (matchCat && matchWat && matchPrice && matchStock && matchSale && matchSearch) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (countBadge) {
                    countBadge.textContent = `${visibleCount} Product${visibleCount === 1 ? '' : 's'}`;
                }

                if (noProductsMsg) {
                    if (visibleCount === 0) {
                        noProductsMsg.classList.remove('hidden');
                    } else {
                        noProductsMsg.classList.add('hidden');
                    }
                }

                // Sort cards
                const sortMode = sortSelect ? sortSelect.value : 'featured';
                const sorted = [...cards].sort((a, b) => {
                    const priceA = parseInt(a.dataset.price) || 0;
                    const priceB = parseInt(b.dataset.price) || 0;
                    const discA = parseInt(a.dataset.discount) || 0;
                    const discB = parseInt(b.dataset.discount) || 0;
                    const rateA = parseFloat(a.dataset.rating) || 0;
                    const rateB = parseFloat(b.dataset.rating) || 0;

                    if (sortMode === 'price-asc') return priceA - priceB;
                    if (sortMode === 'price-desc') return priceB - priceA;
                    if (sortMode === 'discount') return discB - discA;
                    if (sortMode === 'rating') return rateB - rateA;
                    return 0; // default order
                });

                sorted.forEach(c => productGrid.appendChild(c));
                if (noProductsMsg) productGrid.appendChild(noProductsMsg);
            }

            function resetFilters() {
                categoryCheckboxes.forEach(c => c.checked = false);
                wattageCheckboxes.forEach(c => c.checked = false);
                if (priceSlider) priceSlider.value = <?= $maxSliderVal ?>;
                if (instockToggle) instockToggle.checked = false;
                if (saleToggle) saleToggle.checked = false;
                if (searchInput) searchInput.value = '';
                if (sortSelect) sortSelect.value = 'featured';
                applyFiltersAndSort();
            }

            categoryCheckboxes.forEach(c => c.addEventListener('change', applyFiltersAndSort));
            wattageCheckboxes.forEach(c => c.addEventListener('change', applyFiltersAndSort));
            if (priceSlider) priceSlider.addEventListener('input', applyFiltersAndSort);
            if (instockToggle) instockToggle.addEventListener('change', applyFiltersAndSort));
            if (saleToggle) saleToggle.addEventListener('change', applyFiltersAndSort));
            if (searchInput) searchInput.addEventListener('input', applyFiltersAndSort);
            if (sortSelect) sortSelect.addEventListener('change', applyFiltersAndSort);
            if (resetBtn) resetBtn.addEventListener('click', resetFilters);
            if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetFilters);

            // Read URL search / brand filter parameters if present
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const brandParam = urlParams.get('brand') || urlParams.get('q') || urlParams.get('search');
                if (brandParam && searchInput) {
                    searchInput.value = decodeURIComponent(brandParam);
                }
                const catParam = urlParams.get('category') || urlParams.get('cat');
                if (catParam) {
                    let matchedCheckbox = false;
                    categoryCheckboxes.forEach(cb => {
                        if (cb.value.toLowerCase() === catParam.toLowerCase()) {
                            cb.checked = true;
                            matchedCheckbox = true;
                        }
                    });
                    if (!matchedCheckbox && searchInput && !searchInput.value) {
                        searchInput.value = decodeURIComponent(catParam);
                    }
                }
            } catch(e) {}

            // Initial check
            applyFiltersAndSort();
        });
    </script>

    <?php require_once __DIR__ . '/includes/cart_drawer.php'; ?>

</body>
</html>
