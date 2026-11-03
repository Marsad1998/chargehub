<?php
$page_title = 'Categories Management';
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please refresh.';
    } else {
        $action = $_POST['action'] ?? '';

        // 1. CREATE CATEGORY
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
            $description = trim($_POST['description'] ?? '');
            $badge_text = trim($_POST['badge_text'] ?? '');
            $models_count_text = trim($_POST['models_count_text'] ?? '');
            $bg_color = trim($_POST['bg_color'] ?? '') ?: 'bg-blue-50';
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $image_url = trim($_POST['custom_image_url'] ?? '');

            // Handle file upload
            if (!empty($_FILES['category_image']['name'])) {
                $upload = handle_image_upload($_FILES['category_image'], __DIR__ . '/../uploads/categories', 'cat_');
                if ($upload['success']) {
                    $image_url = 'uploads/categories/' . $upload['filename'];
                } else {
                    $error = 'Image Upload Error: ' . $upload['error'];
                }
            }

            if (empty($name)) {
                $error = 'Category name is required.';
            }

            if (empty($error)) {
                // Check slug uniqueness
                $stmtChk = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
                $stmtChk->execute([$slug]);
                if ($stmtChk->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image_url, badge_text, models_count_text, bg_color, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $image_url, $badge_text, $models_count_text, $bg_color, $description, $sort_order, $is_active]);
                set_flash_message('success', 'Category "' . $name . '" created successfully!');
                header('Location: categories.php');
                exit;
            }
        }

        // 2. UPDATE CATEGORY
        elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
            $description = trim($_POST['description'] ?? '');
            $badge_text = trim($_POST['badge_text'] ?? '');
            $models_count_text = trim($_POST['models_count_text'] ?? '');
            $bg_color = trim($_POST['bg_color'] ?? '') ?: 'bg-blue-50';
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $image_url = trim($_POST['existing_image_url'] ?? '');

            if (!empty($_POST['custom_image_url'])) {
                $image_url = trim($_POST['custom_image_url']);
            }

            // Handle new file upload
            if (!empty($_FILES['category_image']['name'])) {
                $upload = handle_image_upload($_FILES['category_image'], __DIR__ . '/../uploads/categories', 'cat_');
                if ($upload['success']) {
                    $image_url = 'uploads/categories/' . $upload['filename'];
                } else {
                    $error = 'Image Upload Error: ' . $upload['error'];
                }
            }

            if (empty($name)) {
                $error = 'Category name cannot be empty.';
            }

            if (empty($error) && $id > 0) {
                // Check slug uniqueness (excluding current ID)
                $stmtChk = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
                $stmtChk->execute([$slug, $id]);
                if ($stmtChk->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, image_url = ?, badge_text = ?, models_count_text = ?, bg_color = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $image_url, $badge_text, $models_count_text, $bg_color, $description, $sort_order, $is_active, $id]);
                set_flash_message('success', 'Category updated successfully!');
                header('Location: categories.php');
                exit;
            }
        }

        // 3. DELETE CATEGORY
        elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                // Detach from products first
                $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$id]);
                // Delete category
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Category deleted successfully!');
                header('Location: categories.php');
                exit;
            }
        }

        // 4. TOGGLE ACTIVE STATUS
        elseif ($action === 'toggle_status') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET is_active = (CASE WHEN is_active = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Category status updated.');
                header('Location: categories.php');
                exit;
            }
        }
    }
}

// Count total categories for pagination
$totalCats = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$per_page = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$total_pages = max(1, (int)ceil($totalCats / $per_page));
if ($page > $total_pages && $totalCats > 0) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch categories with product count
$stmtCats = $pdo->prepare("
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
    LIMIT $per_page OFFSET $offset
");
$stmtCats->execute();
$categories = $stmtCats->fetchAll();

$flashMessages = get_flash_messages();
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">

    <!-- TOPBAR -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- MAIN BODY (Concise Container) -->
    <main class="p-4 sm:p-6 space-y-5 flex-1 max-w-[1140px] mx-auto w-full">

        <!-- HEADER SECTION -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-app">
            <div>
                <h2 class="text-lg font-black text-app-title brand-font">Categories Management</h2>
                <p class="text-xs text-app-muted">Create, edit, and organize product categories with custom thumbnail images.</p>
            </div>
            <button onclick="openAddModal()" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all shadow-md flex items-center space-x-1.5 self-start sm:self-auto">
                <span>➕</span>
                <span>Add New Category</span>
            </button>
        </div>

        <!-- FLASH NOTIFICATIONS -->
        <?php foreach ($flashMessages as $msg): ?>
        <div class="flash-alert p-3 rounded-xl <?= $msg['type'] === 'success' ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-500' : 'bg-red-500/15 border-red-500/30 text-red-500' ?> border text-xs font-semibold flex items-center justify-between">
            <span><?= e($msg['text']) ?></span>
            <button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-500/15 border border-red-500/30 text-red-500 text-xs font-semibold">
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <!-- CATEGORIES LIST TABLE (Concise & Compact) -->
        <div class="rounded-xl bg-app-card border border-app overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-500/5 text-app-muted font-bold text-[10px] uppercase tracking-wider border-b border-app">
                        <tr>
                            <th class="py-2.5 px-3.5 w-16">Image</th>
                            <th class="py-2.5 px-3.5">Category Details</th>
                            <th class="py-2.5 px-3.5">Slug</th>
                            <th class="py-2.5 px-3.5">Products</th>
                            <th class="py-2.5 px-3.5">Sort</th>
                            <th class="py-2.5 px-3.5">Status</th>
                            <th class="py-2.5 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app">
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-app-muted">No categories created yet. Click "+ Add New Category" to start!</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <!-- Image Preview -->
                                <td class="py-2.5 px-3.5">
                                    <?php 
                                    $cImg = trim($cat['image_url'] ?? '');
                                    $isImgPath = (strpos($cImg, '/') !== false || strpos($cImg, '.') !== false);
                                    $cBg = !empty($cat['bg_color']) ? $cat['bg_color'] : 'bg-blue-50';
                                    ?>
                                    <div class="w-11 h-11 rounded-xl <?= e($cBg) ?> border border-app p-1 flex items-center justify-center overflow-hidden shrink-0 relative">
                                        <?php if ($isImgPath): ?>
                                            <img src="<?= (preg_match('#^https?://#i', $cImg) ? '' : '../') . e($cImg) ?>" alt="<?= e($cat['name']) ?>" class="w-full h-full object-contain">
                                        <?php elseif (!empty($cImg)): ?>
                                            <span class="text-xl"><?= e($cImg) ?></span>
                                        <?php else: ?>
                                            <span class="text-base">📂</span>
                                        <?php endif; ?>
                                        <?php if (!empty($cat['badge_text'])): ?>
                                            <span class="absolute top-0.5 right-0.5 px-1 py-0.2 bg-blue-600 text-white text-[7.5px] font-black rounded uppercase"><?= e($cat['badge_text']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Category Name & Description -->
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center space-x-1.5">
                                        <div class="font-bold text-app-title text-sm"><?= e($cat['name']) ?></div>
                                        <?php if (!empty($cat['badge_text'])): ?>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-500/15 text-blue-500 border border-blue-500/30"><?= e($cat['badge_text']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-app-muted line-clamp-1 max-w-sm mt-0.5">
                                        <span class="font-semibold text-blue-500"><?= e($cat['models_count_text'] ?: ($cat['product_count'] . ' Models')) ?></span>
                                        <?php if (!empty($cat['description'])): ?> &bull; <?= e($cat['description']) ?><?php endif; ?>
                                    </div>
                                </td>

                                <!-- Slug -->
                                <td class="py-2.5 px-3.5 font-mono text-[11px] text-app-muted">
                                    <?= e($cat['slug']) ?>
                                </td>

                                <!-- Products Count -->
                                <td class="py-2.5 px-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-500">
                                        <?= (int)$cat['product_count'] ?> items
                                    </span>
                                </td>

                                <!-- Sort Order -->
                                <td class="py-2.5 px-3.5 font-mono text-app-muted">
                                    #<?= (int)$cat['sort_order'] ?>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-2.5 px-3.5">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-colors <?= $cat['is_active'] ? 'bg-emerald-500/15 text-emerald-500 hover:bg-emerald-500/25' : 'bg-slate-500/15 text-app-muted hover:bg-slate-500/25' ?>">
                                            <?= $cat['is_active'] ? '● Active' : '○ Inactive' ?>
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="py-2.5 px-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete category \'<?= e($cat['name']) ?>\'? Associated products will become uncategorized.');">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-600/15 hover:bg-red-600 text-red-500 hover:text-white font-bold text-[11px] transition-colors">
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
            <?= render_admin_pagination($totalCats, $per_page, $page) ?>
        </div>

    </main>

</div>

<!-- ADD CATEGORY MODAL -->
<div id="add-modal" style="display: none;" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-app-card border border-app rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-app">
            <h3 class="text-sm font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                <span>➕</span>
                <span>Add New Category</span>
            </h3>
            <button onclick="closeAddModal()" class="w-7 h-7 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Category Name *</label>
                <input type="text" name="name" required placeholder="Category name" class="w-full px-3 py-2 rounded-xl input-app border text-xs" oninput="document.getElementById('add-slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Slug (URL identifier)</label>
                <input type="text" id="add-slug" name="slug" placeholder="category-slug" class="w-full px-3 py-2 rounded-xl input-app border text-xs font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Badge Text (Optional)</label>
                    <input type="text" name="badge_text" placeholder="e.g. HOT, NEW" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs uppercase font-bold text-blue-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Models Subtitle</label>
                    <input type="text" name="models_count_text" placeholder="e.g. 18 Models, 40+ Models" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Card Background Tint</label>
                <select name="bg_color" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs font-semibold">
                    <option value="bg-blue-50">Light Blue (Party Speakers)</option>
                    <option value="bg-indigo-50">Light Indigo (Earbuds & ANC)</option>
                    <option value="bg-emerald-50">Light Emerald (SmartWatches)</option>
                    <option value="bg-amber-50">Light Amber (Fast Chargers)</option>
                    <option value="bg-purple-50">Light Purple (Cables & Hubs)</option>
                    <option value="bg-rose-50">Light Rose (Data Storage)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Category Icon / Emoji / Image</label>
                <input type="text" name="custom_image_url" placeholder="Enter Emoji (🔊, 🎧, ⌚, ⚡, 🔌, 💾) or image path..." class="w-full px-3 py-1.5 rounded-xl input-app border text-xs mb-1.5">
                <span class="text-[10px] text-app-muted block mb-1">Or upload image file:</span>
                <input type="file" name="category_image" accept="image/*" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description" class="w-full px-3 py-2 rounded-xl input-app border text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center space-x-2 text-xs font-bold text-app-title cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-blue-600 border-gray-300">
                        <span>Active in Store</span>
                    </label>
                </div>
            </div>

            <div class="pt-3 border-t border-app flex justify-end space-x-2">
                <button type="button" onclick="closeAddModal()" class="px-3.5 py-1.5 rounded-xl bg-slate-500/10 hover:bg-slate-500/20 text-app-muted text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div id="edit-modal" style="display: none;" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-app-card border border-app rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-app">
            <h3 class="text-sm font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                <span>✏️</span>
                <span>Edit Category</span>
            </h3>
            <button onclick="closeEditModal()" class="w-7 h-7 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-id" name="id" value="">
            <input type="hidden" id="edit-existing-image" name="existing_image_url" value="">

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Category Name *</label>
                <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 rounded-xl input-app border text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Slug (URL identifier)</label>
                <input type="text" id="edit-slug" name="slug" class="w-full px-3 py-2 rounded-xl input-app border text-xs font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Badge Text (Optional)</label>
                    <input type="text" id="edit-badge" name="badge_text" placeholder="e.g. HOT, NEW" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs uppercase font-bold text-blue-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Models Subtitle</label>
                    <input type="text" id="edit-models-count" name="models_count_text" placeholder="e.g. 18 Models, 40+ Models" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Card Background Tint</label>
                <select id="edit-bg-color" name="bg_color" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs font-semibold">
                    <option value="bg-blue-50">Light Blue (Party Speakers)</option>
                    <option value="bg-indigo-50">Light Indigo (Earbuds & ANC)</option>
                    <option value="bg-emerald-50">Light Emerald (SmartWatches)</option>
                    <option value="bg-amber-50">Light Amber (Fast Chargers)</option>
                    <option value="bg-purple-50">Light Purple (Cables & Hubs)</option>
                    <option value="bg-rose-50">Light Rose (Data Storage)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Current Image / Icon</label>
                <div class="flex items-center space-x-2.5 mb-2">
                    <div id="edit-preview-box" class="w-10 h-10 rounded-xl border border-app p-1 bg-slate-500/10 flex items-center justify-center overflow-hidden">
                        <img id="edit-image-preview" src="" alt="" class="w-full h-full object-contain hidden">
                        <span id="edit-emoji-preview" class="text-xl hidden"></span>
                    </div>
                    <span id="edit-image-label" class="text-[11px] text-app-muted truncate font-mono"></span>
                </div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Replace Icon (Emoji or Image Path)</label>
                <input type="text" id="edit-custom-image" name="custom_image_url" placeholder="Emoji (🔊, 🎧, ⌚, ⚡, 🔌, 💾) or image path..." class="w-full px-3 py-1.5 rounded-xl input-app border text-xs mb-1.5">
                <span class="text-[10px] text-app-muted block mb-1">Or upload new image file:</span>
                <input type="file" name="category_image" accept="image/*" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Description</label>
                <textarea id="edit-description" name="description" rows="2" class="w-full px-3 py-2 rounded-xl input-app border text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-app-muted mb-1">Sort Order</label>
                    <input type="number" id="edit-sort" name="sort_order" class="w-full px-3 py-1.5 rounded-xl input-app border text-xs">
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center space-x-2 text-xs font-bold text-app-title cursor-pointer">
                        <input type="checkbox" id="edit-active" name="is_active" value="1" class="w-4 h-4 rounded text-blue-600 border-gray-300">
                        <span>Active in Store</span>
                    </label>
                </div>
            </div>

            <div class="pt-3 border-t border-app flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-3.5 py-1.5 rounded-xl bg-slate-500/10 hover:bg-slate-500/20 text-app-muted text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        const m = document.getElementById('add-modal');
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
            m.style.display = 'flex';
        }
    }
    function closeAddModal() {
        const m = document.getElementById('add-modal');
        if (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
            m.style.display = 'none';
        }
    }

    function openEditModal(cat) {
        document.getElementById('edit-id').value = cat.id;
        document.getElementById('edit-name').value = cat.name;
        document.getElementById('edit-slug').value = cat.slug;
        document.getElementById('edit-badge').value = cat.badge_text || '';
        document.getElementById('edit-models-count').value = cat.models_count_text || '';
        document.getElementById('edit-bg-color').value = cat.bg_color || 'bg-blue-50';
        document.getElementById('edit-description').value = cat.description || '';
        document.getElementById('edit-sort').value = cat.sort_order;
        document.getElementById('edit-active').checked = (parseInt(cat.is_active) === 1);
        document.getElementById('edit-existing-image').value = cat.image_url || '';
        document.getElementById('edit-custom-image').value = '';
        
        const preview = document.getElementById('edit-image-preview');
        const emojiPrev = document.getElementById('edit-emoji-preview');
        const label = document.getElementById('edit-image-label');
        const imgVal = (cat.image_url || '').trim();
        const isImgFile = imgVal.includes('/') || imgVal.includes('.');

        if (imgVal) {
            if (isImgFile) {
                preview.src = /^https?:\/\//i.test(imgVal) ? imgVal : ('../' + imgVal);
                preview.classList.remove('hidden');
                emojiPrev.classList.add('hidden');
            } else {
                preview.classList.add('hidden');
                emojiPrev.textContent = imgVal;
                emojiPrev.classList.remove('hidden');
            }
            label.textContent = imgVal;
        } else {
            preview.classList.add('hidden');
            emojiPrev.classList.add('hidden');
            label.textContent = 'No icon/image set';
        }

        const m = document.getElementById('edit-modal');
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
            m.style.display = 'flex';
        }
    }
    function closeEditModal() {
        const m = document.getElementById('edit-modal');
        if (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
            m.style.display = 'none';
        }
    }

    // Close on Escape or click outside
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });

    ['add-modal', 'edit-modal'].forEach(function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    if (id === 'add-modal') closeAddModal();
                    else closeEditModal();
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
