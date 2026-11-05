<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$error = '';
$success = '';

// Handle Actions (POST) BEFORE including HTML layout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please refresh.';
        set_flash_message('error', $error);
        admin_redirect('products.php');
    } else {
        $action = $_POST['action'] ?? '';

        // DELETE PRODUCT
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Product deleted successfully.');
                admin_redirect('products.php');
            }
        }

        // TOGGLE ACTIVE STATUS
        elseif ($action === 'toggle_status') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE products SET is_active = (CASE WHEN is_active = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Product status updated.');
                admin_redirect('products.php');
            }
        }

        // TOGGLE FEATURED
        elseif ($action === 'toggle_featured') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE products SET is_featured = (CASE WHEN is_featured = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Featured status updated.');
                admin_redirect('products.php');
            }
        }
    }
}

$page_title = 'Products Catalog';
require_once __DIR__ . '/includes/header.php';

// Filters & Search
$search = trim($_GET['search'] ?? '');
$filter_cat = (int)($_GET['category'] ?? 0);
$filter_brand = (int)($_GET['brand'] ?? 0);

$query = "
    SELECT p.*, c.name AS category_name, b.name AS brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_cat > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $filter_cat;
}

if ($filter_brand > 0) {
    $query .= " AND p.brand_id = ?";
    $params[] = $filter_brand;
}

// Count total products matching filters
$countQuery = "
    SELECT COUNT(*) 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE 1=1
";
if (!empty($search)) {
    $countQuery .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
}
if ($filter_cat > 0) {
    $countQuery .= " AND p.category_id = ?";
}
if ($filter_brand > 0) {
    $countQuery .= " AND p.brand_id = ?";
}
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalProducts = (int)$stmtCount->fetchColumn();

// Pagination params (15 products per page)
$per_page = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$total_pages = max(1, (int)ceil($totalProducts / $per_page));
if ($page > $total_pages && $totalProducts > 0) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$query .= " ORDER BY p.id DESC LIMIT $per_page OFFSET $offset";
$stmtProds = $pdo->prepare($query);
$stmtProds->execute($params);
$products = $stmtProds->fetchAll();

// Fetch categories and brands for filter dropdowns
$allCats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$allBrands = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC")->fetchAll();

$flashMessages = get_flash_messages();
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">

    <!-- TOPBAR -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- MAIN BODY (Concise Container) -->
    <main class="p-4 sm:p-6 space-y-4 flex-1 max-w-[1140px] mx-auto w-full">

        <!-- HEADER & ACTIONS BAR -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-app">
            <div>
                <h2 class="text-lg font-black text-app-title brand-font">Products Catalog</h2>
                <p class="text-xs text-app-muted">All products with full specifications, pricing, gallery images, and bundle offers.</p>
            </div>
            <a href="product-form.php" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all shadow-md flex items-center space-x-1.5 self-start sm:self-auto">
                <span>➕</span>
                <span>Add New Product</span>
            </a>
        </div>

        <!-- FLASH NOTIFICATIONS -->
        <?php foreach ($flashMessages as $msg): ?>
        <div class="flash-alert p-3 rounded-xl <?= $msg['type'] === 'success' ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-500' : 'bg-red-500/15 border-red-500/30 text-red-500' ?> border text-xs font-semibold flex items-center justify-between">
            <span><?= e($msg['text']) ?></span>
            <button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endforeach; ?>

        <!-- FILTER & SEARCH BAR -->
        <div class="p-3.5 rounded-xl bg-app-card border border-app shadow-sm flex flex-wrap items-center justify-between gap-2.5">
            <form method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
                <!-- Search input -->
                <div class="relative flex-1 min-w-[180px]">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search product name, SKU, slug..." class="w-full pl-8 pr-3 py-1.5 rounded-lg input-app border text-xs">
                    <span class="absolute left-2.5 top-2 text-app-muted text-xs">🔍</span>
                </div>

                <!-- Category Filter -->
                <select name="category" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg input-app border text-xs">
                    <option value="0">All Categories</option>
                    <?php foreach ($allCats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filter_cat == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Brand Filter -->
                <select name="brand" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg input-app border text-xs">
                    <option value="0">All Brands</option>
                    <?php foreach ($allBrands as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filter_brand == $b['id']) ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition-colors">
                    Filter
                </button>

                <?php if (!empty($search) || $filter_cat > 0 || $filter_brand > 0): ?>
                <a href="products.php" class="px-2.5 py-1.5 rounded-lg bg-slate-500/10 hover:bg-slate-500/20 text-app-muted text-xs font-semibold">
                    Clear
                </a>
                <?php endif; ?>
            </form>

            <span class="text-xs text-app-muted shrink-0">
                Found: <strong class="text-app-title"><?= number_format($totalProducts) ?></strong> product(s)
            </span>
        </div>

        <!-- PRODUCTS TABLE -->
        <div class="rounded-xl bg-app-card border border-app overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-500/5 text-app-muted font-bold text-[10px] uppercase tracking-wider border-b border-app">
                        <tr>
                            <th class="py-2.5 px-3.5">Product</th>
                            <th class="py-2.5 px-3.5">Category &amp; Brand</th>
                            <th class="py-2.5 px-3.5">Price</th>
                            <th class="py-2.5 px-3.5">Stock</th>
                            <th class="py-2.5 px-3.5">Rating / Orders</th>
                            <th class="py-2.5 px-3.5">Status</th>
                            <th class="py-2.5 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app">
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-app-muted">No products found matching criteria.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <!-- Main Image & Name -->
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-lg bg-slate-500/10 p-1 shrink-0 border border-app flex items-center justify-center overflow-hidden">
                                            <img src="../<?= e($p['main_image']) ?>" alt="<?= e($p['name']) ?>" class="w-full h-full object-contain">
                                        </div>
                                        <div class="min-w-0 max-w-xs">
                                            <?php if (!empty($p['badge_text'])): ?>
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[9.5px] font-black uppercase bg-orange-500/15 text-orange-500 mb-0.5">
                                                <?= e($p['badge_text']) ?>
                                            </span>
                                            <?php endif; ?>
                                            <div class="font-bold text-app-title truncate text-xs"><?= e($p['name']) ?></div>
                                            <div class="text-[10.5px] text-app-muted font-mono"><?= e($p['sku'] ?? 'NO-SKU') ?> &bull; <?= e($p['slug']) ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category & Brand -->
                                <td class="py-2.5 px-3.5">
                                    <div class="font-semibold text-app-title"><?= e($p['brand_name'] ?? 'Generic') ?></div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-500">
                                        <?= e($p['category_name'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="py-2.5 px-3.5">
                                    <div class="font-bold text-app-title">Rs. <?= number_format($p['price']) ?></div>
                                    <?php if (!empty($p['original_price']) && $p['original_price'] > $p['price']): ?>
                                    <div class="text-[10px] text-app-muted line-through">Rs. <?= number_format($p['original_price']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- Stock Status & Qty -->
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full <?= ($p['stock_quantity'] > 0) ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                                        <span class="font-semibold text-app-title"><?= (int)$p['stock_quantity'] ?> units</span>
                                    </div>
                                    <div class="text-[10px] text-app-muted mt-0.5 truncate max-w-[130px]"><?= e($p['stock_status'] ?? 'In Stock') ?></div>
                                </td>

                                <!-- Rating & Orders -->
                                <td class="py-2.5 px-3.5">
                                    <div class="font-bold text-amber-500 flex items-center space-x-1">
                                        <span>⭐ <?= number_format((float)$p['rating'], 1) ?></span>
                                        <span class="text-app-muted text-[10px] font-normal">(<?= (int)$p['reviews_count'] ?>)</span>
                                    </div>
                                    <div class="text-[10px] text-app-muted mt-0.5"><?= number_format((int)$p['orders_count']) ?> sold</div>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-2.5 px-3.5 whitespace-nowrap">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-colors <?= $p['is_active'] ? 'bg-emerald-500/15 text-emerald-500 hover:bg-emerald-500/25' : 'bg-slate-500/15 text-app-muted hover:bg-slate-500/25' ?>" title="Click to toggle active status">
                                            <?= $p['is_active'] ? '● Active' : '○ Inactive' ?>
                                        </button>
                                    </form>
                                    <?php if ($p['is_featured']): ?>
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-500 font-bold">Featured</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions -->
                                <td class="py-2.5 px-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="product-form.php?id=<?= $p['id'] ?>" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] transition-colors inline-block">
                                        Edit
                                    </a>
                                    <a href="../product-detail.php?slug=<?= urlencode($p['slug'] ?? '') ?>" target="_blank" class="px-2.5 py-1 rounded-lg bg-app-canvas border border-app hover:bg-slate-500/10 text-app-title font-semibold text-[11px] transition-colors inline-block" title="Preview Product Detail Page">
                                        Detail ↗
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete \'<?= e($p['name']) ?>\'?');">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-red-600/15 hover:bg-red-600 text-red-500 hover:text-white font-bold text-[11px] transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Component -->
            <?= render_admin_pagination($totalProducts, $per_page, $page) ?>
        </div>

    </main>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
