<?php
/**
 * ChargeHub.pk - Global Mobile & Tablet Sticky Top Bar
 * Always fixed at top on screens < 1024px (mobile & tablet)
 * Provides permanent, 1-tap access to Logo, Search, Cart, and Hamburger Sidebar.
 */
?>
<!-- STICKY MOBILE & TABLET TOP BAR (Visible < 1024px, Fixed at Top) -->
<header id="mobile-sticky-topbar" 
        class="fixed top-0 inset-x-0 w-full h-14 bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-[0_2px_12px_rgba(0,0,0,0.06)] z-40 flex lg:hidden items-center justify-between px-3.5 sm:px-6 transition-all">
    
    <!-- LEFT: CHARGEHUB LOGO -->
    <a href="index.php" class="flex items-center space-x-2 group shrink-0" title="ChargeHub.pk - Genuine Chargers, Genuine Cables">
        <img src="images/chargehub_horizontal.png" alt="ChargeHub.pk" class="h-7 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105">
    </a>

    <!-- RIGHT: SEARCH, CART & SIDEBAR HAMBURGER -->
    <div class="flex items-center space-x-1.5 sm:space-x-2.5 shrink-0">
        <!-- Search Button -->
        <a href="explore.php" class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-700 hover:text-brand-blue transition-all active:scale-95" title="Search catalog">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </a>

        <!-- Shopping Bag Cart Button -->
        <button type="button" onclick="openCartDrawer()" class="relative w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-700 hover:text-brand-blue transition-all active:scale-95 cursor-pointer" title="View Cart">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span class="cart-counter-badge absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-orange text-[10px] font-bold text-white flex items-center justify-center shadow-xs">
                0
            </span>
        </button>

        <!-- Mobile & Tablet Sidebar Hamburger Trigger -->
        <button type="button" onclick="openMobileSidebar()" class="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-800 hover:text-brand-blue transition-all active:scale-95 cursor-pointer" title="Open Navigation Menu" aria-label="Open Navigation Sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16m-7 6h7"/>
            </svg>
        </button>
    </div>
</header>
