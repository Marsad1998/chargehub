<?php
// Always start an unlimited output buffer to prevent 4KB chunk overflows
ob_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_admin_login();

// 1. Canonical slash handling: If accessed as /admin without trailing slash, redirect with 301
$request_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!empty($request_path) && substr($request_path, -1) !== '/' && !str_ends_with($request_path, '.php')) {
    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    admin_redirect($request_path . '/' . $queryString);
}

$current_page = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$current_tab = $_GET['tab'] ?? '';

// 2. Compute canonical admin base URL so relative links ALWAYS stay within admin
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$admin_base_url = rtrim($script_dir, '/') . '/';
if (!str_ends_with($admin_base_url, '/admin/')) {
    if (preg_match('#^(.*/admin)(/.*)?$#i', $_SERVER['SCRIPT_NAME'] ?? '', $m)) {
        $admin_base_url = rtrim($m[1], '/') . '/';
    }
}
// 3. Global Reusable Admin Pagination Component
if (!function_exists('render_admin_pagination')) {
    function render_admin_pagination($total_items, $per_page, $current_page) {
        $total_pages = max(1, (int)ceil($total_items / $per_page));
        if ($total_items === 0) return '';
        
        $start_item = (($current_page - 1) * $per_page) + 1;
        $end_item = min($current_page * $per_page, $total_items);
        
        $build_url = function($page_num) {
            $p = $_GET;
            $p['page'] = $page_num;
            return '?' . http_build_query($p);
        };

        ob_start();
        ?>
        <div class="px-4 py-3 bg-app-card border-t border-app flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="text-app-muted font-medium">
                Showing <strong class="text-app-title"><?= $start_item ?></strong> to <strong class="text-app-title"><?= $end_item ?></strong> of <strong class="text-app-title"><?= number_format($total_items) ?></strong> entries
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="flex items-center space-x-1">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                    <a href="<?= e($build_url($current_page - 1)) ?>" class="px-2.5 sm:px-3 py-1.5 rounded-lg input-app border border-app text-app-title hover:bg-slate-500/10 transition-colors font-semibold flex items-center space-x-1 cursor-pointer">
                        <span>&larr;</span>
                        <span class="hidden sm:inline">Prev</span>
                    </a>
                <?php else: ?>
                    <span class="px-2.5 sm:px-3 py-1.5 rounded-lg border border-app/50 text-app-muted/50 font-semibold cursor-not-allowed">
                        <span>&larr;</span>
                        <span class="hidden sm:inline">Prev</span>
                    </span>
                <?php endif; ?>

                <!-- Page Number Pills (Windowed around current) -->
                <?php
                $start_p = max(1, $current_page - 2);
                $end_p = min($total_pages, $current_page + 2);
                if ($start_p > 1): ?>
                    <a href="<?= e($build_url(1)) ?>" class="w-8 h-8 rounded-lg input-app border border-app text-app-title hover:bg-slate-500/10 flex items-center justify-center font-bold">1</a>
                    <?php if ($start_p > 2): ?><span class="px-1 text-app-muted">...</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start_p; $p <= $end_p; $p++): ?>
                    <?php if ($p == $current_page): ?>
                        <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black shadow-sm"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= e($build_url($p)) ?>" class="w-8 h-8 rounded-lg input-app border border-app text-app-title hover:bg-slate-500/10 flex items-center justify-center font-bold transition-colors"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end_p < $total_pages): ?>
                    <?php if ($end_p < $total_pages - 1): ?><span class="px-1 text-app-muted">...</span><?php endif; ?>
                    <a href="<?= e($build_url($total_pages)) ?>" class="w-8 h-8 rounded-lg input-app border border-app text-app-title hover:bg-slate-500/10 flex items-center justify-center font-bold"><?= $total_pages ?></a>
                <?php endif; ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= e($build_url($current_page + 1)) ?>" class="px-2.5 sm:px-3 py-1.5 rounded-lg input-app border border-app text-app-title hover:bg-slate-500/10 transition-colors font-semibold flex items-center space-x-1 cursor-pointer">
                        <span class="hidden sm:inline">Next</span>
                        <span>&rarr;</span>
                    </a>
                <?php else: ?>
                    <span class="px-2.5 sm:px-3 py-1.5 rounded-lg border border-app/50 text-app-muted/50 font-semibold cursor-not-allowed">
                        <span class="hidden sm:inline">Next</span>
                        <span>&rarr;</span>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="theme-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= e($admin_base_url) ?>">
    <title><?= isset($page_title) ? e($page_title) . ' &bull; ChargeHub Admin' : 'Admin Portal &bull; ChargeHub.pk' ?></title>
    
    <!-- Fast Theme Detector (Zero Flash of Unstyled Content) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('chargehub_admin_theme') || 'dark';
                if (savedTheme === 'light') {
                    document.documentElement.classList.remove('theme-dark');
                    document.documentElement.classList.add('theme-light');
                } else {
                    document.documentElement.classList.remove('theme-light');
                    document.documentElement.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Cabinet+Grotesk:wght@700;800;900&display=swap" rel="stylesheet">
    
    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .brand-font { font-family: 'Cabinet Grotesk', sans-serif; }

        /* THEME VARIABLES FOR DARK / LIGHT MODE */
        html.theme-dark {
            --app-bg: #0b0f19;
            --app-sidebar-bg: #0d1424;
            --app-header-bg: #0d1424;
            --app-card-bg: #111827;
            --app-card-border: #1e293b;
            --app-text-title: #ffffff;
            --app-text-body: #e2e8f0;
            --app-text-muted: #94a3b8;
            --app-input-bg: #0f172a;
            --app-input-border: #334155;
            --app-hover-bg: rgba(255, 255, 255, 0.05);
            --app-table-header: #0d1424;
            --app-table-stripe: #131b2e;
            --app-badge-bg: rgba(255, 255, 255, 0.06);
            --app-card-shadow: none;
        }

        /* REFINED HIGH-CONTRAST LIGHT MODE */
        html.theme-light {
            --app-bg: #edf2f7;                  /* Distinct cool slate canvas (not washed out white) */
            --app-sidebar-bg: #ffffff;          /* Pure crisp white sidebar */
            --app-header-bg: #ffffff;           /* Pure white topbar */
            --app-card-bg: #ffffff;             /* Crisp white cards standing out on gray canvas */
            --app-card-border: #cbd5e1;         /* Defined, visible Slate-300 borders */
            --app-text-title: #090d16;          /* Deep jet black-navy for titles (maximum contrast) */
            --app-text-body: #1e293b;           /* Slate-800 for high readability */
            --app-text-muted: #475569;          /* Slate-600 (not faint light gray) */
            --app-input-bg: #ffffff;            /* White input field */
            --app-input-border: #94a3b8;        /* Slate-400 for clearly delineated inputs */
            --app-hover-bg: #e2e8f0;            /* Distinct hover effect */
            --app-table-header: #e2e8f0;        /* Clear table column header band */
            --app-table-stripe: #f8fafc;
            --app-badge-bg: #e2e8f0;
            --app-card-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.08), 0 1px 2px -1px rgba(15, 23, 42, 0.06);
        }

        .bg-app-canvas { background-color: var(--app-bg); }
        .bg-app-sidebar { background-color: var(--app-sidebar-bg); }
        .bg-app-header { background-color: var(--app-header-bg); }
        .bg-app-card { 
            background-color: var(--app-card-bg); 
            box-shadow: var(--app-card-shadow);
        }
        .border-app { border-color: var(--app-card-border); }
        .text-app-title { color: var(--app-text-title); }
        .text-app-body { color: var(--app-text-body); }
        .text-app-muted { color: var(--app-text-muted); }
        .input-app {
            background-color: var(--app-input-bg);
            border-color: var(--app-input-border);
            color: var(--app-text-title);
        }
        .input-app:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        select, select.input-app, select option {
            background-color: var(--app-input-bg) !important;
            color: var(--app-text-title) !important;
        }

        /* Modern Select2 Theme Integration */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            background-color: var(--app-input-bg) !important;
            border: 1px solid var(--app-input-border) !important;
            border-radius: 0.5rem !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.15s ease-in-out;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--app-text-title) !important;
            line-height: 40px !important;
            padding-left: 12px !important;
            padding-right: 28px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--app-text-muted) !important;
            font-weight: 400 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--app-text-muted) transparent transparent transparent !important;
        }
        .select2-dropdown {
            background-color: var(--app-card-bg) !important;
            border: 1px solid var(--app-card-border) !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3) !important;
            overflow: hidden !important;
            z-index: 99999 !important;
        }
        .select2-search--dropdown {
            padding: 8px !important;
            background-color: var(--app-card-bg) !important;
            border-bottom: 1px solid var(--app-card-border) !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: var(--app-input-bg) !important;
            border: 1px solid var(--app-input-border) !important;
            border-radius: 0.375rem !important;
            color: var(--app-text-title) !important;
            padding: 8px 12px !important;
            outline: none !important;
            font-size: 0.875rem !important;
            width: 100% !important;
        }
        .select2-container--default .select2-results__option {
            color: var(--app-text-body) !important;
            padding: 9px 12px !important;
            font-size: 0.875rem !important;
            transition: background 0.1s ease;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: rgba(37, 99, 235, 0.15) !important;
            color: var(--app-text-title) !important;
            font-weight: 600 !important;
        }

        /* High contrast light mode helper overrides */
        html.theme-light .bg-slate-500\/10 {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
        html.theme-light .bg-slate-500\/5 {
            background-color: #e2e8f0 !important;
        }
        html.theme-light .bg-slate-500\/15 {
            background-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        html.theme-light .hover\:bg-slate-500\/10:hover {
            background-color: #e2e8f0 !important;
        }
        html.theme-light .hover\:bg-slate-500\/5:hover {
            background-color: #f1f5f9 !important;
        }
        html.theme-light thead.bg-slate-500\/5 {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        html.theme-light .text-slate-400 {
            color: #475569 !important;
        }
        html.theme-light .border-app {
            border-color: #cbd5e1 !important;
        }
        html.theme-light table thead {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
    </style>
</head>
<body class="bg-app-canvas text-app-body min-h-screen flex antialiased transition-colors duration-200">
