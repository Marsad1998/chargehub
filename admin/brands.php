<?php
$page_title = 'Brands Management';
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please refresh.';
    } else {
        $action = $_POST['action'] ?? '';

        // 1. CREATE BRAND
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
            $website = trim($_POST['website'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $logo_url = trim($_POST['custom_logo_url'] ?? '');

            // Handle file upload
            if (!empty($_FILES['brand_logo']['name'])) {
                $upload = handle_image_upload($_FILES['brand_logo'], __DIR__ . '/../uploads/brands', 'brand_');
                if ($upload['success']) {
                    $logo_url = 'uploads/brands/' . $upload['filename'];
                } else {
                    $error = 'Logo Upload Error: ' . $upload['error'];
                }
            }

            if (empty($name)) {
                $error = 'Brand name is required.';
            }

            if (empty($error)) {
                $stmtChk = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE slug = ?");
                $stmtChk->execute([$slug]);
                if ($stmtChk->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $pdo->prepare("INSERT INTO brands (name, slug, logo_url, website, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $logo_url, $website, $description, $sort_order, $is_active]);
                set_flash_message('success', 'Brand "' . $name . '" created successfully!');
                header('Location: brands.php');
                exit;
            }
        }

        // 2. UPDATE BRAND
        elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
            $website = trim($_POST['website'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $logo_url = trim($_POST['existing_logo_url'] ?? '');

            if (!empty($_POST['custom_logo_url'])) {
                $logo_url = trim($_POST['custom_logo_url']);
            }

            // Handle new file upload
            if (!empty($_FILES['brand_logo']['name'])) {
                $upload = handle_image_upload($_FILES['brand_logo'], __DIR__ . '/../uploads/brands', 'brand_');
                if ($upload['success']) {
                    $logo_url = 'uploads/brands/' . $upload['filename'];
                } else {
                    $error = 'Logo Upload Error: ' . $upload['error'];
                }
            }

            if (empty($name)) {
                $error = 'Brand name cannot be empty.';
            }

            if (empty($error) && $id > 0) {
                $stmtChk = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE slug = ? AND id != ?");
                $stmtChk->execute([$slug, $id]);
                if ($stmtChk->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $pdo->prepare("UPDATE brands SET name = ?, slug = ?, logo_url = ?, website = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $logo_url, $website, $description, $sort_order, $is_active, $id]);
                set_flash_message('success', 'Brand updated successfully!');
                header('Location: brands.php');
                exit;
            }
        }

        // 3. DELETE BRAND
        elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("UPDATE products SET brand_id = NULL WHERE brand_id = ?")->execute([$id]);
                $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Brand deleted successfully!');
                header('Location: brands.php');
                exit;
            }
        }

        // 4. TOGGLE STATUS
        elseif ($action === 'toggle_status') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE brands SET is_active = (CASE WHEN is_active = 1 THEN 0 ELSE 1 END) WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Brand status updated.');
                header('Location: brands.php');
                exit;
            }
        }
    }
}

// Fetch all brands with product count
$stmtBrands = $pdo->prepare("
    SELECT b.*, COUNT(p.id) AS product_count 
    FROM brands b 
    LEFT JOIN products p ON b.id = p.brand_id 
    GROUP BY b.id 
    ORDER BY b.sort_order ASC, b.id ASC
");
$stmtBrands->execute();
$brands = $stmtBrands->fetchAll();

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
                <h2 class="text-lg font-black text-app-title brand-font">Brands Management</h2>
                <p class="text-xs text-app-muted">Manage charging brands (Anker, Ugreen, Baseus, etc.) with official logos.</p>
            </div>
            <button onclick="openAddModal()" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-all shadow-md flex items-center space-x-1.5 self-start sm:self-auto">
                <span>➕</span>
                <span>Add New Brand</span>
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

        <!-- BRANDS LIST TABLE (Concise & Compact) -->
        <div class="rounded-xl bg-app-card border border-app overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-500/5 text-app-muted font-bold text-[10px] uppercase tracking-wider border-b border-app">
                        <tr>
                            <th class="py-2.5 px-3.5 w-16">Logo</th>
                            <th class="py-2.5 px-3.5">Brand Name</th>
                            <th class="py-2.5 px-3.5">Slug</th>
                            <th class="py-2.5 px-3.5">Website</th>
                            <th class="py-2.5 px-3.5">Products</th>
                            <th class="py-2.5 px-3.5">Sort</th>
                            <th class="py-2.5 px-3.5">Status</th>
                            <th class="py-2.5 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app">
                        <?php if (empty($brands)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-app-muted">No brands found. Click "+ Add New Brand" to create one!</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($brands as $brand): ?>
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <!-- Logo Preview -->
                                <td class="py-2.5 px-3.5">
                                    <div class="w-11 h-11 rounded-lg bg-slate-500/10 border border-app p-1 flex items-center justify-center overflow-hidden shrink-0">
                                        <?php 
                                        $lUrl = $brand['logo_url'] ?? '';
                                        $lSrc = (!empty($lUrl) && !preg_match('#^https?://#i', $lUrl) && !str_starts_with($lUrl, '/')) ? ('../' . $lUrl) : $lUrl;
                                        ?>
                                        <?php if (!empty($lUrl)): ?>
                                        <img src="<?= e($lSrc) ?>" alt="<?= e($brand['name']) ?>" class="w-full h-full object-contain">
                                        <?php else: ?>
                                        <span class="text-sm">🏷️</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Brand Name & Description -->
                                <td class="py-2.5 px-3.5">
                                    <div class="font-bold text-app-title text-sm"><?= e($brand['name']) ?></div>
                                    <div class="text-[11px] text-app-muted line-clamp-1 max-w-xs"><?= e($brand['description'] ?? 'Official manufacturer partner') ?></div>
                                </td>

                                <!-- Slug -->
                                <td class="py-2.5 px-3.5 font-mono text-[11px] text-app-muted">
                                    <?= e($brand['slug']) ?>
                                </td>

                                <!-- Website -->
                                <td class="py-2.5 px-3.5">
                                    <?php if (!empty($brand['website'])): ?>
                                    <a href="<?= e($brand['website']) ?>" target="_blank" class="text-blue-500 hover:underline flex items-center space-x-1">
                                        <span class="truncate max-w-[130px]"><?= e(parse_url($brand['website'], PHP_URL_HOST) ?: $brand['website']) ?></span>
                                        <span class="text-[10px]">↗</span>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-app-muted text-[11px]">&mdash;</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Products Count -->
                                <td class="py-2.5 px-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-500">
                                        <?= (int)$brand['product_count'] ?> items
                                    </span>
                                </td>

                                <!-- Sort Order -->
                                <td class="py-2.5 px-3.5 font-mono text-app-muted">
                                    #<?= (int)$brand['sort_order'] ?>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-2.5 px-3.5">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $brand['id'] ?>">
                                        <button type="submit" class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-colors <?= $brand['is_active'] ? 'bg-emerald-500/15 text-emerald-500 hover:bg-emerald-500/25' : 'bg-slate-500/15 text-app-muted hover:bg-slate-500/25' ?>">
                                            <?= $brand['is_active'] ? '● Active' : '○ Inactive' ?>
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="py-2.5 px-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($brand), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete brand \'<?= e($brand['name']) ?>\'?');">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $brand['id'] ?>">
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
        </div>

    </main>

</div>

<!-- ADD BRAND MODAL -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-app-card border border-app rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-app">
            <h3 class="text-sm font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                <span>➕</span>
                <span>Add New Brand</span>
            </h3>
            <button onclick="closeAddModal()" class="text-app-muted hover:text-app-title text-base">&times;</button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Brand Name *</label>
                <input type="text" name="name" required placeholder="Brand name" class="w-full px-3 py-2 rounded-xl input-app border text-xs" oninput="document.getElementById('add-slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Slug</label>
                <input type="text" id="add-slug" name="slug" placeholder="brand-slug" class="w-full px-3 py-2 rounded-xl input-app border text-xs font-mono">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Brand Logo (Upload File)</label>
                <input type="file" name="brand_logo" accept="image/*" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                <span class="text-[10px] text-app-muted mt-0.5 block">Or enter logo image URL path:</span>
                <input type="text" name="custom_logo_url" placeholder="images/..." class="w-full mt-1 px-3 py-1.5 rounded-xl input-app border text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Official Website</label>
                <input type="url" name="website" placeholder="https://..." class="w-full px-3 py-2 rounded-xl input-app border text-xs">
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
                <button type="submit" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md">Create Brand</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT BRAND MODAL -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-app-card border border-app rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-app">
            <h3 class="text-sm font-black text-app-title uppercase tracking-wider flex items-center space-x-1.5">
                <span>✏️</span>
                <span>Edit Brand</span>
            </h3>
            <button onclick="closeEditModal()" class="text-app-muted hover:text-app-title text-base">&times;</button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-id" name="id" value="">
            <input type="hidden" id="edit-existing-logo" name="existing_logo_url" value="">

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Brand Name *</label>
                <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 rounded-xl input-app border text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Slug</label>
                <input type="text" id="edit-slug" name="slug" class="w-full px-3 py-2 rounded-xl input-app border text-xs font-mono">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Current Logo</label>
                <div class="flex items-center space-x-2.5 mb-2">
                    <img id="edit-logo-preview" src="" alt="" class="w-10 h-10 object-contain rounded-lg border border-app p-1 bg-slate-500/10">
                    <span id="edit-logo-label" class="text-[11px] text-app-muted truncate font-mono"></span>
                </div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Replace Logo (Upload File)</label>
                <input type="file" name="brand_logo" accept="image/*" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                <input type="text" id="edit-custom-logo" name="custom_logo_url" placeholder="Or enter new logo path..." class="w-full mt-1.5 px-3 py-1.5 rounded-xl input-app border text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-app-muted mb-1">Official Website</label>
                <input type="url" id="edit-website" name="website" class="w-full px-3 py-2 rounded-xl input-app border text-xs">
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
        document.getElementById('add-modal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
    }

    function openEditModal(brand) {
        document.getElementById('edit-id').value = brand.id;
        document.getElementById('edit-name').value = brand.name;
        document.getElementById('edit-slug').value = brand.slug;
        document.getElementById('edit-website').value = brand.website || '';
        document.getElementById('edit-description').value = brand.description || '';
        document.getElementById('edit-sort').value = brand.sort_order;
        document.getElementById('edit-active').checked = (parseInt(brand.is_active) === 1);
        document.getElementById('edit-existing-logo').value = brand.logo_url || '';
        document.getElementById('edit-custom-logo').value = '';
        
        const preview = document.getElementById('edit-logo-preview');
        const label = document.getElementById('edit-logo-label');
        if (brand.logo_url) {
            preview.src = (brand.logo_url && /^https?:\/\//i.test(brand.logo_url)) ? brand.logo_url : ('../' + brand.logo_url);
            preview.classList.remove('hidden');
            label.textContent = brand.logo_url;
        } else {
            preview.classList.add('hidden');
            label.textContent = 'No logo set';
        }

        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
