<?php
// Admin Sidebar Component
$current_file = basename($_SERVER['PHP_SELF']);
$active_tab = $_GET['tab'] ?? '';

// Fetch quick counter badges for sidebar
try {
    $cProds = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $cCats  = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $cBrands = $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
    $cOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    if ($cOrders === false || $cOrders === null) $cOrders = 0;
} catch (Exception $e) {
    $cProds = $cCats = $cBrands = $cOrders = 0;
}
?>
<!-- SIDEBAR (Concise 240px Width, Dual Light/Dark Theme) -->
<aside id="admin-sidebar" class="w-60 bg-app-sidebar border-r border-app flex flex-col justify-between shrink-0 hidden md:flex min-h-screen transition-colors duration-200">
    <div>
        <!-- Brand Header -->
        <div class="px-5 py-4 border-b border-app flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-2">
                <img src="../images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-7 w-auto object-contain">
            </a>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-500/15 text-blue-500 border border-blue-500/30">Admin</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="p-3 space-y-1 text-xs font-semibold">
            <!-- 1. Dashboard -->
            <a href="index.php" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition-all <?= ($current_file === 'index.php') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <span class="text-sm">📊</span>
                <span>Dashboard</span>
            </a>

            <!-- SECTION: SALES & ORDERS -->
            <div class="pt-3 pb-1 px-3">
                <span class="text-[9.5px] font-black uppercase tracking-widest text-app-muted opacity-70">Sales &amp; Orders</span>
            </div>

            <!-- Orders Link -->
            <a href="orders.php" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all <?= in_array($current_file, ['orders.php', 'order-detail.php']) ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">📦</span>
                    <span>Orders</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= in_array($current_file, ['orders.php', 'order-detail.php']) ? 'bg-white/20 text-white' : ($cOrders > 0 ? 'bg-amber-500/20 text-amber-500 font-black animate-pulse' : 'bg-slate-500/15 text-app-muted') ?>"><?= (int)$cOrders ?></span>
            </a>

            <!-- SECTION: STORE CATALOG -->
            <div class="pt-3 pb-1 px-3">
                <span class="text-[9.5px] font-black uppercase tracking-widest text-app-muted opacity-70">Catalog Management</span>
            </div>

            <!-- Products Link -->
            <a href="products.php" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all <?= in_array($current_file, ['products.php', 'product-form.php']) ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">⚡</span>
                    <span>Products</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= in_array($current_file, ['products.php', 'product-form.php']) ? 'bg-white/20 text-white' : 'bg-slate-500/15 text-app-muted' ?>"><?= (int)$cProds ?></span>
            </a>

            <!-- Categories Link -->
            <a href="categories.php" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all <?= ($current_file === 'categories.php') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">📂</span>
                    <span>Categories</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= ($current_file === 'categories.php') ? 'bg-white/20 text-white' : 'bg-slate-500/15 text-app-muted' ?>"><?= (int)$cCats ?></span>
            </a>

            <!-- Brands Link -->
            <a href="brands.php" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all <?= ($current_file === 'brands.php') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">🏷️</span>
                    <span>Brands</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= ($current_file === 'brands.php') ? 'bg-white/20 text-white' : 'bg-slate-500/15 text-app-muted' ?>"><?= (int)$cBrands ?></span>
            </a>

            <!-- SECTION: CONTENT MANAGEMENT -->
            <div class="pt-3 pb-1 px-3">
                <span class="text-[9.5px] font-black uppercase tracking-widest text-app-muted opacity-70">Content</span>
            </div>

            <!-- CMS Media Link -->
            <a href="cms.php" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all <?= ($current_file === 'cms.php') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">🖼️</span>
                    <span>CMS Media</span>
                </div>
            </a>

            <!-- SECTION: SETTINGS & SYSTEM -->
            <div class="pt-3 pb-1 px-3">
                <span class="text-[9.5px] font-black uppercase tracking-widest text-app-muted opacity-70">Settings &amp; System</span>
            </div>

            <!-- Email & SMTP Settings Link -->
            <a href="settings-email.php" class="flex items-center justify-between px-3 py-2 rounded-xl transition-all <?= ($current_file === 'settings-email.php') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-bold' : 'text-app-muted hover:text-app-title hover:bg-slate-500/10' ?>">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">✉️</span>
                    <span>Email &amp; SMTP</span>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= ($current_file === 'settings-email.php') ? 'bg-white/20 text-white' : 'bg-slate-500/15 text-app-muted' ?>">Setup</span>
            </a>

            <!-- SECTION: STORE FRONT (The only website link) -->
            <div class="pt-3 pb-1 px-3">
                <span class="text-[9.5px] font-black uppercase tracking-widest text-app-muted opacity-70">Storefront</span>
            </div>
            <a href="../index.php" target="_blank" class="flex items-center justify-between px-3 py-2 rounded-xl text-app-muted hover:text-app-title hover:bg-slate-500/10 transition-all">
                <div class="flex items-center space-x-2.5">
                    <span class="text-sm">🌐</span>
                    <span>View Store</span>
                </div>
                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </nav>
    </div>

    <!-- User Profile & Sign Out -->
    <div class="p-3 border-t border-app">
        <!-- MySQL Engine Status Pill -->
        <div class="mb-2 px-2.5 py-1.5 rounded-lg text-[10px] font-bold flex items-center justify-between bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
            <span class="flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>MySQL Active</span>
            </span>
            <span class="text-[9px] opacity-80">chargehub_db</span>
        </div>

        <div class="flex items-center justify-between p-2 rounded-xl bg-app-card border border-app">
            <div class="flex items-center space-x-2 overflow-hidden">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-xs font-bold text-white shrink-0">
                    <?= strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="truncate">
                    <div class="text-xs font-bold text-app-title truncate"><?= e($_SESSION['admin_user'] ?? 'Admin') ?></div>
                    <div class="text-[9.5px] text-app-muted truncate">Administrator</div>
                </div>
            </div>
            <a href="logout.php" title="Sign Out" class="p-1.5 rounded-lg text-app-muted hover:text-red-500 hover:bg-red-500/10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </div>
</aside>
