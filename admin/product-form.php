<?php
if (!ob_get_level()) {
    ob_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);
$is_edit = ($id > 0);
$page_title = $is_edit ? 'Edit Product' : 'Add New Product';

$error = '';
$success = '';

// Default values for new product (clean, no assumed pre-fills)
$product = [
    'id' => 0,
    'category_id' => '',
    'brand_id' => '',
    'name' => '',
    'slug' => '',
    'sku' => '',
    'badge_text' => '',
    'price' => '',
    'original_price' => '',
    'stock_status' => 'In Stock',
    'stock_quantity' => '',
    'warranty_text' => '',
    'rating' => '',
    'reviews_count' => '',
    'orders_count' => '',
    'main_image' => '',
    'gallery_images' => json_encode([]),
    'short_description' => '',
    'full_description' => '',
    'features' => json_encode([]),
    'specs' => json_encode([]),
    'power_matrix' => json_encode([]),
    'bundle_name' => '',
    'bundle_price' => '',
    'bundle_original_price' => '',
    'is_featured' => 0,
    'is_active' => 1,
];

// If Edit, fetch current product
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if ($existing) {
        $product = $existing;
    } else {
        set_flash_message('error', 'Product not found.');
        if (ob_get_level()) ob_end_clean();
        header('Location: products.php');
        exit;
    }
}

// Handle Form Submission (POST) BEFORE including HTML header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please refresh.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $sku = trim($_POST['sku'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
        $brand_id = (int)($_POST['brand_id'] ?? 0) ?: null;
        $badge_text = trim($_POST['badge_text'] ?? '');
        
        $price = (float)($_POST['price'] ?? 0);
        $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $stock_status = trim($_POST['stock_status'] ?? 'In Stock');
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $warranty_text = trim($_POST['warranty_text'] ?? '');

        $rating = !empty($_POST['rating']) ? (float)$_POST['rating'] : 5.0;
        $reviews_count = (int)($_POST['reviews_count'] ?? 0);
        $orders_count = (int)($_POST['orders_count'] ?? 0);

        $short_description = trim($_POST['short_description'] ?? '');
        $full_description = trim($_POST['full_description'] ?? '');

        // Features (stored as JSON array)
        $raw_features = explode("\n", trim($_POST['features_lines'] ?? ''));
        $features_list = array_values(array_filter(array_map('trim', $raw_features)));
        $features_json = json_encode($features_list);

        // Product Specs / Delivery Info
        $delivery_time = trim($_POST['delivery_time'] ?? '24-72 hrs');
        $specs_data = [
            'Delivery'   => $delivery_time,
            'Model'      => $sku,
        ];
        $specs_json = json_encode($specs_data);

        // Retain any existing power matrix or bundle safely
        $matrix_json = !empty($product['power_matrix']) ? $product['power_matrix'] : json_encode([]);
        $bundle_name = $product['bundle_name'] ?? '';
        $bundle_price = $product['bundle_price'] ?? null;
        $bundle_original_price = $product['bundle_original_price'] ?? null;

        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Main Image Handling
        $main_image = trim($_POST['existing_main_image'] ?? $product['main_image']);
        if (!empty($_POST['custom_main_image'])) {
            $main_image = trim($_POST['custom_main_image']);
        }
        if (!empty($_FILES['main_image_file']['name'])) {
            $upload = handle_image_upload($_FILES['main_image_file'], __DIR__ . '/../uploads/products', 'prod_main_');
            if ($upload['success']) {
                $main_image = 'uploads/products/' . $upload['filename'];
            } else {
                $error = 'Main Image Error: ' . $upload['error'];
            }
        }

        // Gallery Images Handling
        $gallery_arr = [];
        // Keep existing if requested
        if (!empty($_POST['existing_gallery'])) {
            $gallery_arr = json_decode($_POST['existing_gallery'], true) ?: [];
        }
        // Additional URL gallery paths
        if (!empty($_POST['gallery_urls_text'])) {
            $url_lines = explode("\n", trim($_POST['gallery_urls_text']));
            foreach ($url_lines as $ul) {
                $ul = trim($ul);
                if (!empty($ul) && !in_array($ul, $gallery_arr)) {
                    $gallery_arr[] = $ul;
                }
            }
        }
        // Upload additional gallery file if provided
        if (!empty($_FILES['gallery_file']['name'])) {
            $uploadG = handle_image_upload($_FILES['gallery_file'], __DIR__ . '/../uploads/products', 'prod_gal_');
            if ($uploadG['success']) {
                $gallery_arr[] = 'uploads/products/' . $uploadG['filename'];
            }
        }
        $gallery_json = json_encode(array_values($gallery_arr));

        if (empty($name)) {
            $error = 'Product name is required.';
        }

        if (empty($error)) {
            // Slug collision check
            $chkSql = "SELECT COUNT(*) FROM products WHERE slug = ?" . ($is_edit ? " AND id != ?" : "");
            $chkParams = $is_edit ? [$slug, $id] : [$slug];
            $stmtChk = $pdo->prepare($chkSql);
            $stmtChk->execute($chkParams);
            if ($stmtChk->fetchColumn() > 0) {
                $slug .= '-' . time();
            }

            if ($is_edit) {
                // Update statement
                $sql = "UPDATE products SET 
                    category_id = ?, brand_id = ?, name = ?, slug = ?, sku = ?, badge_text = ?,
                    price = ?, original_price = ?, stock_status = ?, stock_quantity = ?, warranty_text = ?,
                    rating = ?, reviews_count = ?, orders_count = ?,
                    main_image = ?, gallery_images = ?, short_description = ?, full_description = ?,
                    features = ?, specs = ?, power_matrix = ?, bundle_name = ?, bundle_price = ?, bundle_original_price = ?,
                    is_featured = ?, is_active = ?
                    WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $category_id, $brand_id, $name, $slug, $sku, $badge_text,
                    $price, $original_price, $stock_status, $stock_quantity, $warranty_text,
                    $rating, $reviews_count, $orders_count,
                    $main_image, $gallery_json, $short_description, $full_description,
                    $features_json, $specs_json, $matrix_json, $bundle_name, $bundle_price, $bundle_original_price,
                    $is_featured, $is_active, $id
                ]);
                set_flash_message('success', 'Product "' . $name . '" updated successfully!');
            } else {
                // Insert statement
                $sql = "INSERT INTO products (
                    category_id, brand_id, name, slug, sku, badge_text,
                    price, original_price, stock_status, stock_quantity, warranty_text,
                    rating, reviews_count, orders_count,
                    main_image, gallery_images, short_description, full_description,
                    features, specs, power_matrix, bundle_name, bundle_price, bundle_original_price,
                    is_featured, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $category_id, $brand_id, $name, $slug, $sku, $badge_text,
                    $price, $original_price, $stock_status, $stock_quantity, $warranty_text,
                    $rating, $reviews_count, $orders_count,
                    $main_image, $gallery_json, $short_description, $full_description,
                    $features_json, $specs_json, $matrix_json, $bundle_name, $bundle_price, $bundle_original_price,
                    $is_featured, $is_active
                ]);
                $newId = $pdo->lastInsertId();
                set_flash_message('success', 'Product "' . $name . '" created successfully!');
            }

            if (ob_get_level()) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                header('Location: products.php');
                exit;
            } else {
                echo '<script>window.location.href="products.php";</script>';
                echo '<noscript><meta http-equiv="refresh" content="0;url=products.php"></noscript>';
                exit;
            }
        }

        // If error occurred, preserve input in form
        $product['name'] = $name;
        $product['slug'] = $slug;
        $product['sku'] = $sku;
        $product['category_id'] = $category_id;
        $product['brand_id'] = $brand_id;
        $product['badge_text'] = $badge_text;
        $product['price'] = $price;
        $product['original_price'] = $original_price;
        $product['stock_status'] = $stock_status;
        $product['stock_quantity'] = $stock_quantity;
        $product['warranty_text'] = $warranty_text;
        $product['rating'] = $rating;
        $product['reviews_count'] = $reviews_count;
        $product['orders_count'] = $orders_count;
        $product['short_description'] = $short_description;
        $product['full_description'] = $full_description;
        $product['main_image'] = $main_image;
        $product['is_featured'] = $is_featured;
        $product['is_active'] = $is_active;
    }
}

// Include HTML layout header only now for rendering page
require_once __DIR__ . '/includes/header.php';

// Fetch categories and brands with icons/images for searchable selectors
$categories = $pdo->query("SELECT id, name, slug, image_url FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();
$brands = $pdo->query("SELECT id, name, slug, logo_url FROM brands ORDER BY sort_order ASC, name ASC")->fetchAll();

$categories_json = json_encode(array_map(function($c) {
    return [
        'id' => (int)$c['id'],
        'name' => (string)$c['name'],
        'slug' => (string)($c['slug'] ?? ''),
        'image' => !empty($c['image_url']) ? (string)$c['image_url'] : ''
    ];
}, $categories));

$brands_json = json_encode(array_map(function($b) {
    return [
        'id' => (int)$b['id'],
        'name' => (string)$b['name'],
        'slug' => (string)($b['slug'] ?? ''),
        'image' => !empty($b['logo_url']) ? (string)$b['logo_url'] : ''
    ];
}, $brands));

// Parse JSON fields for form display
$specs_arr = json_decode($product['specs'] ?? '', true) ?: [];
$features_arr = json_decode($product['features'] ?? '', true) ?: [];
$gallery_arr = json_decode($product['gallery_images'] ?? '', true) ?: [];
$pm_arr = json_decode($product['power_matrix'] ?? '', true) ?: [];

// Normalize power matrix values (supports both new multi-port format and legacy arrays)
$pm_single = '';
if (!empty($pm_arr['single_port'])) {
    if (is_string($pm_arr['single_port'])) {
        $pm_single = $pm_arr['single_port'];
    } elseif (is_array($pm_arr['single_port'])) {
        $parts = [];
        foreach ($pm_arr['single_port'] as $k => $v) { $parts[] = strtoupper(str_replace('_', '-', $k)) . ': ' . $v; }
        $pm_single = implode(' | ', $parts);
    }
}

$pm_dual = '';
if (!empty($pm_arr['dual_port'])) {
    if (is_string($pm_arr['dual_port'])) {
        $pm_dual = $pm_arr['dual_port'];
    } elseif (is_array($pm_arr['dual_port'])) {
        $parts = [];
        foreach ($pm_arr['dual_port'] as $k => $v) { $parts[] = $v; }
        $pm_dual = implode(' | ', $parts);
    }
}

$pm_multi = '';
if (!empty($pm_arr['multi_port'])) {
    $pm_multi = $pm_arr['multi_port'];
} elseif (!empty($pm_arr['triple_port'])) {
    $pm_multi = $pm_arr['triple_port'];
}

$pm_note = $pm_arr['allocation_note'] ?? '';
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">

    <!-- TOPBAR -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- MAIN BODY (Concise Container) -->
    <main class="p-4 sm:p-6 space-y-4 flex-1 max-w-[1140px] mx-auto w-full">

        <!-- HEADER & BREADCRUMB -->
        <div class="flex items-center justify-between pb-2 border-b border-app">
            <div class="flex items-center space-x-2">
                <a href="products.php" class="text-xs font-bold text-blue-500 hover:underline">&larr; Back to Products</a>
                <span class="text-app-muted text-xs">/</span>
                <h2 class="text-base font-black text-app-title brand-font"><?= $is_edit ? 'Edit Product' : 'Add New Product' ?></h2>
            </div>
            <?php if ($is_edit): ?>
            <a href="../product-detail.php?slug=<?= urlencode($product['slug'] ?? '') ?>" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-500/10 hover:bg-slate-500/20 text-app-title text-xs font-semibold flex items-center space-x-1">
                <span>View on Store ↗</span>
            </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-500/15 border border-red-500/30 text-red-500 text-xs font-semibold">
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <!-- PRODUCT FORM (Comprehensive, Organized into Compact Cards) -->
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="existing_main_image" value="<?= e($product['main_image']) ?>">
            <input type="hidden" name="existing_gallery" value="<?= e(json_encode($gallery_arr)) ?>">

            <!-- CARD 1: BASIC INFORMATION -->
            <div class="p-4 sm:p-5 rounded-xl bg-app-card border border-app shadow-sm space-y-3">
                <h3 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5 pb-2 border-b border-app">
                    <span>⚡</span>
                    <span>1. Basic Product Identity</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Full Product Name (Required) *</label>
                        <input type="text" name="name" value="<?= e($product['name']) ?>" required placeholder="Product name" class="w-full px-3 py-2 rounded-xl input-app border text-xs font-semibold" oninput="if (!document.getElementById('product-slug').getAttribute('data-edited')) { document.getElementById('product-slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">URL Slug</label>
                        <input type="text" id="product-slug" name="slug" value="<?= e($product['slug']) ?>" oninput="this.setAttribute('data-edited', 'true')" placeholder="product-slug" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">SKU / Model Code</label>
                        <input type="text" name="sku" value="<?= e($product['sku']) ?>" placeholder="SKU code" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs font-mono">
                    </div>

                    <!-- SEARCHABLE CATEGORY SELECTOR -->
                    <div class="relative" id="combobox-cat-container">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-app-muted">Category *</label>
                            <a href="categories.php" target="_blank" class="text-[10px] text-blue-500 hover:underline font-semibold">+ Manage Categories</a>
                        </div>
                        <input type="hidden" name="category_id" id="field_category_id" value="<?= e($product['category_id'] ?? '') ?>">
                        
                        <button type="button" id="cat_combobox_btn" class="w-full px-3 py-2 rounded-xl input-app border text-xs text-left flex items-center justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all select-none">
                            <div id="cat_combobox_display" class="flex items-center space-x-2 truncate min-w-0">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div class="flex items-center space-x-1.5 shrink-0 ml-2">
                                <span id="cat_combobox_clear" class="p-0.5 text-app-muted hover:text-red-500 rounded text-xs hidden leading-none" title="Clear category selection">&times;</span>
                                <svg id="cat_combobox_chevron" class="w-3.5 h-3.5 text-app-muted transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="cat_combobox_menu" class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 rounded-xl bg-app-card border border-app shadow-2xl p-2 space-y-2">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-app-muted text-xs">🔍</span>
                                <input type="text" id="cat_combobox_search" placeholder="Search category..." autocomplete="off" class="w-full pl-8 pr-3 py-1.5 rounded-lg input-app border text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div id="cat_combobox_list" class="max-h-52 overflow-y-auto space-y-0.5 custom-scrollbar text-xs">
                                <!-- Rendered dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- SEARCHABLE BRAND SELECTOR -->
                    <div class="relative" id="combobox-brand-container">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-app-muted">Brand / Manufacturer *</label>
                            <a href="brands.php" target="_blank" class="text-[10px] text-blue-500 hover:underline font-semibold">+ Manage Brands</a>
                        </div>
                        <input type="hidden" name="brand_id" id="field_brand_id" value="<?= e($product['brand_id'] ?? '') ?>">
                        
                        <button type="button" id="brand_combobox_btn" class="w-full px-3 py-2 rounded-xl input-app border text-xs text-left flex items-center justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all select-none">
                            <div id="brand_combobox_display" class="flex items-center space-x-2 truncate min-w-0">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div class="flex items-center space-x-1.5 shrink-0 ml-2">
                                <span id="brand_combobox_clear" class="p-0.5 text-app-muted hover:text-red-500 rounded text-xs hidden leading-none" title="Clear brand selection">&times;</span>
                                <svg id="brand_combobox_chevron" class="w-3.5 h-3.5 text-app-muted transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="brand_combobox_menu" class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 rounded-xl bg-app-card border border-app shadow-2xl p-2 space-y-2">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-app-muted text-xs">🔍</span>
                                <input type="text" id="brand_combobox_search" placeholder="Search brand..." autocomplete="off" class="w-full pl-8 pr-3 py-1.5 rounded-lg input-app border text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div id="brand_combobox_list" class="max-h-52 overflow-y-auto space-y-0.5 custom-scrollbar text-xs">
                                <!-- Rendered dynamically -->
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Badge Text (Appears over product)</label>
                        <input type="text" name="badge_text" value="<?= e($product['badge_text']) ?>" placeholder="e.g. Best Seller" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div class="flex items-center space-x-5 pt-4">
                        <label class="flex items-center space-x-2 text-xs font-bold text-app-title cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?> class="w-4 h-4 rounded text-blue-600 border-gray-300">
                            <span>Active in Store</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-bold text-app-title cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?> class="w-4 h-4 rounded text-blue-600 border-gray-300">
                            <span>Featured on Homepage</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- CARD 2: PRICING & INVENTORY -->
            <div class="p-4 sm:p-5 rounded-xl bg-app-card border border-app shadow-sm space-y-3">
                <h3 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5 pb-2 border-b border-app">
                    <span>💰</span>
                    <span>2. Pricing, Inventory &amp; Warranty</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Sale Price (PKR) *</label>
                        <input type="number" step="0.01" name="price" value="<?= e($product['price']) ?>" placeholder="0.00" required class="w-full px-3 py-1.5 rounded-xl input-app border text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Original / Strikethrough Price</label>
                        <input type="number" step="0.01" name="original_price" value="<?= e($product['original_price'] ?? '') ?>" placeholder="Optional original price" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Stock Status Text</label>
                        <input type="text" name="stock_status" value="<?= e($product['stock_status']) ?>" placeholder="In Stock" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Available Quantity</label>
                        <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] !== '' ? (int)$product['stock_quantity'] : '' ?>" placeholder="0" min="0" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Official Warranty Guarantee</label>
                        <input type="text" name="warranty_text" value="<?= e($product['warranty_text']) ?>" placeholder="Warranty details" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Rating</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" value="<?= e($product['rating']) ?>" placeholder="5.0" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Verified Reviews Count</label>
                        <input type="number" name="reviews_count" value="<?= $product['reviews_count'] !== '' ? (int)$product['reviews_count'] : '' ?>" placeholder="0" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>
                </div>
            </div>

            <!-- CARD 3: MEDIA (MAIN IMAGE & GALLERY) -->
            <div class="p-4 sm:p-5 rounded-xl bg-app-card border border-app shadow-sm space-y-3">
                <h3 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5 pb-2 border-b border-app">
                    <span>🖼️</span>
                    <span>3. Product Images &amp; Interactive Gallery</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Main Hero Image -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold text-app-muted">Main Hero Product Image *</label>
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 rounded-xl bg-slate-500/10 border border-app p-1.5 flex items-center justify-center shrink-0 overflow-hidden text-center">
                                <?php if (!empty($product['main_image'])): ?>
                                    <img src="../<?= e($product['main_image']) ?>" alt="Main Preview" class="w-full h-full object-contain">
                                <?php else: ?>
                                    <span class="text-app-muted text-[10px] font-semibold leading-tight">No image</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 space-y-1">
                                <input type="file" name="main_image_file" accept="image/*" class="w-full text-xs text-app-muted file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                                <input type="text" name="custom_main_image" placeholder="Image path or upload" value="<?= e($product['main_image']) ?>" class="w-full px-3 py-1 rounded-lg input-app border text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Gallery Images -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold text-app-muted">Gallery Angle Thumbnails (Shown on Detail Page)</label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <?php foreach ($gallery_arr as $gImg): ?>
                            <div class="w-12 h-12 rounded-lg bg-slate-500/10 border border-app p-1 flex items-center justify-center relative group overflow-hidden">
                                <img src="../<?= e($gImg) ?>" alt="Gallery" class="w-full h-full object-contain">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="file" name="gallery_file" accept="image/*" class="w-full text-xs text-app-muted file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-700 file:text-white cursor-pointer">
                        <span class="text-[10px] text-app-muted block">Or enter image URLs, one per line:</span>
                        <textarea name="gallery_urls_text" rows="2" placeholder="images/angle1.jpg&#10;images/angle2.jpg" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs font-mono"><?= e(implode("\n", $gallery_arr)) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- CARD 4: KEY BULLET POINTS (SHOWN ON PRODUCT PAGE RIGHT SIDE) -->
            <div class="p-4 sm:p-5 rounded-xl bg-app-card border border-app shadow-sm space-y-3">
                <div class="pb-2 border-b border-app">
                    <h3 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                        <span>•</span>
                        <span>4. Key Feature Bullets (Shown directly under price on Product Page)</span>
                    </h3>
                    <p class="text-[11px] text-app-muted mt-0.5">Enter each highlight bullet on a new line (e.g. Input, Output, Cable type, Model code).</p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Key Specifications / Bullets (One per line)</label>
                    <textarea name="features_lines" rows="4" placeholder="Input: 100-240V 50-60Hz&#10;Output: 5V=2A*&#10;Cable: Micro-USB&#10;Model: OCW-1111U" class="w-full px-3 py-2 rounded-xl input-app border text-xs font-mono"><?= e(implode("\n", $features_arr)) ?></textarea>
                </div>
            </div>

            <!-- CARD 5: TRUST, WARRANTY & DELIVERY SETTINGS -->
            <div class="p-4 sm:p-5 rounded-xl bg-app-card border border-app shadow-sm space-y-3">
                <div class="pb-2 border-b border-app">
                    <h3 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                        <span>🛡️</span>
                        <span>5. Trust, Warranty &amp; Delivery Settings</span>
                    </h3>
                    <p class="text-[11px] text-app-muted mt-0.5">Controls the stacked service cards on the product detail page.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Official Brand Warranty Guarantee</label>
                        <input type="text" name="warranty_text" value="<?= e($product['warranty_text']) ?>" placeholder="e.g. 1-Year Official Replacement Warranty" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Expected Delivery Timeframe</label>
                        <input type="text" name="delivery_time" value="<?= e($specs_arr['Delivery'] ?? '24-72 hrs') ?>" placeholder="e.g. 24-72 hrs" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                    </div>
                </div>
            </div>

            <!-- CARD 6: PRODUCT DETAIL "DESCRIPTION" TAB CONTENT -->
            <div class="p-4 sm:p-5 rounded-xl bg-app-card border border-app shadow-sm space-y-3">
                <div class="pb-2 border-b border-app">
                    <h3 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                        <span>📄</span>
                        <span>6. Product Description (Product Page "Description" Tab)</span>
                    </h3>
                    <p class="text-[11px] text-app-muted mt-0.5">This content is rendered directly inside the Description tab at the bottom of the product page.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Full Long Description (HTML supported)</label>
                        <textarea name="full_description" rows="5" placeholder="Enter full rich product details, description paragraphs, and specs..." class="w-full px-3 py-2 rounded-xl input-app border text-xs font-mono"><?= e($product['full_description']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-app-muted mb-1">Short Description (Fallback Summary)</label>
                        <textarea name="short_description" rows="2" placeholder="Brief summary if full description is empty" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs"><?= e($product['short_description']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR (STICKY BOTTOM) -->
            <div class="p-3.5 rounded-xl bg-app-card border border-app shadow-lg flex items-center justify-between sticky bottom-4 z-20">
                <a href="products.php" class="px-4 py-2 rounded-xl bg-slate-500/10 hover:bg-slate-500/20 text-app-muted text-xs font-bold transition-colors">
                    &larr; Cancel &amp; Back
                </a>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all">
                        <?= $is_edit ? '💾 Save All Product Changes' : '🚀 Publish Product to Catalog' ?>
                    </button>
                </div>
            </div>

        </form>

    </main>

</div>

<script>
(function() {
    const categoriesData = <?= $categories_json ?>;
    const brandsData = <?= $brands_json ?>;

    function setupSearchableCombobox(config) {
        const {
            containerId,
            hiddenInputId,
            triggerBtnId,
            displayId,
            chevronId,
            clearBtnId,
            menuId,
            searchId,
            listId,
            items,
            placeholder,
            emptyManageUrl,
            emptyManageText,
            fallbackIcon
        } = config;

        const hiddenInput = document.getElementById(hiddenInputId);
        const triggerBtn = document.getElementById(triggerBtnId);
        const displayEl = document.getElementById(displayId);
        const chevronEl = document.getElementById(chevronId);
        const clearBtn = document.getElementById(clearBtnId);
        const menuEl = document.getElementById(menuId);
        const searchInput = document.getElementById(searchId);
        const listEl = document.getElementById(listId);

        if (!hiddenInput || !triggerBtn || !displayEl || !menuEl || !searchInput || !listEl) return;

        let selectedId = hiddenInput.value ? parseInt(hiddenInput.value, 10) : null;
        let isOpen = false;
        let highlightedIndex = -1;
        let currentFilter = '';

        function renderDisplay() {
            const selectedItem = items.find(it => it.id === selectedId);
            if (selectedItem) {
                let imgHtml = '';
                if (selectedItem.image) {
                    imgHtml = `<img src="../${escapeHtml(selectedItem.image)}" alt="" class="w-5 h-5 rounded object-contain bg-slate-500/10 shrink-0 p-0.5">`;
                } else {
                    imgHtml = `<span class="w-5 h-5 rounded bg-blue-500/15 text-blue-500 flex items-center justify-center text-[11px] shrink-0 font-bold">${fallbackIcon}</span>`;
                }
                displayEl.innerHTML = `
                    ${imgHtml}
                    <span class="font-bold text-app-title text-xs truncate">${escapeHtml(selectedItem.name)}</span>
                    ${selectedItem.slug ? `<span class="text-[10px] text-app-muted font-mono hidden sm:inline">(${escapeHtml(selectedItem.slug)})</span>` : ''}
                `;
                clearBtn.classList.remove('hidden');
            } else {
                displayEl.innerHTML = `
                    <span class="text-app-muted text-xs">${escapeHtml(placeholder)}</span>
                `;
                clearBtn.classList.add('hidden');
            }
        }

        function getFilteredItems() {
            const q = currentFilter.trim().toLowerCase();
            if (!q) return items;
            return items.filter(it => 
                it.name.toLowerCase().includes(q) || 
                (it.slug && it.slug.toLowerCase().includes(q))
            );
        }

        function renderList() {
            const filtered = getFilteredItems();
            listEl.innerHTML = '';

            if (filtered.length === 0) {
                listEl.innerHTML = `
                    <div class="p-3 text-center text-app-muted text-xs">
                        <div class="mb-1.5 font-medium">No matches found for "${escapeHtml(currentFilter)}"</div>
                        <a href="${emptyManageUrl}" target="_blank" class="text-blue-500 hover:underline font-bold text-[11px] inline-flex items-center space-x-1">
                            <span>+</span>
                            <span>${escapeHtml(emptyManageText)}</span>
                        </a>
                    </div>
                `;
                return;
            }

            filtered.forEach((it, idx) => {
                const isSelected = (it.id === selectedId);
                const isHighlighted = (idx === highlightedIndex);

                const itemBtn = document.createElement('button');
                itemBtn.type = 'button';
                itemBtn.className = `w-full text-left px-2.5 py-1.5 rounded-lg flex items-center justify-between text-xs transition-colors cursor-pointer ${
                    isSelected ? 'bg-blue-600 text-white font-bold' : 
                    isHighlighted ? 'bg-blue-500/15 text-app-title' : 'text-app-title hover:bg-slate-500/10'
                }`;

                let imgHtml = '';
                if (it.image) {
                    imgHtml = `<img src="../${escapeHtml(it.image)}" alt="" class="w-5 h-5 rounded object-contain ${isSelected ? 'bg-white/20' : 'bg-slate-500/10'} shrink-0 p-0.5">`;
                } else {
                    imgHtml = `<span class="w-5 h-5 rounded ${isSelected ? 'bg-white/20 text-white' : 'bg-slate-500/15 text-app-muted'} flex items-center justify-center text-[10px] shrink-0 font-bold">${fallbackIcon}</span>`;
                }

                itemBtn.innerHTML = `
                    <div class="flex items-center space-x-2 truncate min-w-0">
                        ${imgHtml}
                        <span class="truncate">${escapeHtml(it.name)}</span>
                    </div>
                    <div class="flex items-center space-x-1.5 shrink-0 ml-2">
                        ${it.slug ? `<span class="text-[9.5px] font-mono ${isSelected ? 'text-white/80' : 'text-app-muted'}">${escapeHtml(it.slug)}</span>` : ''}
                        ${isSelected ? '<span class="text-xs font-black">✓</span>' : ''}
                    </div>
                `;

                itemBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectItem(it.id);
                });

                itemBtn.addEventListener('mouseenter', () => {
                    highlightedIndex = idx;
                });

                listEl.appendChild(itemBtn);
            });
        }

        function openMenu() {
            // Close any other open comboboxes
            document.querySelectorAll('[id$="_combobox_menu"]').forEach(el => {
                if (el !== menuEl) el.classList.add('hidden');
            });
            document.querySelectorAll('[id$="_combobox_chevron"]').forEach(el => {
                if (el !== chevronEl) el.classList.remove('rotate-180');
            });

            isOpen = true;
            menuEl.classList.remove('hidden');
            chevronEl.classList.add('rotate-180');
            currentFilter = '';
            searchInput.value = '';
            highlightedIndex = -1;
            renderList();
            setTimeout(() => searchInput.focus(), 50);
        }

        function closeMenu() {
            if (!isOpen) return;
            isOpen = false;
            menuEl.classList.add('hidden');
            chevronEl.classList.remove('rotate-180');
            highlightedIndex = -1;
        }

        function selectItem(id) {
            selectedId = id;
            hiddenInput.value = id;
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            renderDisplay();
            closeMenu();
            triggerBtn.focus();
        }

        function clearSelection() {
            selectedId = null;
            hiddenInput.value = '';
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            renderDisplay();
            closeMenu();
        }

        // Event Listeners
        triggerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        clearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            clearSelection();
        });

        searchInput.addEventListener('input', (e) => {
            currentFilter = e.target.value;
            highlightedIndex = 0;
            renderList();
        });

        searchInput.addEventListener('keydown', (e) => {
            const filtered = getFilteredItems();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (filtered.length > 0) {
                    highlightedIndex = (highlightedIndex + 1) % filtered.length;
                    renderList();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (filtered.length > 0) {
                    highlightedIndex = (highlightedIndex - 1 + filtered.length) % filtered.length;
                    renderList();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (filtered.length > 0 && highlightedIndex >= 0 && highlightedIndex < filtered.length) {
                    selectItem(filtered[highlightedIndex].id);
                } else if (filtered.length === 1) {
                    selectItem(filtered[0].id);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeMenu();
                triggerBtn.focus();
            }
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            const container = document.getElementById(containerId);
            if (container && !container.contains(e.target)) {
                closeMenu();
            }
        });

        // Initial render
        renderDisplay();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Initialize Category Combobox
    setupSearchableCombobox({
        containerId: 'combobox-cat-container',
        hiddenInputId: 'field_category_id',
        triggerBtnId: 'cat_combobox_btn',
        displayId: 'cat_combobox_display',
        chevronId: 'cat_combobox_chevron',
        clearBtnId: 'cat_combobox_clear',
        menuId: 'cat_combobox_menu',
        searchId: 'cat_combobox_search',
        listId: 'cat_combobox_list',
        items: categoriesData,
        placeholder: '-- Select Category (Searchable) --',
        emptyManageUrl: 'categories.php',
        emptyManageText: 'Create New Category',
        fallbackIcon: '📂'
    });

    // Initialize Brand Combobox
    setupSearchableCombobox({
        containerId: 'combobox-brand-container',
        hiddenInputId: 'field_brand_id',
        triggerBtnId: 'brand_combobox_btn',
        displayId: 'brand_combobox_display',
        chevronId: 'brand_combobox_chevron',
        clearBtnId: 'brand_combobox_clear',
        menuId: 'brand_combobox_menu',
        searchId: 'brand_combobox_search',
        listId: 'brand_combobox_list',
        items: brandsData,
        placeholder: '-- Select Brand (Searchable) --',
        emptyManageUrl: 'brands.php',
        emptyManageText: 'Create New Brand',
        fallbackIcon: '🏷️'
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
