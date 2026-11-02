<?php
$page_title = 'Dashboard Overview';
require_once __DIR__ . '/includes/header.php';

// Fetch metrics using prepared statements
$prodCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();

$catCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$brandCount = (int)$pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();

$heroCount = (int)$pdo->query("SELECT COUNT(*) FROM hero_slides WHERE is_active = 1")->fetchColumn();
$spotCount = (int)$pdo->query("SELECT COUNT(*) FROM spotlight_items WHERE is_active = 1")->fetchColumn();

// Fetch orders metrics
try {
    $orderCount = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrderCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    $revenueTotal = (float)$pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
} catch (Exception $e) {
    $orderCount = $pendingOrderCount = $revenueTotal = 0;
}

// Fetch 5 recent orders
try {
    $stmtRecentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
    $recentOrders = $stmtRecentOrders ? $stmtRecentOrders->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Exception $e) {
    $recentOrders = [];
}

// Fetch 5 recent products
$stmtRecent = $pdo->prepare("
    SELECT p.*, c.name AS category_name, b.name AS brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    ORDER BY p.id DESC LIMIT 5
");
$stmtRecent->execute();
$recentProducts = $stmtRecent->fetchAll();
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">

    <!-- TOPBAR -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- MAIN BODY (Concise Max-Width Container) -->
    <main class="p-4 sm:p-6 space-y-5 flex-1 max-w-[1140px] mx-auto w-full">

        <!-- WELCOME BANNER (Concise & Theme-Aware) -->
        <div class="rounded-2xl bg-gradient-to-r from-blue-600/15 via-indigo-600/10 to-transparent border border-blue-500/20 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-500 text-[10px] font-black uppercase tracking-wider">
                    <span>🛡️</span>
                    <span>MySQL / Prepared Statements Protected</span>
                </div>
                <h2 class="text-lg sm:text-xl font-black text-app-title brand-font">
                    Welcome back, <?= e($_SESSION['admin_user'] ?? 'Admin') ?>
                </h2>
                <p class="text-xs text-app-muted">
                    Full control over your E-Commerce catalog (Products, Categories, Brands) and Website CMS.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="product-form.php" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all shadow-md flex items-center space-x-1.5">
                    <span>➕</span>
                    <span>Add Product</span>
                </a>
                <a href="cms.php" class="px-3 py-2 rounded-xl bg-app-card border border-app hover:bg-slate-500/10 text-app-title text-xs font-semibold transition-all">
                    <span>🖼️ CMS Media</span>
                </a>
            </div>
        </div>


        <!-- METRICS TILES (Concise 5-Column Grid) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Stat 0: Orders & Sales -->
            <a href="orders.php" class="p-4 rounded-xl bg-app-card border border-app hover:border-amber-500/40 transition-all group shadow-sm col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Orders &amp; Sales</span>
                    <span class="text-base">📦</span>
                </div>
                <div class="text-xl font-black text-amber-500"><?= $orderCount ?> <span class="text-[11px] font-normal text-app-muted">(<?= $pendingOrderCount ?> pending)</span></div>
                <div class="text-[10px] text-emerald-500 font-semibold mt-1">Rs. <?= number_format($revenueTotal) ?> Net &rarr;</div>
            </a>

            <!-- Stat 1: Products -->
            <a href="products.php" class="p-4 rounded-xl bg-app-card border border-app hover:border-blue-500/40 transition-all group shadow-sm">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Products</span>
                    <span class="text-base">⚡</span>
                </div>
                <div class="text-xl font-black text-app-title"><?= $activeProdCount ?> <span class="text-[11px] font-normal text-app-muted">/ <?= $prodCount ?> total</span></div>
                <div class="text-[10px] text-blue-500 font-semibold mt-1">Manage Catalog &rarr;</div>
            </a>

            <!-- Stat 2: Categories -->
            <a href="categories.php" class="p-4 rounded-xl bg-app-card border border-app hover:border-blue-500/40 transition-all group shadow-sm">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Categories</span>
                    <span class="text-base">📂</span>
                </div>
                <div class="text-xl font-black text-app-title"><?= $catCount ?></div>
                <div class="text-[10px] text-blue-500 font-semibold mt-1">With Images &rarr;</div>
            </a>

            <!-- Stat 3: Brands -->
            <a href="brands.php" class="p-4 rounded-xl bg-app-card border border-app hover:border-blue-500/40 transition-all group shadow-sm">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Brands</span>
                    <span class="text-base">🏷️</span>
                </div>
                <div class="text-xl font-black text-app-title"><?= $brandCount ?></div>
                <div class="text-[10px] text-blue-500 font-semibold mt-1">With Logos &rarr;</div>
            </a>

            <!-- Stat 4: CMS Slides -->
            <a href="cms.php?tab=hero" class="p-4 rounded-xl bg-app-card border border-app hover:border-blue-500/40 transition-all group shadow-sm col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">CMS Media</span>
                    <span class="text-base">🖼️</span>
                </div>
                <div class="text-xl font-black text-app-title"><?= $heroCount ?> Hero <span class="text-[11px] font-normal text-app-muted">+ <?= $spotCount ?> Spot</span></div>
                <div class="text-[10px] text-blue-500 font-semibold mt-1">Control Visuals &rarr;</div>
            </a>
        </div>

        <!-- RECENT CUSTOMER ORDERS TABLE -->
        <div class="rounded-xl bg-app-card border border-app overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-app flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm">📦</span>
                    <h3 class="text-xs font-black text-app-title uppercase tracking-wider">Recent Customer Orders</h3>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="orders.php" class="text-xs font-bold text-blue-500 hover:underline">
                        View All (<?= $orderCount ?>) &rarr;
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-app-body divide-y divide-app">
                    <thead class="bg-slate-500/5 text-app-muted uppercase font-bold text-[10px] tracking-wider">
                        <tr>
                            <th class="p-3 pl-4">Order Ref</th>
                            <th class="p-3">Customer &amp; Phone</th>
                            <th class="p-3">City</th>
                            <th class="p-3">Total Payable</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 pr-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app">
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" class="p-5 text-center text-app-muted">
                                No customer orders recorded yet. As soon as orders are placed via checkout, they appear here.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentOrders as $ro): 
                            $badgeCls = 'bg-slate-500/15 text-slate-400 border-slate-500/30';
                            if ($ro['status'] === 'Pending') $badgeCls = 'bg-amber-500/15 text-amber-500 border-amber-500/30';
                            elseif ($ro['status'] === 'Confirmed') $badgeCls = 'bg-blue-500/15 text-blue-500 border-blue-500/30';
                            elseif ($ro['status'] === 'Dispatched') $badgeCls = 'bg-purple-500/15 text-purple-500 border-purple-500/30';
                            elseif ($ro['status'] === 'Delivered') $badgeCls = 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30';
                        ?>
                        <tr class="hover:bg-slate-500/5 transition-colors">
                            <td class="p-3 pl-4">
                                <a href="orders.php?search=<?= urlencode($ro['order_number']) ?>" class="font-mono font-bold text-blue-500 hover:underline">
                                    <?= e($ro['order_number']) ?>
                                </a>
                                <span class="text-[10px] text-app-muted block mt-0.5"><?= date('M d, H:i', strtotime($ro['created_at'])) ?></span>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-app-title"><?= e($ro['customer_name']) ?></div>
                                <span class="text-[11px] text-app-muted"><?= e($ro['customer_phone']) ?></span>
                            </td>
                            <td class="p-3 font-semibold text-app-title">
                                <?= e($ro['customer_city']) ?>
                            </td>
                            <td class="p-3 font-extrabold text-app-title font-mono">
                                Rs. <?= number_format($ro['total_amount']) ?>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $badgeCls ?>">
                                    <?= e($ro['status']) ?>
                                </span>
                            </td>
                            <td class="p-3 pr-4 text-right">
                                <a href="orders.php?search=<?= urlencode($ro['order_number']) ?>" class="px-2.5 py-1 rounded-lg bg-blue-500/15 hover:bg-blue-500/25 text-blue-500 font-semibold text-[11px] transition-colors inline-block">
                                    Inspect &rarr;
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT PRODUCTS TABLE (Concise & Compact) -->
        <div class="rounded-xl bg-app-card border border-app overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-app flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm">📦</span>
                    <h3 class="text-xs font-black text-app-title uppercase tracking-wider">Catalog Products (Detail Page Integrated)</h3>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="product-form.php" class="text-xs font-bold text-blue-500 hover:underline">
                        + New Product
                    </a>
                    <span class="text-app-muted text-xs">&bull;</span>
                    <a href="products.php" class="text-xs font-semibold text-app-muted hover:text-app-title">
                        View All (<?= $prodCount ?>) &rarr;
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-500/5 text-app-muted font-bold text-[10px] uppercase tracking-wider border-b border-app">
                        <tr>
                            <th class="py-2.5 px-3.5">Product</th>
                            <th class="py-2.5 px-3.5">Category</th>
                            <th class="py-2.5 px-3.5">Brand</th>
                            <th class="py-2.5 px-3.5">Price (PKR)</th>
                            <th class="py-2.5 px-3.5">Stock</th>
                            <th class="py-2.5 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app">
                        <?php if (empty($recentProducts)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-app-muted">No products found. Add your first product!</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recentProducts as $p): ?>
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-lg bg-slate-500/10 p-1 shrink-0 border border-app flex items-center justify-center overflow-hidden">
                                            <img src="../<?= e($p['main_image']) ?>" alt="<?= e($p['name']) ?>" class="w-full h-full object-contain">
                                        </div>
                                        <div class="min-w-0 max-w-xs">
                                            <div class="font-bold text-app-title truncate"><?= e($p['name']) ?></div>
                                            <div class="text-[10px] text-app-muted font-mono"><?= e($p['sku'] ?? 'NO-SKU') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3.5 text-app-muted">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-500/10 text-blue-500">
                                        <?= e($p['category_name'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td class="py-2.5 px-3.5 text-app-muted">
                                    <span class="font-semibold text-app-title"><?= e($p['brand_name'] ?? 'Generic') ?></span>
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <div class="font-bold text-app-title">Rs. <?= number_format($p['price']) ?></div>
                                    <?php if (!empty($p['original_price']) && $p['original_price'] > $p['price']): ?>
                                    <div class="text-[10px] text-app-muted line-through">Rs. <?= number_format($p['original_price']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-3.5">
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= ($p['stock_quantity'] > 0) ? 'bg-emerald-500/15 text-emerald-500' : 'bg-red-500/15 text-red-500' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= ($p['stock_quantity'] > 0) ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                                        <span><?= e($p['stock_status'] ?? 'In Stock') ?></span>
                                    </span>
                                </td>
                                <td class="py-2.5 px-3.5 text-right space-x-1.5">
                                    <a href="product-form.php?id=<?= $p['id'] ?>" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] transition-colors inline-block">
                                        Edit
                                    </a>
                                    <a href="../product-detail.php?slug=<?= urlencode($p['slug'] ?? '') ?>" target="_blank" class="px-2.5 py-1 rounded-lg bg-app-canvas border border-app hover:bg-slate-500/10 text-app-title font-semibold text-[11px] transition-colors inline-block" title="View Detail Page">
                                        View ↗
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- QUICK ACCESS MODULES (Categories, Brands, CMS) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card 1: Categories -->
            <div class="p-4 rounded-xl bg-app-card border border-app space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                        <span>📂</span>
                        <span>Categories</span>
                    </h4>
                    <a href="categories.php" class="text-[11px] text-blue-500 font-bold hover:underline">Manage &rarr;</a>
                </div>
                <p class="text-xs text-app-muted">Organize catalog navigation with category thumbnail images and slugs.</p>
                <a href="categories.php?action=create" class="block w-full text-center py-1.5 px-3 rounded-lg bg-slate-500/10 hover:bg-slate-500/15 text-app-title text-xs font-bold transition-colors">
                    + Add Category
                </a>
            </div>

            <!-- Card 2: Brands -->
            <div class="p-4 rounded-xl bg-app-card border border-app space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                        <span>🏷️</span>
                        <span>Brands</span>
                    </h4>
                    <a href="brands.php" class="text-[11px] text-blue-500 font-bold hover:underline">Manage &rarr;</a>
                </div>
                <p class="text-xs text-app-muted">Manage manufacturers (Anker, Ugreen, Baseus, etc.) with brand logos and websites.</p>
                <a href="brands.php?action=create" class="block w-full text-center py-1.5 px-3 rounded-lg bg-slate-500/10 hover:bg-slate-500/15 text-app-title text-xs font-bold transition-colors">
                    + Add Brand
                </a>
            </div>

            <!-- Card 3: CMS Visuals -->
            <div class="p-4 rounded-xl bg-app-card border border-app space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                        <span>🖼️</span>
                        <span>CMS Visuals</span>
                    </h4>
                    <a href="cms.php" class="text-[11px] text-blue-500 font-bold hover:underline">Open &rarr;</a>
                </div>
                <p class="text-xs text-app-muted">Live control of Hero Section slides and 3D Pop-out Product Spotlight cards.</p>
                <div class="flex space-x-2">
                    <a href="cms.php?tab=hero" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-blue-500/15 text-blue-500 hover:bg-blue-500/25 text-[11px] font-bold transition-colors">
                        Hero Slides
                    </a>
                    <a href="cms.php?tab=spotlight" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-orange-500/15 text-orange-500 hover:bg-orange-500/25 text-[11px] font-bold transition-colors">
                        Spotlight
                    </a>
                </div>
            </div>
        </div>

    </main>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
