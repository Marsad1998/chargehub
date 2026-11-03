<?php
/**
 * ChargeHub.pk - Global Off-Canvas Mobile & Tablet Sidebar Navigation
 * Minimal White Design with Theme Color Contrast (Apple/Anker style)
 * Included globally across storefront pages: index.php, explore.php, product-detail.php, checkout.php
 */
require_once __DIR__ . '/../config/db.php';
$wa_sidebar = get_whatsapp_config();
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!-- ================================================================= -->
<!-- GLOBAL MOBILE & TABLET OFF-CANVAS SIDEBAR NAVIGATION -->
<!-- ================================================================= -->
<div id="mobile-sidebar-wrapper" class="fixed inset-0 z-[9998] transition-opacity duration-300 opacity-0 pointer-events-none" aria-hidden="true">
    <!-- Backdrop Overlay (Tap to close) -->
    <div id="mobile-sidebar-backdrop" onclick="closeMobileSidebar()" class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300 cursor-pointer"></div>

    <!-- Slide-Out Sidebar Panel (Left Side) - Minimal White Design with Theme Color Contrast -->
    <aside id="mobile-sidebar-panel" 
           class="absolute top-0 left-0 h-full w-[315px] sm:w-[350px] max-w-[85vw] bg-white text-gray-900 shadow-2xl flex flex-col justify-between transform -translate-x-full transition-transform duration-300 ease-out border-r border-gray-200/90 z-[9999]"
           role="dialog" aria-modal="true" aria-label="Mobile Navigation Menu">
        
        <!-- TOP SECTION: BRAND HEADER & INSTANT SEARCH -->
        <div class="p-5 border-b border-gray-100 bg-white shrink-0">
            <div class="flex items-center justify-between mb-3.5">
                <!-- Brand Logo -->
                <a href="index.php" onclick="closeMobileSidebar()" class="flex items-center space-x-2 group">
                    <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-8 w-auto object-contain transition-transform group-hover:scale-105">
                </a>
                
                <!-- Close Button -->
                <button type="button" 
                        onclick="closeMobileSidebar()" 
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-900 flex items-center justify-center transition-all cursor-pointer active:scale-95" 
                        title="Close navigation menu"
                        aria-label="Close menu">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Store Trust Pill -->
            <div class="flex items-center space-x-2 text-[11px] text-blue-900 mb-3 bg-blue-50/80 border border-blue-100 px-3 py-1.5 rounded-full font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-gray-900">Official Pakistan Store</span>
                <span class="text-gray-300">&bull;</span>
                <span class="text-brand-orange font-bold">100% Genuine</span>
            </div>

            <!-- Instant Search Bar -->
            <form action="explore.php" method="GET" class="relative" onsubmit="closeMobileSidebar()">
                <input type="text" 
                       name="search" 
                       placeholder="Search chargers, cables, banks..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-gray-50 focus:bg-white border border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-blue-100 text-xs text-gray-900 placeholder-gray-400 focus:outline-none transition-all"
                       autocomplete="off">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>
        </div>

        <!-- MIDDLE SECTION: SCROLLABLE NAVIGATION LINKS & SHORTCUTS -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-5 divide-y divide-gray-100 scrollbar-thin">
            
            <!-- MAIN NAVIGATION LIST -->
            <nav class="space-y-1">
                <p class="text-[10.5px] font-bold tracking-wider uppercase text-gray-400 mb-2 px-3">
                    Menu Navigation
                </p>

                <!-- Home -->
                <a href="index.php" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all <?= ($current_page === 'index.php' || $current_page === '') ? 'bg-brand-blue text-white shadow-md shadow-blue-500/25' : 'text-gray-700 hover:bg-blue-50/80 hover:text-brand-blue' ?>">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">🏠</span>
                        <span>Home</span>
                    </span>
                    <span class="text-[10px] <?= ($current_page === 'index.php' || $current_page === '') ? 'text-white/80' : 'text-gray-400' ?>">Storefront</span>
                </a>

                <!-- Product Catalog -->
                <a href="explore.php" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all <?= ($current_page === 'explore.php') ? 'bg-brand-blue text-white shadow-md shadow-blue-500/25' : 'text-gray-700 hover:bg-blue-50/80 hover:text-brand-blue' ?>">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">📂</span>
                        <span>All Products Catalog</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-md <?= ($current_page === 'explore.php') ? 'bg-white/20 text-white' : 'bg-blue-50 text-brand-blue border border-blue-100' ?> text-[10px] font-extrabold">FULL</span>
                </a>

                <!-- Flagship Prime 140W -->
                <a href="product-detail.php?slug=anker-prime-140w-gan-charger" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all <?= ($current_page === 'product-detail.php') ? 'bg-brand-blue text-white shadow-md shadow-blue-500/25' : 'text-gray-700 hover:bg-blue-50/80 hover:text-brand-blue' ?>">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">⚡</span>
                        <span>Anker Prime 140W</span>
                    </span>
                    <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">HOT</span>
                </a>

                <!-- Fast Chargers -->
                <a href="explore.php?category=Chargers" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 hover:bg-blue-50/80 hover:text-brand-blue transition-all">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">🔌</span>
                        <span>Fast Wall Chargers</span>
                    </span>
                    <span class="text-[10px] text-gray-400">GaN Tech</span>
                </a>

                <!-- Genuine Cables -->
                <a href="explore.php?category=Cables" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 hover:bg-blue-50/80 hover:text-brand-blue transition-all">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">⚡</span>
                        <span>Genuine Kevlar Cables</span>
                    </span>
                    <span class="text-[10px] text-gray-400">100W-240W</span>
                </a>

                <!-- Power Banks -->
                <a href="explore.php?category=PowerBanks" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 hover:bg-blue-50/80 hover:text-brand-blue transition-all">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">🔋</span>
                        <span>Power Banks &amp; Stations</span>
                    </span>
                    <span class="text-[10px] text-gray-400">MagSafe</span>
                </a>

                <!-- 10.10 Sale Deals -->
                <a href="index.php#sale" onclick="closeMobileSidebar()" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-gradient-to-r from-brand-orange to-[#ff6b00] text-white shadow-md shadow-orange-500/20 hover:brightness-105 transition-all mt-1">
                    <span class="flex items-center space-x-3">
                        <span class="text-base">🔥</span>
                        <span>10.10 Super SALE Deals</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-white text-brand-orange text-[10px] font-black">UP TO 50%</span>
                </a>
            </nav>

            <!-- POPULAR QUICK FILTERS / CATEGORIES -->
            <div class="pt-4 space-y-2">
                <p class="text-[10.5px] font-bold tracking-wider uppercase text-gray-400 mb-2 px-3">
                    Quick Categories
                </p>
                <div class="grid grid-cols-2 gap-2 px-1">
                    <a href="explore.php?category=Chargers" onclick="closeMobileSidebar()" 
                       class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-blue-50/60 border border-gray-200/70 hover:border-blue-200 text-[11px] font-semibold text-gray-700 hover:text-brand-blue flex items-center space-x-2 transition-all">
                        <span>🔌</span>
                        <span class="truncate">GaN Chargers</span>
                    </a>
                    <a href="explore.php?category=Cables" onclick="closeMobileSidebar()" 
                       class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-blue-50/60 border border-gray-200/70 hover:border-blue-200 text-[11px] font-semibold text-gray-700 hover:text-brand-blue flex items-center space-x-2 transition-all">
                        <span>⚡</span>
                        <span class="truncate">Type-C Cables</span>
                    </a>
                    <a href="explore.php?category=PowerBanks" onclick="closeMobileSidebar()" 
                       class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-blue-50/60 border border-gray-200/70 hover:border-blue-200 text-[11px] font-semibold text-gray-700 hover:text-brand-blue flex items-center space-x-2 transition-all">
                        <span>🧲</span>
                        <span class="truncate">MagSafe Banks</span>
                    </a>
                    <a href="explore.php?category=Hubs" onclick="closeMobileSidebar()" 
                       class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-blue-50/60 border border-gray-200/70 hover:border-blue-200 text-[11px] font-semibold text-gray-700 hover:text-brand-blue flex items-center space-x-2 transition-all">
                        <span>🖥️</span>
                        <span class="truncate">USB-C Hubs</span>
                    </a>
                </div>
            </div>

            <!-- TRUST BADGES -->
            <div class="pt-4 px-2 space-y-2 text-[11px] text-gray-600">
                <div class="flex items-center space-x-2.5">
                    <span class="text-brand-orange text-base">🛡️</span>
                    <span><strong>100% Genuine Certified</strong> Gear</span>
                </div>
                <div class="flex items-center space-x-2.5">
                    <span class="text-brand-blue text-base">🚚</span>
                    <span><strong>Free Express Delivery</strong> over ₨3,500</span>
                </div>
                <div class="flex items-center space-x-2.5">
                    <span class="text-emerald-600 text-base">🔄</span>
                    <span><strong>7-Day Replacement</strong> Warranty</span>
                </div>
            </div>

        </div>

        <!-- BOTTOM SECTION: CART & WHATSAPP SUPPORT HELPLINE -->
        <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50/60 space-y-2.5 shrink-0">
            <!-- View Cart Action Button -->
            <button type="button" 
                    onclick="closeMobileSidebar(); openCartDrawer();" 
                    class="w-full py-2.5 px-4 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs flex items-center justify-between transition-all active:scale-95 cursor-pointer shadow-sm">
                <span class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>View Shopping Bag</span>
                </span>
                <span class="cart-counter-badge px-2 py-0.5 rounded-full bg-brand-orange text-white text-[10px] font-extrabold shadow-sm">
                    0
                </span>
            </button>

            <?php
            $wa_url = 'https://wa.me/' . urlencode($wa_sidebar['number_clean'] ?? '923001234567') . '?text=' . urlencode($wa_sidebar['default_msg'] ?? 'Hi ChargeHub, I have a question about your products.');
            $wa_disp = $wa_sidebar['number_raw'] ?? '+92 300 1234567';
            ?>
            <!-- Live WhatsApp Help Button (Dynamic Setup from Admin) -->
            <a href="<?= htmlspecialchars($wa_url, ENT_QUOTES, 'UTF-8') ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               class="w-full py-2.5 px-4 rounded-xl bg-[#25d366] hover:bg-[#20ba59] text-white font-bold text-xs flex items-center justify-between transition-all shadow-md active:scale-95">
                <span class="flex items-center space-x-2">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.074-1.813-.418-1.503-.629-2.463-2.164-2.538-2.264-.075-.101-.607-.808-.607-1.543s.385-1.097.521-1.246c.137-.149.299-.187.4-.187.1 0 .2.001.288.006.096.005.225-.036.35.267.13.313.444 1.082.483 1.162.039.08.065.174.013.279-.052.104-.078.17-.154.26-.077.091-.162.203-.231.273-.078.079-.16.165-.069.321.091.156.405.669.869 1.083.598.534 1.102.7 1.258.779.156.078.247.065.338-.039.091-.104.39-.455.494-.611.104-.156.208-.13.35-.078.143.052.91.43 1.066.508.156.078.26.117.299.182.039.065.039.377-.105.782z"/>
                    </svg>
                    <span>WhatsApp Helpline</span>
                </span>
                <span class="text-[10px] font-mono text-emerald-950 bg-emerald-100 font-bold px-1.5 py-0.5 rounded">
                    <?= htmlspecialchars($wa_disp, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </a>
        </div>

    </aside>
</div>

<!-- ================================================================= -->
<!-- MOBILE SIDEBAR CONTROLLER SCRIPT (SELF-INITIALIZING) -->
<!-- ================================================================= -->
<script>
    (function() {
        const wrapper = document.getElementById('mobile-sidebar-wrapper');
        const panel = document.getElementById('mobile-sidebar-panel');

        function openSidebar() {
            if (!wrapper || !panel) return;
            wrapper.classList.remove('opacity-0', 'pointer-events-none');
            wrapper.classList.add('opacity-100', 'pointer-events-auto');
            panel.classList.remove('-translate-x-full');
            panel.classList.add('translate-x-0');
            document.body.style.overflow = 'hidden';
            wrapper.setAttribute('aria-hidden', 'false');
        }

        function closeSidebar() {
            if (!wrapper || !panel) return;
            panel.classList.remove('translate-x-0');
            panel.classList.add('-translate-x-full');
            wrapper.classList.remove('opacity-100', 'pointer-events-auto');
            wrapper.classList.add('opacity-0', 'pointer-events-none');
            document.body.style.overflow = '';
            wrapper.setAttribute('aria-hidden', 'true');
        }

        window.openMobileSidebar = openSidebar;
        window.closeMobileSidebar = closeSidebar;

        // Auto-wire any hamburger buttons or triggers
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('#mobile-menu-trigger, .mobile-menu-trigger, #mobile-sidebar-open, .mobile-sidebar-open, [data-mobile-sidebar-toggle]');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                openSidebar();
            }
        });

        // Close on Escape Key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && wrapper && wrapper.classList.contains('opacity-100')) {
                closeSidebar();
            }
        });
    })();
</script>