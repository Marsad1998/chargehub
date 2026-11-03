<?php
$page_title = 'Orders Management';
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

// Handle Order Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please refresh.';
    } else {
        $action = $_POST['action'] ?? '';

        // 1. UPDATE ORDER STATUS
        if ($action === 'update_status') {
            $order_id = (int)($_POST['order_id'] ?? 0);
            $new_status = trim($_POST['status'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            $allowed_statuses = ['Pending', 'Confirmed', 'Dispatched', 'Delivered', 'Cancelled'];
            if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
                if (!empty($notes)) {
                    $stmt = $pdo->prepare("UPDATE orders SET status = ?, notes = ? WHERE id = ?");
                    $stmt->execute([$new_status, $notes, $order_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    $stmt->execute([$new_status, $order_id]);
                }
                set_flash_message('success', "Order status successfully updated to '{$new_status}'.");
                header('Location: orders.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                exit;
            }
        }

        // 2. DELETE ORDER
        elseif ($action === 'delete_order') {
            $order_id = (int)($_POST['order_id'] ?? 0);
            if ($order_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$order_id]);
                set_flash_message('success', 'Order record deleted successfully.');
                header('Location: orders.php');
                exit;
            }
        }

        // 3. BULK UPDATE STATUS
        elseif ($action === 'bulk_update_status') {
            $order_ids = $_POST['selected_orders'] ?? [];
            $bulk_status = trim($_POST['bulk_status'] ?? '');
            $allowed_statuses = ['Pending', 'Confirmed', 'Dispatched', 'Delivered', 'Cancelled'];

            if (!empty($order_ids) && is_array($order_ids) && in_array($bulk_status, $allowed_statuses)) {
                $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
                $params = array_merge([$bulk_status], array_map('intval', $order_ids));
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id IN ($placeholders)");
                $stmt->execute($params);
                set_flash_message('success', count($order_ids) . " orders updated to '{$bulk_status}'.");
                header('Location: orders.php');
                exit;
            }
        }

        // 4. RESEND ORDER CONFIRMATION EMAIL
        elseif ($action === 'resend_email') {
            $order_id = (int)($_POST['order_id'] ?? 0);
            if ($order_id > 0) {
                require_once __DIR__ . '/../includes/mailer.php';
                $mRes = send_order_confirmation_email($order_id);
                if (!empty($mRes['success'])) {
                    set_flash_message('success', 'Order confirmation email sent successfully!');
                } else {
                    set_flash_message('error', 'Failed to send email: ' . ($mRes['message'] ?? $mRes['error'] ?? 'Check SMTP settings in Email & SMTP Settings'));
                }
                header('Location: orders.php');
                exit;
            }
        }
    }
}

// Ensure orders table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(150) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_email VARCHAR(150) NULL,
        customer_address TEXT NOT NULL,
        customer_city VARCHAR(100) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        order_items_json TEXT NOT NULL,
        notes TEXT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {
    // Continue
}

// Metrics
try {
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    $confirmedOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Confirmed'")->fetchColumn();
    $dispatchedOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Dispatched'")->fetchColumn();
    $deliveredOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Delivered'")->fetchColumn();
    $cancelledOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Cancelled'")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
} catch (Exception $e) {
    $totalOrders = $pendingOrders = $confirmedOrders = $dispatchedOrders = $deliveredOrders = $cancelledOrders = $totalRevenue = 0;
}

// Search & Filter Parameters
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$filter_city = trim($_GET['city'] ?? '');
$filter_date = trim($_GET['date_range'] ?? '');

// Count total filtered orders for pagination
$countSql = "SELECT COUNT(*) FROM orders WHERE 1=1";
$params = [];

if (!empty($search)) {
    $countSql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR customer_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_status)) {
    $countSql .= " AND status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_city)) {
    $countSql .= " AND customer_city = ?";
    $params[] = $filter_city;
}

if ($filter_date === 'today') {
    $countSql .= " AND DATE(created_at) = CURDATE()";
} elseif ($filter_date === 'week') {
    $countSql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter_date === 'month') {
    $countSql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalFilteredOrders = (int)$stmtCount->fetchColumn();

// Pagination Parameters (15 orders per page)
$per_page = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$total_pages = max(1, (int)ceil($totalFilteredOrders / $per_page));
if ($page > $total_pages && $totalFilteredOrders > 0) $page = $total_pages;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM orders WHERE 1=1";
$queryParams = [];

if (!empty($search)) {
    $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR customer_address LIKE ?)";
    $queryParams[] = "%$search%";
    $queryParams[] = "%$search%";
    $queryParams[] = "%$search%";
    $queryParams[] = "%$search%";
}

if (!empty($filter_status)) {
    $sql .= " AND status = ?";
    $queryParams[] = $filter_status;
}

if (!empty($filter_city)) {
    $sql .= " AND customer_city = ?";
    $queryParams[] = $filter_city;
}

if ($filter_date === 'today') {
    $sql .= " AND DATE(created_at) = CURDATE()";
} elseif ($filter_date === 'week') {
    $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter_date === 'month') {
    $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

$sql .= " ORDER BY id DESC LIMIT $per_page OFFSET $offset";

$stmtOrders = $pdo->prepare($sql);
$stmtOrders->execute($queryParams);
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

// Unique cities for city dropdown
try {
    $cities = $pdo->query("SELECT DISTINCT customer_city FROM orders WHERE customer_city IS NOT NULL AND customer_city != '' ORDER BY customer_city ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $cities = [];
}
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">

    <!-- TOPBAR -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- MAIN BODY -->
    <main class="p-4 sm:p-6 space-y-5 flex-1 max-w-[1280px] mx-auto w-full">

        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-app-title brand-font tracking-tight flex items-center space-x-2">
                    <span>📦 Orders Management</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-500/15 text-blue-500 font-bold border border-blue-500/20">
                        <?= count($orders) ?> of <?= $totalOrders ?> orders
                    </span>
                </h2>
                <p class="text-xs text-app-muted mt-0.5">
                    Track customer delivery dispatches, manage order statuses, inspect order items &amp; customer receipts.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="../explore.php" target="_blank" class="px-3.5 py-2 rounded-xl bg-app-card border border-app hover:bg-slate-500/10 text-app-title text-xs font-semibold transition-all flex items-center space-x-1.5 shadow-sm">
                    <span>🛒</span>
                    <span>Test Storefront</span>
                </a>
                <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm">
                    <span>🖨️</span>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- METRICS TILES -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total Revenue -->
            <div class="p-4 rounded-xl bg-app-card border border-app shadow-sm col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Net Sales</span>
                    <span class="text-base">💰</span>
                </div>
                <div class="text-lg sm:text-xl font-black text-emerald-500">
                    Rs. <?= number_format($totalRevenue) ?>
                </div>
                <div class="text-[10px] text-app-muted mt-0.5">Across <?= $totalOrders ?> total orders</div>
            </div>

            <!-- Pending Orders -->
            <a href="orders.php?status=Pending" class="p-4 rounded-xl bg-app-card border <?= $filter_status === 'Pending' ? 'border-amber-500' : 'border-app hover:border-amber-500/40' ?> shadow-sm transition-all group">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Pending COD</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                </div>
                <div class="text-lg sm:text-xl font-black text-amber-500"><?= $pendingOrders ?></div>
                <div class="text-[10px] text-amber-500/80 font-semibold mt-0.5 group-hover:underline">Awaiting Dispatch &rarr;</div>
            </a>

            <!-- Confirmed Orders -->
            <a href="orders.php?status=Confirmed" class="p-4 rounded-xl bg-app-card border <?= $filter_status === 'Confirmed' ? 'border-blue-500' : 'border-app hover:border-blue-500/40' ?> shadow-sm transition-all group">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Confirmed</span>
                    <span class="text-base">📋</span>
                </div>
                <div class="text-lg sm:text-xl font-black text-blue-500"><?= $confirmedOrders ?></div>
                <div class="text-[10px] text-blue-500/80 font-semibold mt-0.5 group-hover:underline">Packed &bull; Ready &rarr;</div>
            </a>

            <!-- Dispatched Orders -->
            <a href="orders.php?status=Dispatched" class="p-4 rounded-xl bg-app-card border <?= $filter_status === 'Dispatched' ? 'border-purple-500' : 'border-app hover:border-purple-500/40' ?> shadow-sm transition-all group">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">In Transit</span>
                    <span class="text-base">🚚</span>
                </div>
                <div class="text-lg sm:text-xl font-black text-purple-500"><?= $dispatchedOrders ?></div>
                <div class="text-[10px] text-purple-500/80 font-semibold mt-0.5 group-hover:underline">With Courier &rarr;</div>
            </a>

            <!-- Delivered Orders -->
            <a href="orders.php?status=Delivered" class="p-4 rounded-xl bg-app-card border <?= $filter_status === 'Delivered' ? 'border-emerald-500' : 'border-app hover:border-emerald-500/40' ?> shadow-sm transition-all group col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between text-xs text-app-muted mb-1">
                    <span class="font-bold uppercase tracking-wider text-[10px]">Delivered</span>
                    <span class="text-base">✅</span>
                </div>
                <div class="text-lg sm:text-xl font-black text-emerald-600"><?= $deliveredOrders ?></div>
                <div class="text-[10px] text-emerald-600/80 font-semibold mt-0.5 group-hover:underline">Payment Received &rarr;</div>
            </a>
        </div>

        <!-- SEARCH & FILTER TOOLBAR -->
        <div class="p-3.5 rounded-2xl bg-app-card border border-app shadow-sm space-y-3">
            <form method="GET" action="orders.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 text-xs">
                
                <!-- Search Box -->
                <div class="relative lg:col-span-2">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-app-muted">🔍</span>
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by Order #, Customer Name, Phone, Address..." class="w-full pl-8 pr-3 py-2 rounded-xl input-app border border-app text-app-title text-xs placeholder:text-app-muted focus:outline-none focus:border-blue-500 transition-all">
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full px-3 py-2 rounded-xl input-app border border-app text-app-title text-xs font-semibold focus:outline-none focus:border-blue-500 cursor-pointer transition-all">
                        <option value="">All Statuses (<?= $totalOrders ?>)</option>
                        <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending (<?= $pendingOrders ?>)</option>
                        <option value="Confirmed" <?= $filter_status === 'Confirmed' ? 'selected' : '' ?>>Confirmed (<?= $confirmedOrders ?>)</option>
                        <option value="Dispatched" <?= $filter_status === 'Dispatched' ? 'selected' : '' ?>>Dispatched (<?= $dispatchedOrders ?>)</option>
                        <option value="Delivered" <?= $filter_status === 'Delivered' ? 'selected' : '' ?>>Delivered (<?= $deliveredOrders ?>)</option>
                        <option value="Cancelled" <?= $filter_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled (<?= $cancelledOrders ?>)</option>
                    </select>
                </div>

                <!-- City Filter -->
                <div>
                    <select name="city" class="w-full px-3 py-2 rounded-xl input-app border border-app text-app-title text-xs font-semibold focus:outline-none focus:border-blue-500 cursor-pointer transition-all">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= e($c) ?>" <?= $filter_city === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow transition-all">
                        Filter
                    </button>
                    <?php if (!empty($search) || !empty($filter_status) || !empty($filter_city) || !empty($filter_date)): ?>
                    <a href="orders.php" class="px-3 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-muted hover:text-app-title font-semibold text-xs transition-colors">
                        Reset
                    </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- ORDERS DATA TABLE -->
        <div class="rounded-2xl bg-app-card border border-app overflow-hidden shadow-sm">
            
            <form id="bulk-form" method="POST" action="orders.php">
                <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                <input type="hidden" name="action" value="bulk_update_status">

                <!-- Bulk Actions Toolbar (Visible when checkboxes selected) -->
                <div id="bulk-bar" class="hidden px-4 py-2.5 bg-blue-500/10 border-b border-blue-500/20 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2 text-blue-500 font-bold">
                        <span>Selected <span id="selected-count">0</span> orders:</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <select name="bulk_status" class="px-3 py-1.5 rounded-lg input-app border border-blue-500/40 text-app-title text-xs font-semibold cursor-pointer">
                            <option value="Confirmed">Mark as Confirmed</option>
                            <option value="Dispatched">Mark as Dispatched</option>
                            <option value="Delivered">Mark as Delivered</option>
                            <option value="Cancelled">Mark as Cancelled</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs">
                            Apply to Selected
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-app-body divide-y divide-app">
                        <thead class="bg-slate-500/5 text-app-muted uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="p-3.5 pl-4 w-8">
                                    <input type="checkbox" id="select-all" class="rounded text-blue-600 focus:ring-0 cursor-pointer">
                                </th>
                                <th class="p-3.5">Order Ref</th>
                                <th class="p-3.5">Customer &amp; Phone</th>
                                <th class="p-3.5">Destination</th>
                                <th class="p-3.5">Items Summary</th>
                                <th class="p-3.5">Total &amp; Payment</th>
                                <th class="p-3.5">Status</th>
                                <th class="p-3.5 pr-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-app">
                            <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-app-muted">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 mx-auto flex items-center justify-center text-xl mb-2">📦</div>
                                    <p class="font-bold text-sm text-app-title">No orders found</p>
                                    <p class="text-xs text-app-muted mt-0.5">
                                        <?= (!empty($search) || !empty($filter_status)) ? 'Try adjusting your search criteria.' : 'Customer orders will appear here as soon as they checkout.' ?>
                                    </p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($orders as $o): 
                                $items = json_decode($o['order_items_json'], true) ?: [];
                                $itemsCount = 0;
                                foreach ($items as $it) {
                                    $itemsCount += (int)($it['qty'] ?? 1);
                                }

                                // Status color mapping
                                $statusBadgeClass = 'bg-slate-500/15 text-slate-400 border-slate-500/30';
                                if ($o['status'] === 'Pending') {
                                    $statusBadgeClass = 'bg-amber-500/15 text-amber-500 border-amber-500/30';
                                } elseif ($o['status'] === 'Confirmed') {
                                    $statusBadgeClass = 'bg-blue-500/15 text-blue-500 border-blue-500/30';
                                } elseif ($o['status'] === 'Dispatched') {
                                    $statusBadgeClass = 'bg-purple-500/15 text-purple-500 border-purple-500/30';
                                } elseif ($o['status'] === 'Delivered') {
                                    $statusBadgeClass = 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30';
                                } elseif ($o['status'] === 'Cancelled') {
                                    $statusBadgeClass = 'bg-red-500/15 text-red-500 border-red-500/30';
                                }

                                // Clean phone for WhatsApp Link (e.g. 03001234567 -> 923001234567)
                                $rawPhone = preg_replace('/[^0-9]/', '', $o['customer_phone']);
                                if (str_starts_with($rawPhone, '0')) {
                                    $waNumber = '92' . substr($rawPhone, 1);
                                } elseif (str_starts_with($rawPhone, '92')) {
                                    $waNumber = $rawPhone;
                                } else {
                                    $waNumber = '92' . $rawPhone;
                                }
                                $waMsg = urlencode("Hi {$o['customer_name']}, this is ChargeHub.pk regarding your order #{$o['order_number']}. Total: Rs. " . number_format($o['total_amount']));
                            ?>
                            <tr class="hover:bg-slate-500/5 transition-colors">
                                <td class="p-3.5 pl-4">
                                    <input type="checkbox" name="selected_orders[]" value="<?= $o['id'] ?>" class="order-cb rounded text-blue-600 focus:ring-0 cursor-pointer">
                                </td>
                                <td class="p-3.5">
                                    <button type="button" onclick="viewOrderDetails(<?= htmlspecialchars(json_encode($o)) ?>)" class="font-mono font-bold text-blue-500 hover:text-blue-400 hover:underline flex items-center space-x-1 cursor-pointer">
                                        <span><?= e($o['order_number']) ?></span>
                                    </button>
                                    <span class="text-[10px] text-app-muted block mt-0.5">
                                        <?= date('M d, Y &bull; h:i A', strtotime($o['created_at'])) ?>
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-app-title"><?= e($o['customer_name']) ?></div>
                                    <div class="flex items-center space-x-1.5 mt-0.5">
                                        <a href="tel:<?= e($o['customer_phone']) ?>" class="text-[11px] text-app-muted hover:text-app-title">
                                            <?= e($o['customer_phone']) ?>
                                        </a>
                                        <a href="https://wa.me/<?= $waNumber ?>?text=<?= $waMsg ?>" target="_blank" class="px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-500 text-[10px] font-bold hover:bg-emerald-500/25 transition-colors" title="Chat on WhatsApp">
                                            WhatsApp
                                        </a>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <span class="font-semibold text-app-title"><?= e($o['customer_city']) ?></span>
                                    <span class="text-[10px] text-app-muted block truncate max-w-[180px]" title="<?= e($o['customer_address']) ?>">
                                        <?= e($o['customer_address']) ?>
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-500/15 text-[10.5px] font-bold text-app-title">
                                            <?= $itemsCount ?> item<?= $itemsCount === 1 ? '' : 's' ?>
                                        </span>
                                    </div>
                                    <div class="text-[10.5px] text-app-muted mt-0.5 truncate max-w-[200px]" title="<?php 
                                        $itNames = array_map(function($i) { return ($i['name'] ?? 'Product') . ' (x' . ($i['qty'] ?? 1) . ')'; }, $items);
                                        echo e(implode(', ', $itNames));
                                    ?>">
                                        <?php if (!empty($items)): ?>
                                            <?= e($items[0]['name'] ?? 'Product') ?><?= count($items) > 1 ? ' + ' . (count($items) - 1) . ' more' : '' ?>
                                        <?php else: ?>
                                            Order item
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-extrabold text-app-title">Rs. <?= number_format($o['total_amount']) ?></div>
                                    <span class="text-[10px] text-app-muted block mt-0.5"><?= e($o['payment_method']) ?></span>
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $statusBadgeClass ?>">
                                        <?= e($o['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3.5 pr-4 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <!-- View Modal Button -->
                                        <button type="button" onclick="viewOrderDetails(<?= htmlspecialchars(json_encode($o)) ?>)" class="p-1.5 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-500 transition-colors cursor-pointer" title="View Full Order Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        
                                        <!-- Delete Trigger Button -->
                                        <button type="button" onclick="openDeleteOrderModal(<?= $o['id'] ?>, '<?= e(addslashes($o['order_number'])) ?>')" class="p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-500 transition-colors cursor-pointer" title="Delete Order">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Component -->
                <?= render_admin_pagination($totalFilteredOrders, $per_page, $page) ?>

            </form>

        </div>

    </main>

</div>

<!-- ===================================================================== -->
<!-- ORDER DETAILS & PACKING SLIP MODAL -->
<!-- ===================================================================== -->
<div id="order-details-modal" style="display: none;" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm overflow-y-auto p-3 sm:p-6 justify-center items-start">
    <div class="bg-app-card border border-app rounded-3xl max-w-2xl w-full max-h-[92vh] flex flex-col my-auto sm:my-8 shadow-2xl relative overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-app flex items-center justify-between bg-slate-500/5 shrink-0">
            <div>
                <h3 class="text-base font-black text-app-title brand-font flex items-center space-x-2">
                    <span id="modal-order-number">Order #</span>
                    <span id="modal-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">Status</span>
                </h3>
                <p id="modal-order-date" class="text-xs text-app-muted mt-0.5">Date &bull; Time</p>
            </div>
            <button type="button" onclick="closeOrderModal()" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-red-500/20 text-app-muted hover:text-red-500 flex items-center justify-center transition-all cursor-pointer border border-app shadow-sm" title="Close dialog">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1 text-xs">
            
            <!-- Customer & Shipping Card -->
            <div class="p-4 rounded-2xl bg-slate-500/10 border border-app grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-[10px] font-bold text-app-muted uppercase tracking-wider block mb-1">Customer Details</span>
                    <div id="modal-cust-name" class="font-bold text-sm text-app-title">Customer Name</div>
                    <div class="flex items-center space-x-2 mt-1">
                        <span id="modal-cust-phone" class="text-app-body font-mono">0300 1234567</span>
                        <a id="modal-whatsapp-link" href="#" target="_blank" class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-500 font-bold text-[10px] hover:bg-emerald-500/30 transition-colors">
                            WhatsApp Chat
                        </a>
                    </div>
                    <div class="flex items-center space-x-2 mt-1">
                        <span id="modal-cust-email" class="text-app-muted">email@example.com</span>
                        <form id="resend-email-form" method="POST" action="orders.php" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                            <input type="hidden" name="action" value="resend_email">
                            <input type="hidden" name="order_id" id="resend-email-order-id" value="">
                            <button type="submit" id="modal-resend-email-btn" title="Send or resend order confirmation email via SMTP" class="hidden px-2 py-0.5 rounded bg-blue-500/15 text-blue-500 hover:bg-blue-500/25 font-bold text-[10px] transition-colors cursor-pointer">
                                ✉️ Resend Confirmation Email
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-app-muted uppercase tracking-wider block mb-1">Shipping Destination</span>
                    <div id="modal-cust-city" class="font-bold text-app-title">City</div>
                    <div id="modal-cust-address" class="text-app-muted mt-0.5 leading-relaxed">Delivery Address</div>
                    <div id="modal-cust-notes" class="text-amber-500 mt-1 italic"></div>
                </div>
            </div>

            <!-- Items Table -->
            <div>
                <h4 class="font-bold text-app-title uppercase tracking-wider text-[11px] mb-2 flex items-center justify-between">
                    <span>Ordered Items</span>
                    <span id="modal-items-count-badge" class="text-app-muted font-normal">0 items</span>
                </h4>
                <div class="rounded-xl border border-app overflow-hidden">
                    <table class="w-full text-left text-xs divide-y divide-app">
                        <thead class="bg-slate-500/5 text-app-muted font-bold text-[10px] uppercase">
                            <tr>
                                <th class="p-2.5 pl-3">Item</th>
                                <th class="p-2.5 text-center">Qty</th>
                                <th class="p-2.5 text-right">Price</th>
                                <th class="p-2.5 pr-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="modal-items-tbody" class="divide-y divide-app">
                            <!-- Items injected dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financials Breakdown -->
            <div class="p-3.5 rounded-xl bg-slate-500/5 border border-app space-y-1.5">
                <div class="flex items-center justify-between text-app-muted">
                    <span>Subtotal</span>
                    <span id="modal-subtotal" class="font-semibold text-app-title">Rs. 0</span>
                </div>
                <div class="flex items-center justify-between text-app-muted">
                    <span>Shipping (Pakistan Express)</span>
                    <span class="text-emerald-500 font-bold uppercase text-[10px]">Free</span>
                </div>
                <div id="modal-discount-row" class="hidden flex items-center justify-between text-emerald-500">
                    <span>Discount</span>
                    <span id="modal-discount" class="font-bold">-Rs. 0</span>
                </div>
                <div class="flex items-center justify-between text-sm font-black text-app-title pt-1.5 border-t border-app">
                    <span>Grand Total Payable (<span id="modal-payment-method">COD</span>)</span>
                    <span id="modal-total" class="text-blue-500">Rs. 0</span>
                </div>
            </div>

            <!-- Status Update Form Inside Modal -->
            <form method="POST" action="orders.php" class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/25 space-y-3">
                <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modal-update-order-id" value="">

                <div class="flex items-center justify-between">
                    <label class="block font-bold text-xs text-blue-400 uppercase tracking-wide">
                        Change Order Status &amp; Admin Notes
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-app-muted mb-1 font-semibold">New Status:</label>
                        <select name="status" id="modal-status-select" class="w-full px-3 py-2 rounded-xl input-app border border-app text-app-title text-xs font-semibold focus:outline-none focus:border-blue-500 cursor-pointer">
                            <option value="Pending">⏳ Pending (New Order)</option>
                            <option value="Confirmed">📋 Confirmed (Packed &amp; Verified)</option>
                            <option value="Dispatched">🚚 Dispatched (In Transit with Courier)</option>
                            <option value="Delivered">✅ Delivered (Cash Collected)</option>
                            <option value="Cancelled">❌ Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] text-app-muted mb-1 font-semibold">Courier Tracking / Notes:</label>
                        <input type="text" name="notes" id="modal-notes-input" placeholder="e.g. Trax Tracking # 7492819" class="w-full px-3 py-2 rounded-xl input-app border border-app text-app-title text-xs placeholder:text-app-muted focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                        Save Status Update
                    </button>
                </div>
            </form>

        </div>

        <!-- Modal Footer -->
        <div class="p-4 px-6 border-t border-app bg-app-card flex items-center justify-between shrink-0">
            <button type="button" onclick="printOrderSlip()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition-colors flex items-center space-x-1.5 cursor-pointer">
                <span>🖨️</span>
                <span>Print Packing Slip</span>
            </button>
            <button type="button" onclick="closeOrderModal()" class="px-4 py-2 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-muted hover:text-app-title text-xs font-bold cursor-pointer">
                Close
            </button>
        </div>

    </div>
</div>

<!-- ===================================================================== -->
<!-- DELETE CONFIRMATION MODAL -->
<!-- ===================================================================== -->
<div id="delete-order-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm p-4 flex justify-center items-center">
    <div class="bg-app-card border border-app rounded-2xl max-w-sm w-full p-5 shadow-2xl text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-red-500/15 text-red-500 mx-auto flex items-center justify-center text-xl">
            ⚠️
        </div>
        <div>
            <h4 class="font-bold text-base text-app-title">Delete Order Record?</h4>
            <p class="text-xs text-app-muted mt-1">
                Are you sure you want to permanently delete order <strong id="delete-order-num-text" class="text-app-title"></strong>? This cannot be undone.
            </p>
        </div>
        <form method="POST" action="orders.php" class="flex items-center justify-center space-x-2">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="action" value="delete_order">
            <input type="hidden" name="order_id" id="delete-order-id-input" value="">

            <button type="button" onclick="closeDeleteOrderModal()" class="px-4 py-2 rounded-xl bg-slate-500/15 text-app-muted hover:text-app-title text-xs font-bold cursor-pointer">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold shadow-md cursor-pointer">
                Yes, Delete
            </button>
        </form>
    </div>
</div>

<script>
    let activeOrderData = null;

    // View Order Details Modal
    function viewOrderDetails(order) {
        activeOrderData = order;
        document.getElementById('modal-order-number').textContent = 'Order #' + order.order_number;
        document.getElementById('modal-update-order-id').value = order.id;
        document.getElementById('modal-order-date').textContent = new Date(order.created_at).toLocaleString();

        // Status badge
        const badge = document.getElementById('modal-status-badge');
        badge.textContent = order.status;
        badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border ';
        if (order.status === 'Pending') badge.className += 'bg-amber-500/15 text-amber-500 border-amber-500/30';
        else if (order.status === 'Confirmed') badge.className += 'bg-blue-500/15 text-blue-500 border-blue-500/30';
        else if (order.status === 'Dispatched') badge.className += 'bg-purple-500/15 text-purple-500 border-purple-500/30';
        else if (order.status === 'Delivered') badge.className += 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30';
        else badge.className += 'bg-red-500/15 text-red-500 border-red-500/30';

        document.getElementById('modal-status-select').value = order.status;
        document.getElementById('modal-notes-input').value = order.notes || '';

        // Customer details
        document.getElementById('modal-cust-name').textContent = order.customer_name;
        document.getElementById('modal-cust-phone').textContent = order.customer_phone;
        const emailEl = document.getElementById('modal-cust-email');
        const resendBtn = document.getElementById('modal-resend-email-btn');
        if (order.customer_email && order.customer_email.trim()) {
            emailEl.textContent = order.customer_email;
            if (resendBtn) {
                resendBtn.classList.remove('hidden');
                document.getElementById('resend-email-order-id').value = order.id;
            }
        } else {
            emailEl.textContent = 'No email provided';
            if (resendBtn) resendBtn.classList.add('hidden');
        }
        document.getElementById('modal-cust-city').textContent = order.customer_city;
        document.getElementById('modal-cust-address').textContent = order.customer_address;
        
        const notesEl = document.getElementById('modal-cust-notes');
        if (order.notes) {
            notesEl.textContent = 'Note: ' + order.notes;
            notesEl.classList.remove('hidden');
        } else {
            notesEl.classList.add('hidden');
        }

        // WhatsApp Link
        let rawPhone = (order.customer_phone || '').replace(/[^0-9]/g, '');
        if (rawPhone.startsWith('0')) rawPhone = '92' + rawPhone.slice(1);
        else if (!rawPhone.startsWith('92')) rawPhone = '92' + rawPhone;
        const msg = encodeURIComponent(`Hi ${order.customer_name}, this is ChargeHub.pk regarding your order #${order.order_number}.`);
        document.getElementById('modal-whatsapp-link').href = `https://wa.me/${rawPhone}?text=${msg}`;

        // Items list
        let items = [];
        try {
            items = JSON.parse(order.order_items_json) || [];
        } catch(e) {
            items = [];
        }

        let itemsCount = 0;
        let tbodyHtml = '';
        items.forEach(it => {
            const qty = parseInt(it.qty) || 1;
            const price = parseFloat(it.price) || 0;
            const lineTotal = qty * price;
            itemsCount += qty;

            tbodyHtml += `
                <tr class="hover:bg-slate-500/5">
                    <td class="p-2.5 pl-3">
                        <div class="flex items-center space-x-2">
                            <img src="../${it.main_image || 'images/Hero Banner.jpg'}" class="w-8 h-8 rounded-lg object-contain bg-slate-900/10 p-0.5 border border-app shrink-0" onerror="this.src='../images/Hero Banner.jpg'">
                            <div>
                                <strong class="text-app-title font-bold block line-clamp-1">${it.name}</strong>
                                <span class="text-[10px] text-app-muted">${it.slug || ''}</span>
                            </div>
                        </div>
                    </td>
                    <td class="p-2.5 text-center font-bold text-app-title">${qty}</td>
                    <td class="p-2.5 text-right text-app-body font-mono">Rs. ${price.toLocaleString()}</td>
                    <td class="p-2.5 pr-3 text-right font-bold text-app-title font-mono">Rs. ${lineTotal.toLocaleString()}</td>
                </tr>
            `;
        });

        document.getElementById('modal-items-tbody').innerHTML = tbodyHtml || '<tr><td colspan="4" class="p-3 text-center text-app-muted">No items recorded</td></tr>';
        document.getElementById('modal-items-count-badge').textContent = `${itemsCount} item${itemsCount === 1 ? '' : 's'}`;

        // Financials
        document.getElementById('modal-subtotal').textContent = `Rs. ${Number(order.subtotal || order.total_amount).toLocaleString()}`;
        document.getElementById('modal-total').textContent = `Rs. ${Number(order.total_amount).toLocaleString()}`;
        document.getElementById('modal-payment-method').textContent = order.payment_method || 'COD';

        const discountRow = document.getElementById('modal-discount-row');
        if (parseFloat(order.discount_amount) > 0) {
            discountRow.classList.remove('hidden');
            document.getElementById('modal-discount').textContent = `-Rs. ${Number(order.discount_amount).toLocaleString()}`;
        } else {
            discountRow.classList.add('hidden');
        }

        const m = document.getElementById('order-details-modal');
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
            m.style.display = 'flex';
        }
    }

    function closeOrderModal() {
        const m = document.getElementById('order-details-modal');
        if (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
            m.style.display = 'none';
        }
    }

    // Delete Modal
    function openDeleteOrderModal(id, num) {
        document.getElementById('delete-order-id-input').value = id;
        document.getElementById('delete-order-num-text').textContent = num;
        const m = document.getElementById('delete-order-modal');
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
            m.style.display = 'flex';
        }
    }

    function closeDeleteOrderModal() {
        const m = document.getElementById('delete-order-modal');
        if (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
            m.style.display = 'none';
        }
    }

    // Print Packing Slip
    function printOrderSlip() {
        if (!activeOrderData) return;
        const printWin = window.open('', '_blank', 'width=800,height=900');
        let itemsHtml = '';
        let items = [];
        try { items = JSON.parse(activeOrderData.order_items_json) || []; } catch(e) {}
        
        items.forEach((it, idx) => {
            printWin;
            itemsHtml += `
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">${idx + 1}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>${it.name}</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">${it.qty}</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">Rs. ${Number(it.price).toLocaleString()}</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">Rs. ${(it.qty * it.price).toLocaleString()}</td>
                </tr>
            `;
        });

        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Packing Slip - ${activeOrderData.order_number}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 24px; color: #111; line-height: 1.4; font-size: 13px; }
                    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0052cc; padding-bottom: 12px; margin-bottom: 16px; }
                    .logo { font-size: 20px; font-weight: bold; color: #0052cc; }
                    .box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 12px; }
                    th { background: #f1f3f5; padding: 8px; border: 1px solid #ddd; text-align: left; }
                    .total { text-align: right; font-size: 16px; font-weight: bold; color: #0052cc; margin-top: 12px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <div class="logo">⚡ ChargeHub.pk</div>
                        <div style="font-size: 11px; color: #666;">Official GaN Chargers &amp; Kevlar Cables Fulfillment Center</div>
                    </div>
                    <div style="text-align: right;">
                        <h2 style="margin: 0; font-size: 18px;">PACKING SLIP</h2>
                        <div style="font-weight: bold; color: #0052cc;">#${activeOrderData.order_number}</div>
                        <div style="font-size: 11px; color: #777;">${new Date(activeOrderData.created_at).toLocaleDateString()}</div>
                    </div>
                </div>

                <div class="box">
                    <strong>Ship To:</strong><br>
                    ${activeOrderData.customer_name}<br>
                    ${activeOrderData.customer_address}<br>
                    ${activeOrderData.customer_city}, Pakistan<br>
                    <strong>Phone:</strong> ${activeOrderData.customer_phone}<br>
                    ${activeOrderData.notes ? '<strong>Notes:</strong> ' + activeOrderData.notes : ''}
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Product Description</th>
                            <th style="width: 50px; text-align: center;">Qty</th>
                            <th style="width: 90px; text-align: right;">Unit Price</th>
                            <th style="width: 100px; text-align: right;">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>

                <div class="total">
                    Payment Method: ${activeOrderData.payment_method || 'Cash on Delivery'}<br>
                    Total Amount: Rs. ${Number(activeOrderData.total_amount).toLocaleString()}
                </div>

                <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #888; border-top: 1px dashed #ccc; padding-top: 12px;">
                    Thank you for choosing ChargeHub.pk! All products carry official 18-month swap warranty.
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
        printWin.focus();
        setTimeout(() => { printWin.print(); }, 250);
    }

    // Bulk selection handler
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.order-cb');
    const bulkBar = document.getElementById('bulk-bar');
    const selectedCount = document.getElementById('selected-count');

    function updateBulkState() {
        const checked = Array.from(checkboxes).filter(c => c.checked);
        if (selectedCount) selectedCount.textContent = checked.length;
        if (checked.length > 0) {
            bulkBar.classList.remove('hidden');
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(c => c.checked = selectAll.checked);
            updateBulkState();
        });
    }

    checkboxes.forEach(c => c.addEventListener('change', updateBulkState));

    // Close on Escape Key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeOrderModal();
            closeDeleteOrderModal();
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
