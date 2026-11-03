<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch product dynamically using prepared statement
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (!empty($product_slug)) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE p.slug = ? AND p.is_active = 1 LIMIT 1");
    $stmt->execute([$product_slug]);
    $product = $stmt->fetch();
} elseif ($product_id > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE p.id = ? AND p.is_active = 1 LIMIT 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} else {
    // Default to first active product (Anker Prime 140W)
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE p.is_active = 1 ORDER BY p.id ASC LIMIT 1");
    $product = $stmt->fetch();
}

// Fallback safety if database is empty
if (!$product) {
    $product = [
        'id' => 1,
        'name' => 'Anker Prime 140W GaN Wall Charger (3-Port 2C1A Fast GaNPrime™)',
        'slug' => 'anker-prime-140w-gan-charger',
        'sku' => 'ANK-PRM-140W-GRY',
        'badge_text' => '⚡ PD 3.1 140W PEAK',
        'price' => 22499,
        'original_price' => 28999,
        'stock_status' => 'In Stock (Lahore Hub)',
        'stock_quantity' => 45,
        'warranty_text' => '18-Month Official Warranty & Swap Guarantee',
        'rating' => 4.9,
        'reviews_count' => 348,
        'orders_count' => 1420,
        'main_image' => 'images/product_detail/gallery_1_hero.jpg',
        'gallery_images' => json_encode(['images/product_detail/gallery_1_hero.jpg', 'images/product_detail/gallery_2_ports.jpg', 'images/product_detail/gallery_3_speed.jpg', 'images/product_detail/gallery_4_chip.jpg']),
        'short_description' => 'Ultra-compact multi-port power. Fast charge your MacBook Pro, iPhone 16 & Galaxy simultaneously at peak speeds with intelligent dynamic power distribution.',
        'full_description' => '<p>The Anker Prime 140W GaN Wall Charger is the ultimate all-in-one charging solution designed for demanding professionals.</p>',
        'category_name' => 'Fast Chargers (GaN)',
        'brand_name' => 'Anker',
        'bundle_name' => 'Bundle + Anker 140W Bio-Braided Cable (6ft)',
        'bundle_price' => 24999,
        'bundle_original_price' => 32499,
    ];
}

// Parse structured JSON fields
$gallery = json_decode($product['gallery_images'] ?? '[]', true) ?: [$product['main_image']];
$features = json_decode($product['features'] ?? '[]', true) ?: [];
$specs = json_decode($product['specs'] ?? '[]', true) ?: [];
$powerMatrix = json_decode($product['power_matrix'] ?? '[]', true) ?: [];

// Normalize power matrix values (supporting both string and legacy array formats)
$pm_single = '';
if (!empty($powerMatrix['single_port'])) {
    if (is_string($powerMatrix['single_port'])) {
        $pm_single = $powerMatrix['single_port'];
    } elseif (is_array($powerMatrix['single_port'])) {
        $parts = [];
        foreach ($powerMatrix['single_port'] as $k => $v) { $parts[] = strtoupper(str_replace('_', '-', $k)) . ': ' . $v; }
        $pm_single = implode(' | ', $parts);
    }
}

$pm_dual = '';
if (!empty($powerMatrix['dual_port'])) {
    if (is_string($powerMatrix['dual_port'])) {
        $pm_dual = $powerMatrix['dual_port'];
    } elseif (is_array($powerMatrix['dual_port'])) {
        $parts = [];
        foreach ($powerMatrix['dual_port'] as $k => $v) { $parts[] = $v; }
        $pm_dual = implode(' | ', $parts);
    }
}

$pm_multi = '';
if (!empty($powerMatrix['multi_port'])) {
    $pm_multi = $powerMatrix['multi_port'];
} elseif (!empty($powerMatrix['triple_port'])) {
    $pm_multi = $powerMatrix['triple_port'];
}

$pm_note = $powerMatrix['allocation_note'] ?? '';

// Normalize technical specs - cleanly read from database without hardcoded assumptions
$specModel = $specs['Model'] ?? '';
$specOutput = $specs['Total Output'] ?? '';
$specPorts = $specs['Ports'] ?? '';
$specLaptopTier = $specs['Laptop Tier'] ?? '';
$specPhoneSpeed = $specs['Phone Speed'] ?? '';
$specProtocols = $specs['Protocols'] ?? '';
$specGaN = $specs['GaN Tech'] ?? '';
$specSafety = $specs['Safety System'] ?? '';
$specInput = $specs['Input'] ?? '';
$specPlug = $specs['Plug Type'] ?? '';
$specDimensions = $specs['Dimensions'] ?? '';
$specWeight = $specs['Weight'] ?? '';
$compatLaptops = $specs['Compat Laptops'] ?? '';
$compatPhones = $specs['Compat Phones'] ?? '';
$compatOther = $specs['Compat Other'] ?? '';

// Calculate discount percentage
$discountPct = 0;
if (!empty($product['original_price']) && $product['original_price'] > $product['price']) {
    $discountPct = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}

// Fetch related products for bottom section
$catId = (int)($product['category_id'] ?? 1);
$stmtRelated = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 AND id != ? ORDER BY id ASC LIMIT 4");
$stmtRelated->execute([$product['id']]);
$relatedProducts = $stmtRelated->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product['name']) ?> – ChargeHub.pk | Genuine Chargers &amp; Indestructible Cables</title>
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
        .tab-btn.active {
            border-bottom: 3px solid #0066ff;
            color: #0066ff;
            font-weight: 700;
        }
        .gallery-thumb.active {
            border-color: #0066ff;
            box-shadow: 0 0 0 2px rgba(0, 102, 255, 0.3);
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
                <a href="explore.php" class="hover:text-brand-blue transition-colors whitespace-nowrap">Catalog</a>
                <a href="product-detail.php" class="text-brand-blue font-bold whitespace-nowrap">Prime 140W</a>
                <a href="index.php#charging" class="hover:text-brand-blue transition-colors whitespace-nowrap">Fast Chargers</a>
                <a href="index.php#charging" class="hover:text-brand-blue transition-colors whitespace-nowrap">Genuine Cables</a>
                <a href="index.php#charging" class="hover:text-brand-blue transition-colors whitespace-nowrap">Power Banks</a>
                <a href="index.php#sale" class="font-bold text-brand-orange hover:text-brand-gold flex items-center space-x-1 whitespace-nowrap transition-transform hover:scale-105">
                    <span>10.10 SALE</span>
                </a>
            </div>
            <!-- Actions -->
            <div class="flex items-center space-x-2.5 sm:space-x-3.5 flex-shrink-0">
                <a href="explore.php" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all hover:scale-105" title="Search catalog">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </a>
                <button type="button" onclick="openCartDrawer()" class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all hover:scale-105 cursor-pointer" title="View Cart">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="cart-counter-badge absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-orange text-[10px] font-bold text-white flex items-center justify-center shadow-sm shadow-orange-500/30">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- ================================================================= -->
    <!-- TOP HEADER: SIGNATURE CURVED SCOOP NAVBAR (CLEAN & COMPACT) -->
    <!-- ================================================================= -->
    <section class="w-full px-1.5 sm:px-2 md:px-2.5 pt-0.5 sm:pt-1">
        <div class="relative w-full rounded-[18px] sm:rounded-[24px] md:rounded-[28px] overflow-visible bg-gradient-to-r from-[#070b19] via-[#001f5c] to-[#070b19] pt-2 pb-3 px-4 sm:px-8 border border-blue-900/40 shadow-lg">
            
            <!-- THE CURVED NAV NOTCH / SCOOP HEADER (SIGNATURE COMPONENT) -->
            <header class="relative w-full h-[52px] sm:h-[58px] flex items-start justify-between z-40 pointer-events-none">
                
                <!-- LEFT WING: CHARGEHUB LOGO -->
                <div class="pointer-events-auto flex items-center pt-2 sm:pt-2.5 z-50">
                    <a href="index.php" class="flex items-center space-x-2 group" title="ChargeHub.pk - Genuine Chargers, Genuine Cables">
                        <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-8 sm:h-9 lg:h-10 w-auto object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,0.6)] transition-transform group-hover:scale-105">
                    </a>
                </div>

                <!-- CENTRAL DIPPING TAB (Smooth Bézier S-Curves) -->
                <div id="scoop-nav-container" class="pointer-events-auto hidden md:flex items-center absolute top-0 left-1/2 -translate-x-1/2 scoop-tab z-50 transition-all duration-700">
                    <!-- LEFT S-CURVE TRANSITION WING -->
                    <svg class="h-[42px] sm:h-[48px] lg:h-[50px] w-[58px] sm:w-[65px] lg:w-[70px] -mr-[0.5px] flex-shrink-0 pointer-events-none transition-all duration-700" viewBox="0 0 52 33" fill="none" preserveAspectRatio="none">
                        <path id="scoop-left-path" d="M0 0 H52 V33 C30 33 22 0 0 0 Z" fill="#0052cc" class="transition-colors duration-700"/>
                    </svg>

                    <!-- CENTRAL NAVIGATION LINKS BODY -->
                    <nav id="scoop-nav-center" class="h-[42px] sm:h-[48px] lg:h-[50px] px-8 sm:px-10 lg:px-12 flex items-center space-x-6 sm:space-x-8 lg:space-x-10 text-[13.5px] sm:text-[15px] lg:text-[15.5px] font-semibold text-white/95 tracking-normal transition-colors duration-700 bg-[#0052cc]">
                        <a href="index.php" class="hover:text-white transition-colors whitespace-nowrap">Home</a>
                        <a href="explore.php" class="hover:text-white transition-colors whitespace-nowrap">Catalog</a>
                        <a href="product-detail.php" class="text-white font-black underline decoration-brand-orange underline-offset-4 whitespace-nowrap">Product Detail</a>
                        <a href="index.php#charging" class="hover:text-white transition-colors whitespace-nowrap">Fast Chargers</a>
                        <a href="index.php#charging" class="hover:text-white transition-colors whitespace-nowrap">Genuine Cables</a>
                        <a href="index.php#sale" class="font-bold text-[#ffd700] hover:text-yellow-300 flex items-center space-x-1 whitespace-nowrap transition-transform hover:scale-105">
                            <span class="tracking-wider">SALE</span>
                        </a>
                    </nav>

                    <!-- RIGHT S-CURVE TRANSITION WING -->
                    <svg class="h-[42px] sm:h-[48px] lg:h-[50px] w-[58px] sm:w-[65px] lg:w-[70px] -ml-[0.5px] flex-shrink-0 pointer-events-none transition-all duration-700" viewBox="0 0 52 33" fill="none" preserveAspectRatio="none">
                        <path id="scoop-right-path" d="M0 0 H52 C30 0 22 33 0 33 V0 Z" fill="#0052cc" class="transition-colors duration-700"/>
                    </svg>
                </div>

                <!-- RIGHT WING: SEARCH & CART ICONS -->
                <div class="pointer-events-auto flex items-center space-x-3 sm:space-x-4 pt-2 sm:pt-2.5 z-50">
                    <a href="explore.php" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-white/80 hover:text-white transition-all hover:scale-105" title="Search catalog">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </a>
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
            <div id="mobile-menu" class="hidden md:hidden absolute top-[60px] inset-x-4 z-50 bg-[#1e2026]/95 backdrop-blur-xl rounded-2xl p-5 border border-white/20 shadow-2xl space-y-3">
                <a href="index.php" class="block text-white font-medium hover:text-brand-gold py-1">🏠 Home</a>
                <a href="explore.php" class="block text-white font-medium hover:text-brand-gold py-1">📂 Catalog / All Products</a>
                <a href="index.php#charging" class="block text-white font-medium hover:text-brand-gold py-1">🔌 Fast Chargers</a>
                <a href="index.php#charging" class="block text-white font-medium hover:text-brand-gold py-1">⚡ Genuine Cables</a>
            </div>

        </div>
    </section>

    <!-- CLEAN BREADCRUMBS (MATCHING REFERENCE) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-2">
        <div class="flex items-center space-x-2 text-xs text-gray-500 overflow-x-auto no-scrollbar whitespace-nowrap">
            <a href="index.php" class="hover:text-brand-blue transition-colors">Home</a>
            <span class="text-gray-300">/</span>
            <?php if (!empty($product['category_name'])): ?>
            <a href="explore.php?cat=<?= urlencode($product['category_slug'] ?? '') ?>" class="hover:text-brand-blue transition-colors"><?= e($product['category_name']) ?></a>
            <span class="text-gray-300">/</span>
            <?php endif; ?>
            <?php if (!empty($product['brand_name'])): ?>
            <span class="text-gray-600 font-medium"><?= e($product['brand_name']) ?></span>
            <span class="text-gray-300">/</span>
            <?php endif; ?>
            <span class="text-gray-900 font-semibold truncate"><?= e($product['name']) ?></span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MAIN PRODUCT DETAIL SECTION (CLEAN 2-COLUMN LAYOUT) -->
    <!-- ================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex-1 w-full">
        
        <!-- TOP SECTION: MEDIA GALLERY + PURCHASING STREAM -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 bg-white rounded-2xl p-4 sm:p-8">
            
            <!-- LEFT COLUMN: VERTICAL THUMBNAIL STRIP + MAIN IMAGE -->
            <div class="lg:col-span-6 flex flex-col sm:flex-row gap-4 items-start">
                
                <!-- Vertical Thumbnail Strip (Left of Image) -->
                <?php if (count($gallery) > 1): ?>
                <div class="flex sm:flex-col gap-2.5 order-2 sm:order-1 overflow-x-auto sm:overflow-y-auto max-h-[460px] no-scrollbar shrink-0 w-full sm:w-20">
                    <?php foreach ($gallery as $idx => $thumb): ?>
                    <button type="button" class="gallery-thumb <?= $idx === 0 ? 'active border-2 border-brand-blue' : 'border border-gray-200 hover:border-gray-400' ?> rounded-lg bg-white p-1 transition-all focus:outline-none w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center shrink-0 cursor-pointer" onclick="switchGalleryImage('<?= e($thumb) ?>', this)">
                        <img src="<?= e($thumb) ?>" alt="Product angle" class="max-w-full max-h-full object-contain">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Main Showcase Image Canvas -->
                <div class="relative flex-1 w-full bg-white rounded-xl border border-gray-100 flex items-center justify-center min-h-[360px] sm:min-h-[460px] p-6 order-1 sm:order-2 group">
                    <!-- Magnifying Glass Zoom Icon (Top Right) -->
                    <button type="button" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 p-1.5 rounded-full hover:bg-gray-50 transition-colors cursor-pointer z-10" title="Zoom In" onclick="openImageModal()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Main Featured Image -->
                    <img id="main-product-img" src="<?= e($product['main_image']) ?>" alt="<?= e($product['name']) ?>" class="w-full max-w-[380px] max-h-[380px] object-contain transition-transform duration-200 cursor-zoom-in group-hover:scale-105" onclick="openImageModal()">
                </div>
            </div>

            <!-- RIGHT COLUMN: DETAILS, PRICING & STACKED TRUST CARDS -->
            <div class="lg:col-span-6 space-y-4">
                
                <!-- Product Title -->
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-snug tracking-tight">
                    <?= e($product['name']) ?>
                </h1>

                <!-- Theme Accent Divider Line (Under Title) -->
                <div class="w-full h-[1.5px] bg-[#c4b338] my-3"></div>

                <!-- Price Block -->
                <div class="space-y-0.5">
                    <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                    <div class="text-sm sm:text-base text-gray-400 line-through font-semibold">
                        Rs <?= number_format($product['original_price']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="text-2xl sm:text-3xl font-black text-gray-950">
                        Rs <?= number_format($product['price']) ?>
                    </div>
                </div>

                <!-- Action Controls: Quantity + Buy Now + Add to Cart -->
                <div class="flex items-center gap-3 pt-2 flex-wrap sm:flex-nowrap">
                    <!-- Quantity Input Box -->
                    <div class="flex items-center border border-gray-300 rounded-md bg-white w-20 h-11 shrink-0 overflow-hidden">
                        <button type="button" class="w-6 text-gray-500 hover:text-gray-900 font-bold transition-colors text-sm" onclick="changeQty(-1)">−</button>
                        <input type="number" id="product-qty-input" value="1" min="1" class="w-8 text-center text-xs font-bold text-gray-900 border-0 focus:ring-0 p-0" onchange="currentQty = Math.max(1, parseInt(this.value)||1); this.value = currentQty;">
                        <button type="button" class="w-6 text-gray-500 hover:text-gray-900 font-bold transition-colors text-sm" onclick="changeQty(1)">+</button>
                    </div>

                    <!-- Buy Now Button (Active Cart Trigger) -->
                    <button id="buy-now-action" type="button" class="px-6 sm:px-8 h-11 rounded-full bg-[#c4b338] hover:bg-[#b0a030] text-white font-extrabold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center shrink-0 cursor-pointer active:scale-95 space-x-1.5">
                        <span>⚡ Buy Now</span>
                    </button>

                    <!-- Add to Cart Button (Active Cart Trigger) -->
                    <button id="add-to-cart-action" type="button" class="px-6 sm:px-8 h-11 rounded-full bg-[#1b2c4e] hover:bg-[#15233e] text-white font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center justify-center shrink-0 cursor-pointer active:scale-95">
                        Add to cart
                    </button>
                </div>

                <!-- 5 STACKED POLICY & SERVICE CARDS (MATCHING REFERENCE EXACTLY) -->
                <div class="space-y-2 pt-3">
                    
                    <!-- 1. Brand Refund & Warranty -->
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 bg-white text-xs">
                        <div class="flex items-center space-x-3 text-gray-800 font-medium">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Brand Refund &amp; Warranty</span>
                        </div>
                        <button type="button" onclick="openWarrantyModal()" class="px-3.5 py-1 rounded-full border border-gray-300 hover:border-gray-400 text-gray-600 hover:text-gray-900 text-[11px] font-semibold transition-colors">
                            Details
                        </button>
                    </div>

                    <!-- 2. Positive Feedback -->
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 bg-white text-xs">
                        <div class="flex items-center space-x-3 text-gray-800 font-medium">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Positive Feedback</span>
                        </div>
                        <button type="button" onclick="scrollToReviews()" class="px-3.5 py-1 rounded-full border border-gray-300 hover:border-gray-400 text-gray-600 hover:text-gray-900 text-[11px] font-semibold transition-colors">
                            Check Reviews
                        </button>
                    </div>

                    <!-- 3. Expected Delivery -->
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 bg-white text-xs">
                        <div class="flex items-center space-x-3 text-gray-800 font-medium">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            <span>Expected Delivery</span>
                        </div>
                        <span class="text-gray-400 text-[11px] font-medium"><?= e($product['stock_status'] ? ($product['stock_status'] . ' • 24-72 hrs') : '24-72 hrs') ?></span>
                    </div>

                    <!-- 4. Self Collection – Lahore Outlet -->
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 bg-white text-xs">
                        <div class="flex items-center space-x-3 text-gray-800 font-medium">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Self Collection – Lahore Outlet</span>
                        </div>
                        <a href="tel:03001234567" class="px-3.5 py-1 rounded-full border border-gray-300 hover:border-gray-400 text-gray-600 hover:text-gray-900 text-[11px] font-semibold transition-colors">
                            Call Before Visiting
                        </a>
                    </div>

                    <!-- 5. Need help? Ask our Experts -->
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 bg-white text-xs">
                        <div class="flex items-center space-x-3 text-gray-800 font-medium">
                            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Need help? Ask our Experts</span>
                        </div>
                        <a href="https://wa.me/923001234567?text=<?= urlencode('Hi ChargeHub, I need help regarding ' . $product['name']) ?>" target="_blank" rel="noopener noreferrer" class="px-3.5 py-1 rounded-full border border-emerald-500 text-emerald-600 hover:bg-emerald-50 text-[11px] font-bold transition-colors">
                            WhatsApp
                        </a>
                    </div>
                </div>

                <!-- KEY FEATURE BULLETS (SOLID BULLETS MATCHING REFERENCE) -->
                <div class="pt-3 space-y-1.5 text-xs sm:text-sm text-gray-800 font-medium">
                    <?php if (!empty($features) && is_array($features)): ?>
                        <?php foreach ($features as $f): ?>
                        <div class="flex items-start space-x-2">
                            <span class="text-brand-blue font-bold text-base leading-none">•</span>
                            <span><?= e($f) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if (!empty($specInput)): ?>
                        <div class="flex items-start space-x-2">
                            <span class="text-brand-blue font-bold text-base leading-none">•</span>
                            <span>Input: <?= e($specInput) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($specOutput)): ?>
                        <div class="flex items-start space-x-2">
                            <span class="text-brand-blue font-bold text-base leading-none">•</span>
                            <span>Output: <?= e($specOutput) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($specPorts)): ?>
                        <div class="flex items-start space-x-2">
                            <span class="text-brand-blue font-bold text-base leading-none">•</span>
                            <span>Ports: <?= e($specPorts) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($product['sku'])): ?>
                        <div class="flex items-start space-x-2">
                            <span class="text-brand-blue font-bold text-base leading-none">•</span>
                            <span>Model: <?= e($product['sku']) ?></span>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- ================================================================= -->
        <!-- BOTTOM SECTION: DESCRIPTION & REVIEWS TABS (CLEAN & CENTERED) -->
        <!-- ================================================================= -->
        <section id="product-tabs-section" class="mt-14 pt-8 border-t border-gray-100">
            <!-- Centered Tab Headers -->
            <div class="flex justify-center space-x-12 border-b border-gray-200">
                <button id="tab-desc-btn" type="button" class="pb-3 text-sm sm:text-base font-bold text-gray-900 border-b-2 border-[#c4b338] transition-all cursor-pointer" onclick="showProductTab('desc')">
                    Description
                </button>
                <button id="tab-reviews-btn" type="button" class="pb-3 text-sm sm:text-base font-semibold text-gray-500 hover:text-gray-900 border-b-2 border-transparent transition-all cursor-pointer" onclick="showProductTab('reviews')">
                    Reviews (<?= (int)($product['reviews_count'] ?? 0) ?>)
                </button>
            </div>

            <!-- TAB 1: FULL DESCRIPTION -->
            <div id="tab-desc-content" class="py-8 max-w-4xl mx-auto text-sm sm:text-base text-gray-700 leading-relaxed space-y-4">
                <?php if (!empty($product['full_description'])): ?>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        <?= $product['full_description'] ?>
                    </div>
                <?php elseif (!empty($product['short_description'])): ?>
                    <p class="text-gray-700 leading-relaxed"><?= nl2br(e($product['short_description'])) ?></p>
                <?php else: ?>
                    <p class="text-gray-400 text-center py-6">Detailed description will be updated soon.</p>
                <?php endif; ?>
            </div>

            <!-- TAB 2: REVIEWS -->
            <div id="tab-reviews-content" class="hidden py-8 max-w-4xl mx-auto space-y-6">
                <!-- Rating Summary -->
                <div class="flex items-center space-x-4 bg-gray-50 p-5 rounded-2xl border border-gray-200">
                    <div class="text-center">
                        <span class="text-3xl font-black text-gray-900"><?= number_format($product['rating'] ?? 5.0, 1) ?></span>
                        <span class="block text-[11px] text-gray-500">out of 5.0</span>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-amber-500 text-base">★★★★★</div>
                        <span class="block text-xs font-semibold text-gray-700"><?= (int)($product['reviews_count'] ?? 0) ?> Verified Customer Ratings</span>
                    </div>
                </div>

                <!-- Review Cards -->
                <div class="space-y-4">
                    <div class="p-5 rounded-xl border border-gray-200 bg-white space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-amber-500 text-xs">★★★★★</span>
                            <span class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded">✓ Verified Buyer</span>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900">100% Genuine and super fast delivery!</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            "Excellent quality product. Original box with security scratch-off seal. Charges my devices quickly without getting hot. Highly recommended!"
                        </p>
                        <span class="block text-[11px] text-gray-400">Hamza K. (Lahore) • Verified Purchase</span>
                    </div>

                    <div class="p-5 rounded-xl border border-gray-200 bg-white space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-amber-500 text-xs">★★★★★</span>
                            <span class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded">✓ Verified Buyer</span>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900">Great build quality and prompt delivery</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            "Received in Karachi within 2 days via TCS COD. Tested thoroughly and output speed is genuine. Very happy with the purchase."
                        </p>
                        <span class="block text-[11px] text-gray-400">Daniyal M. (Karachi) • Verified Purchase</span>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- WARRANTY DETAILS MODAL -->
    <div id="warranty-modal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 relative">
            <button type="button" onclick="closeWarrantyModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl font-bold">&times;</button>
            <div class="flex items-center space-x-3 text-brand-blue">
                <span class="text-2xl">🛡️</span>
                <h3 class="text-base font-extrabold text-gray-900">Brand Refund &amp; Warranty</h3>
            </div>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed">
                <p><strong>100% Authentic Stock:</strong> Every product from ChargeHub.pk comes in official manufacturer packaging with scannable/scratch-off authenticity seals.</p>
                <p><strong>Official Replacement Warranty:</strong> <?= e($product['warranty_text'] ?: '1-Year Official Replacement Guarantee') ?> against technical and hardware defects.</p>
                <p><strong>7-Day Easy Return:</strong> Enjoy hassle-free returns on sealed and undamaged items if you change your mind.</p>
                <p><strong>Express Claim Support:</strong> Our Lahore hub provides fast unit swap support across all major Pakistan cities.</p>
            </div>
            <button type="button" onclick="closeWarrantyModal()" class="w-full py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition-colors">
                Got it
            </button>
        </div>
    </div>

    <!-- IMAGE LIGHTBOX MODAL -->
    <div id="lightbox-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeImageModal()">
        <div class="relative max-w-3xl max-h-[90vh] flex items-center justify-center" onclick="event.stopPropagation()">
            <button type="button" onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-2xl font-bold">&times;</button>
            <img id="lightbox-img" src="<?= e($product['main_image']) ?>" alt="High-Res Product Preview" class="max-w-full max-h-[85vh] object-contain rounded-xl bg-white p-4">
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-brand-dark text-slate-300 pt-12 pb-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Brand Info -->
                <div class="space-y-4">
                    <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-8 w-auto brightness-200 invert object-contain">
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pakistan's premier destination for genuine fast chargers, indestructible cables, and portable power technology.
                    </p>
                    <div class="text-xs font-semibold text-slate-400">
                        <span>Helpline: </span>
                        <a href="https://wa.me/923001234567" class="text-brand-orange hover:text-brand-gold font-bold">+92 300 1234567</a>
                    </div>
                </div>

                <!-- Col 2: Fast Chargers -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">Fast Chargers</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">Fast GaN Chargers</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">Laptop Power Adapters</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">Multi-Port Desktop Docks</a></li>
                    </ul>
                </div>

                <!-- Col 3: Genuine Cables -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">Cables &amp; Docks</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">100W-240W Type-C Cables</a></li>
                        <li><a href="explore.php" class="hover:text-brand-blue transition-colors">Braided Micro-USB &amp; Lightning</a></li>
                    </ul>
                </div>

                <!-- Col 4: Customer Care -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">Customer Support</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#" class="hover:text-brand-blue transition-colors">Track Your Order</a></li>
                        <li><a href="#" class="hover:text-brand-blue transition-colors">Warranty &amp; Replacement</a></li>
                        <li><a href="admin/login.php" class="text-brand-orange font-bold">Admin Portal ↗</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
                <p>&copy; 2026 ChargeHub.pk. All Rights Reserved. Genuine Chargers, Genuine Cables.</p>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="hover:text-slate-300">Home</a>
                    <a href="explore.php" class="hover:text-slate-300">Catalog</a>
                    <a href="admin/login.php" class="text-brand-orange hover:text-brand-gold font-bold">Admin</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- FLOATING WHATSAPP BUTTON (Dynamic from Dashboard Setup) -->
    <?php 
    $waProdConfig = function_exists('get_whatsapp_config') ? get_whatsapp_config() : ['enabled' => true, 'number_clean' => '923001234567', 'number_raw' => '+92 300 1234567'];
    if (!empty($waProdConfig['enabled'])): 
    ?>
    <a href="https://wa.me/<?= e($waProdConfig['number_clean']) ?>?text=<?= urlencode('Hi ChargeHub, I have a question about ' . $product['name']) ?>" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 bg-[#25d366] text-white rounded-full shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 group" title="Chat on WhatsApp (<?= e($waProdConfig['number_raw']) ?>)">
        <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
    </a>
    <?php endif; ?>

    <!-- SCRIPTS -->
    <script>
        // Gallery Image Switcher
        function switchGalleryImage(src, thumbElement) {
            const mainImg = document.getElementById('main-product-img');
            const lightboxImg = document.getElementById('lightbox-img');
            mainImg.style.opacity = '0.3';
            mainImg.src = src;
            if (lightboxImg) lightboxImg.src = src;
            setTimeout(() => {
                mainImg.style.opacity = '1';
            }, 100);

            document.querySelectorAll('.gallery-thumb').forEach(t => {
                t.classList.remove('active', 'border-2', 'border-brand-blue');
                t.classList.add('border', 'border-gray-200');
            });
            thumbElement.classList.remove('border', 'border-gray-200');
            thumbElement.classList.add('active', 'border-2', 'border-brand-blue');
        }

        // Quantity Stepper
        let currentQty = 1;
        function changeQty(delta) {
            currentQty = Math.max(1, currentQty + delta);
            const input = document.getElementById('product-qty-input');
            if (input) input.value = currentQty;
        }

        // Bottom Description / Reviews Tabs Switcher
        function showProductTab(tab) {
            const descBtn = document.getElementById('tab-desc-btn');
            const reviewsBtn = document.getElementById('tab-reviews-btn');
            const descContent = document.getElementById('tab-desc-content');
            const reviewsContent = document.getElementById('tab-reviews-content');

            if (tab === 'desc') {
                descBtn.classList.add('text-gray-900', 'font-bold', 'border-[#c4b338]');
                descBtn.classList.remove('text-gray-500', 'font-semibold', 'border-transparent');
                reviewsBtn.classList.remove('text-gray-900', 'font-bold', 'border-[#c4b338]');
                reviewsBtn.classList.add('text-gray-500', 'font-semibold', 'border-transparent');
                descContent.classList.remove('hidden');
                reviewsContent.classList.add('hidden');
            } else {
                reviewsBtn.classList.add('text-gray-900', 'font-bold', 'border-[#c4b338]');
                reviewsBtn.classList.remove('text-gray-500', 'font-semibold', 'border-transparent');
                descBtn.classList.remove('text-gray-900', 'font-bold', 'border-[#c4b338]');
                descBtn.classList.add('text-gray-500', 'font-semibold', 'border-transparent');
                reviewsContent.classList.remove('hidden');
                descContent.classList.add('hidden');
            }
        }

        function scrollToReviews() {
            showProductTab('reviews');
            const section = document.getElementById('product-tabs-section');
            if (section) section.scrollIntoView({ behavior: 'smooth' });
        }

        // Warranty Modal
        function openWarrantyModal() {
            const m = document.getElementById('warranty-modal');
            if (m) m.classList.remove('hidden');
        }
        function closeWarrantyModal() {
            const m = document.getElementById('warranty-modal');
            if (m) m.classList.add('hidden');
        }

        // Lightbox Modal
        function openImageModal() {
            const m = document.getElementById('lightbox-modal');
            if (m) m.classList.remove('hidden');
        }
        function closeImageModal() {
            const m = document.getElementById('lightbox-modal');
            if (m) m.classList.add('hidden');
        }

        // Current Product Data for Cart
        const currentProductData = {
            id: <?= json_encode((int)$product['id']) ?>,
            name: <?= json_encode($product['name']) ?>,
            slug: <?= json_encode($product['slug']) ?>,
            price: <?= json_encode((float)$product['price']) ?>,
            original_price: <?= json_encode((float)($product['original_price'] ?? 0)) ?>,
            main_image: <?= json_encode($product['main_image']) ?>
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Cart initialization & Button Handlers
            updateCartCounterBadges();

            // 1. BUY NOW BUTTON HANDLER: Adds product and pops open the Cart Sidebar immediately
            const buyNowBtn = document.getElementById('buy-now-action');
            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', () => {
                    const qty = parseInt(document.getElementById('product-qty-input').value) || 1;
                    addToCart(currentProductData, qty, true);
                });
            }

            // 2. ADD TO CART BUTTON HANDLER: Adds product and shows visual feedback & opens Cart Sidebar
            const addBtn = document.getElementById('add-to-cart-action');
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    const qty = parseInt(document.getElementById('product-qty-input').value) || 1;
                    addToCart(currentProductData, qty, true);

                    const origHTML = addBtn.innerHTML;
                    addBtn.innerHTML = '<span>✓ Added to Cart!</span>';
                    addBtn.classList.remove('bg-[#1b2c4e]');
                    addBtn.classList.add('bg-emerald-600');
                    setTimeout(() => {
                        addBtn.innerHTML = origHTML;
                        addBtn.classList.remove('bg-emerald-600');
                        addBtn.classList.add('bg-[#1b2c4e]');
                    }, 1600);
                });
            }

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
        });
    </script>

<?php require_once __DIR__ . '/includes/cart_drawer.php'; ?>

</body>
</html>
