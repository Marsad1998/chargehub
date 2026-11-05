<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$active_tab = $_GET['tab'] ?? 'hero';
if (!in_array($active_tab, ['hero', 'spotlight', 'new_arrivals', 'flash_sale'])) {
    $active_tab = 'hero';
}

$success_msg = '';
$error_msg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error_msg = 'Security token invalid (CSRF failure). Action blocked.';
    } else {

        // =============================================================
        // HERO SECTION ACTIONS
        // =============================================================
        if ($action === 'add_hero') {
            $title     = trim($_POST['title'] ?? '');
            $subtitle  = trim($_POST['subtitle'] ?? '');
            $badge     = trim($_POST['badge_text'] ?? '');
            $color     = trim($_POST['nav_color'] ?? '#0052cc');
            $cta_link  = trim($_POST['cta_link'] ?? 'explore.php');
            $cta_text  = trim($_POST['cta_text'] ?? 'Explore Catalog');
            $sort      = (int)($_POST['sort_order'] ?? 1);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $img_url   = '';

            // Handle file upload if provided
            if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                $upload = handle_image_upload($_FILES['hero_image'], __DIR__ . '/../uploads/hero', 'hero_');
                if ($upload['success']) {
                    $img_url = 'uploads/hero/' . $upload['filename'];
                } else {
                    $error_msg = 'Upload failed: ' . $upload['error'];
                }
            } elseif (!empty($_POST['custom_image_url'])) {
                $img_url = trim($_POST['custom_image_url']);
            } else {
                $img_url = 'images/Hero Banner.jpg'; // default fallback
            }

            if (empty($error_msg) && !empty($title)) {
                // Prepared statement to prevent SQL injection
                $stmt = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, badge_text, image_url, nav_color, cta_link, cta_text, sort_order, is_active) VALUES (:title, :sub, :badge, :img, :color, :link, :cta, :sort, :active)");
                $stmt->execute([
                    ':title'  => $title,
                    ':sub'    => $subtitle,
                    ':badge'  => $badge,
                    ':img'    => $img_url,
                    ':color'  => $color,
                    ':link'   => $cta_link,
                    ':cta'    => $cta_text,
                    ':sort'   => $sort,
                    ':active' => $is_active
                ]);
                $success_msg = 'Hero banner slide successfully added!';
            } elseif (empty($title)) {
                $error_msg = 'Please provide a title for the hero slide.';
            }

        } elseif ($action === 'edit_hero') {
            $id        = (int)($_POST['id'] ?? 0);
            $title     = trim($_POST['title'] ?? '');
            $subtitle  = trim($_POST['subtitle'] ?? '');
            $badge     = trim($_POST['badge_text'] ?? '');
            $color     = trim($_POST['nav_color'] ?? '#0052cc');
            $cta_link  = trim($_POST['cta_link'] ?? 'explore.php');
            $cta_text  = trim($_POST['cta_text'] ?? 'Explore Catalog');
            $sort      = (int)($_POST['sort_order'] ?? 1);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            // Fetch existing to keep old image if no new file is provided
            $stmtEx = $pdo->prepare("SELECT image_url FROM hero_slides WHERE id = :id");
            $stmtEx->execute([':id' => $id]);
            $existing = $stmtEx->fetch();

            if ($existing) {
                $img_url = $existing['image_url'];

                if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = handle_image_upload($_FILES['hero_image'], __DIR__ . '/../uploads/hero', 'hero_');
                    if ($upload['success']) {
                        $img_url = 'uploads/hero/' . $upload['filename'];
                    } else {
                        $error_msg = 'Upload failed: ' . $upload['error'];
                    }
                } elseif (!empty($_POST['custom_image_url'])) {
                    $img_url = trim($_POST['custom_image_url']);
                }

                if (empty($error_msg)) {
                    $stmt = $pdo->prepare("UPDATE hero_slides SET title = :title, subtitle = :sub, badge_text = :badge, image_url = :img, nav_color = :color, cta_link = :link, cta_text = :cta, sort_order = :sort, is_active = :active WHERE id = :id");
                    $stmt->execute([
                        ':title'  => $title,
                        ':sub'    => $subtitle,
                        ':badge'  => $badge,
                        ':img'    => $img_url,
                        ':color'  => $color,
                        ':link'   => $cta_link,
                        ':cta'    => $cta_text,
                        ':sort'   => $sort,
                        ':active' => $is_active,
                        ':id'     => $id
                    ]);
                    $success_msg = 'Hero banner slide updated successfully!';
                }
            } else {
                $error_msg = 'Slide not found.';
            }

        } elseif ($action === 'delete_hero') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $success_msg = 'Hero banner slide deleted successfully!';

        } elseif ($action === 'toggle_hero') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE hero_slides SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $success_msg = 'Hero slide status toggled!';

        // =============================================================
        // PRODUCT SPOTLIGHT ACTIONS
        // =============================================================
        } elseif ($action === 'add_spotlight') {
            $title     = trim($_POST['title'] ?? '');
            $badge     = trim($_POST['badge_text'] ?? 'HOT DEAL');
            $link      = trim($_POST['link_url'] ?? 'product-detail.php');
            $sort      = (int)($_POST['sort_order'] ?? 1);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $img_url   = '';

            if (isset($_FILES['spotlight_image']) && $_FILES['spotlight_image']['error'] === UPLOAD_ERR_OK) {
                $upload = handle_image_upload($_FILES['spotlight_image'], __DIR__ . '/../uploads/spotlight', 'spot_');
                if ($upload['success']) {
                    $img_url = 'uploads/spotlight/' . $upload['filename'];
                } else {
                    $error_msg = 'Upload failed: ' . $upload['error'];
                }
            } elseif (!empty($_POST['custom_image_url'])) {
                $img_url = trim($_POST['custom_image_url']);
            } else {
                $img_url = 'images/Spotlight Section/1.png';
            }

            if (empty($error_msg) && !empty($title)) {
                $stmt = $pdo->prepare("INSERT INTO spotlight_items (title, image_url, link_url, badge_text, sort_order, is_active) VALUES (:title, :img, :link, :badge, :sort, :active)");
                $stmt->execute([
                    ':title'  => $title,
                    ':img'    => $img_url,
                    ':link'   => $link,
                    ':badge'  => $badge,
                    ':sort'   => $sort,
                    ':active' => $is_active
                ]);
                $success_msg = 'Product spotlight image added successfully!';
            } elseif (empty($title)) {
                $error_msg = 'Please enter a title for the spotlight product.';
            }

        } elseif ($action === 'edit_spotlight') {
            $id        = (int)($_POST['id'] ?? 0);
            $title     = trim($_POST['title'] ?? '');
            $badge     = trim($_POST['badge_text'] ?? 'HOT DEAL');
            $link      = trim($_POST['link_url'] ?? 'product-detail.php');
            $sort      = (int)($_POST['sort_order'] ?? 1);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $stmtEx = $pdo->prepare("SELECT image_url FROM spotlight_items WHERE id = :id");
            $stmtEx->execute([':id' => $id]);
            $existing = $stmtEx->fetch();

            if ($existing) {
                $img_url = $existing['image_url'];

                if (isset($_FILES['spotlight_image']) && $_FILES['spotlight_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = handle_image_upload($_FILES['spotlight_image'], __DIR__ . '/../uploads/spotlight', 'spot_');
                    if ($upload['success']) {
                        $img_url = 'uploads/spotlight/' . $upload['filename'];
                    } else {
                        $error_msg = 'Upload failed: ' . $upload['error'];
                    }
                } elseif (!empty($_POST['custom_image_url'])) {
                    $img_url = trim($_POST['custom_image_url']);
                }

                if (empty($error_msg)) {
                    $stmt = $pdo->prepare("UPDATE spotlight_items SET title = :title, image_url = :img, link_url = :link, badge_text = :badge, sort_order = :sort, is_active = :active WHERE id = :id");
                    $stmt->execute([
                        ':title'  => $title,
                        ':img'    => $img_url,
                        ':link'   => $link,
                        ':badge'  => $badge,
                        ':sort'   => $sort,
                        ':active' => $is_active,
                        ':id'     => $id
                    ]);
                    $success_msg = 'Product spotlight image updated successfully!';
                }
            } else {
                $error_msg = 'Spotlight item not found.';
            }

        } elseif ($action === 'delete_spotlight') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM spotlight_items WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $success_msg = 'Product spotlight item deleted!';

        } elseif ($action === 'toggle_spotlight') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE spotlight_items SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $success_msg = 'Spotlight item status toggled!';

        // =============================================================
        // NEW ARRIVALS ACTIONS
        // =============================================================
        } elseif ($action === 'add_new_arrival') {
            $title          = trim($_POST['title'] ?? '');
            $sku            = trim($_POST['sku'] ?? '');
            $badge          = trim($_POST['badge_text'] ?? 'NEW ARRIVAL');
            $link           = trim($_POST['link_url'] ?? 'product-detail.php');
            $price          = (float)($_POST['price'] ?? 0);
            $orig_price     = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
            $discount_pct   = (int)($_POST['discount_pct'] ?? 0);
            if ($orig_price && $orig_price > $price && $discount_pct == 0) {
                $discount_pct = round((($orig_price - $price) / $orig_price) * 100);
            }
            $rating         = !empty($_POST['rating']) ? (float)$_POST['rating'] : 0.0;
            $spec_1_title   = trim($_POST['spec_1_title'] ?? '');
            $spec_1_sub     = trim($_POST['spec_1_sub'] ?? '');
            $spec_2_title   = trim($_POST['spec_2_title'] ?? '');
            $spec_2_sub     = trim($_POST['spec_2_sub'] ?? '');
            $spec_3_title   = trim($_POST['spec_3_title'] ?? '');
            $spec_3_sub     = trim($_POST['spec_3_sub'] ?? '');
            $sort           = (int)($_POST['sort_order'] ?? 1);
            $is_active      = isset($_POST['is_active']) ? 1 : 0;
            $img_url        = '';

            if (isset($_FILES['new_arrival_image']) && $_FILES['new_arrival_image']['error'] === UPLOAD_ERR_OK) {
                $upload = handle_image_upload($_FILES['new_arrival_image'], __DIR__ . '/../uploads/spotlight', 'arrival_');
                if ($upload['success']) {
                    $img_url = 'uploads/spotlight/' . $upload['filename'];
                } else {
                    $error_msg = 'Upload failed: ' . $upload['error'];
                }
            } elseif (!empty($_POST['custom_image_url'])) {
                $img_url = trim($_POST['custom_image_url']);
            } else {
                $img_url = 'images/1000x1000 transparent PNG/1.png';
            }

            if (empty($error_msg) && !empty($title)) {
                $stmt = $pdo->prepare("INSERT INTO new_arrival_items 
                    (title, sku, image_url, link_url, price, original_price, discount_pct, rating, badge_text, spec_1_title, spec_1_sub, spec_2_title, spec_2_sub, spec_3_title, spec_3_sub, sort_order, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title, $sku, $img_url, $link, $price, $orig_price, $discount_pct, $rating, $badge,
                    $spec_1_title, $spec_1_sub, $spec_2_title, $spec_2_sub, $spec_3_title, $spec_3_sub, $sort, $is_active
                ]);
                $success_msg = 'New Arrival product successfully added!';
            } elseif (empty($title)) {
                $error_msg = 'Please enter a title for the new arrival product.';
            }

        } elseif ($action === 'edit_new_arrival') {
            $id             = (int)($_POST['id'] ?? 0);
            $title          = trim($_POST['title'] ?? '');
            $sku            = trim($_POST['sku'] ?? '');
            $badge          = trim($_POST['badge_text'] ?? 'NEW ARRIVAL');
            $link           = trim($_POST['link_url'] ?? 'product-detail.php');
            $price          = (float)($_POST['price'] ?? 0);
            $orig_price     = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
            $discount_pct   = (int)($_POST['discount_pct'] ?? 0);
            if ($orig_price && $orig_price > $price && $discount_pct == 0) {
                $discount_pct = round((($orig_price - $price) / $orig_price) * 100);
            }
            $rating         = !empty($_POST['rating']) ? (float)$_POST['rating'] : 0.0;
            $spec_1_title   = trim($_POST['spec_1_title'] ?? '');
            $spec_1_sub     = trim($_POST['spec_1_sub'] ?? '');
            $spec_2_title   = trim($_POST['spec_2_title'] ?? '');
            $spec_2_sub     = trim($_POST['spec_2_sub'] ?? '');
            $spec_3_title   = trim($_POST['spec_3_title'] ?? '');
            $spec_3_sub     = trim($_POST['spec_3_sub'] ?? '');
            $sort           = (int)($_POST['sort_order'] ?? 1);
            $is_active      = isset($_POST['is_active']) ? 1 : 0;

            $stmtEx = $pdo->prepare("SELECT image_url FROM new_arrival_items WHERE id = ?");
            $stmtEx->execute([$id]);
            $existing = $stmtEx->fetch();

            if ($existing) {
                $img_url = $existing['image_url'];

                if (isset($_FILES['new_arrival_image']) && $_FILES['new_arrival_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = handle_image_upload($_FILES['new_arrival_image'], __DIR__ . '/../uploads/spotlight', 'arrival_');
                    if ($upload['success']) {
                        $img_url = 'uploads/spotlight/' . $upload['filename'];
                    } else {
                        $error_msg = 'Upload failed: ' . $upload['error'];
                    }
                } elseif (!empty($_POST['custom_image_url'])) {
                    $img_url = trim($_POST['custom_image_url']);
                }

                if (empty($error_msg)) {
                    $stmt = $pdo->prepare("UPDATE new_arrival_items SET 
                        title = ?, sku = ?, image_url = ?, link_url = ?, price = ?, original_price = ?, discount_pct = ?, rating = ?, badge_text = ?,
                        spec_1_title = ?, spec_1_sub = ?, spec_2_title = ?, spec_2_sub = ?, spec_3_title = ?, spec_3_sub = ?, sort_order = ?, is_active = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $title, $sku, $img_url, $link, $price, $orig_price, $discount_pct, $rating, $badge,
                        $spec_1_title, $spec_1_sub, $spec_2_title, $spec_2_sub, $spec_3_title, $spec_3_sub, $sort, $is_active, $id
                    ]);
                    $success_msg = 'New Arrival product updated successfully!';
                }
            } else {
                $error_msg = 'Item not found.';
            }

        } elseif ($action === 'delete_new_arrival') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM new_arrival_items WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = 'New Arrival product deleted successfully!';

        } elseif ($action === 'toggle_new_arrival') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE new_arrival_items SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = 'New Arrival item status toggled!';

        // =============================================================
        // FLASH SALE DEALS ACTIONS
        // =============================================================
        } elseif ($action === 'add_flash_sale') {
            $title          = trim($_POST['title'] ?? '');
            $ribbon_text    = trim($_POST['ribbon_text'] ?? '10.10 sale is live');
            $discount_text  = trim($_POST['discount_text'] ?? '14% Off');
            $link           = trim($_POST['link_url'] ?? 'product-detail.php');
            $price          = (float)($_POST['price'] ?? 0);
            $orig_price     = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
            $color_swatch   = trim($_POST['color_swatch'] ?? '#000000');
            $sort           = (int)($_POST['sort_order'] ?? 1);
            $is_active      = isset($_POST['is_active']) ? 1 : 0;
            $img_url        = '';

            if (isset($_FILES['flash_sale_image']) && $_FILES['flash_sale_image']['error'] === UPLOAD_ERR_OK) {
                $upload = handle_image_upload($_FILES['flash_sale_image'], __DIR__ . '/../uploads/spotlight', 'flash_');
                if ($upload['success']) {
                    $img_url = 'uploads/spotlight/' . $upload['filename'];
                } else {
                    $error_msg = 'Upload failed: ' . $upload['error'];
                }
            } elseif (!empty($_POST['custom_image_url'])) {
                $img_url = trim($_POST['custom_image_url']);
            } else {
                $img_url = 'images/v7/flash_sale/l265_ronaq.png';
            }

            if (empty($error_msg) && !empty($title)) {
                $stmt = $pdo->prepare("INSERT INTO flash_sale_items 
                    (title, ribbon_text, discount_text, image_url, link_url, price, original_price, color_swatch, sort_order, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title, $ribbon_text, $discount_text, $img_url, $link, $price, $orig_price, $color_swatch, $sort, $is_active
                ]);
                $success_msg = 'Flash Sale deal successfully added!';
            } elseif (empty($title)) {
                $error_msg = 'Please enter a title for the flash sale deal.';
            }

        } elseif ($action === 'edit_flash_sale') {
            $id             = (int)($_POST['id'] ?? 0);
            $title          = trim($_POST['title'] ?? '');
            $ribbon_text    = trim($_POST['ribbon_text'] ?? '10.10 sale is live');
            $discount_text  = trim($_POST['discount_text'] ?? '14% Off');
            $link           = trim($_POST['link_url'] ?? 'product-detail.php');
            $price          = (float)($_POST['price'] ?? 0);
            $orig_price     = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
            $color_swatch   = trim($_POST['color_swatch'] ?? '#000000');
            $sort           = (int)($_POST['sort_order'] ?? 1);
            $is_active      = isset($_POST['is_active']) ? 1 : 0;

            $stmtEx = $pdo->prepare("SELECT image_url FROM flash_sale_items WHERE id = ?");
            $stmtEx->execute([$id]);
            $existing = $stmtEx->fetch();

            if ($existing) {
                $img_url = $existing['image_url'];

                if (isset($_FILES['flash_sale_image']) && $_FILES['flash_sale_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = handle_image_upload($_FILES['flash_sale_image'], __DIR__ . '/../uploads/spotlight', 'flash_');
                    if ($upload['success']) {
                        $img_url = 'uploads/spotlight/' . $upload['filename'];
                    } else {
                        $error_msg = 'Upload failed: ' . $upload['error'];
                    }
                } elseif (!empty($_POST['custom_image_url'])) {
                    $img_url = trim($_POST['custom_image_url']);
                }

                if (empty($error_msg)) {
                    $stmt = $pdo->prepare("UPDATE flash_sale_items SET 
                        title = ?, ribbon_text = ?, discount_text = ?, image_url = ?, link_url = ?, price = ?, original_price = ?, color_swatch = ?, sort_order = ?, is_active = ?
                        WHERE id = ?");
                    $stmt->execute([
                        $title, $ribbon_text, $discount_text, $img_url, $link, $price, $orig_price, $color_swatch, $sort, $is_active, $id
                    ]);
                    $success_msg = 'Flash Sale deal updated successfully!';
                }
            } else {
                $error_msg = 'Item not found.';
            }

        } elseif ($action === 'delete_flash_sale') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM flash_sale_items WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = 'Flash Sale deal deleted successfully!';

        } elseif ($action === 'toggle_flash_sale') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE flash_sale_items SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
            $stmt->execute([$id]);
            $success_msg = 'Flash Sale deal status toggled!';
        }

        if (!empty($success_msg)) {
            set_flash_message('success', $success_msg);
            admin_redirect('cms.php?tab=' . urlencode($active_tab));
        } elseif (!empty($error_msg)) {
            set_flash_message('error', $error_msg);
            admin_redirect('cms.php?tab=' . urlencode($active_tab));
        }
    }
}

$page_title = 'CMS &bull; Media & Section Manager';
require_once __DIR__ . '/includes/header.php';

// Consume any session flash messages
foreach (get_flash_messages() as $fl) {
    if ($fl['type'] === 'success') {
        $success_msg = $fl['text'];
    } elseif ($fl['type'] === 'error') {
        $error_msg = $fl['text'];
    }
}

// Fetch all slides & spotlight items using prepared queries
$stmtHeroes = $pdo->prepare("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC");
$stmtHeroes->execute();
$hero_slides = $stmtHeroes->fetchAll();

$stmtSpots = $pdo->prepare("SELECT * FROM spotlight_items ORDER BY sort_order ASC, id ASC");
$stmtSpots->execute();
$spotlight_items = $stmtSpots->fetchAll();

// Fetch new arrival items
$stmtNewArrivals = $pdo->prepare("SELECT * FROM new_arrival_items ORDER BY sort_order ASC, id ASC");
$stmtNewArrivals->execute();
$new_arrival_items = $stmtNewArrivals->fetchAll();

// Fetch flash sale items
$stmtFlashSales = $pdo->prepare("SELECT * FROM flash_sale_items ORDER BY sort_order ASC, id ASC");
$stmtFlashSales->execute();
$flash_sale_items = $stmtFlashSales->fetchAll();

// Fetch active products for slug-based link selection
$stmtProducts = $pdo->prepare("SELECT id, name, slug, sku, badge_text, main_image, price, original_price FROM products WHERE is_active = 1 ORDER BY name ASC");
$stmtProducts->execute();
$products_list = $stmtProducts->fetchAll();

// Pagination for CMS tabs
$cms_page = max(1, (int)($_GET['page'] ?? 1));
$cms_per_page = 10;
$hero_slides_paged = array_slice($hero_slides, ($cms_page - 1) * $cms_per_page, $cms_per_page);
$spotlight_items_paged = array_slice($spotlight_items, ($cms_page - 1) * $cms_per_page, $cms_per_page);
$new_arrival_items_paged = array_slice($new_arrival_items, ($cms_page - 1) * $cms_per_page, $cms_per_page);
$flash_sale_items_paged = array_slice($flash_sale_items, ($cms_page - 1) * $cms_per_page, $cms_per_page);

?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">

    <!-- TOPBAR -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- MAIN BODY WITH CMS TABS (Concise Container) -->
    <main class="p-4 sm:p-6 space-y-4 flex-1 max-w-[1140px] mx-auto w-full">

        <!-- FLASH MESSAGES -->
        <div>
            <?php if (!empty($success_msg)): ?>
                <div class="flash-alert p-3.5 mb-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-xs font-bold flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-base">✓</span>
                        <span><?= e($success_msg) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-app-title">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="flash-alert p-3.5 mb-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500 text-xs font-bold flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-base">⚠️</span>
                        <span><?= e($error_msg) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-app-title">&times;</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- CMS TABS SWITCHER -->
        <div class="flex border-b border-app/80 space-x-2 overflow-x-auto no-scrollbar">
            <a href="cms.php?tab=hero" class="px-5 py-3 text-xs font-black uppercase tracking-wider flex items-center space-x-2 border-b-2 transition-all whitespace-nowrap <?= ($active_tab === 'hero') ? 'border-blue-500 text-blue-400 bg-blue-500/5' : 'border-transparent text-app-muted hover:text-slate-200 hover:border-app' ?>">
                <span>🖼️</span>
                <span>Hero Section Images (<?= count($hero_slides) ?>)</span>
            </a>
            <a href="cms.php?tab=spotlight" class="px-5 py-3 text-xs font-black uppercase tracking-wider flex items-center space-x-2 border-b-2 transition-all whitespace-nowrap <?= ($active_tab === 'spotlight') ? 'border-orange-500 text-orange-400 bg-orange-500/5' : 'border-transparent text-app-muted hover:text-slate-200 hover:border-app' ?>">
                <span>⚡</span>
                <span>Product Spotlight Images (<?= count($spotlight_items) ?>)</span>
            </a>
            <a href="cms.php?tab=new_arrivals" class="px-5 py-3 text-xs font-black uppercase tracking-wider flex items-center space-x-2 border-b-2 transition-all whitespace-nowrap <?= ($active_tab === 'new_arrivals') ? 'border-purple-500 text-purple-400 bg-purple-500/5' : 'border-transparent text-app-muted hover:text-slate-200 hover:border-app' ?>">
                <span>🔥</span>
                <span>New Arrivals Section (<?= count($new_arrival_items) ?>)</span>
            </a>
            <a href="cms.php?tab=flash_sale" class="px-5 py-3 text-xs font-black uppercase tracking-wider flex items-center space-x-2 border-b-2 transition-all whitespace-nowrap <?= ($active_tab === 'flash_sale') ? 'border-amber-500 text-amber-400 bg-amber-500/5' : 'border-transparent text-app-muted hover:text-slate-200 hover:border-app' ?>">
                <span>⚡</span>
                <span>Flash Sale Deals (<?= count($flash_sale_items) ?>)</span>
            </a>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: HERO SECTION IMAGES & BANNER MANAGEMENT -->
        <!-- ============================================================= -->
        <?php if ($active_tab === 'hero'): ?>
            <div class="space-y-6">

                <!-- Header and Add Button -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-app-title brand-font">Hero Section Banners</h2>
                        <p class="text-xs text-app-muted">Manage the flagship banners displayed at the top of the homepage</p>
                    </div>
                    <button onclick="openCmsModal('add-hero-modal')" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-app-title text-xs font-bold transition-all shadow-lg shadow-blue-600/30 flex items-center space-x-1.5 self-start">
                        <span>＋</span>
                        <span>Upload New Hero Image</span>
                    </button>
                </div>

                <!-- Quick Search Filter for Hero Slides -->
                <div class="relative w-full sm:max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                    <input type="text" oninput="filterSectionCards(this, 'hero-cards-grid')" placeholder="Filter hero slides by title, slug, or badge..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-app-card border border-app text-app-title text-xs placeholder-app-muted focus:outline-none focus:border-blue-500">
                </div>

                <!-- Hero Slides Table / Cards Grid -->
                <div id="hero-cards-grid" class="grid grid-cols-1 gap-4">
                    <?php foreach ($hero_slides_paged as $slide): ?>
                        <div class="p-5 rounded-2xl bg-app-card border border-app flex flex-col md:flex-row items-start md:items-center justify-between gap-6 transition-all hover:border-app">
                            
                            <!-- Thumbnail & Info -->
                            <div class="flex items-center space-x-4 min-w-0 flex-1">
                                <div class="w-28 sm:w-36 aspect-[16/9] rounded-xl overflow-hidden bg-slate-950 border border-app shrink-0 relative group">
                                    <img src="../<?= e($slide['image_url']) ?>" alt="<?= e($slide['title']) ?>" class="w-full h-full object-cover">
                                    <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded text-[9px] font-black text-app-title" style="background-color: <?= e($slide['nav_color']) ?>;">
                                        <?= e($slide['nav_color']) ?>
                                    </span>
                                </div>
                                <div class="min-w-0 space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 rounded text-[9.5px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                            <?= e($slide['badge_text'] ?: 'Hero Slide') ?>
                                        </span>
                                        <span class="text-xs text-slate-500 font-semibold">Order: #<?= (int)$slide['sort_order'] ?></span>
                                    </div>
                                    <h3 class="text-sm font-bold text-app-title truncate"><?= e($slide['title']) ?></h3>
                                    <p class="text-xs text-app-muted line-clamp-1 max-w-lg"><?= e($slide['subtitle']) ?></p>
                                    <div class="text-[11px] text-slate-500 truncate">
                                        <span>File: <code class="text-app-muted"><?= e($slide['image_url']) ?></code></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2 shrink-0 self-end md:self-center">
                                <!-- Status Badge Toggle -->
                                <form method="POST" action="cms.php?tab=hero" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_hero">
                                    <input type="hidden" name="id" value="<?= (int)$slide['id'] ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors <?= $slide['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-slate-800 text-app-muted hover:text-app-title' ?>" title="Click to toggle active status">
                                        <?= $slide['is_active'] ? '● Active' : '○ Inactive' ?>
                                    </button>
                                </form>

                                <!-- Edit Trigger Button -->
                                <button onclick="openEditHeroModal(<?= htmlspecialchars(json_encode($slide), ENT_QUOTES, 'UTF-8') ?>)" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-colors">
                                    Edit
                                </button>

                                <!-- Delete Form -->
                                <form method="POST" action="cms.php?tab=hero" class="inline" onsubmit="return confirm('Are you sure you want to delete this hero slide?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_hero">
                                    <input type="hidden" name="id" value="<?= (int)$slide['id'] ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-bold transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Hero Section Pagination -->
                <?= render_admin_pagination(count($hero_slides), $cms_per_page, $cms_page) ?>

            </div>
        <?php endif; ?>

        <!-- TAB 2: PRODUCT SPOTLIGHT SECTION IMAGES MANAGEMENT -->
        <!-- ============================================================= -->
        <?php if ($active_tab === 'spotlight'): ?>
            <div class="space-y-6">

                <!-- Header and Add Button -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-app-title brand-font">Product Spotlight Section Images</h2>
                        <p class="text-xs text-app-muted">Control the 1000x1000 transparent PNGs displayed in the rotating 3D spotlight carousel</p>
                    </div>
                    <button onclick="openCmsModal('add-spotlight-modal')" class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-app-title text-xs font-bold transition-all shadow-lg shadow-orange-600/30 flex items-center space-x-1.5 self-start">
                        <span>＋</span>
                        <span>Upload New Spotlight Product</span>
                    </button>
                </div>

                <!-- Quick Search Filter for Spotlight Cards -->
                <div class="relative w-full sm:max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                    <input type="text" oninput="filterSectionCards(this, 'spotlight-cards-grid')" placeholder="Filter spotlight items by title, slug, or badge..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-app-card border border-app text-app-title text-xs placeholder-app-muted focus:outline-none focus:border-orange-500">
                </div>

                <!-- Spotlight Cards Grid -->
                <div id="spotlight-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($spotlight_items_paged as $item): ?>
                        <div class="p-5 rounded-2xl bg-app-card border border-app space-y-4 transition-all hover:border-app flex flex-col justify-between">
                            
                            <div>
                                <!-- Image Preview Box (Checkered/Dark to show transparency) -->
                                <div class="w-full aspect-[4/3] rounded-xl bg-slate-950/80 border border-app/80 p-4 flex items-center justify-center relative overflow-hidden mb-3">
                                    <img src="../<?= e($item['image_url']) ?>" alt="<?= e($item['title']) ?>" class="max-h-full max-w-full object-contain filter drop-shadow-md">
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-black uppercase bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                        <?= e($item['badge_text']) ?>
                                    </span>
                                    <span class="absolute top-2 right-2 px-1.5 py-0.5 rounded text-[10px] font-bold text-app-muted input-app border border-app">
                                        #<?= (int)$item['sort_order'] ?>
                                    </span>
                                </div>

                                <h3 class="text-sm font-bold text-app-title truncate"><?= e($item['title']) ?></h3>
                                <div class="text-[11px] text-app-muted truncate mt-1">
                                    <span>File: <code class="text-app-body"><?= e($item['image_url']) ?></code></span>
                                </div>
                            </div>

                            <!-- Card Bottom Controls -->
                            <div class="pt-3 border-t border-app/80 flex items-center justify-between">
                                <form method="POST" action="cms.php?tab=spotlight" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_spotlight">
                                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                    <button type="submit" class="px-2.5 py-1 rounded-md text-[11px] font-bold transition-colors <?= $item['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-app-muted' ?>">
                                        <?= $item['is_active'] ? '● Active' : '○ Inactive' ?>
                                    </button>
                                </form>

                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditSpotlightModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                                        Edit
                                    </button>
                                    <form method="POST" action="cms.php?tab=spotlight" class="inline" onsubmit="return confirm('Delete this spotlight image?');">
                                        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_spotlight">
                                        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-md bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Spotlight Section Pagination -->
                <?= render_admin_pagination(count($spotlight_items), $cms_per_page, $cms_page) ?>

            </div>
        <?php endif; ?>

        <!-- TAB 3: NEW ARRIVALS CAROUSEL SECTION -->
        <!-- ============================================================= -->
        <?php if ($active_tab === 'new_arrivals'): ?>
            <div class="space-y-6">

                <!-- Header and Add Button -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-app-title brand-font">New Arrivals Carousel Section</h2>
                        <p class="text-xs text-app-muted">Manage the sculpted pop-out product cards displayed in the New Arrivals carousel on the homepage</p>
                    </div>
                    <button onclick="openCmsModal('add-new-arrival-modal')" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-app-title text-xs font-bold transition-all shadow-lg shadow-purple-600/30 flex items-center space-x-1.5 self-start">
                        <span>＋</span>
                        <span>Add New Arrival Item</span>
                    </button>
                </div>

                <!-- Quick Search Filter for New Arrival Cards -->
                <div class="relative w-full sm:max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                    <input type="text" oninput="filterSectionCards(this, 'arrivals-cards-grid')" placeholder="Filter new arrivals by title, SKU, or specs..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-app-card border border-app text-app-title text-xs placeholder-app-muted focus:outline-none focus:border-purple-500">
                </div>

                <!-- New Arrivals List -->
                <div id="arrivals-cards-grid" class="grid grid-cols-1 gap-4">
                    <?php if (empty($new_arrival_items)): ?>
                        <div class="p-8 rounded-2xl bg-app-card border border-app text-center">
                            <p class="text-sm font-semibold text-app-muted">No New Arrival items yet. Click "+ Add New Arrival Item" to add one.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($new_arrival_items_paged as $item): ?>
                            <div class="p-5 rounded-2xl bg-app-card border border-app flex flex-col md:flex-row items-start md:items-center justify-between gap-6 transition-all hover:border-app">
                                
                                <!-- Thumbnail & Info -->
                                <div class="flex items-center space-x-4 min-w-0 flex-1">
                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white/5 border border-app p-2 shrink-0 flex items-center justify-center relative group">
                                        <img src="../<?= e($item['image_url']) ?>" alt="<?= e($item['title']) ?>" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-500/15 text-purple-400">
                                                <?= e($item['badge_text'] ?: 'NEW ARRIVAL') ?>
                                            </span>
                                            <?php if (!empty($item['sku'])): ?>
                                                <span class="text-[10px] font-mono text-app-muted"><?= e($item['sku']) ?></span>
                                            <?php endif; ?>
                                            <span class="text-[10px] text-app-muted">Order: <?= (int)$item['sort_order'] ?></span>
                                        </div>
                                        <h3 class="text-sm font-black text-app-title truncate"><?= e($item['title']) ?></h3>
                                        <div class="flex items-center space-x-3 text-xs">
                                            <span class="font-bold text-emerald-400">Rs. <?= number_format($item['price']) ?></span>
                                            <?php if (!empty($item['original_price']) && $item['original_price'] > $item['price']): ?>
                                                <span class="text-app-muted line-through text-[11px]">Rs. <?= number_format($item['original_price']) ?></span>
                                                <span class="px-1.5 py-0.5 rounded bg-red-500/15 text-red-400 font-bold text-[10px]"><?= (int)$item['discount_pct'] ?>% Off</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center space-x-1.5 text-[11px] text-app-muted">
                                            <span>Link:</span>
                                            <a href="../<?= e($item['link_url']) ?>" target="_blank" class="font-mono text-blue-400 hover:underline truncate max-w-[280px]">
                                                <?= e($item['link_url']) ?> ↗
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Specs Preview Chips -->
                                <div class="hidden lg:grid grid-cols-3 gap-2 px-3.5 py-2.5 rounded-xl bg-app-canvas border border-app/60 text-[10.5px]">
                                    <div class="border-r border-app/40 pr-2">
                                        <span class="font-bold text-app-title block truncate"><?= e($item['spec_1_title'] ?: '-') ?></span>
                                        <span class="text-app-muted block truncate text-[9.5px]"><?= e($item['spec_1_sub'] ?: '-') ?></span>
                                    </div>
                                    <div class="border-r border-app/40 pr-2">
                                        <span class="font-bold text-app-title block truncate"><?= e($item['spec_2_title'] ?: '-') ?></span>
                                        <span class="text-app-muted block truncate text-[9.5px]"><?= e($item['spec_2_sub'] ?: '-') ?></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-app-title block truncate"><?= e($item['spec_3_title'] ?: '-') ?></span>
                                        <span class="text-app-muted block truncate text-[9.5px]"><?= e($item['spec_3_sub'] ?: '-') ?></span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-3 self-end md:self-center shrink-0">
                                    <form method="POST" action="cms.php?tab=new_arrivals" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle_new_arrival">
                                        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-md text-xs font-semibold <?= $item['is_active'] ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-700 text-slate-400' ?>">
                                            <?= $item['is_active'] ? '● Active' : '○ Inactive' ?>
                                        </button>
                                    </form>

                                    <div class="flex items-center space-x-2">
                                        <button onclick="openEditNewArrivalModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                                            Edit
                                        </button>
                                        <form method="POST" action="cms.php?tab=new_arrivals" class="inline" onsubmit="return confirm('Delete this New Arrival item?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete_new_arrival">
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-md bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-semibold">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- New Arrivals Section Pagination -->
                <?= render_admin_pagination(count($new_arrival_items), $cms_per_page, $cms_page) ?>

            </div>
        <?php endif; ?>

        <!-- ============================================================= -->
        <!-- TAB 4: FLASH SALE DEALS MANAGEMENT (10.10 COUNTDOWN) -->
        <!-- ============================================================= -->
        <?php if ($active_tab === 'flash_sale'): ?>
            <div class="space-y-6">

                <!-- Header and Add Button -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-app-title brand-font">Flash Sale Deals</h2>
                        <p class="text-xs text-app-muted">Manage the 5 promotional product cards displayed under the 10.10 countdown timer</p>
                    </div>
                    <button onclick="openCmsModal('add-flash-sale-modal')" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition-all shadow-lg shadow-amber-600/30 flex items-center space-x-1.5 self-start">
                        <span>＋</span>
                        <span>Add Flash Sale Deal</span>
                    </button>
                </div>

                <!-- Quick Search Filter for Flash Sale Deals -->
                <div class="relative w-full sm:max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
                    <input type="text" oninput="filterSectionCards(this, 'flash-cards-grid')" placeholder="Filter flash sale deals by title, slug, or ribbon..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-app-card border border-app text-app-title text-xs placeholder-app-muted focus:outline-none focus:border-amber-500">
                </div>

                <!-- Flash Sale Cards List -->
                <div id="flash-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <?php if (empty($flash_sale_items)): ?>
                        <div class="col-span-full p-8 text-center bg-app-card border border-app rounded-2xl text-app-muted text-xs">
                            No flash sale deals added yet. Click &quot;Add Flash Sale Deal&quot; to create one.
                        </div>
                    <?php else: ?>
                        <?php foreach ($flash_sale_items_paged as $item): ?>
                            <div class="bg-app-card border border-app rounded-2xl p-3.5 flex flex-col justify-between space-y-3 relative overflow-hidden group">
                                <!-- Top Ribbon Preview -->
                                <div class="w-full bg-gradient-to-r from-blue-600 via-cyan-500 to-orange-500 rounded-lg px-2 py-1 flex items-center justify-between text-white text-[9px] font-bold">
                                    <span class="truncate"><?= e($item['ribbon_text'] ?? '10.10 sale is live') ?></span>
                                    <span class="bg-white/20 px-1.5 py-0.5 rounded text-[8px]"><?= e($item['discount_text'] ?? 'Sale') ?></span>
                                </div>

                                <!-- Image & Swatch Preview -->
                                <div class="relative w-full h-32 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center p-2 overflow-hidden">
                                    <img src="../<?= e($item['image_url']) ?>" alt="<?= e($item['title']) ?>" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform" onerror="this.src='../images/Hero Banner.jpg'">
                                    
                                    <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-slate-900/80 text-amber-400 border border-amber-500/30">
                                        #<?= (int)$item['sort_order'] ?>
                                    </span>

                                    <!-- Color Swatch Circle -->
                                    <span class="absolute bottom-2 left-2 w-3.5 h-3.5 rounded-full border border-white/60 shadow-sm" style="background-color: <?= e($item['color_swatch'] ?? '#000000') ?>;" title="Color swatch: <?= e($item['color_swatch'] ?? '#000000') ?>"></span>
                                </div>

                                <!-- Title & Price -->
                                <div>
                                    <h3 class="text-xs font-bold text-app-title truncate" title="<?= e($item['title']) ?>">
                                        <?= e($item['title']) ?>
                                    </h3>
                                    <div class="flex items-baseline space-x-1.5 mt-1">
                                        <span class="text-xs font-extrabold text-emerald-400">Rs. <?= number_format($item['price'], 0) ?></span>
                                        <?php if (!empty($item['original_price'])): ?>
                                            <span class="text-[10px] text-app-muted line-through">Rs. <?= number_format($item['original_price'], 0) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[10px] text-app-muted font-mono truncate mt-0.5">
                                        🔗 <?= e($item['link_url'] ?? 'product-detail.php') ?>
                                    </p>
                                </div>

                                <!-- Actions Bar -->
                                <div class="pt-2 border-t border-app flex items-center justify-between text-xs">
                                    <form method="POST" action="cms.php?tab=flash_sale" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle_flash_sale">
                                        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                        <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold transition-colors <?= $item['is_active'] ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 hover:bg-red-500/20' ?>">
                                            <?= $item['is_active'] ? '● Active' : '○ Inactive' ?>
                                        </button>
                                    </form>

                                    <div class="flex items-center space-x-1.5">
                                        <button onclick="openEditFlashSaleModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-semibold">
                                            Edit
                                        </button>
                                        <form method="POST" action="cms.php?tab=flash_sale" class="inline" onsubmit="return confirm('Delete this Flash Sale deal?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete_flash_sale">
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-red-500/10 hover:bg-red-500/20 text-red-400 text-[10px] font-semibold">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Flash Sale Section Pagination -->
                <?= render_admin_pagination(count($flash_sale_items), $cms_per_page, $cms_page) ?>

            </div>
        <?php endif; ?>

    </main>

</div>

<!-- ===================================================================== -->
<!-- MODALS -->
<!-- ===================================================================== -->

<!-- 1. ADD HERO MODAL -->
<div id="add-hero-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <h3 class="text-base font-black text-app-title brand-font">Upload New Hero Banner Image</h3>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=hero" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="add_hero">

                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-blue-500/10 border border-blue-500/25 space-y-2">
                <label class="block text-xs font-black text-blue-500 dark:text-blue-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Feature a Catalog Product (Auto-Fills Details &amp; Link)</span>
                </label>
                <select id="add_hero_product_picker" class="cms-product-select2 w-full" data-modal="#add-hero-modal" data-placeholder="-- Choose Existing Product (Optional) --" onchange="onHeroProductSelect(this, 'add')">
                    <option value="">-- Choose Existing Product (Optional) --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-blue-500/80 dark:text-blue-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Banner Image File (JPG, PNG, WebP)</label>
                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-app-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-app-title hover:file:bg-blue-500 cursor-pointer">
                <p class="text-[10px] text-slate-500 mt-1">Or provide existing path below if already in images/</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Or Existing Image Path</label>
                <input type="text" id="add_hero_custom_image" name="custom_image_url" placeholder="images/Hero Banner.jpg" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Slide Title</label>
                <input type="text" id="add_hero_title" name="title" required placeholder="e.g. Next-Gen Fast Charging." class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Badge Text</label>
                <input type="text" id="add_hero_badge" name="badge_text" placeholder="⚡ GENUINE POWER TECH • OFFICIAL STORE" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Description / Subtitle</label>
                <textarea id="add_hero_subtitle" name="subtitle" rows="2" placeholder="Ultra-compact multi-port power..." class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">CTA Button Text</label>
                    <input type="text" id="add_hero_cta_text" name="cta_text" value="Explore Catalog" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Nav Dipping Color</label>
                    <input type="color" name="nav_color" value="#0052cc" class="w-full h-10 rounded-xl input-app border border-app cursor-pointer">
                </div>
            </div>

            <!-- Full-Width CTA Target Link & Clickable Verification Preview -->
            <div class="p-3.5 rounded-2xl bg-blue-500/5 border border-blue-500/20 space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-blue-400 uppercase tracking-wide">CTA Button Target Link (Slug / URL)</label>
                    <button type="button" onclick="toggleInputVisibility('add_hero_cta_link', this)" class="text-[11px] text-blue-400 hover:text-blue-300 underline font-medium">✏️ Edit text input</button>
                </div>
                <input type="text" id="add_hero_cta_link" name="cta_link" value="explore.php" oninput="updateLinkVerification('add_hero_cta_link', 'add_hero_cta_link_preview')" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                <!-- Clickable Link Verification Preview -->
                <div id="add_hero_cta_link_preview"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="1" min="1" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="add_hero_active" name="is_active" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                <label for="add_hero_active" class="text-xs font-bold text-app-body">Set active immediately</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-app-title text-xs font-bold shadow-lg">Save Slide</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. EDIT HERO MODAL -->
<div id="edit-hero-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <h3 class="text-base font-black text-app-title brand-font">Edit Hero Banner Slide</h3>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=hero" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="edit_hero">
            <input type="hidden" id="edit_hero_id" name="id" value="">

                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-blue-500/10 border border-blue-500/25 space-y-2">
                <label class="block text-xs font-black text-blue-500 dark:text-blue-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Link to Catalog Product (Auto-updates Slug &amp; Details)</span>
                </label>
                <select id="edit_hero_product_picker" class="cms-product-select2 w-full" data-modal="#edit-hero-modal" data-placeholder="-- Keep Existing / Custom URL --" onchange="onHeroProductSelect(this, 'edit')">
                    <option value="">-- Keep Existing / Custom URL --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-blue-500/80 dark:text-blue-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Replace Image File (Optional)</label>
                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-app-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-app-title hover:file:bg-blue-500 cursor-pointer">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Slide Title</label>
                <input type="text" id="edit_hero_title" name="title" required class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Badge Text</label>
                <input type="text" id="edit_hero_badge" name="badge_text" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Description / Subtitle</label>
                <textarea id="edit_hero_subtitle" name="subtitle" rows="2" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">CTA Button Text</label>
                    <input type="text" id="edit_hero_cta_text" name="cta_text" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Nav Dipping Color</label>
                    <input type="color" id="edit_hero_color" name="nav_color" class="w-full h-10 rounded-xl input-app border border-app cursor-pointer">
                </div>
            </div>

            <!-- Full-Width CTA Target Link & Clickable Verification Preview -->
            <div class="p-3.5 rounded-2xl bg-blue-500/5 border border-blue-500/20 space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-blue-400 uppercase tracking-wide">CTA Button Target Link (Slug / URL)</label>
                    <button type="button" onclick="toggleInputVisibility('edit_hero_cta_link', this)" class="text-[11px] text-blue-400 hover:text-blue-300 underline font-medium">✏️ Edit text input</button>
                </div>
                <input type="text" id="edit_hero_cta_link" name="cta_link" oninput="updateLinkVerification('edit_hero_cta_link', 'edit_hero_cta_link_preview')" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                <!-- Clickable Link Verification Preview -->
                <div id="edit_hero_cta_link_preview"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                <input type="number" id="edit_hero_sort" name="sort_order" min="1" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="edit_hero_active" name="is_active" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                <label for="edit_hero_active" class="text-xs font-bold text-app-body">Active status</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-app-title text-xs font-bold shadow-lg">Update Slide</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. ADD SPOTLIGHT MODAL -->
<div id="add-spotlight-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <h3 class="text-base font-black text-app-title brand-font">Upload Product Spotlight Image</h3>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=spotlight" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="add_spotlight">

                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-orange-500/10 border border-orange-500/25 space-y-2">
                <label class="block text-xs font-black text-orange-500 dark:text-orange-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Select Product From Catalog (Auto-Fills Slug Link)</span>
                </label>
                <select id="add_spot_product_picker" class="cms-product-select2 w-full" data-modal="#add-spotlight-modal" data-placeholder="-- Choose Existing Product (Optional) --" onchange="onSpotlightProductSelect(this, 'add')">
                    <option value="">-- Choose Existing Product (Optional) --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-orange-500/80 dark:text-orange-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Product PNG File (1000x1000 Transparent)</label>
                <input type="file" name="spotlight_image" accept="image/png,image/webp,image/jpeg" class="w-full text-xs text-app-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-600 file:text-app-title hover:file:bg-orange-500 cursor-pointer">
                <p class="text-[10px] text-slate-500 mt-1">Transparent PNGs preserve the authentic pop-out floating look</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Or Existing Image Path</label>
                <input type="text" id="add_spot_custom_image" name="custom_image_url" placeholder="images/Spotlight Section/1.png" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Product Title</label>
                <input type="text" id="add_spot_title" name="title" required placeholder="e.g. ChargeHub Prime 140W Multi-Port GaN" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Badge Tag</label>
                    <input type="text" id="add_spot_badge" name="badge_text" value="⚡ 140W GaN" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="1" min="1" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1 flex items-center justify-between">
                    <span>Target Link (Slug Based)</span>
                    <span class="text-[10px] text-orange-400 font-normal">Auto-populated with product slug</span>
                </label>
                <input type="text" id="add_spot_link" name="link_url" value="product-detail.php" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="add_spot_active" name="is_active" value="1" checked class="rounded text-orange-600 focus:ring-orange-500">
                <label for="add_spot_active" class="text-xs font-bold text-app-body">Set active in spotlight carousel</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-app-title text-xs font-bold shadow-lg">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. EDIT SPOTLIGHT MODAL -->
<div id="edit-spotlight-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-lg w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <h3 class="text-base font-black text-app-title brand-font">Edit Product Spotlight Image</h3>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=spotlight" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="edit_spotlight">
            <input type="hidden" id="edit_spot_id" name="id" value="">

            <!-- Quick Catalog Product Selector -->
                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-orange-500/10 border border-orange-500/25 space-y-2">
                <label class="block text-xs font-black text-orange-500 dark:text-orange-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Link to Catalog Product (Auto-updates Slug &amp; Details)</span>
                </label>
                <select id="edit_spot_product_picker" class="cms-product-select2 w-full" data-modal="#edit-spotlight-modal" data-placeholder="-- Keep Existing Link / Custom URL --" onchange="onSpotlightProductSelect(this, 'edit')">
                    <option value="">-- Keep Existing Link / Custom URL --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-orange-500/80 dark:text-orange-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Replace Image (Optional PNG)</label>
                <input type="file" name="spotlight_image" accept="image/png,image/webp,image/jpeg" class="w-full text-xs text-app-muted file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-600 file:text-app-title hover:file:bg-orange-500 cursor-pointer">
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Product Title</label>
                <input type="text" id="edit_spot_title" name="title" required class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Badge Tag</label>
                    <input type="text" id="edit_spot_badge" name="badge_text" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                    <input type="number" id="edit_spot_sort" name="sort_order" min="1" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1 flex items-center justify-between">
                    <span>Target Link (Slug Based)</span>
                    <span class="text-[10px] text-orange-400 font-normal">Auto-linked via product slug</span>
                </label>
                <input type="text" id="edit_spot_link" name="link_url" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="edit_spot_active" name="is_active" value="1" class="rounded text-orange-600 focus:ring-orange-500">
                <label for="edit_spot_active" class="text-xs font-bold text-app-body">Active status</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 text-app-title text-xs font-bold shadow-lg">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. ADD NEW ARRIVAL MODAL -->
<div id="add-new-arrival-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-xl w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <h3 class="text-base font-black text-app-title brand-font">Add New Arrival Product Card</h3>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=new_arrivals" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="add_new_arrival">

            <!-- 1. Quick Catalog Product Selector -->
                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-purple-500/10 border border-purple-500/25 space-y-2">
                <label class="block text-xs font-black text-purple-500 dark:text-purple-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Select Product From Catalog (Auto-Fills Details &amp; Slug Link)</span>
                </label>
                <select id="add_arr_product_picker" class="cms-product-select2 w-full" data-modal="#add-new-arrival-modal" data-placeholder="-- Choose Existing Product from Store --" onchange="onNewArrivalProductSelect(this, 'add')">
                    <option value="">-- Choose Existing Product from Store --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-purple-500/80 dark:text-purple-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <!-- Image File / Path -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Pop-out PNG File (1000x1000)</label>
                    <input type="file" name="new_arrival_image" accept="image/png,image/webp,image/jpeg" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-app-title hover:file:bg-purple-500 cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Or Existing Image Path</label>
                    <input type="text" id="add_arr_custom_image" name="custom_image_url" placeholder="images/1000x1000 transparent PNG/1.png" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- Title & Model -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Product Title</label>
                    <input type="text" id="add_arr_title" name="title" required placeholder="e.g. CHARGEHUB PRO | 65W GAN" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Model / SKU</label>
                    <input type="text" id="add_arr_sku" name="sku" placeholder="e.g. CH-65W" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
            </div>

            <!-- Pricing & Discount -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sale Price (Rs.)</label>
                    <input type="number" id="add_arr_price" name="price" step="0.01" required placeholder="5511" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs" oninput="calcNewArrivalDiscount('add')">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Original Price (Rs.)</label>
                    <input type="number" id="add_arr_orig_price" name="original_price" step="0.01" placeholder="6599" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs" oninput="calcNewArrivalDiscount('add')">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Discount %</label>
                    <input type="number" id="add_arr_discount" name="discount_pct" placeholder="16" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- 3 Bottom Specs Chips -->
            <div class="p-3.5 rounded-2xl bg-app-canvas border border-app/60 space-y-2">
                <label class="block text-[11px] font-bold text-app-muted uppercase tracking-wider">3 Highlight Spec Chips (Shown at bottom of card)</label>
                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <input type="text" id="add_arr_spec1_t" name="spec_1_title" placeholder="e.g. 65W Max" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[11px] font-bold">
                        <input type="text" id="add_arr_spec1_s" name="spec_1_sub" placeholder="e.g. Fast PD 3.0" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[10px] text-app-muted">
                    </div>
                    <div class="space-y-1">
                        <input type="text" id="add_arr_spec2_t" name="spec_2_title" placeholder="e.g. Dual Port" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[11px] font-bold">
                        <input type="text" id="add_arr_spec2_s" name="spec_2_sub" placeholder="e.g. GaN Tech" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[10px] text-app-muted">
                    </div>
                    <div class="space-y-1">
                        <input type="text" id="add_arr_spec3_t" name="spec_3_title" placeholder="e.g. Foldable" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[11px] font-bold">
                        <input type="text" id="add_arr_spec3_s" name="spec_3_sub" placeholder="e.g. US/UK Pin" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[10px] text-app-muted">
                    </div>
                </div>
            </div>

            <!-- Target Link & Sort -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Target Link (Slug Based)</label>
                    <input type="text" id="add_arr_link" name="link_url" value="product-detail.php" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="1" min="1" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <input type="checkbox" id="add_arr_active" name="is_active" value="1" checked class="rounded text-purple-600 focus:ring-purple-500">
                <label for="add_arr_active" class="text-xs font-bold text-app-body">Set active in New Arrivals carousel</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-app-title text-xs font-bold shadow-lg">Save New Arrival</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. EDIT NEW ARRIVAL MODAL -->
<div id="edit-new-arrival-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-xl w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <h3 class="text-base font-black text-app-title brand-font">Edit New Arrival Product Card</h3>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=new_arrivals" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="edit_new_arrival">
            <input type="hidden" id="edit_arr_id" name="id" value="">

            <!-- Quick Catalog Product Selector -->
                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-purple-500/10 border border-purple-500/25 space-y-2">
                <label class="block text-xs font-black text-purple-500 dark:text-purple-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Link to Catalog Product (Auto-updates Slug &amp; Details)</span>
                </label>
                <select id="edit_arr_product_picker" class="cms-product-select2 w-full" data-modal="#edit-new-arrival-modal" data-placeholder="-- Keep Existing Link / Custom URL --" onchange="onNewArrivalProductSelect(this, 'edit')">
                    <option value="">-- Keep Existing Link / Custom URL --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-purple-500/80 dark:text-purple-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <!-- Replace Image -->
            <div>
                <label class="block text-xs font-bold text-app-body uppercase mb-1">Replace Image File (Optional PNG)</label>
                <input type="file" name="new_arrival_image" accept="image/png,image/webp,image/jpeg" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-app-title hover:file:bg-purple-500 cursor-pointer">
            </div>

            <!-- Title & Model -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Product Title</label>
                    <input type="text" id="edit_arr_title" name="title" required class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Model / SKU</label>
                    <input type="text" id="edit_arr_sku" name="sku" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
            </div>

            <!-- Pricing & Discount -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sale Price (Rs.)</label>
                    <input type="number" id="edit_arr_price" name="price" step="0.01" required class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs" oninput="calcNewArrivalDiscount('edit')">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Original Price (Rs.)</label>
                    <input type="number" id="edit_arr_orig_price" name="original_price" step="0.01" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs" oninput="calcNewArrivalDiscount('edit')">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Discount %</label>
                    <input type="number" id="edit_arr_discount" name="discount_pct" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- 3 Bottom Specs Chips -->
            <div class="p-3.5 rounded-2xl bg-app-canvas border border-app/60 space-y-2">
                <label class="block text-[11px] font-bold text-app-muted uppercase tracking-wider">3 Highlight Spec Chips</label>
                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <input type="text" id="edit_arr_spec1_t" name="spec_1_title" placeholder="Title" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[11px] font-bold">
                        <input type="text" id="edit_arr_spec1_s" name="spec_1_sub" placeholder="Subtitle" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[10px] text-app-muted">
                    </div>
                    <div class="space-y-1">
                        <input type="text" id="edit_arr_spec2_t" name="spec_2_title" placeholder="Title" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[11px] font-bold">
                        <input type="text" id="edit_arr_spec2_s" name="spec_2_sub" placeholder="Subtitle" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[10px] text-app-muted">
                    </div>
                    <div class="space-y-1">
                        <input type="text" id="edit_arr_spec3_t" name="spec_3_title" placeholder="Title" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[11px] font-bold">
                        <input type="text" id="edit_arr_spec3_s" name="spec_3_sub" placeholder="Subtitle" class="w-full px-2.5 py-1 rounded-lg input-app border border-app text-[10px] text-app-muted">
                    </div>
                </div>
            </div>

            <!-- Target Link & Sort -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Target Link (Slug Based)</label>
                    <input type="text" id="edit_arr_link" name="link_url" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                    <input type="number" id="edit_arr_sort" name="sort_order" min="1" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <input type="checkbox" id="edit_arr_active" name="is_active" value="1" class="rounded text-purple-600 focus:ring-purple-500">
                <label for="edit_arr_active" class="text-xs font-bold text-app-body">Active status</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-app-title text-xs font-bold shadow-lg">Update New Arrival</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- 7. ADD FLASH SALE MODAL -->
<!-- ===================================================================== -->
<div id="add-flash-sale-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-xl w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-black text-app-title brand-font">Add Flash Sale Deal</h3>
                <p class="text-xs text-app-muted">Create a promotional card for the 10.10 live countdown section</p>
            </div>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=flash_sale" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="add_flash_sale">

                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/25 space-y-2">
                <label class="block text-xs font-black text-amber-500 dark:text-amber-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Auto-Fill From Catalog Product</span>
                </label>
                <select id="add_flash_product_picker" class="cms-product-select2 w-full" data-modal="#add-flash-sale-modal" data-placeholder="-- Choose Existing Product (Optional) --" onchange="onFlashSaleProductSelect(this, 'add')">
                    <option value="">-- Choose Existing Product (Optional) --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-amber-500/80 dark:text-amber-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <!-- Title & Ribbons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Product Title</label>
                    <input type="text" id="add_flash_title" name="title" required placeholder="e.g. L-265 Ronaq | 12 inch Wireless Speaker" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Color Swatch</label>
                    <input type="text" id="add_flash_color" name="color_swatch" value="#000000" placeholder="#000000" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
            </div>

            <!-- Ribbon text & Discount Badge -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Top Ribbon Text</label>
                    <input type="text" id="add_flash_ribbon" name="ribbon_text" value="10.10 sale is live" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Discount Pill Badge</label>
                    <input type="text" id="add_flash_discount" name="discount_text" value="14% Off" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- Pricing -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sale Price (Rs.)</label>
                    <input type="number" id="add_flash_price" name="price" step="0.01" required placeholder="17859.00" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Original Price (Rs.)</label>
                    <input type="number" id="add_flash_orig_price" name="original_price" step="0.01" placeholder="20999.00" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- Image Upload or Existing Path -->
            <div class="p-3.5 rounded-2xl bg-app-canvas border border-app/60 space-y-3">
                <label class="block text-xs font-bold text-app-body uppercase">Product Image</label>
                <div>
                    <label class="block text-[11px] text-app-muted mb-1">Upload File (PNG / JPG / WEBP)</label>
                    <input type="file" name="flash_sale_image" accept="image/*" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-600/20 file:text-amber-400 hover:file:bg-amber-600/30">
                </div>
                <div>
                    <label class="block text-[11px] text-app-muted mb-1">Or Custom Path / Store URL</label>
                    <input type="text" id="add_flash_custom_image" name="custom_image_url" placeholder="images/v7/flash_sale/l265_ronaq.png" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
            </div>

            <!-- Target Link & Sort -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Target Link (Slug Based)</label>
                    <input type="text" id="add_flash_link" name="link_url" value="product-detail.php" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="1" min="1" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <input type="checkbox" id="add_flash_active" name="is_active" value="1" checked class="rounded text-amber-600 focus:ring-amber-500">
                <label for="add_flash_active" class="text-xs font-bold text-app-body">Publish immediately (Active)</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-lg">Save Flash Deal</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- 8. EDIT FLASH SALE MODAL -->
<!-- ===================================================================== -->
<div id="edit-flash-sale-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-xl w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        <div class="p-5 sm:p-6 pb-3.5 border-b border-app flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-black text-app-title brand-font">Edit Flash Sale Deal</h3>
                <p class="text-xs text-app-muted">Update card details, price, discount or banner image</p>
            </div>
            <button type="button" onclick="closeCmsModal('')" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <form method="POST" action="cms.php?tab=flash_sale" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="edit_flash_sale">
            <input type="hidden" id="edit_flash_id" name="id" value="">

                        <!-- Searchable Select2 Catalog Product Selector -->
            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/25 space-y-2">
                <label class="block text-xs font-black text-amber-500 dark:text-amber-400 uppercase tracking-wide flex items-center space-x-1.5">
                    <span>⚡ Link to Catalog Product (Auto-updates Slug &amp; Details)</span>
                </label>
                <select id="edit_flash_product_picker" class="cms-product-select2 w-full" data-modal="#edit-flash-sale-modal" data-placeholder="-- Keep Existing / Custom URL --" onchange="onFlashSaleProductSelect(this, 'edit')">
                    <option value="">-- Keep Existing / Custom URL --</option>
                    <?php foreach ($products_list as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-name="<?= e($prod['name']) ?>" 
                            data-slug="<?= e($prod['slug']) ?>" 
                            data-sku="<?= e($prod['sku'] ?? '') ?>"
                            data-badge="<?= e($prod['badge_text']) ?>" 
                            data-image="<?= e($prod['main_image']) ?>"
                            data-price="<?= $prod['price'] ?? 0 ?>"
                            data-orig="<?= $prod['original_price'] ?? 0 ?>">
                        <?= e($prod['name']) ?> <?= !empty($prod['sku']) ? '(' . e($prod['sku']) . ')' : '' ?> &bull; slug: <?= e($prod['slug']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-[10.5px] text-amber-500/80 dark:text-amber-300/80">Type in dropdown to search by title, SKU, or slug to auto-populate</div>
            </div>

            <!-- Title & Swatch -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Product Title</label>
                    <input type="text" id="edit_flash_title" name="title" required class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Color Swatch</label>
                    <input type="text" id="edit_flash_color" name="color_swatch" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
            </div>

            <!-- Ribbon & Discount -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Top Ribbon Text</label>
                    <input type="text" id="edit_flash_ribbon" name="ribbon_text" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Discount Pill Badge</label>
                    <input type="text" id="edit_flash_discount" name="discount_text" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- Pricing -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sale Price (Rs.)</label>
                    <input type="number" id="edit_flash_price" name="price" step="0.01" required class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Original Price (Rs.)</label>
                    <input type="number" id="edit_flash_orig_price" name="original_price" step="0.01" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <!-- Image Upload or Existing Path -->
            <div class="p-3.5 rounded-2xl bg-app-canvas border border-app/60 space-y-3">
                <label class="block text-xs font-bold text-app-body uppercase">Product Image</label>
                <div>
                    <label class="block text-[11px] text-app-muted mb-1">Replace File (Leave blank to keep current)</label>
                    <input type="file" name="flash_sale_image" accept="image/*" class="w-full text-xs text-app-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-600/20 file:text-amber-400 hover:file:bg-amber-600/30">
                </div>
                <div>
                    <label class="block text-[11px] text-app-muted mb-1">Or Edit Custom Path</label>
                    <input type="text" id="edit_flash_custom_image" name="custom_image_url" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
            </div>

            <!-- Target Link & Sort -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Target Link (Slug Based)</label>
                    <input type="text" id="edit_flash_link" name="link_url" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-app-body uppercase mb-1">Sort Order</label>
                    <input type="number" id="edit_flash_sort" name="sort_order" min="1" class="w-full px-3.5 py-2 rounded-xl input-app border border-app text-app-title text-xs">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <input type="checkbox" id="edit_flash_active" name="is_active" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                <label for="edit_flash_active" class="text-xs font-bold text-app-body">Active status</label>
            </div>

            </div>

            <div class="p-4 px-6 border-t border-app bg-app-card/95 flex justify-end space-x-2 shrink-0">
                <button type="button" onclick="closeCmsModal('')" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title border border-app text-xs font-semibold cursor-pointer transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-lg">Update Flash Deal</button>
            </div>
        </form>
    </div>
</div>

<script>

        // Global modal openers and closers
    function openCmsModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';

        // Auto initialize or re-sync Select2 on product pickers inside this modal
        setTimeout(function() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                $('#' + modalId + ' .cms-product-select2').each(function() {
                    const $sel = $(this);
                    if (!$sel.hasClass('select2-hidden-accessible')) {
                        $sel.select2({
                            width: '100%',
                            dropdownParent: $('#' + modalId),
                            placeholder: $sel.data('placeholder') || '-- Choose Existing Product (Optional) --',
                            allowClear: true
                        });
                    }
                });
            }
        }, 50);
    }

    function closeCmsModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.style.display = 'none';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id$="-modal"]').forEach(function(m) {
                closeCmsModal(m.id);
            });
        }
    });

    // Close modal on clicking outer backdrop
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id$="-modal"]').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeCmsModal(modal.id);
                }
            });
        });
    });

    // Real-time Search Filter for Product Dropdowns in Modals
    function filterProductPicker(searchInput, selectId, countBadgeId) {
        const query = searchInput.value.toLowerCase().trim();
        const select = document.getElementById(selectId);
        if (!select) return;

        let matched = 0;
        let total = 0;
        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.hidden = false;
                opt.style.display = '';
                continue;
            }
            total++;
            const name = (opt.getAttribute('data-name') || '').toLowerCase();
            const slug = (opt.getAttribute('data-slug') || '').toLowerCase();
            const sku  = (opt.getAttribute('data-sku') || '').toLowerCase();
            const text = opt.text.toLowerCase();

            if (query === '' || name.includes(query) || slug.includes(query) || sku.includes(query) || text.includes(query)) {
                opt.hidden = false;
                opt.style.display = '';
                matched++;
            } else {
                opt.hidden = true;
                opt.style.display = 'none';
            }
        }

        const badge = document.getElementById(countBadgeId);
        if (badge) {
            badge.innerText = query ? `${matched} of ${total} matched` : `${total} products`;
        }
    }

    function clearProductPickerSearch(searchId, selectId, countBadgeId) {
        const searchInput = document.getElementById(searchId);
        if (searchInput) {
            searchInput.value = '';
            filterProductPicker(searchInput, selectId, countBadgeId);
            searchInput.focus();
        }
    }

    // Toggle Manual Input Visibility
    function toggleInputVisibility(inputId, btnEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.classList.contains('hidden')) {
            input.classList.remove('hidden');
            input.focus();
            if (btnEl) btnEl.innerText = 'Hide raw URL input';
        } else {
            input.classList.add('hidden');
            if (btnEl) btnEl.innerText = '✏️ Edit text input';
        }
    }

    // Live Clickable Link Verification Widget (Two-Row Zero-Overlap Responsive Design)
    function updateLinkVerification(inputId, previewId, overrideUrl) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!preview) return;

        const rawUrl = (overrideUrl !== undefined ? overrideUrl : (input ? input.value : '')).trim();
        if (input && overrideUrl !== undefined) {
            input.value = rawUrl;
        }

        if (!rawUrl) {
            preview.innerHTML = '<div class="text-[11px] text-app-muted italic px-2 py-1">No link provided yet.</div>';
            return;
        }

        // Determine destination URL relative to admin/ directory
        let hrefUrl = rawUrl;
        if (!rawUrl.startsWith('http://') && !rawUrl.startsWith('https://') && !rawUrl.startsWith('/') && !rawUrl.startsWith('../')) {
            hrefUrl = '../' + rawUrl;
        }

        const isProduct = rawUrl.includes('product-detail.php');
        const isCategory = rawUrl.includes('explore.php');
        const badgeText = isProduct ? '📦 Product Page' : (isCategory ? '🏷️ Catalog / Explore' : '🔗 Target Page');
        const badgeBg = isProduct ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-blue-500/10 text-blue-400 border-blue-500/30';
        const btnText = isProduct ? 'Verify Product' : 'Verify Link';

        preview.innerHTML = `
            <div class="p-3 rounded-2xl bg-slate-900 border border-blue-500/30 shadow-md space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border ${badgeBg}">
                        ${badgeText}
                    </span>
                    <a href="${hrefUrl}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all hover:scale-105 active:scale-95" title="Click to test & verify this live page in a new tab">
                        <span>🔗 ${btnText}</span>
                        <span class="text-xs font-black">↗</span>
                    </a>
                </div>
                <div class="flex items-center space-x-2 bg-black/50 rounded-xl px-3 py-2 border border-white/5 overflow-hidden">
                    <span class="text-slate-400 text-xs shrink-0">🌐</span>
                    <a href="${hrefUrl}" target="_blank" rel="noopener noreferrer" class="font-mono text-xs text-blue-300 hover:text-white underline hover:no-underline truncate flex-1" title="Click to test: ${rawUrl}">
                        ${rawUrl}
                    </a>
                </div>
            </div>
        `;
    }

    // Quick Search Filter for Cards on CMS Tabs
    function filterSectionCards(searchInput, containerId) {
        const query = searchInput.value.toLowerCase().trim();
        const container = document.getElementById(containerId);
        if (!container) return;

        const cards = container.children;
        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const text = card.textContent.toLowerCase();
            if (query === '' || text.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        }
    }

    function onHeroProductSelect(selectEl, mode) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;

        const name = opt.getAttribute('data-name') || '';
        const slug = opt.getAttribute('data-slug') || '';
        const badge = opt.getAttribute('data-badge') || '';
        const slugLink = slug ? ('product-detail.php?slug=' + encodeURIComponent(slug)) : 'product-detail.php';

        if (mode === 'add') {
            const titleInput = document.getElementById('add_hero_title');
            const linkInput = document.getElementById('add_hero_cta_link');
            const ctaTextInput = document.getElementById('add_hero_cta_text');
            const badgeInput = document.getElementById('add_hero_badge');

            if (titleInput && !titleInput.value) titleInput.value = name;
            if (linkInput) linkInput.value = slugLink;
            if (ctaTextInput) ctaTextInput.value = 'Shop ' + (name.split(' ')[0] || 'Now');
            if (badgeInput && badge && !badgeInput.value) badgeInput.value = badge;
            updateLinkVerification('add_hero_cta_link', 'add_hero_cta_link_preview');
        } else if (mode === 'edit') {
            const linkInput = document.getElementById('edit_hero_cta_link');
            if (linkInput) linkInput.value = slugLink;
            updateLinkVerification('edit_hero_cta_link', 'edit_hero_cta_link_preview');
        }
    }

    function openEditHeroModal(slide) {
        document.getElementById('edit_hero_id').value = slide.id;
        document.getElementById('edit_hero_title').value = slide.title || '';
        document.getElementById('edit_hero_badge').value = slide.badge_text || '';
        document.getElementById('edit_hero_subtitle').value = slide.subtitle || '';
        document.getElementById('edit_hero_color').value = slide.nav_color || '#0052cc';
        document.getElementById('edit_hero_sort').value = slide.sort_order || 1;
        document.getElementById('edit_hero_cta_link').value = slide.cta_link || 'explore.php';
        document.getElementById('edit_hero_cta_text').value = slide.cta_text || 'Explore Catalog';
        document.getElementById('edit_hero_active').checked = slide.is_active == 1;

        // Auto-match hero product picker
        const picker = document.getElementById('edit_hero_product_picker');
        if (picker) {
            picker.value = '';
            for (let i = 0; i < picker.options.length; i++) {
                const opt = picker.options[i];
                const optSlug = opt.getAttribute('data-slug');
                if (optSlug && (slide.cta_link || '').includes('slug=' + optSlug)) {
                    picker.selectedIndex = i;
                    $(picker).trigger('change.select2');
                    break;
                }
            }
        }

        updateLinkVerification('edit_hero_cta_link', 'edit_hero_cta_link_preview', slide.cta_link || 'explore.php');
        openCmsModal('edit-hero-modal');
    }

    function onSpotlightProductSelect(selectEl, mode) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) {
            return;
        }

        const name = opt.getAttribute('data-name') || '';
        const slug = opt.getAttribute('data-slug') || '';
        const badge = opt.getAttribute('data-badge') || '';
        const image = opt.getAttribute('data-image') || '';

        // Construct clean slug-based link URL
        const slugLink = slug ? ('product-detail.php?slug=' + encodeURIComponent(slug)) : 'product-detail.php';

        if (mode === 'add') {
            const titleInput = document.getElementById('add_spot_title');
            const linkInput = document.getElementById('add_spot_link');
            const badgeInput = document.getElementById('add_spot_badge');
            const imgInput = document.getElementById('add_spot_custom_image');

            if (titleInput) titleInput.value = name;
            if (linkInput) linkInput.value = slugLink;
            if (badgeInput && badge) badgeInput.value = badge;
            if (imgInput && image && !imgInput.value) imgInput.value = image;
            updateLinkVerification('add_spot_link', 'add_spot_link_preview');
        } else if (mode === 'edit') {
            const titleInput = document.getElementById('edit_spot_title');
            const linkInput = document.getElementById('edit_spot_link');
            const badgeInput = document.getElementById('edit_spot_badge');

            if (titleInput && name) titleInput.value = name;
            if (linkInput && slugLink) linkInput.value = slugLink;
            if (badgeInput && badge) badgeInput.value = badge;
            updateLinkVerification('edit_spot_link', 'edit_spot_link_preview');
        }
    }

    function openEditSpotlightModal(item) {
        document.getElementById('edit_spot_id').value = item.id;
        document.getElementById('edit_spot_title').value = item.title || '';
        document.getElementById('edit_spot_badge').value = item.badge_text || '';
        
        let linkUrl = item.link_url || 'product-detail.php';
        document.getElementById('edit_spot_link').value = linkUrl;

        // Auto-match in product picker
        const picker = document.getElementById('edit_spot_product_picker');
        if (picker) {
            picker.value = '';
            for (let i = 0; i < picker.options.length; i++) {
                const opt = picker.options[i];
                const optSlug = opt.getAttribute('data-slug');
                const optId = opt.value;
                if ((optSlug && (linkUrl.includes('slug=' + encodeURIComponent(optSlug)) || linkUrl.includes('slug=' + optSlug))) ||
                    (optId && linkUrl.includes('id=' + optId))) {
                    picker.selectedIndex = i;
                    // Migrate old id link to clean slug link
                    if (linkUrl.includes('id=' + optId) && optSlug) {
                        document.getElementById('edit_spot_link').value = 'product-detail.php?slug=' + encodeURIComponent(optSlug);
                    }
                    break;
                }
            }
        }

        document.getElementById('edit_spot_sort').value = item.sort_order || 1;
        document.getElementById('edit_spot_active').checked = item.is_active == 1;
        updateLinkVerification('edit_spot_link', 'edit_spot_link_preview', item.link_url || 'product-detail.php');
        openCmsModal('edit-spotlight-modal');
    }

    function calcNewArrivalDiscount(mode) {
        const priceEl = document.getElementById(mode === 'add' ? 'add_arr_price' : 'edit_arr_price');
        const origEl = document.getElementById(mode === 'add' ? 'add_arr_orig_price' : 'edit_arr_orig_price');
        const discEl = document.getElementById(mode === 'add' ? 'add_arr_discount' : 'edit_arr_discount');

        if (priceEl && origEl && discEl) {
            const p = parseFloat(priceEl.value) || 0;
            const o = parseFloat(origEl.value) || 0;
            if (o > p && p > 0) {
                discEl.value = Math.round(((o - p) / o) * 100);
            }
        }
    }

    function onNewArrivalProductSelect(selectEl, mode) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;

        const name = opt.getAttribute('data-name') || '';
        const slug = opt.getAttribute('data-slug') || '';
        const badge = opt.getAttribute('data-badge') || 'NEW ARRIVAL';
        const image = opt.getAttribute('data-image') || '';
        const slugLink = slug ? ('product-detail.php?slug=' + encodeURIComponent(slug)) : 'product-detail.php';

        const prefix = mode === 'add' ? 'add_arr_' : 'edit_arr_';
        const titleEl = document.getElementById(prefix + 'title');
        const linkEl = document.getElementById(prefix + 'link');
        const badgeEl = document.getElementById(prefix + 'badge');
        const imgEl = document.getElementById(prefix + 'custom_image');

        if (titleEl) titleEl.value = name;
        if (linkEl) linkEl.value = slugLink;
        if (badgeEl && badge) badgeEl.value = badge;
        if (imgEl && image && !imgEl.value) imgEl.value = image;
        updateLinkVerification(mode === 'add' ? 'add_arr_link' : 'edit_arr_link', mode === 'add' ? 'add_arr_link_preview' : 'edit_arr_link_preview');
    }

    function openEditNewArrivalModal(item) {
        document.getElementById('edit_arr_id').value = item.id;
        document.getElementById('edit_arr_title').value = item.title || '';
        document.getElementById('edit_arr_sku').value = item.sku || '';
        document.getElementById('edit_arr_price').value = item.price || '';
        document.getElementById('edit_arr_orig_price').value = item.original_price || '';
        document.getElementById('edit_arr_discount').value = item.discount_pct || '';
        
        let linkUrl = item.link_url || 'product-detail.php';
        document.getElementById('edit_arr_link').value = linkUrl;

        document.getElementById('edit_arr_spec1_t').value = item.spec_1_title || '';
        document.getElementById('edit_arr_spec1_s').value = item.spec_1_sub || '';
        document.getElementById('edit_arr_spec2_t').value = item.spec_2_title || '';
        document.getElementById('edit_arr_spec2_s').value = item.spec_2_sub || '';
        document.getElementById('edit_arr_spec3_t').value = item.spec_3_title || '';
        document.getElementById('edit_arr_spec3_s').value = item.spec_3_sub || '';

        document.getElementById('edit_arr_sort').value = item.sort_order || 1;
        document.getElementById('edit_arr_active').checked = item.is_active == 1;

        // Auto-match picker
        const picker = document.getElementById('edit_arr_product_picker');
        if (picker) {
            picker.value = '';
            for (let i = 0; i < picker.options.length; i++) {
                const opt = picker.options[i];
                const optSlug = opt.getAttribute('data-slug');
                const optId = opt.value;
                if ((optSlug && (linkUrl.includes('slug=' + encodeURIComponent(optSlug)) || linkUrl.includes('slug=' + optSlug))) ||
                    (optId && linkUrl.includes('id=' + optId))) {
                    picker.selectedIndex = i;
                    if (linkUrl.includes('id=' + optId) && optSlug) {
                        document.getElementById('edit_arr_link').value = 'product-detail.php?slug=' + encodeURIComponent(optSlug);
                    }
                    break;
                }
            }
        }

        updateLinkVerification('edit_arr_link', 'edit_arr_link_preview', item.link_url || 'product-detail.php');
        openCmsModal('edit-new-arrival-modal');
    }

    function onFlashSaleProductSelect(selectEl, mode) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;

        const name = opt.getAttribute('data-name') || '';
        const slug = opt.getAttribute('data-slug') || '';
        const image = opt.getAttribute('data-image') || '';
        const price = opt.getAttribute('data-price') || '';
        const orig = opt.getAttribute('data-orig') || '';
        const slugLink = slug ? ('product-detail.php?slug=' + encodeURIComponent(slug)) : 'product-detail.php';

        const prefix = mode === 'add' ? 'add_flash_' : 'edit_flash_';
        const titleEl = document.getElementById(prefix + 'title');
        const linkEl = document.getElementById(prefix + 'link');
        const imgEl = document.getElementById(prefix + 'custom_image');
        const priceEl = document.getElementById(prefix + 'price');
        const origEl = document.getElementById(prefix + 'orig_price');
        const discEl = document.getElementById(prefix + 'discount');

        if (titleEl) titleEl.value = name;
        if (linkEl) linkEl.value = slugLink;
        if (imgEl && image && !imgEl.value) imgEl.value = image;
        if (priceEl && price) priceEl.value = price;
        if (origEl && orig) origEl.value = orig;

        if (price && orig && parseFloat(orig) > parseFloat(price)) {
            const p = parseFloat(price);
            const o = parseFloat(orig);
            const pct = Math.round(((o - p) / o) * 100);
            if (discEl) discEl.value = pct + '% Off';
        }
        updateLinkVerification(mode === 'add' ? 'add_flash_link' : 'edit_flash_link', mode === 'add' ? 'add_flash_link_preview' : 'edit_flash_link_preview');
    }

    function openEditFlashSaleModal(item) {
        document.getElementById('edit_flash_id').value = item.id;
        document.getElementById('edit_flash_title').value = item.title || '';
        document.getElementById('edit_flash_ribbon').value = item.ribbon_text || '10.10 sale is live';
        document.getElementById('edit_flash_discount').value = item.discount_text || '14% Off';
        document.getElementById('edit_flash_price').value = item.price || '';
        document.getElementById('edit_flash_orig_price').value = item.original_price || '';
        document.getElementById('edit_flash_color').value = item.color_swatch || '#000000';
        
        let linkUrl = item.link_url || 'product-detail.php';
        document.getElementById('edit_flash_link').value = linkUrl;
        document.getElementById('edit_flash_sort').value = item.sort_order || 1;
        document.getElementById('edit_flash_active').checked = item.is_active == 1;

        // Auto-match picker
        const picker = document.getElementById('edit_flash_product_picker');
        if (picker) {
            picker.value = '';
            for (let i = 0; i < picker.options.length; i++) {
                const opt = picker.options[i];
                const optSlug = opt.getAttribute('data-slug');
                const optId = opt.value;
                if ((optSlug && (linkUrl.includes('slug=' + encodeURIComponent(optSlug)) || linkUrl.includes('slug=' + optSlug))) ||
                    (optId && linkUrl.includes('id=' + optId))) {
                    picker.selectedIndex = i;
                    if (linkUrl.includes('id=' + optId) && optSlug) {
                        document.getElementById('edit_flash_link').value = 'product-detail.php?slug=' + encodeURIComponent(optSlug);
                    }
                    break;
                }
            }
        }

        updateLinkVerification('edit_flash_link', 'edit_flash_link_preview', item.link_url || 'product-detail.php');
        openCmsModal('edit-flash-sale-modal');
    }

    // Initialize verification links on page load for add modals
    document.addEventListener('DOMContentLoaded', function() {
        updateLinkVerification('add_hero_cta_link', 'add_hero_cta_link_preview');
        updateLinkVerification('add_spot_link', 'add_spot_link_preview');
        updateLinkVerification('add_arr_link', 'add_arr_link_preview');
        updateLinkVerification('add_flash_link', 'add_flash_link_preview');
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
