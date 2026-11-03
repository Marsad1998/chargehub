<?php
/**
 * Concise Admin Topbar Component with Dark/Light Toggle & DB Status
 */
?>
<header class="h-14 bg-app-header border-b border-app px-4 sm:px-6 flex items-center justify-between shrink-0 transition-colors duration-200">
    <!-- Left: Mobile Menu & Breadcrumbs -->
    <div class="flex items-center space-x-3">
        <button onclick="toggleMobileSidebar()" class="md:hidden p-1.5 rounded-lg text-app-muted hover:text-app-title hover:bg-slate-500/10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
        </button>
        <div class="flex items-center space-x-2">
            <h1 class="text-sm font-black text-app-title brand-font tracking-tight"><?= e($page_title ?? 'Admin Portal') ?></h1>
            <span class="text-xs text-app-muted hidden sm:inline">&bull; ChargeHub Control Center</span>
        </div>
    </div>

    <!-- Right: Theme Toggle, DB Status & Action Buttons -->
    <div class="flex items-center space-x-2.5">
        <!-- MySQL Status Badge -->
        <span class="hidden lg:inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>MySQL (phpMyAdmin)</span>
        </span>

        <!-- Light / Dark Theme Toggle Button -->
        <button type="button" onclick="toggleAdminTheme()" class="px-2.5 py-1 rounded-xl bg-app-card border border-app text-app-title hover:bg-slate-500/10 text-xs font-semibold flex items-center space-x-1.5 transition-all shadow-sm" title="Toggle Light / Dark Mode">
            <span id="theme-toggle-icon">☀️</span>
            <span id="theme-toggle-text" class="hidden sm:inline text-[11px]">Theme</span>
        </button>

        <!-- Quick Store Front Link -->
        <a href="../index.php" target="_blank" class="px-3 py-1 rounded-xl bg-slate-500/10 hover:bg-slate-500/20 text-app-title text-xs font-semibold transition-all flex items-center space-x-1" title="View Storefront">
            <span>🌐</span>
            <span class="hidden sm:inline text-[11px]">Store</span>
        </a>

        <!-- Logout Button -->
        <a href="logout.php" class="px-2.5 sm:px-3 py-1 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-500 hover:text-red-600 text-xs font-bold transition-all flex items-center space-x-1 border border-red-500/20 shadow-xs" title="Sign out of Admin Portal">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="text-[11px]">Logout</span>
        </a>
    </div>
</header>
