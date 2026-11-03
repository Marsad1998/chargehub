<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// 1. Query hero slides dynamically with prepared statements
$stmtHero = $pdo->prepare("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$stmtHero->execute();
$hero_slides = $stmtHero->fetchAll();

// Robust fallback if none in database (contains all 4 complete design slides)
if (empty($hero_slides)) {
    $hero_slides = [
        [
            'id' => 1,
            'title' => 'Next-Gen GaN Fast Chargers',
            'subtitle' => 'Hyper-speed Gallium Nitride charging for laptops, phones, and wearables. Zero clutter, peak thermal efficiency.',
            'badge_text' => '⚡ OFFICIAL DISTRIBUTOR IN PAKISTAN',
            'image_url' => 'images/Hero Banner.jpg',
            'nav_color' => '#0052cc',
            'cta_link' => 'explore.php',
            'cta_text' => 'Explore GaN Chargers'
        ],
        [
            'id' => 2,
            'title' => 'Military-Grade Kevlar Cables',
            'subtitle' => 'Engineered to withstand 35,000+ bends with smart E-Marker chips delivering verified 100W-240W sustained throughput.',
            'badge_text' => '🛡️ INDESTRUCTIBLE BUILD',
            'image_url' => 'images/v7/chargers/hero_fast_cable_ultra.jpg',
            'nav_color' => '#10b981',
            'cta_link' => 'explore.php?category=cables',
            'cta_text' => 'Shop Heavy-Duty Cables'
        ],
        [
            'id' => 3,
            'title' => 'High-Capacity Desktop Power Stations',
            'subtitle' => 'Massive battery capacity with high-wattage AC/DC output ports. Keep your whole workstation powered through load-shedding.',
            'badge_text' => '🔋 UNINTERRUPTED POWER',
            'image_url' => 'images/v7/chargers/hero_powerstation_ultra.jpg',
            'nav_color' => '#f59e0b',
            'cta_link' => 'explore.php?category=storage',
            'cta_text' => 'View Power Stations'
        ],
        [
            'id' => 4,
            'title' => 'Qi2 MagSafe Wireless Stands',
            'subtitle' => 'Snap and charge at maximum 15W MagSafe speeds with active cooling fans and aerospace zinc-alloy construction.',
            'badge_text' => '🧲 SNAP & CHARGE',
            'image_url' => 'images/v7/chargers/hero_wireless_station.jpg',
            'nav_color' => '#8b5cf6',
            'cta_link' => 'explore.php?category=chargers',
            'cta_text' => 'Explore Wireless Tech'
        ]
    ];
}

// 2. Query spotlight items dynamically with prepared statements
$stmtSpot = $pdo->prepare("SELECT * FROM spotlight_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$stmtSpot->execute();
$spotlight_items = $stmtSpot->fetchAll();

if (empty($spotlight_items)) {
    $spotlight_items = [
        ['title' => 'Anker Prime 140W GaN', 'image_url' => 'images/Spotlight Section/1.png', 'link_url' => 'product-detail.php?slug=anker-prime-140w'],
        ['title' => 'Ugreen Nexode 100W 4-Port', 'image_url' => 'images/Spotlight Section/2.png', 'link_url' => 'product-detail.php?slug=ugreen-nexode-100w-4port-charger'],
        ['title' => 'ChargeHub Armor 240W Kevlar Cable', 'image_url' => 'images/Spotlight Section/3.png', 'link_url' => 'product-detail.php?slug=chargehub-240w-kevlar-cable'],
        ['title' => 'Baseus MagPro 3-in-1 Magnetic Dock', 'image_url' => 'images/Spotlight Section/4.png', 'link_url' => 'product-detail.php?slug=baseus-magpro-3-in-1-wireless-dock'],
        ['title' => 'Anker Prime 200W Power Bank', 'image_url' => 'images/Spotlight Section/5.png', 'link_url' => 'product-detail.php?slug=anker-prime-20000mah-200w-powerbank'],
    ];
}

// 3. Query new arrival items dynamically with prepared statements
$stmtNew = $pdo->prepare("SELECT * FROM new_arrival_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$stmtNew->execute();
$new_arrival_items = $stmtNew->fetchAll();

if (empty($new_arrival_items)) {
    $new_arrival_items = [
        [
            'id' => 1,
            'title' => 'CHARGEHUB PRO | 65W GAN',
            'sku' => 'CH-65W',
            'image_url' => 'images/1000x1000 transparent PNG/1.png',
            'link_url' => 'product-detail.php?slug=chargehub-pro-65w-gan',
            'price' => 5511,
            'original_price' => 6599,
            'discount_pct' => 16,
            'rating' => 0.0,
            'badge_text' => 'NEW ARRIVAL',
            'spec_1_title' => 'Quad Mic', 'spec_1_sub' => 'ENC Support',
            'spec_2_title' => 'ANC', 'spec_2_sub' => 'Support',
            'spec_3_title' => 'Upto 7 Hrs', 'spec_3_sub' => 'Playback',
        ],
        [
            'id' => 2,
            'title' => 'VOLTRA PRO | 100W GAN',
            'sku' => 'CH-100PRO',
            'image_url' => 'images/1000x1000 transparent PNG/2.png',
            'link_url' => 'product-detail.php?slug=voltra-pro-100w-gan',
            'price' => 7139,
            'original_price' => 8499,
            'discount_pct' => 16,
            'rating' => 0.0,
            'badge_text' => 'NEW ARRIVAL',
            'spec_1_title' => '22.5Watt', 'spec_1_sub' => 'Max Output',
            'spec_2_title' => 'Attached', 'spec_2_sub' => 'Cables',
            'spec_3_title' => '30000mAh', 'spec_3_sub' => 'Capacity',
        ],
        [
            'id' => 3,
            'title' => 'HYPERSPEED | 45W DUAL',
            'sku' => 'CH-45D',
            'image_url' => 'images/1000x1000 transparent PNG/3.png',
            'link_url' => 'product-detail.php?slug=hyperspeed-45w-dual',
            'price' => 2624,
            'original_price' => 4499,
            'discount_pct' => 42,
            'rating' => 0.0,
            'badge_text' => 'NEW ARRIVAL',
            'spec_1_title' => '45Watt', 'spec_1_sub' => 'Max Output',
            'spec_2_title' => 'GaN', 'spec_2_sub' => 'Technology',
            'spec_3_title' => 'PD Port', 'spec_3_sub' => 'Output',
        ],
        [
            'id' => 4,
            'title' => 'ARMOR CABLE | 240W PD',
            'sku' => 'CH-240C',
            'image_url' => 'images/1000x1000 transparent PNG/4.png',
            'link_url' => 'product-detail.php?slug=chargehub-240w-kevlar-cable',
            'price' => 5774,
            'original_price' => 6499,
            'discount_pct' => 11,
            'rating' => 0.0,
            'badge_text' => 'NEW ARRIVAL',
            'spec_1_title' => 'Open Ear', 'spec_1_sub' => 'Design',
            'spec_2_title' => '11mm', 'spec_2_sub' => 'Bass Drivers',
            'spec_3_title' => 'AI', 'spec_3_sub' => 'System',
        ],
        [
            'id' => 5,
            'title' => 'NANO CUBE | 30W ULTRA',
            'sku' => 'CH-30N',
            'image_url' => 'images/1000x1000 transparent PNG/5.png',
            'link_url' => 'product-detail.php?slug=anker-prime-140w',
            'price' => 1899,
            'original_price' => 2999,
            'discount_pct' => 37,
            'rating' => 0.0,
            'badge_text' => 'NEW ARRIVAL',
            'spec_1_title' => '30W Ultra', 'spec_1_sub' => 'Pocket Cube',
            'spec_2_title' => 'Type-C PD', 'spec_2_sub' => 'Fast iPhone',
            'spec_3_title' => 'Safety Guard', 'spec_3_sub' => 'Thermal Safe',
        ]
    ];
}

// 4. Query flash sale items dynamically with prepared statements
$stmtFlash = $pdo->prepare("SELECT * FROM flash_sale_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$stmtFlash->execute();
$flash_sale_items = $stmtFlash->fetchAll(PDO::FETCH_ASSOC);

if (empty($flash_sale_items)) {
    $flash_sale_items = [
        [
            'id' => 1,
            'title' => 'L-265 Ronaq | 12 inch Wireless Speaker',
            'ribbon_text' => '10.10 sale is live',
            'discount_text' => '14% Off',
            'image_url' => 'images/v7/flash_sale/l265_ronaq.png',
            'link_url' => 'product-detail.php?slug=l265-ronaq-12inch-speaker',
            'price' => 17859.00,
            'original_price' => 20999.00,
            'rating' => 0.0,
            'color_swatch' => '#000000',
            'sort_order' => 1,
        ],
        [
            'id' => 2,
            'title' => 'L-267 Jamster | 30 Watt Wireless Speaker',
            'ribbon_text' => '10.10 sale is live',
            'discount_text' => '22% Off',
            'image_url' => 'images/v7/flash_sale/l267_jamster.png',
            'link_url' => 'product-detail.php?slug=l267-jamster-30w-speaker',
            'price' => 7799.00,
            'original_price' => 9999.00,
            'rating' => 0.0,
            'color_swatch' => '#000000',
            'sort_order' => 2,
        ],
        [
            'id' => 3,
            'title' => 'L-268 Jamster Pro | 50 Watt Wireless Speaker',
            'ribbon_text' => '10.10 sale is live',
            'discount_text' => '18% Off',
            'image_url' => 'images/v7/flash_sale/l268_jamster_pro.png',
            'link_url' => 'product-detail.php?slug=l268-jamster-pro-50w-speaker',
            'price' => 11479.00,
            'original_price' => 13999.00,
            'rating' => 0.0,
            'color_swatch' => '#000000',
            'sort_order' => 3,
        ],
        [
            'id' => 4,
            'title' => 'L-269 Jamster Prime | 60 Watt Wireless Speaker',
            'ribbon_text' => '10.10 sale is live',
            'discount_text' => '20% Off',
            'image_url' => 'images/v7/flash_sale/l269_jamster_prime.png',
            'link_url' => 'product-detail.php?slug=l269-jamster-prime-60w-speaker',
            'price' => 14399.00,
            'original_price' => 17999.00,
            'rating' => 0.0,
            'color_swatch' => '#000000',
            'sort_order' => 4,
        ],
        [
            'id' => 5,
            'title' => 'L-270 Predox | Wireless Soundbar',
            'ribbon_text' => '10.10 sale is live',
            'discount_text' => '15% Off',
            'image_url' => 'images/v7/flash_sale/l270_predox.png',
            'link_url' => 'product-detail.php?slug=l270-predox-wireless-soundbar',
            'price' => 8499.00,
            'original_price' => 9999.00,
            'rating' => 0.0,
            'color_swatch' => '#000000',
            'sort_order' => 5,
        ]
    ];
}

// 5. Query latest 4 products dynamically
$stmtLatest = $pdo->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.id DESC 
    LIMIT 4
");
$stmtLatest->execute();
$latest_products = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);

if (empty($latest_products)) {
    $latest_products = [
        [
            'id' => 15,
            'name' => 'Belkin BoostCharge Pro 3-in-1 Wireless Charging Pad with MagSafe',
            'slug' => 'belkin-boostcharge-pro-3in1-pad',
            'badge_text' => 'OFFICIAL MAGSAFE',
            'category_name' => 'Wireless Charging',
            'main_image' => 'images/v7/chargers/hero_wireless_station.jpg',
            'price' => 24999.00,
            'original_price' => 29999.00,
            'rating' => 4.9,
            'reviews_count' => 84,
            'short_description' => 'Official 15W MagSafe charging for iPhone, fast Apple Watch charging puck, and Qi pad for AirPods.'
        ],
        [
            'id' => 14,
            'name' => 'Baseus Blade 100W Ultra-Thin Power Bank 20000mAh',
            'slug' => 'baseus-blade-100w-powerbank',
            'badge_text' => 'ULTRA SLIM 100W',
            'category_name' => 'Power Banks',
            'main_image' => 'images/v7/featured/card_anker_powerbank.jpg',
            'price' => 18499.00,
            'original_price' => 22999.00,
            'rating' => 4.8,
            'reviews_count' => 142,
            'short_description' => 'Ultra-thin 18mm design with dual Type-C 100W bidirectional fast charging for laptops and phones.'
        ],
        [
            'id' => 13,
            'name' => 'Anker 737 Power Bank (PowerCore 24K) 140W Output',
            'slug' => 'anker-737-powercore-24000mah',
            'badge_text' => 'FLAGSHIP',
            'category_name' => 'Power Banks',
            'main_image' => 'images/v7/featured/card_anker_powerbank.jpg',
            'price' => 28999.00,
            'original_price' => 34999.00,
            'rating' => 5.0,
            'reviews_count' => 512,
            'short_description' => 'High-capacity 24,000mAh power bank with bidirectional 140W fast recharging and smart digital display.'
        ],
        [
            'id' => 12,
            'name' => 'Ugreen 145W Power Bank 25000mAh 3-Port Laptop Charger',
            'slug' => 'ugreen-145w-powerbank-25000mah',
            'badge_text' => '145W DUAL LAPTOP',
            'category_name' => 'Power Banks',
            'main_image' => 'images/v7/chargers/hero_powerstation_ultra.jpg',
            'price' => 21999.00,
            'original_price' => 26999.00,
            'rating' => 4.9,
            'reviews_count' => 96,
            'short_description' => 'PD 3.0 fast charging power bank capable of 100W + 45W simultaneous high-speed laptop output.'
        ]
    ];
}
$featured_products = $latest_products;

// 6. Query brands dynamically from database
$stmtBrands = $pdo->prepare("SELECT * FROM brands WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$stmtBrands->execute();
$brands = $stmtBrands->fetchAll(PDO::FETCH_ASSOC);

if (empty($brands)) {
    $brands = [
        ['name' => 'Anker', 'slug' => 'anker', 'logo_url' => 'images/v7/brands/brand_anker_2x.png', 'description' => 'Anker Genuine Fast Chargers & Cables'],
        ['name' => 'A4TECH', 'slug' => 'a4tech', 'logo_url' => 'images/v7/brands/brand_a4tech_2x.png', 'description' => 'A4Tech Accessories'],
        ['name' => 'Baseus', 'slug' => 'baseus', 'logo_url' => 'images/v7/brands/brand_baseus_2x.png', 'description' => 'Baseus Fast Chargers & Cables'],
        ['name' => 'Apple', 'slug' => 'apple', 'logo_url' => 'images/v7/brands/brand_apple_2x.png', 'description' => 'Apple Genuine MagSafe & Accessories'],
        ['name' => 'BOYA', 'slug' => 'boya', 'logo_url' => 'images/v7/brands/brand_boya_2x.png', 'description' => 'Boya Audio & Microphones'],
        ['name' => 'JisuLife', 'slug' => 'jisulife', 'logo_url' => 'images/v7/brands/brand_jisulife_2x.png', 'description' => 'JisuLife Portable Tech'],
        ['name' => 'Mcdodo', 'slug' => 'mcdodo', 'logo_url' => 'images/v7/brands/brand_mcdodo_2x.png', 'description' => 'Mcdodo Fast Charging & Digital Display Cables'],
        ['name' => 'PITAKA', 'slug' => 'pitaka', 'logo_url' => 'images/v7/brands/brand_pitaka_2x.png', 'description' => 'Pitaka Aramid Fiber Cases & Wireless Docks'],
        ['name' => 'Samsung', 'slug' => 'samsung', 'logo_url' => 'images/v7/brands/brand_samsung_2x.png', 'description' => 'Samsung Genuine Super Fast Charging'],
        ['name' => 'UGREEN', 'slug' => 'ugreen', 'logo_url' => 'images/v7/brands/brand_ugreen_2x.png', 'description' => 'UGREEN Nexode GaN Fast Chargers'],
        ['name' => 'JBL', 'slug' => 'jbl', 'logo_url' => 'images/v7/brands/brand_jbl_2x.png', 'description' => 'JBL Genuine Audio Gear'],
        ['name' => 'Xiaomi', 'slug' => 'xiaomi', 'logo_url' => 'images/v7/brands/brand_xiaomi_2x.png', 'description' => 'Xiaomi Genuine Power Banks & Chargers'],
    ];
}

// 7. Query categories dynamically from database with product count
$stmtCats = $pdo->prepare("
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    WHERE c.is_active = 1 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
");
$stmtCats->execute();
$categories = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    $categories = [
        ['name' => 'Party Speakers', 'slug' => 'speakers', 'image_url' => '🔊', 'badge_text' => 'HOT', 'models_count_text' => '18 Models', 'bg_color' => 'bg-blue-50', 'product_count' => 18],
        ['name' => 'Earbuds & ANC', 'slug' => 'earbuds', 'image_url' => '🎧', 'badge_text' => '', 'models_count_text' => '24 Models', 'bg_color' => 'bg-indigo-50', 'product_count' => 24],
        ['name' => 'SmartWatches', 'slug' => 'smartwatches', 'image_url' => '⌚', 'badge_text' => '', 'models_count_text' => '15 Models', 'bg_color' => 'bg-emerald-50', 'product_count' => 15],
        ['name' => 'Fast Chargers', 'slug' => 'chargers', 'image_url' => '⚡', 'badge_text' => '', 'models_count_text' => '32 Models', 'bg_color' => 'bg-amber-50', 'product_count' => 32],
        ['name' => 'Cables & Hubs', 'slug' => 'cables', 'image_url' => '🔌', 'badge_text' => '', 'models_count_text' => '40+ Models', 'bg_color' => 'bg-purple-50', 'product_count' => 40],
        ['name' => 'Data Storage', 'slug' => 'storage', 'image_url' => '💾', 'badge_text' => '', 'models_count_text' => '19 Models', 'bg_color' => 'bg-rose-50', 'product_count' => 19],
    ];
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChargeHub.pk - Genuine Chargers, Genuine Cables & Fast Power Tech</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#0066ff',
                            electricBlue: '#0070f0',
                            cyan: '#00c6ff',
                            navy: '#001f5c',
                            darkNavy: '#0a1128',
                            orange: '#ff5500',
                            fireOrange: '#ea580c',
                            gold: '#ffaa00',
                            yellow: '#ffd000',
                            bgGray: '#f1f4f9',
                            cardGray: '#ffffff'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    boxShadow: {
                        'neon-blue': '0 0 25px rgba(34, 28, 188, 0.45)',
                        'neon-gold': '0 0 20px rgba(255, 210, 51, 0.4)',
                        'speaker-glow': '0 20px 50px -10px rgba(0,0,0,0.5)',
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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

        /* Nav scoop transition curve shadow */
        .scoop-tab {
            filter: drop-shadow(0 4px 12px rgba(10, 6, 90, 0.35));
        }

        /* Carousel slide animation */
        .carousel-slide {
            transition: opacity 0.7s cubic-bezier(0.4, 0, 0.2, 1), transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Hide scrollbars */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Pulse glow animation */
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }
        .animate-pulse-glow {
            animation: pulse-glow 3s infinite ease-in-out;
        }

        /* WhatsApp float ripple */
        @keyframes ripple {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.6); opacity: 0; }
        }
        .whatsapp-ripple {
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            border: 2px solid #25d366;
            animation: ripple 2s infinite cubic-bezier(0, 0.2, 0.8, 1);
        }
    </style>
</head>
<body class="selection:bg-brand-blue selection:text-white relative min-h-screen overflow-x-hidden">

    <!-- ================================================================= -->
    <!-- FLOATING STICKY CAPSULE NAVBAR (Active on Scroll, Exact Reference) -->
    <!-- ================================================================= -->
    <nav id="sticky-navbar" 
         class="fixed top-2.5 sm:top-3.5 left-1/2 -translate-x-1/2 w-[95%] max-w-[1360px] z-50 transition-all duration-300 transform -translate-y-24 opacity-0 pointer-events-none">
        <div class="w-full bg-white/95 backdrop-blur-md rounded-full shadow-[0_8px_30px_rgba(0,0,0,0.12)] border border-gray-200/90 px-3.5 sm:px-6 lg:px-8 h-[48px] sm:h-[52px] flex items-center justify-between">
            
            <!-- LEFT WING: CHARGEHUB LOGO -->
            <a href="index.php" class="flex items-center space-x-2 group flex-shrink-0" title="ChargeHub.pk - Genuine Chargers, Genuine Cables">
                <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-6.5 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105">
            </a>

            <!-- CENTER: DESKTOP NAVIGATION LINKS (Visible on Laptop & Desktop screens >= 1024px) -->
            <div class="hidden lg:flex items-center space-x-4 lg:space-x-6 text-[12.5px] lg:text-[13.5px] font-semibold text-gray-800 tracking-normal">
                <a href="index.php" class="text-brand-blue font-bold whitespace-nowrap">Home</a>
                <a href="explore.php" class="hover:text-brand-blue transition-colors whitespace-nowrap">Catalog</a>
                <a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="hover:text-brand-blue transition-colors whitespace-nowrap">Prime 140W</a>
                <a href="explore.php?category=Chargers" class="hover:text-brand-blue transition-colors whitespace-nowrap">Fast Chargers</a>
                <a href="explore.php?category=Cables" class="hover:text-brand-blue transition-colors whitespace-nowrap">Genuine Cables</a>
                <a href="explore.php?category=PowerBanks" class="hover:text-brand-blue transition-colors whitespace-nowrap">Power Banks</a>
                <a href="index.php#sale" class="font-bold text-brand-orange hover:text-brand-gold flex items-center space-x-1 whitespace-nowrap transition-transform hover:scale-105">
                    <span>10.10 SALE</span>
                </a>
            </div>

            <!-- RIGHT WING: SEARCH, CART & MOBILE/TABLET SIDEBAR HAMBURGER -->
            <div class="flex items-center space-x-2 sm:space-x-3.5 flex-shrink-0">
                <!-- Search Button -->
                <a href="explore.php" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all hover:scale-105" title="Search catalog">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </a>

                <!-- Shopping Bag Button -->
                <button type="button" onclick="openCartDrawer()" class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all hover:scale-105 cursor-pointer" title="View Cart">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="cart-counter-badge absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-orange text-[10px] font-bold text-white flex items-center justify-center shadow-sm shadow-orange-500/30">
                        0
                    </span>
                </button>

                <!-- Mobile / Tablet Sidebar Navigation Trigger (Visible < 1024px) -->
                <button type="button" onclick="openMobileSidebar()" class="lg:hidden w-8 h-8 sm:w-9 sm:h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 transition-all cursor-pointer active:scale-95" title="Open Navigation Menu" aria-label="Open Navigation Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16m-7 6h7"/>
                    </svg>
                </button>
            </div>

        </div>
    </nav>

    <!-- ================================================================= -->
    <!-- FULL WIDTH HERO SECTION (Edge-to-Edge Full Screen Width) -->
    <!-- ================================================================= -->
    <section class="w-full px-1.5 sm:px-2 md:px-2.5 pt-0.5 sm:pt-1 pb-2">
        <div class="relative w-full rounded-[18px] sm:rounded-[24px] md:rounded-[30px] overflow-hidden shadow-2xl aspect-[16/7.3] sm:aspect-[16/7.2] md:aspect-[16/7.1] min-h-[460px] sm:min-h-[490px] md:min-h-[540px] lg:min-h-[580px] xl:min-h-[620px] bg-[#1a1c23]">

            <!-- ======================================================== -->
            <!-- THE CURVED NAV NOTCH / SCOOP HEADER (THE SIGNATURE COMPONENT) -->
            <!-- ======================================================== -->
            <header class="absolute top-0 inset-x-0 z-40 pointer-events-none">
                <div class="relative w-full h-[52px] sm:h-[58px] flex items-start justify-between px-4 sm:px-8 lg:px-10">

                    <!-- LEFT WING: CHARGEHUB LOGO (Overlaid on Hero Image) -->
                    <div class="pointer-events-auto flex items-center pt-2 sm:pt-2.5 z-50">
                        <a href="index.php" class="flex items-center space-x-2 group" title="ChargeHub.pk - Genuine Chargers, Genuine Cables">
                            <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-7 sm:h-9 lg:h-10 w-auto object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,0.6)] transition-transform group-hover:scale-105">
                        </a>
                    </div>

                    <!-- ========================================================== -->
                    <!-- CENTRAL DIPPING TAB (Visible on Desktop >= 1024px with Bézier S-Curves) -->
                    <!-- ========================================================== -->
                    <div id="scoop-nav-container" class="pointer-events-auto hidden lg:flex items-center absolute top-0 left-1/2 -translate-x-1/2 scoop-tab z-50 transition-all duration-700">
                        
                        <!-- LEFT S-CURVE TRANSITION WING -->
                        <svg class="h-[42px] sm:h-[48px] lg:h-[50px] w-[58px] sm:w-[65px] lg:w-[70px] -mr-[0.5px] flex-shrink-0 pointer-events-none transition-all duration-700" 
                             viewBox="0 0 52 33" fill="none" preserveAspectRatio="none">
                            <path id="scoop-left-path" d="M0 0 H52 V33 C30 33 22 0 0 0 Z" fill="#0052cc" class="transition-colors duration-700"/>
                        </svg>

                        <!-- CENTRAL NAVIGATION LINKS BODY -->
                        <nav id="scoop-nav-center" class="h-[42px] sm:h-[48px] lg:h-[50px] px-8 sm:px-10 lg:px-12 flex items-center space-x-6 sm:space-x-8 lg:space-x-10 text-[13.5px] sm:text-[15px] lg:text-[15.5px] font-semibold text-white/95 tracking-normal transition-colors duration-700 bg-[#0052cc]">
                            <a href="index.php" class="text-white font-black underline decoration-brand-orange underline-offset-4 whitespace-nowrap">Home</a>
                            <a href="explore.php" class="hover:text-white transition-colors whitespace-nowrap">Catalog</a>
                            <a href="product-detail.php?slug=anker-prime-140w-gan-charger" class="hover:text-white transition-colors whitespace-nowrap">Prime 140W</a>
                            <a href="explore.php?category=Chargers" class="hover:text-white transition-colors whitespace-nowrap">Fast Chargers</a>
                            <a href="explore.php?category=Cables" class="hover:text-white transition-colors whitespace-nowrap">Genuine Cables</a>
                            <a href="explore.php?category=PowerBanks" class="hover:text-white transition-colors whitespace-nowrap">Power Banks</a>
                            <a href="index.php#sale" class="font-bold text-[#ffd700] hover:text-yellow-300 flex items-center space-x-1 whitespace-nowrap transition-transform hover:scale-105">
                                <span class="tracking-wider">10.10 SALE</span>
                            </a>
                        </nav>

                        <!-- RIGHT S-CURVE TRANSITION WING -->
                        <svg class="h-[42px] sm:h-[48px] lg:h-[50px] w-[58px] sm:w-[65px] lg:w-[70px] -ml-[0.5px] flex-shrink-0 pointer-events-none transition-all duration-700" 
                             viewBox="0 0 52 33" fill="none" preserveAspectRatio="none">
                            <path id="scoop-right-path" d="M0 0 H52 C30 0 22 33 0 33 V0 Z" fill="#0052cc" class="transition-colors duration-700"/>
                        </svg>
                    </div>

                    <!-- RIGHT WING: SEARCH, CART & MOBILE/TABLET SIDEBAR HAMBURGER -->
                    <div class="pointer-events-auto flex items-center space-x-2.5 sm:space-x-3 pt-2 sm:pt-2.5 z-50">
                        <!-- Search Button -->
                        <a href="explore.php" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-white/80 hover:text-white transition-all hover:scale-105" title="Search catalog">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </a>

                        <!-- Shopping Bag Button -->
                        <button type="button" id="cart-trigger" onclick="openCartDrawer()" class="relative w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-white/80 hover:text-white transition-all hover:scale-105 cursor-pointer" title="View Cart">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="cart-counter-badge absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-orange text-[10px] font-bold text-white flex items-center justify-center shadow-sm shadow-orange-500/30">
                                0
                            </span>
                        </button>

                        <!-- Mobile & Tablet Sidebar Navigation Trigger (Visible < 1024px) -->
                        <button type="button" id="mobile-menu-trigger" onclick="openMobileSidebar()" class="lg:hidden w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-white/90 hover:text-white transition-all rounded-lg hover:bg-white/10 active:scale-95 cursor-pointer" title="Open Navigation Menu" aria-label="Open Navigation Sidebar">
                            <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16m-7 6h7"/>
                            </svg>
                        </button>
                    </div>

                </div>
            </header>

            <!-- ======================================================== -->
            <!-- HERO CAROUSEL: DYNAMIC CMS POWERED (SQL INJECTION SAFE) -->
            <!-- ======================================================== -->
            <div id="hero-carousel" class="relative w-full h-full select-none">

                <?php foreach ($hero_slides as $index => $slide): 
                    $isActive = ($index === 0);
                    $navColor = !empty($slide['nav_color']) ? $slide['nav_color'] : '#0052cc';
                ?>
                <!-- DYNAMIC SLIDE <?= $index + 1 ?>: <?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?> -->
                <div class="carousel-slide absolute inset-0 w-full h-full <?= $isActive ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none' ?> transition-opacity duration-700 ease-in-out" data-slide="<?= $index ?>" data-nav-color="<?= htmlspecialchars($navColor, ENT_QUOTES, 'UTF-8') ?>">
                    <img src="<?= htmlspecialchars($slide['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover object-center">
                    
                    <?php if ($index > 0): ?>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#000814]/95 via-[#04122b]/80 to-transparent"></div>
                    <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/50 via-black/15 to-transparent pointer-events-none"></div>
                    <?php endif; ?>

                    <!-- SLIDE CONTENT -->
                    <div class="relative z-20 w-full h-full flex flex-col justify-center px-6 sm:px-12 md:px-16 lg:px-20 pt-12 pb-8 max-w-[860px]">
                        <?php if (!empty($slide['badge_text'])): ?>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full <?= $index === 0 ? 'bg-gradient-to-r from-brand-orange to-brand-gold text-white shadow-[0_2px_10px_rgba(255,85,0,0.35)]' : 'bg-blue-600/90 text-white shadow-[0_0_15px_rgba(37,99,235,0.4)]' ?> text-[10.5px] sm:text-[11px] font-extrabold tracking-widest uppercase mb-3 w-max">
                            <span>⚡</span> <?= htmlspecialchars($slide['badge_text'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php endif; ?>
                        
                        <h2 class="brand-font text-[28px] sm:text-[40px] md:text-[50px] font-black <?= $index === 0 ? 'text-gray-950' : 'text-white drop-shadow-lg' ?> leading-[1.08] tracking-tight mb-3">
                            <?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        
                        <?php if (!empty($slide['subtitle'])): ?>
                        <p class="text-xs sm:text-sm md:text-base <?= $index === 0 ? 'text-gray-700' : 'text-blue-100/90' ?> font-medium mb-5 max-w-xl line-clamp-2 leading-relaxed">
                            <?= htmlspecialchars($slide['subtitle'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <?php endif; ?>

                        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 mb-6">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg <?= $index === 0 ? 'bg-white/80 border-gray-300 text-gray-900 shadow-sm' : 'bg-white/10 border-white/15 text-white' ?> backdrop-blur-md border text-[11px] font-semibold">
                                ⚡ Ultra Fast Delivery
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg <?= $index === 0 ? 'bg-white/80 border-gray-300 text-gray-900 shadow-sm' : 'bg-white/10 border-white/15 text-white' ?> backdrop-blur-md border text-[11px] font-semibold">
                                🛡️ Multi-Tier Heat Shield
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg <?= $index === 0 ? 'bg-white/80 border-gray-300 text-gray-900 shadow-sm' : 'bg-white/10 border-white/15 text-white' ?> backdrop-blur-md border text-[11px] font-semibold">
                                🔋 100% Genuine Certified
                            </span>
                        </div>

                        <div class="flex items-center space-x-3.5">
                            <a href="<?= htmlspecialchars(!empty($slide['cta_link']) ? $slide['cta_link'] : 'explore.php', ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-2.5 sm:py-3 rounded-full <?= $index === 0 ? 'bg-gradient-to-r from-brand-orange to-[#ff6b00] text-white shadow-orange-500/30 hover:shadow-orange-500/50' : 'bg-white text-gray-950 hover:bg-brand-gold' ?> font-bold text-xs sm:text-sm tracking-wide shadow-lg hover:brightness-110 transition-all transform hover:-translate-y-0.5">
                                <?= htmlspecialchars(!empty($slide['cta_text']) ? $slide['cta_text'] : 'Explore Catalog', ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <a href="product-detail.php" class="px-5 py-2.5 sm:py-3 rounded-full <?= $index === 0 ? 'bg-white/85 text-gray-900 border-gray-300 hover:border-gray-400' : 'bg-white/15 text-white border-white/20 hover:bg-white/25' ?> hover:bg-white font-bold text-xs sm:text-sm backdrop-blur-md border shadow-sm transition-all">
                                Product Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            <!-- ======================================================== -->
                <!-- CAROUSEL CONTROLS: PREV / NEXT ARROWS -->
                <!-- ======================================================== -->
                <button id="carousel-prev" class="absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-30 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/20 hover:bg-white text-white hover:text-black backdrop-blur-md border border-white/20 flex items-center justify-center transition-all hover:scale-110 shadow-lg" title="Previous Slide">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button id="carousel-next" class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-30 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/20 hover:bg-white text-white hover:text-black backdrop-blur-md border border-white/20 flex items-center justify-center transition-all hover:scale-110 shadow-lg" title="Next Slide">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- ======================================================== -->
                <!-- CAROUSEL PAGINATION PILLS (Matching Reference) -->
                <!-- ======================================================== -->
                <div id="carousel-dots" class="absolute bottom-3 sm:bottom-5 left-1/2 -translate-x-1/2 z-30 flex items-center space-x-2 bg-black/25 backdrop-blur-md px-3 py-1 rounded-full border border-white/10">
                    <?php foreach ($hero_slides as $index => $slide): ?>
                    <button class="carousel-dot h-1.5 rounded-full transition-all duration-300 <?= $index === 0 ? 'w-6 bg-white shadow-sm' : 'w-1.5 bg-white/40 hover:bg-white/70' ?>" data-index="<?= $index ?>" title="Slide <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- 10.10 — SUPER SALE IS LIVE & FLASH SALE DEALS SECTION -->
    <!-- ================================================================= -->
    <section id="sale" class="max-w-[1440px] mx-auto px-3 sm:px-6 lg:px-8 pt-8 sm:pt-12 pb-4 sm:pb-6">
        
        <!-- SUPER SALE HEADER & LIVE COUNTDOWN TIMER -->
        <div class="flex flex-col items-center text-center mb-6 sm:mb-8">
            <span class="text-xs sm:text-[13px] font-extrabold tracking-wide text-gray-900 uppercase mb-3">
                10.10 &mdash; CHARGEHUB SUPER SALE is Live
            </span>

            <!-- COUNTDOWN TIMER 4 FLOATING CARDS -->
            <div class="flex items-center justify-center space-x-2.5 sm:space-x-3.5 mb-5 sm:mb-6">
                <!-- DAYS -->
                <div class="w-13 sm:w-16 h-14 sm:h-16 rounded-xl sm:rounded-2xl bg-white shadow-[0_6px_20px_rgba(0,102,255,0.12)] border border-blue-100/80 flex flex-col items-center justify-center px-2">
                    <span id="countdown-days" class="brand-font text-xl sm:text-2xl font-black text-brand-blue leading-none">01</span>
                    <span class="text-[8px] sm:text-[9.5px] font-bold text-gray-400 tracking-wider uppercase mt-1">DAYS</span>
                </div>
                <!-- HOURS -->
                <div class="w-13 sm:w-16 h-14 sm:h-16 rounded-xl sm:rounded-2xl bg-white shadow-[0_6px_20px_rgba(0,102,255,0.12)] border border-blue-100/80 flex flex-col items-center justify-center px-2">
                    <span id="countdown-hours" class="brand-font text-xl sm:text-2xl font-black text-brand-blue leading-none">21</span>
                    <span class="text-[8px] sm:text-[9.5px] font-bold text-gray-400 tracking-wider uppercase mt-1">HOURS</span>
                </div>
                <!-- MINUTES -->
                <div class="w-13 sm:w-16 h-14 sm:h-16 rounded-xl sm:rounded-2xl bg-white shadow-[0_6px_20px_rgba(0,102,255,0.12)] border border-blue-100/80 flex flex-col items-center justify-center px-2">
                    <span id="countdown-mins" class="brand-font text-xl sm:text-2xl font-black text-brand-blue leading-none">37</span>
                    <span class="text-[8px] sm:text-[9.5px] font-bold text-gray-400 tracking-wider uppercase mt-1">MINUTES</span>
                </div>
                <!-- SECONDS -->
                <div class="w-13 sm:w-16 h-14 sm:h-16 rounded-xl sm:rounded-2xl bg-white shadow-[0_6px_20px_rgba(0,102,255,0.12)] border border-blue-100/80 flex flex-col items-center justify-center px-2">
                    <span id="countdown-secs" class="brand-font text-xl sm:text-2xl font-black text-brand-blue leading-none">18</span>
                    <span class="text-[8px] sm:text-[9.5px] font-bold text-gray-400 tracking-wider uppercase mt-1">SECONDS</span>
                </div>
            </div>

            <!-- SECTION TITLE: Flash Sale Deals -->
            <h2 class="brand-font text-xl sm:text-2xl md:text-[26px] font-black text-gray-950 tracking-tight">
                Flash Sale Deals
            </h2>
        </div>

        <!-- 5 PRODUCT CARDS (RESPONSIVE 2-COL MOBILE, 3-COL TABLET, 5-COL DESKTOP) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-4.5">
            <?php foreach ($flash_sale_items as $fItem): ?>
            <!-- Flash Sale Card -->
            <div class="bg-white rounded-2xl sm:rounded-[20px] overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-purple-100/80 flex flex-col justify-between relative group">
                <!-- Top Ribbon -->
                <div class="w-full bg-gradient-to-r from-[#0052cc] via-[#00a8ff] to-[#ff5500] h-[24px] sm:h-[26px] flex items-center justify-center relative px-2">
                    <span class="text-[9px] sm:text-[10.5px] font-medium text-white tracking-wide truncate max-w-[70%] sm:max-w-none"><?= htmlspecialchars($fItem['ribbon_text'] ?? '10.10 sale is live') ?></span>
                    <!-- Discount Pill Badge -->
                    <span class="absolute right-1.5 sm:right-2 top-1 sm:top-1.5 px-2 sm:px-2.5 py-0.5 rounded-full bg-brand-blue text-[8.5px] sm:text-[9.5px] font-extrabold text-white shadow-xs">
                        <?= htmlspecialchars($fItem['discount_text'] ?? 'Sale') ?>
                    </span>
                </div>

                <div class="p-3 sm:p-4 flex flex-col flex-1 justify-between bg-[#faf8fe]/60">
                    <!-- Product Image -->
                    <a href="<?= htmlspecialchars($fItem['link_url'] ?? 'explore.php') ?>" class="w-full h-[110px] sm:h-[135px] flex items-center justify-center mb-2 overflow-hidden block">
                        <img src="<?= htmlspecialchars($fItem['image_url']) ?>" alt="<?= htmlspecialchars($fItem['title']) ?>" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.src='images/Hero Banner.jpg'">
                    </a>

                    <!-- Product Details -->
                    <div>
                        <a href="<?= htmlspecialchars($fItem['link_url'] ?? 'explore.php') ?>" class="block">
                            <h3 class="font-bold text-[12px] sm:text-[13px] text-gray-900 leading-snug line-clamp-1 group-hover:text-brand-blue transition-colors" title="<?= htmlspecialchars($fItem['title']) ?>">
                                <?= htmlspecialchars($fItem['title']) ?>
                            </h3>
                        </a>

                        <!-- Rating Stars -->
                        <div class="flex items-center space-x-0.5 mt-1">
                            <span class="text-amber-400 text-xs">★</span>
                            <span class="text-amber-400 text-xs">★</span>
                            <span class="text-amber-400 text-xs">★</span>
                            <span class="text-amber-400 text-xs">★</span>
                            <span class="text-amber-400 text-xs">★</span>
                            <span class="text-[9.5px] sm:text-[10px] text-gray-500 font-bold ml-1"><?= !empty($fItem['rating']) && (float)$fItem['rating'] > 0 ? number_format((float)$fItem['rating'], 1) : '5.0' ?></span>
                        </div>

                        <!-- Divider line -->
                        <div class="border-t border-gray-200/60 my-1.5 sm:my-2"></div>

                        <!-- Price -->
                        <div class="flex items-baseline space-x-1 sm:space-x-1.5 flex-wrap">
                            <span class="text-[12.5px] sm:text-[14px] font-extrabold text-gray-950">Rs.<?= number_format($fItem['price'], 0) ?></span>
                            <?php if (!empty($fItem['original_price'])): ?>
                                <span class="text-[9.5px] sm:text-[11px] text-gray-400 line-through">Rs.<?= number_format($fItem['original_price'], 0) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Color Swatch -->
                        <div class="mt-1.5 sm:mt-2 mb-2 sm:mb-3">
                            <span class="inline-block w-3 sm:w-3.5 h-3 sm:h-3.5 rounded-full border-2 border-white ring-1 ring-gray-400 cursor-pointer shadow-xs" style="background-color: <?= htmlspecialchars($fItem['color_swatch'] ?? '#000000') ?>;" title="Color"></span>
                        </div>
                    </div>

                    <!-- Buy Now Button -->
                    <button type="button" class="w-full py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-[#0066ff] via-[#0088ff] to-[#ff5500] hover:from-[#0052cc] hover:to-[#ea580c] text-white font-bold text-[11px] sm:text-xs flex items-center justify-center space-x-1 sm:space-x-1.5 shadow-md hover:shadow-lg transition-all active:scale-[0.98] add-cart-btn cursor-pointer" data-product="<?= htmlspecialchars($fItem['title']) ?>" data-slug="<?= htmlspecialchars($fItem['slug'] ?? slugify($fItem['title'])) ?>" data-price="<?= $fItem['price'] ?>" data-image="<?= htmlspecialchars($fItem['image_url'] ?? '') ?>">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Buy Now</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- NEW ARRIVALS SHOWCASE (Exact Replica with Floating Pop-Out Cards) -->
    <!-- ================================================================= -->
    <section id="new-arrivals" class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 pb-10 sm:pb-14">
        
        <!-- SECTION TITLE & SUBTITLE -->
        <div class="mb-8 sm:mb-12">
            <div class="flex items-center space-x-2 mb-1">
                <span class="inline-block w-4 h-0.5 bg-red-500 rounded-full"></span>
                <span class="text-[11px] sm:text-xs font-bold tracking-[0.2em] uppercase text-gray-500">
                    INTRODUCING
                </span>
            </div>
            <h2 class="brand-font text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-950 tracking-tight">
                New Arrivals
            </h2>
        </div>

        <!-- DYNAMIC POP-OUT PRODUCT CARDS CAROUSEL (With Sculpted Organic S-Curve Cutout) -->
        <div id="new-arrivals-cards-container" class="flex gap-6 sm:gap-7 overflow-x-auto scroll-smooth no-scrollbar pt-6 sm:pt-10 pb-4 snap-x snap-mandatory">
            <?php foreach ($new_arrival_items as $arrIdx => $arr): 
                $arrLink = !empty($arr['link_url']) ? $arr['link_url'] : 'explore.php';
                $arrImg = !empty($arr['image_url']) ? $arr['image_url'] : 'images/1000x1000 transparent PNG/1.png';
                $arrPrice = (float)($arr['price'] ?? 0);
                $arrOrigPrice = (float)($arr['original_price'] ?? 0);
                $arrDiscount = (int)($arr['discount_pct'] ?? 0);
                $arrRating = (float)($arr['rating'] ?? 0);
            ?>
            <!-- CARD <?= $arrIdx + 1 ?>: <?= htmlspecialchars($arr['title']) ?> -->
            <div class="new-arrival-card snap-start flex-shrink-0 w-[280px] sm:w-[calc(50%-14px)] lg:w-[calc(25%-21px)] group relative flex flex-col justify-between p-5 sm:p-6 transition-all duration-300 hover:-translate-y-1 filter drop-shadow-[0_8px_25px_rgba(0,0,0,0.06)] hover:drop-shadow-[0_16px_35px_rgba(0,0,0,0.12)]">
                
                <!-- SCULPTED CARD SVG BACKGROUND WITH ORGANIC S-CURVE RIGHT SCOOP -->
                <svg class="absolute inset-0 w-full h-full -z-10 pointer-events-none" viewBox="0 0 280 440" preserveAspectRatio="none" fill="none">
                    <path d="M 28 0 H 252 C 268 0 280 12 280 28 V 135 C 280 168 244 172 244 202 V 265 C 244 295 280 298 280 330 V 412 C 280 428 268 440 252 440 H 28 C 12 440 0 428 0 412 V 28 C 0 12 12 0 28 0 Z" fill="#ffffff" stroke="#f1f5f9" stroke-width="1.2"/>
                </svg>

                <!-- VERTICAL "NEW ARRIVAL" RIBBON PILL (Docked on Right Edge of Sculpted Scoop) -->
                <div class="absolute right-0 top-[35%] -translate-y-1/2 z-20 flex items-center justify-center">
                    <div class="w-6 sm:w-6.5 h-28 sm:h-32 rounded-l-2xl bg-gradient-to-b from-[#0036b8] via-[#a21caf] to-[#ff6b00] shadow-md flex items-center justify-center p-1">
                        <span class="text-[8px] sm:text-[8.5px] font-black text-white uppercase tracking-widest whitespace-nowrap rotate-90 select-none">
                            <?= htmlspecialchars($arr['badge_text'] ?: 'NEW ARRIVAL') ?>
                        </span>
                    </div>
                </div>

                <!-- TOP FLOATING PRODUCT IMAGE -->
                <a href="<?= htmlspecialchars($arrLink) ?>" class="relative -mt-12 sm:-mt-16 mb-3 flex items-center justify-center h-[180px] sm:h-[200px] group/img">
                    <img src="<?= htmlspecialchars($arrImg) ?>" alt="<?= htmlspecialchars($arr['title']) ?>" class="w-full h-full object-contain group-hover/img:scale-105 group-hover:scale-105 transition-transform duration-500">
                </a>

                <!-- PRODUCT DETAILS -->
                <div>
                    <a href="<?= htmlspecialchars($arrLink) ?>" class="block">
                        <h3 class="font-black text-sm sm:text-base text-gray-900 tracking-tight uppercase group-hover:text-brand-blue transition-colors line-clamp-1">
                            <?= htmlspecialchars($arr['title']) ?>
                        </h3>
                    </a>
                    <div class="text-[11px] sm:text-xs text-gray-400 font-medium tracking-wide mt-0.5">
                        <?= htmlspecialchars($arr['sku'] ?: 'NEW') ?>
                    </div>

                    <!-- STAR RATING -->
                    <div class="flex items-center space-x-1 my-2">
                        <span class="text-amber-400 text-xs sm:text-sm tracking-tighter">
                            <?php
                            for ($s = 1; $s <= 5; $s++) {
                                echo ($s <= round($arrRating)) ? '&#9733;' : '&#9734;';
                            }
                            ?>
                        </span>
                        <span class="text-[11px] sm:text-xs text-gray-400 font-semibold ml-1"><?= number_format($arrRating, 1) ?></span>
                    </div>

                    <!-- PRICE & COLOR SWATCHES -->
                    <div class="flex items-end justify-between my-2">
                        <div>
                            <?php if ($arrDiscount > 0): ?>
                                <span class="text-[11px] sm:text-xs font-bold text-red-500 block leading-tight"><?= $arrDiscount ?>% Off</span>
                            <?php endif; ?>
                            <div class="flex items-baseline space-x-1.5 mt-0.5">
                                <span class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Rs.<?= number_format($arrPrice) ?></span>
                                <?php if ($arrOrigPrice > $arrPrice): ?>
                                    <span class="text-xs text-gray-400 line-through">Rs.<?= number_format($arrOrigPrice) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Swatches -->
                        <div class="flex items-center space-x-1.5 mb-1">
                            <span class="w-3 h-3 rounded-full bg-[#111827] shadow-inner ring-1 ring-black/5" title="Classic Black"></span>
                            <span class="w-3 h-3 rounded-full bg-[#f1f5f9] border border-gray-300 shadow-inner" title="Silver White"></span>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SPECS & GLOWING BUY NOW BUTTON -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2 mt-2">
                    <!-- 3 Feature Columns -->
                    <div class="grid grid-cols-3 gap-2 flex-grow overflow-hidden">
                        <div>
                            <div class="text-[9.5px] sm:text-[10.5px] font-bold text-gray-900 leading-tight truncate"><?= htmlspecialchars($arr['spec_1_title'] ?: 'Fast') ?></div>
                            <div class="text-[8px] sm:text-[9px] text-gray-400 leading-tight truncate"><?= htmlspecialchars($arr['spec_1_sub'] ?: 'Charge') ?></div>
                        </div>
                        <div>
                            <div class="text-[9.5px] sm:text-[10.5px] font-bold text-gray-900 leading-tight truncate"><?= htmlspecialchars($arr['spec_2_title'] ?: 'Smart') ?></div>
                            <div class="text-[8px] sm:text-[9px] text-gray-400 leading-tight truncate"><?= htmlspecialchars($arr['spec_2_sub'] ?: 'Power') ?></div>
                        </div>
                        <div>
                            <div class="text-[9.5px] sm:text-[10.5px] font-bold text-gray-900 leading-tight truncate"><?= htmlspecialchars($arr['spec_3_title'] ?: 'Safe') ?></div>
                            <div class="text-[8px] sm:text-[9px] text-gray-400 leading-tight truncate"><?= htmlspecialchars($arr['spec_3_sub'] ?: 'GaN Tech') ?></div>
                        </div>
                    </div>
                    <!-- Buy Now Button with Glowing Neon Gradient Border -->
                    <button type="button" class="flex-shrink-0 relative group/btn p-[2px] rounded-full bg-gradient-to-r from-[#00d2ff] via-[#a855f7] to-[#ec4899] shadow-[0_3px_14px_rgba(236,72,153,0.30)] hover:shadow-[0_4px_20px_rgba(0,210,255,0.45)] transition-all active:scale-95 add-cart-btn cursor-pointer" data-product="<?= htmlspecialchars($arr['title']) ?>" data-price="<?= $arrPrice ?>" data-image="<?= htmlspecialchars($arrImg) ?>">
                        <div class="px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full bg-[#0a0b0e] hover:bg-[#15171e] flex items-center space-x-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-white text-[11px] sm:text-xs font-black tracking-tight whitespace-nowrap">Buy Now</span>
                        </div>
                    </button>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- CAROUSEL PAGINATION & PROGRESS SLIDER (Interactive Exact Replica) -->
        <div class="flex items-center justify-center space-x-5 mt-10 sm:mt-12">
            <!-- Arrows & Counter: < 1 / 9 > -->
            <div class="flex items-center space-x-2 text-sm font-semibold text-gray-700 select-none">
                <button id="new-arrivals-prev" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-200 text-gray-600 transition-colors active:scale-90" title="Previous Slide">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span id="new-arrivals-counter" class="tracking-widest text-xs font-extrabold text-gray-900 px-1">1 / <?= count($new_arrival_items) ?: 1 ?></span>
                <button id="new-arrivals-next" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-200 text-gray-600 transition-colors active:scale-90" title="Next Slide">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Horizontal Slider Progress Track -->
            <div class="w-48 sm:w-64 h-2 bg-gray-200/90 rounded-full overflow-hidden relative cursor-pointer" id="new-arrivals-track">
                <div id="new-arrivals-progress" class="h-full bg-[#111317] rounded-full transition-all duration-300" style="width: <?= count($new_arrival_items) > 0 ? max(10, round(100 / count($new_arrival_items), 1)) : 100 ?>%;"></div>
            </div>
        </div>

    </section>

    <!-- ================================================================= -->
    <!-- PRODUCT SPOTLIGHT SECTION (Working Interactive 3D Carousel) -->
    <!-- ================================================================= -->
    <section class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        
        <!-- SECTION TITLE & SUBTITLE -->
        <div class="mb-4 sm:mb-6">
            <div class="flex items-center space-x-2 mb-1.5">
                <span class="inline-block w-4 h-0.5 bg-brand-blue"></span><span class="inline-block w-2 h-0.5 bg-brand-orange ml-1"></span>
                <span class="text-xs sm:text-[13px] font-bold tracking-[0.2em] uppercase text-gray-500">
                    DON'T MISS THESE
                </span>
            </div>
            <h2 class="brand-font text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-950 tracking-tight">
                Product Spotlight
            </h2>
        </div>

        <!-- SPOTLIGHT CAROUSEL CONTAINER (Working Interactive 3D Stage with Overflowing Pop-Out Perspective) -->
        <div id="spotlight-carousel-container" class="relative w-full h-[240px] sm:h-[280px] md:h-[320px] lg:h-[350px] overflow-hidden select-none my-2 group">
            
            <!-- TRACK WITH DYNAMIC CMS POP-OUT SPOTLIGHT CARDS -->
            <div id="spotlight-track" class="relative w-full h-full">

                <?php foreach ($spotlight_items as $index => $item): ?>
                <!-- CARD <?= $index ?>: <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?> -->
                <div class="spotlight-card absolute left-1/2 top-1/2 w-[280px] sm:w-[360px] md:w-[420px] lg:w-[460px] aspect-[518/280] cursor-pointer transition-all duration-700 ease-[cubic-bezier(0.25,1,0.5,1)]" data-index="<?= $index ?>">
                    <a href="<?= htmlspecialchars(!empty($item['link_url']) ? $item['link_url'] : 'product-detail.php', ENT_QUOTES, 'UTF-8') ?>" class="block w-full h-full">
                        <img src="<?= htmlspecialchars($item['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-contain pointer-events-none">
                    </a>
                </div>
                <?php endforeach; ?>

            </div>

            <!-- PREV / NEXT NAV BUTTONS (Visible On Hover / Clean Glass) -->
            <button id="spotlight-prev" class="absolute left-1 sm:left-4 top-1/2 -translate-y-1/2 z-30 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/80 hover:bg-white text-gray-800 shadow-md border border-gray-200/80 flex items-center justify-center transition-all hover:scale-110 opacity-60 group-hover:opacity-100" title="Previous Spotlight">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button id="spotlight-next" class="absolute right-1 sm:right-4 top-1/2 -translate-y-1/2 z-30 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/80 hover:bg-white text-gray-800 shadow-md border border-gray-200/80 flex items-center justify-center transition-all hover:scale-110 opacity-60 group-hover:opacity-100" title="Next Spotlight">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

        </div>

        <!-- CAROUSEL PAGINATION PILL & DOTS (Interactive Exact Reference) -->
        <div id="spotlight-dots" class="flex items-center justify-center space-x-2 mt-4 sm:mt-6">
            <button class="spotlight-dot h-1.5 rounded-full transition-all duration-300 w-1.5 bg-gray-300 hover:bg-gray-400" data-index="0" title="Slide 1"></button>
            <button class="spotlight-dot h-1.5 rounded-full transition-all duration-300 w-6 bg-[#e6222b] shadow-sm" data-index="1" title="Slide 2"></button>
            <button class="spotlight-dot h-1.5 rounded-full transition-all duration-300 w-1.5 bg-gray-300 hover:bg-gray-400" data-index="2" title="Slide 3"></button>
            <button class="spotlight-dot h-1.5 rounded-full transition-all duration-300 w-1.5 bg-gray-300 hover:bg-gray-400" data-index="3" title="Slide 4"></button>
            <button class="spotlight-dot h-1.5 rounded-full transition-all duration-300 w-1.5 bg-gray-300 hover:bg-gray-400" data-index="4" title="Slide 5"></button>
        </div>

    </section>

    <!-- ================================================================= -->
    <!-- BROWSE BY CATEGORY SECTION (Matching Screenshot Exact Typography) -->
    <!-- ================================================================= -->
    <section class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        
        <!-- SECTION TITLE & SUBTITLE -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 sm:mb-10 pb-4 border-b border-gray-300/60">
            <div>
                <span class="text-xs sm:text-sm font-bold tracking-[0.2em] uppercase text-gray-500 flex items-center gap-2 mb-1">
                    <span class="inline-block w-4 h-0.5 bg-brand-blue"></span>
                    BROWSE BY CATEGORY
                </span>
                <h2 class="brand-font text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">
                    Explore Now
                </h2>
            </div>
            
            <a href="explore.php" class="mt-3 sm:mt-0 inline-flex items-center space-x-1.5 text-xs sm:text-sm font-bold text-brand-blue hover:text-blue-800 transition-colors">
                <span>View Full Product Directory</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <!-- DYNAMIC CATEGORIES GRID (Loaded from Database) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 lg:gap-5">
            <?php foreach ($categories as $cat): 
                $catName = $cat['name'] ?? '';
                $catSlug = $cat['slug'] ?? slugify($catName);
                $catImg = trim($cat['image_url'] ?? '');
                $catBadge = trim($cat['badge_text'] ?? '');
                $catBg = trim($cat['bg_color'] ?? '') ?: 'bg-blue-50';
                $modelsCount = !empty($cat['models_count_text']) ? $cat['models_count_text'] : (((int)($cat['product_count'] ?? 0) > 0) ? ($cat['product_count'] . ' Models') : 'Available');
                $catLink = 'explore.php?category=' . urlencode($catSlug);
                $isImgFile = (strpos($catImg, '/') !== false || strpos($catImg, '.') !== false);
            ?>
            <a href="<?= htmlspecialchars($catLink) ?>" class="group bg-white rounded-2xl p-4 sm:p-5 flex flex-col items-center text-center shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 border border-gray-200/80">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl <?= htmlspecialchars($catBg) ?> group-hover:brightness-95 flex items-center justify-center mb-3 sm:mb-4 transition-all relative overflow-hidden">
                    <?php if ($isImgFile): ?>
                        <img src="<?= htmlspecialchars($catImg) ?>" alt="<?= htmlspecialchars($catName) ?>" class="w-10 h-10 sm:w-12 sm:h-12 object-contain transform group-hover:scale-110 transition-transform">
                    <?php else: ?>
                        <span class="text-3xl sm:text-4xl transform group-hover:scale-110 transition-transform"><?= htmlspecialchars($catImg ?: '⚡') ?></span>
                    <?php endif; ?>
                    <?php if (!empty($catBadge)): ?>
                        <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-brand-blue text-[9px] font-bold text-white rounded uppercase shadow-sm"><?= htmlspecialchars($catBadge) ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-sm sm:text-base text-gray-900 group-hover:text-brand-blue transition-colors mb-1">
                    <?= htmlspecialchars($catName) ?>
                </h3>
                <span class="text-[11px] sm:text-xs text-gray-500 font-medium"><?= htmlspecialchars($modelsCount) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- LATEST PRODUCTS SHOWCASE -->
    <!-- ================================================================= -->
    <section id="latest-products" class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 relative">
        <span id="charging" class="absolute -top-24"></span>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-blue flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-orange animate-pulse"></span>
                    LATEST PRODUCTS
                </span>
                <h2 class="brand-font text-2xl sm:text-3xl font-extrabold text-gray-900 mt-0.5">
                    Latest Products
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-brand-blue/10 text-brand-blue border border-brand-blue/20 font-bold text-xs shadow-sm">
                    100% Genuine Guaranteed &bull; 1-Year Warranty
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            <?php foreach ($latest_products as $fProd): ?>
            <!-- Featured Product Card -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-200/80 flex flex-col justify-between group hover:-translate-y-1">
                <div>
                    <a href="product-detail.php?slug=<?= htmlspecialchars($fProd['slug']) ?>" class="block relative w-full aspect-square rounded-xl overflow-hidden bg-gray-900 mb-4 flex items-center justify-center">
                        <img src="<?= htmlspecialchars($fProd['main_image']) ?>" alt="<?= htmlspecialchars($fProd['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='images/Hero Banner.jpg'">
                        <?php if (!empty($fProd['badge_text'])): ?>
                            <span class="absolute top-2 left-2 px-2.5 py-0.5 bg-gradient-to-r from-brand-blue to-blue-700 text-[10px] font-bold text-white rounded-full shadow-sm">
                                <?= htmlspecialchars($fProd['badge_text']) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wider"><?= htmlspecialchars($fProd['category_name'] ?? 'Fast Power Tech') ?></span>
                    <a href="product-detail.php?slug=<?= htmlspecialchars($fProd['slug']) ?>" class="block">
                        <h3 class="font-bold text-base text-gray-900 group-hover:text-brand-blue transition-colors mt-0.5 line-clamp-1" title="<?= htmlspecialchars($fProd['name']) ?>">
                            <?= htmlspecialchars($fProd['name']) ?>
                        </h3>
                    </a>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                        <?= htmlspecialchars($fProd['short_description'] ?? '') ?>
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-lg font-extrabold text-gray-900">Rs. <?= number_format($fProd['price'], 0) ?></span>
                        <?php if (!empty($fProd['original_price'])): ?>
                            <span class="text-xs text-gray-400 line-through block">Rs. <?= number_format($fProd['original_price'], 0) ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-blue to-[#0052cc] hover:from-[#0052cc] hover:to-brand-orange text-white font-bold text-xs transition-all duration-300 shadow-sm hover:shadow-md add-cart-btn active:scale-95 cursor-pointer" data-product="<?= htmlspecialchars($fProd['name']) ?>" data-slug="<?= htmlspecialchars($fProd['slug']) ?>" data-price="<?= $fProd['price'] ?>" data-image="<?= htmlspecialchars($fProd['main_image'] ?? '') ?>">
                        Add to Bag
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- SHOP BY PRICE – FIND YOUR PERFECT DEAL SECTION -->
    <!-- ================================================================= -->
    <section id="shop-by-price" class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 border-t border-gray-200/70">
        <!-- Section Header -->
        <div class="text-center mb-7 sm:mb-9">
            <h2 class="brand-font text-2xl sm:text-3xl md:text-[34px] font-extrabold text-[#112d42] tracking-tight">
                Shop By Price &ndash; Find Your Perfect Deal
            </h2>
        </div>

        <!-- 8 Price Filter Buttons (4 Columns x 2 Rows, Matching Reference) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4 lg:gap-5 max-w-[1360px] mx-auto">
            <a href="#price-under-5000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Under Rs. 5,000
            </a>
            <a href="#price-5000-20000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Rs. 5,000 &ndash; Rs. 20,000
            </a>
            <a href="#price-20000-30000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Rs. 20,000 &ndash; Rs. 30,000
            </a>
            <a href="#price-30000-50000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Rs. 30,000 &ndash; Rs. 50,000
            </a>
            <a href="#price-50000-75000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Rs. 50,000 &ndash; Rs. 75,000
            </a>
            <a href="#price-75000-100000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Rs. 75,000 &ndash; Rs. 100,000
            </a>
            <a href="#price-100000-200000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Rs. 100,000 &ndash; Rs. 200,000
            </a>
            <a href="#price-above-200000" class="group h-[50px] sm:h-[54px] rounded-lg sm:rounded-xl border-2 border-[#0b3f63] bg-white hover:bg-brand-blue hover:border-brand-blue text-[#0b3f63] hover:text-white font-bold text-xs sm:text-[13.5px] tracking-wide flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                Above Rs. 200,000
            </a>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- SHOP BY BRAND SECTION (12 Official Partner Brands) -->
    <!-- ================================================================= -->
    <section id="shop-by-brand" class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 border-t border-gray-200/70">
        <!-- Section Header -->
        <div class="text-center mb-8 sm:mb-10">
            <h2 class="brand-font text-2xl sm:text-3xl md:text-[34px] font-extrabold text-[#112d42] tracking-tight">
                Shop by Brand
            </h2>
        </div>

        <!-- Dynamic Brand Logos Grid (From Database) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5 sm:gap-4 lg:gap-5 max-w-[1360px] mx-auto mb-8 sm:mb-10">
            <?php foreach ($brands as $b): 
                $bName = $b['name'] ?? '';
                $bSlug = $b['slug'] ?? '';
                $bLogo = !empty($b['logo_url']) ? $b['logo_url'] : 'images/v7/brands/brand_' . strtolower($bSlug) . '_2x.png';
                $bDesc = !empty($b['description']) ? $b['description'] : ($bName . ' Official Products');
                $bLink = 'explore.php?brand=' . urlencode($bName);
            ?>
            <a href="<?= htmlspecialchars($bLink) ?>" class="group bg-white rounded-xl sm:rounded-2xl border border-gray-200/90 hover:border-brand-blue/50 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1 flex items-center justify-center p-4 h-[72px] sm:h-[82px]" title="<?= htmlspecialchars($bDesc) ?>">
                <img src="<?= htmlspecialchars($bLogo) ?>" alt="<?= htmlspecialchars($bName) ?>" class="max-h-6 sm:max-h-7 max-w-[110px] w-auto object-contain transition-transform duration-300 group-hover:scale-105">
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Explore All Brands Pill Button (Centered Matching Screenshot) -->
        <div class="flex justify-center">
            <a href="explore.php" class="inline-flex items-center justify-center px-8 sm:px-10 py-3 rounded-full bg-[#c5c85c] hover:bg-[#b3b64c] text-white font-bold text-xs sm:text-sm tracking-wide shadow-sm hover:shadow-md transition-all transform hover:-translate-y-0.5">
                Explore All Brands
            </a>
        </div>
    </section>

    <!-- ================================================================= -->
    <!-- FLOATING WHATSAPP CHAT BUTTON (Dynamic from Dashboard Setup) -->
    <!-- ================================================================= -->
    <?php 
    $waHomeConfig = function_exists('get_whatsapp_config') ? get_whatsapp_config() : ['enabled' => true, 'number_clean' => '923001234567', 'number_raw' => '+92 300 1234567', 'default_msg' => 'Hi ChargeHub, I have a question about your products.'];
    if (!empty($waHomeConfig['enabled'])): 
    ?>
    <div class="fixed bottom-6 right-6 z-50 flex items-center space-x-3">
        <!-- Floating Tooltip Preview -->
        <div id="wa-tooltip" class="hidden sm:flex items-center px-3.5 py-1.5 rounded-full bg-white text-gray-800 text-xs font-semibold shadow-xl border border-gray-100 transition-opacity">
            <span class="w-2 h-2 rounded-full bg-[#25d366] mr-2 animate-ping"></span>
            Chat with ChargeHub.pk Support
        </div>

        <!-- WhatsApp Floating Icon -->
        <a href="https://wa.me/<?= e($waHomeConfig['number_clean']) ?>?text=<?= urlencode($waHomeConfig['default_msg']) ?>" target="_blank" rel="noopener noreferrer" 
           class="relative w-14 h-14 rounded-full bg-[#25d366] text-white flex items-center justify-center shadow-[0_8px_25px_rgba(37,211,102,0.45)] hover:scale-110 active:scale-95 transition-all group"
           title="WhatsApp Live Chat Support: <?= e($waHomeConfig['number_raw']) ?>">
            <div class="whatsapp-ripple pointer-events-none"></div>
            <!-- WhatsApp Handset SVG -->
            <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24">
                <path d="M12.031 2C6.496 2 2 6.495 2 12.031c0 1.97.57 3.81 1.56 5.37L2 22l4.78-1.51c1.51.91 3.28 1.44 5.251 1.44 5.536 0 10.031-4.496 10.031-10.031C22.062 6.495 17.567 2 12.031 2zm5.82 14.28c-.24.68-1.39 1.3-1.92 1.38-.49.07-1.12.1-3.23-.77-2.69-1.11-4.41-3.85-4.54-4.03-.13-.18-1.09-1.45-1.09-2.76 0-1.31.69-1.96.93-2.22.25-.26.54-.33.72-.33.18 0 .36 0 .52.01.17.01.39-.06.61.47.23.54.77 1.88.84 2.02.07.14.12.3.02.48-.09.19-.14.3-.28.46-.14.17-.3.37-.43.5-.14.14-.29.29-.12.58.17.28.75 1.24 1.61 2.01 1.11.99 2.05 1.3 2.34 1.44.29.14.46.12.63-.07.17-.2.74-.86.94-1.15.2-.3.4-.25.68-.15.28.1 1.77.83 2.07.98.3.15.5.23.57.36.08.13.08.76-.16 1.44z"/>
            </svg>
        </a>
    </div>
    <?php endif; ?>

    <!-- ================================================================= -->
    <!-- GLOBAL FOOTER -->
    <!-- ================================================================= -->
    <footer class="bg-gray-950 text-white mt-16 pt-12 pb-8 border-t border-gray-800">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-gray-800 text-sm">
                <!-- Col 1: ChargeHub Brand -->
                <div>
                    <div class="flex items-center space-x-3 mb-3">
                        <img src="images/chargehub_logo.png" alt="ChargeHub.pk" class="h-12 w-auto object-contain drop-shadow-md">
                        <div>
                            <div class="brand-font font-black text-lg tracking-wider uppercase text-white">
                                CHARGE<span class="text-brand-orange">HUB</span><span class="text-brand-cyan">.PK</span>
                            </div>
                            <span class="text-[9.5px] font-bold tracking-widest uppercase text-brand-gold block">
                                GENUINE CHARGERS. GENUINE CABLES.
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed mb-4">
                        Pakistan's official destination for genuine fast chargers, certified PD braided cables, multi-port GaN power adapters, and high-performance tech accessories.
                    </p>
                    <span class="text-xs text-brand-gold font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-cyan animate-pulse"></span>
                        100% Guaranteed Genuine &bull; 1-Year Official Warranty
                    </span>
                </div>

                <!-- Col 2 -->
                <div>
                    <h4 class="font-bold text-white text-xs tracking-wider uppercase mb-3">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs text-gray-400">
                        <li><a href="explore.php" class="hover:text-brand-gold font-semibold transition-colors">Catalog / Left Filters</a></li>
                        <li><a href="product-detail.php" class="hover:text-brand-gold font-semibold transition-colors">Anker 140W Product Detail</a></li>
                        <li><a href="#charging" class="hover:text-white transition-colors">Fast GaN Chargers</a></li>
                        <li><a href="#storage" class="hover:text-white transition-colors">Data Storage & Power Banks</a></li>
                        <li><a href="#sale" class="hover:text-white transition-colors">10.10 Flash Deals</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div>
                    <h4 class="font-bold text-white text-xs tracking-wider uppercase mb-3">Customer Support</h4>
                    <ul class="space-y-2 text-xs text-gray-400">
                        <li>WhatsApp: <a href="https://wa.me/<?= e($waHomeConfig['number_clean']) ?>" target="_blank" class="hover:text-emerald-400 transition-colors"><?= e($waHomeConfig['number_raw']) ?></a></li>
                        <li>Email: support@chargehub.pk</li>
                        <li>7-Day Easy Replacement Policy</li>
                        <li>1-Year Official Warranty</li>
                        <li>Tracking & Order Status</li>
                    </ul>
                </div>

                <!-- Col 4 -->
                <div>
                    <h4 class="font-bold text-white text-xs tracking-wider uppercase mb-3">Quick Navigation</h4>
                    <div class="flex flex-col space-y-1.5 text-xs">
                        <a href="index.php" class="text-brand-orange hover:text-brand-gold font-semibold transition-colors">🏠 Home Storefront</a>
                        <a href="explore.php" class="text-gray-400 hover:text-white transition-colors">📂 Full Catalog &amp; Search</a>
                        <a href="product-detail.php" class="text-gray-400 hover:text-white transition-colors">⚡ Featured Product Detail</a>
                        <a href="admin/login.php" class="text-gray-400 hover:text-white transition-colors">🛡️ Admin Management Portal</a>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500">
                <p>&copy; 2026 ChargeHub.pk. All rights reserved. Genuine Chargers. Genuine Cables. All rights reserved.</p>
                <div class="mt-2 sm:mt-0 flex items-center space-x-4">
                    <p>Engineered with pixel-precise S-curve geometry & ultra HD audio carousel.</p>
                    <a href="admin/login.php" class="text-brand-orange hover:text-brand-gold font-bold flex items-center space-x-1"><span>🛡️ Admin Portal</span></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ================================================================= -->
    <!-- INTERACTIVE JAVASCRIPT: CAROUSEL ENGINE, SEARCH & CART -->
    <!-- ================================================================= -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- CAROUSEL ENGINE ---
            const slides = document.querySelectorAll('.carousel-slide');
            const dots = document.querySelectorAll('.carousel-dot');
            const prevBtn = document.getElementById('carousel-prev');
            const nextBtn = document.getElementById('carousel-next');
            const carouselContainer = document.getElementById('hero-carousel');
            
            let currentIndex = 0;
            let autoPlayTimer = null;
            const slideInterval = 4500; // 4.5 seconds

            function goToSlide(index) {
                // Bounds check
                if (index < 0) index = slides.length - 1;
                if (index >= slides.length) index = 0;

                currentIndex = index;

                slides.forEach((slide, i) => {
                    if (i === currentIndex) {
                        slide.classList.remove('opacity-0', 'pointer-events-none');
                        slide.classList.add('opacity-100', 'pointer-events-auto');
                    } else {
                        slide.classList.remove('opacity-100', 'pointer-events-auto');
                        slide.classList.add('opacity-0', 'pointer-events-none');
                    }
                });

                // Dynamically harmonize nav curve scoop color with current slide
                const activeSlide = slides[currentIndex];
                if (activeSlide) {
                    const navColor = activeSlide.getAttribute('data-nav-color') || '#0052cc';
                    const scoopLeft = document.getElementById('scoop-left-path');
                    const scoopRight = document.getElementById('scoop-right-path');
                    const scoopCenter = document.getElementById('scoop-nav-center');
                    if (scoopLeft) scoopLeft.setAttribute('fill', navColor);
                    if (scoopRight) scoopRight.setAttribute('fill', navColor);
                    if (scoopCenter) scoopCenter.style.backgroundColor = navColor;
                }

                // Update dots (elongate active pill, shrink inactive)
                dots.forEach((dot, i) => {
                    if (i === currentIndex) {
                        dot.classList.remove('w-2', 'bg-white/40');
                        dot.classList.add('w-7', 'bg-white', 'shadow-[0_0_8px_rgba(255,255,255,0.8)]');
                    } else {
                        dot.classList.remove('w-7', 'bg-white', 'shadow-[0_0_8px_rgba(255,255,255,0.8)]');
                        dot.classList.add('w-2', 'bg-white/40');
                    }
                });
            }

            function nextSlide() {
                goToSlide(currentIndex + 1);
            }

            function prevSlide() {
                goToSlide(currentIndex - 1);
            }

            function startAutoPlay() {
                if (autoPlayTimer) clearInterval(autoPlayTimer);
                autoPlayTimer = setInterval(nextSlide, slideInterval);
            }

            function stopAutoPlay() {
                if (autoPlayTimer) clearInterval(autoPlayTimer);
            }

            // Arrow button events
            if (nextBtn) nextBtn.addEventListener('click', () => {
                nextSlide();
                startAutoPlay();
            });

            if (prevBtn) prevBtn.addEventListener('click', () => {
                prevSlide();
                startAutoPlay();
            });

            // Dot navigation
            dots.forEach((dot) => {
                dot.addEventListener('click', (e) => {
                    const idx = parseInt(e.target.dataset.index, 10);
                    goToSlide(idx);
                    startAutoPlay();
                });
            });

            // Pause on hover
            if (carouselContainer) {
                carouselContainer.addEventListener('mouseenter', stopAutoPlay);
                carouselContainer.addEventListener('mouseleave', startAutoPlay);

                // Touch swipe support for mobile
                let touchStartX = 0;
                let touchEndX = 0;

                carouselContainer.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    stopAutoPlay();
                }, { passive: true });

                carouselContainer.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchStartX - touchEndX > 45) {
                        nextSlide(); // swipe left -> next
                    } else if (touchEndX - touchStartX > 45) {
                        prevSlide(); // swipe right -> prev
                    }
                    startAutoPlay();
                }, { passive: true });
            }

            // Initialize first slide and start auto-play
            goToSlide(0);
            startAutoPlay();

            // --- MOBILE MENU TOGGLE ---
            const mobileMenuTrigger = document.getElementById('mobile-menu-trigger');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuTrigger && mobileMenu) {
                mobileMenuTrigger.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // --- 10.10 LIVE COUNTDOWN TIMER ENGINE ---
            let days = 1;
            let hours = 21;
            let minutes = 37;
            let seconds = 18;

            const daysEl = document.getElementById('countdown-days');
            const hoursEl = document.getElementById('countdown-hours');
            const minsEl = document.getElementById('countdown-mins');
            const secsEl = document.getElementById('countdown-secs');

            function updateCountdown() {
                if (seconds > 0) {
                    seconds--;
                } else {
                    seconds = 59;
                    if (minutes > 0) {
                        minutes--;
                    } else {
                        minutes = 59;
                        if (hours > 0) {
                            hours--;
                        } else {
                            hours = 23;
                            if (days > 0) days--;
                        }
                    }
                }

                if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minsEl) minsEl.textContent = String(minutes).padStart(2, '0');
                if (secsEl) secsEl.textContent = String(seconds).padStart(2, '0');
            }

            setInterval(updateCountdown, 1000);

            // --- STICKY CAPSULE NAVBAR SCROLL LISTENER ---
            const stickyNav = document.getElementById('sticky-navbar');
            
            function handleScroll() {
                if (window.scrollY > 80) {
                    stickyNav.classList.remove('-translate-y-24', 'opacity-0', 'pointer-events-none');
                    stickyNav.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
                } else {
                    stickyNav.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
                    stickyNav.classList.add('-translate-y-24', 'opacity-0', 'pointer-events-none');
                }
            }

            window.addEventListener('scroll', handleScroll, { passive: true });
            // Initial check
            handleScroll();


            // --- PRODUCT SPOTLIGHT WORKING 3D CAROUSEL ENGINE ---
            const spotlightCards = document.querySelectorAll('.spotlight-card');
            const spotlightDots = document.querySelectorAll('.spotlight-dot');
            const spotlightPrev = document.getElementById('spotlight-prev');
            const spotlightNext = document.getElementById('spotlight-next');
            const spotlightContainer = document.getElementById('spotlight-carousel-container');

            let spotlightActiveIndex = 1; // Default to 1 (Jamster Series in center, matching screenshot)
            let spotlightTimer = null;
            const spotlightInterval = 3800; // 3.8s per slide

            function updateSpotlightCarousel(index) {
                const total = spotlightCards.length;
                spotlightActiveIndex = (index + total) % total;

                spotlightCards.forEach((card, j) => {
                    let d = (j - spotlightActiveIndex) % total;
                    if (d > Math.floor(total / 2)) d -= total;
                    if (d < -Math.floor(total / 2)) d += total;

                    let scale = 0.92;
                    let opacity = 0.72;
                    let zIndex = 10;
                    let xOffsetPercent = d * 94; // 94% card width spacing

                    if (d === 0) {
                        scale = 1.05;
                        opacity = 1;
                        zIndex = 20;
                        card.classList.remove('pointer-events-none');
                        card.classList.add('pointer-events-auto');
                    } else if (Math.abs(d) === 1) {
                        scale = 0.92;
                        opacity = 0.75;
                        zIndex = 10;
                        card.classList.remove('pointer-events-none');
                        card.classList.add('pointer-events-auto');
                    } else {
                        scale = 0.75;
                        opacity = 0;
                        zIndex = 0;
                        xOffsetPercent = d > 0 ? 190 : -190;
                        card.classList.remove('pointer-events-auto');
                        card.classList.add('pointer-events-none');
                    }

                    card.style.transform = `translate(calc(-50% + ${xOffsetPercent}%), -50%) scale(${scale})`;
                    card.style.opacity = opacity;
                    card.style.zIndex = zIndex;
                });

                // Update dots
                spotlightDots.forEach((dot, i) => {
                    if (i === spotlightActiveIndex) {
                        dot.classList.remove('w-1.5', 'bg-gray-300');
                        dot.classList.add('w-6', 'bg-[#e6222b]', 'shadow-sm');
                    } else {
                        dot.classList.remove('w-6', 'bg-[#e6222b]', 'shadow-sm');
                        dot.classList.add('w-1.5', 'bg-gray-300');
                    }
                });
            }

            function nextSpotlight() {
                updateSpotlightCarousel(spotlightActiveIndex + 1);
            }

            function prevSpotlight() {
                updateSpotlightCarousel(spotlightActiveIndex - 1);
            }

            function startSpotlightTimer() {
                stopSpotlightTimer();
                spotlightTimer = setInterval(nextSpotlight, spotlightInterval);
            }

            function stopSpotlightTimer() {
                if (spotlightTimer) clearInterval(spotlightTimer);
            }

            // Arrow triggers
            if (spotlightNext) spotlightNext.addEventListener('click', () => {
                nextSpotlight();
                startSpotlightTimer();
            });

            if (spotlightPrev) spotlightPrev.addEventListener('click', () => {
                prevSpotlight();
                startSpotlightTimer();
            });

            // Dot triggers
            spotlightDots.forEach(dot => {
                dot.addEventListener('click', (e) => {
                    const idx = parseInt(e.currentTarget.getAttribute('data-index'), 10);
                    updateSpotlightCarousel(idx);
                    startSpotlightTimer();
                });
            });

            // Card click to center
            spotlightCards.forEach((card, idx) => {
                card.addEventListener('click', () => {
                    updateSpotlightCarousel(idx);
                    startSpotlightTimer();
                });
            });

            // Pause on hover
            if (spotlightContainer) {
                spotlightContainer.addEventListener('mouseenter', stopSpotlightTimer);
                spotlightContainer.addEventListener('mouseleave', startSpotlightTimer);

                // Touch support
                let touchStartX = 0;
                spotlightContainer.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    stopSpotlightTimer();
                }, { passive: true });

                spotlightContainer.addEventListener('touchend', (e) => {
                    const touchEndX = e.changedTouches[0].screenX;
                    if (touchStartX - touchEndX > 40) {
                        nextSpotlight();
                    } else if (touchEndX - touchStartX > 40) {
                        prevSpotlight();
                    }
                    startSpotlightTimer();
                }, { passive: true });
            }

            // --- NEW ARRIVALS INTERACTIVE CAROUSEL PAGINATION & SLIDER ---
            (function() {
                const container = document.getElementById('new-arrivals-cards-container');
                const counter = document.getElementById('new-arrivals-counter');
                const progress = document.getElementById('new-arrivals-progress');
                const prevBtn = document.getElementById('new-arrivals-prev');
                const nextBtn = document.getElementById('new-arrivals-next');
                const track = document.getElementById('new-arrivals-track');

                if (!container) return;

                const cards = container.querySelectorAll('.new-arrival-card');
                const totalCards = cards.length;
                if (totalCards === 0) return;

                function getStep() {
                    if (cards.length > 0) {
                        return cards[0].offsetWidth + 24;
                    }
                    return 300;
                }

                function updateFromScroll() {
                    const scrollLeft = container.scrollLeft;
                    const maxScroll = Math.max(1, container.scrollWidth - container.clientWidth);
                    const step = getStep();
                    
                    let currentIndex = Math.min(totalCards, Math.max(1, Math.round(scrollLeft / step) + 1));
                    if (counter) counter.textContent = `${currentIndex} / ${totalCards}`;
                    
                    if (progress) {
                        const ratio = Math.max(0, Math.min(1, scrollLeft / maxScroll));
                        const minW = Math.max(10, (1 / totalCards) * 100);
                        const currentW = minW + ratio * (100 - minW);
                        progress.style.width = `${currentW}%`;
                    }
                }

                container.addEventListener('scroll', updateFromScroll, { passive: true });

                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        container.scrollBy({ left: -getStep(), behavior: 'smooth' });
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        container.scrollBy({ left: getStep(), behavior: 'smooth' });
                    });
                }
                if (track) {
                    track.addEventListener('click', (e) => {
                        const rect = track.getBoundingClientRect();
                        const clickX = e.clientX - rect.left;
                        const ratio = Math.max(0, Math.min(1, clickX / rect.width));
                        const maxScroll = container.scrollWidth - container.clientWidth;
                        container.scrollTo({ left: ratio * maxScroll, behavior: 'smooth' });
                    });
                }

                updateFromScroll();
            })();

            // Initialize on page load (starts with Jamster in center matching screenshot)
            updateSpotlightCarousel(1);
            startSpotlightTimer();
        });
    </script>

    <?php require_once __DIR__ . '/includes/cart_drawer.php'; ?>

</body>
</html>
