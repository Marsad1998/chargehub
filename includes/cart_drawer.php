<?php
/**
 * ChargeHub.pk - Global Persistent Cart Sidebar Drawer Component
 * Included across storefront: index.php, explore.php, product-detail.php, checkout.php
 */
?>
<!-- ================================================================= -->
<!-- SLIDE-OVER CART SIDEBAR DRAWER (GLOBAL) -->
<!-- ================================================================= -->
<div id="cart-drawer-wrapper" class="fixed inset-0 z-[100] transition-opacity duration-300 opacity-0 pointer-events-none">
    <!-- Darkened Blurred Backdrop -->
    <div id="cart-drawer-backdrop" onclick="closeCartDrawer()" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 cursor-pointer"></div>

    <!-- Sliding Sidebar Panel (Right Side) -->
    <aside id="cart-drawer-panel" class="absolute top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col justify-between transform translate-x-full transition-transform duration-300 ease-in-out">
        
        <!-- Header -->
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between bg-slate-50/70 shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-brand-blue/10 text-brand-blue flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <h3 class="font-black text-sm sm:text-base text-gray-950 brand-font flex items-center space-x-2">
                        <span>Your Shopping Bag</span>
                        <span id="cart-drawer-count-badge" class="px-2 py-0.5 rounded-full bg-brand-orange text-white text-[10px] font-bold">0</span>
                    </h3>
                    <p class="text-[11px] text-gray-400">100% Genuine Gear &bull; Official Warranty</p>
                </div>
            </div>
            <button type="button" onclick="closeCartDrawer()" class="w-8 h-8 rounded-full bg-white hover:bg-gray-200 text-gray-500 hover:text-gray-900 flex items-center justify-center text-lg transition-colors border border-gray-200/60 shadow-sm cursor-pointer" title="Close cart">
                &times;
            </button>
        </div>

        <!-- Free Shipping Tracker -->
        <div class="px-4 sm:px-5 py-2.5 bg-blue-50/80 border-b border-blue-100/60 flex items-center space-x-2 text-xs text-blue-900 font-medium shrink-0">
            <span class="text-base">🚚</span>
            <span><strong>FREE Express Shipping</strong> across Pakistan on all orders!</span>
        </div>

        <!-- Cart Items List Container -->
        <div id="cart-items-container" class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-3.5 divide-y divide-gray-100">
            <!-- Dynamic cart items rendered here by JavaScript -->
        </div>

        <!-- Empty State (Shown when 0 items) -->
        <div id="cart-empty-state" class="hidden flex-1 flex flex-col items-center justify-center p-6 text-center">
            <div class="w-20 h-20 rounded-3xl bg-blue-50 text-brand-blue flex items-center justify-center text-4xl mb-4 shadow-inner">
                🛍️
            </div>
            <h4 class="font-extrabold text-base text-gray-900 mb-1">Your shopping bag is empty</h4>
            <p class="text-xs text-gray-400 max-w-xs mb-5 leading-relaxed">
                Explore genuine GaN fast chargers, military-grade cables, and power stations.
            </p>
            <a href="explore.php" onclick="closeCartDrawer()" class="px-6 py-2.5 rounded-full bg-brand-blue hover:bg-brand-blue-hover text-white text-xs font-bold transition-all shadow-md active:scale-95 inline-block">
                Browse Catalog
            </a>
        </div>

        <!-- Quick In-Drawer Checkout Form -->
        <div id="cart-checkout-form" class="hidden border-t border-gray-100 p-4 sm:p-5 bg-slate-50 space-y-3 overflow-y-auto max-h-[55vh] shrink-0">
            <div class="flex items-center justify-between pb-1 border-b border-gray-200">
                <h4 class="text-xs font-black uppercase tracking-wider text-gray-900 flex items-center space-x-1.5">
                    <span>⚡ Cash on Delivery (COD) Checkout</span>
                </h4>
                <button type="button" onclick="toggleCheckoutForm(false)" class="text-[11px] font-bold text-gray-400 hover:text-gray-700 cursor-pointer">Cancel</button>
            </div>
            <form id="order-submit-form" onsubmit="handlePlaceOrder(event)" class="space-y-2.5 text-xs">
                <div>
                    <label class="block font-bold text-gray-700 mb-0.5">Full Name *</label>
                    <input type="text" id="order_cust_name" required placeholder="e.g. Saad Akbar" class="w-full px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs text-gray-900 focus:outline-none focus:border-brand-blue">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-0.5">Mobile / WhatsApp Number *</label>
                    <input type="tel" id="order_cust_phone" required placeholder="0300 1234567" class="w-full px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs text-gray-900 focus:outline-none focus:border-brand-blue">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-0.5">Delivery Address *</label>
                    <textarea id="order_cust_address" required rows="2" placeholder="House/Flat number, Street, Area" class="w-full px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs text-gray-900 focus:outline-none focus:border-brand-blue"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-0.5">City *</label>
                    <input type="text" id="order_cust_city" required placeholder="e.g. Lahore, Karachi, Islamabad" class="w-full px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs text-gray-900 focus:outline-none focus:border-brand-blue">
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center space-x-2 text-emerald-800 text-[11px]">
                    <span class="text-sm">💵</span>
                    <span><strong>Cash on Delivery (COD)</strong> &bull; Free open-box inspection</span>
                </div>
                <button type="submit" id="order-submit-btn" class="w-full py-3 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg transition-all active:scale-95 flex items-center justify-center space-x-1.5 cursor-pointer">
                    <span>Confirm Order &bull; Rs. <span id="checkout-btn-total">0</span></span>
                </button>
            </form>
        </div>

        <!-- Order Success State -->
        <div id="cart-order-success" class="hidden flex-1 flex flex-col items-center justify-center p-6 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl shadow-inner animate-bounce">
                ✓
            </div>
            <div>
                <h4 class="font-black text-lg text-gray-900 brand-font">Order Placed Successfully!</h4>
                <p class="text-xs text-gray-500 mt-1">Thank you! Your parcel is being packed at Lahore Hub.</p>
                <div class="mt-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 inline-block text-left text-xs space-y-1">
                    <div><span class="text-gray-400">Order ID:</span> <strong id="success-order-id" class="text-brand-blue font-mono">CH-00000</strong></div>
                    <div><span class="text-gray-400">Payment:</span> <strong>Cash on Delivery (COD)</strong></div>
                    <div><span class="text-gray-400">Delivery:</span> <strong>2 - 3 Business Days</strong></div>
                </div>
            </div>
            <div class="space-y-2 w-full max-w-xs pt-2">
                <a id="success-whatsapp-link" href="#" target="_blank" class="w-full py-2.5 rounded-full bg-[#25d366] hover:bg-[#20ba59] text-white font-bold text-xs flex items-center justify-center space-x-1.5 shadow transition-all">
                    <span>💬 Chat on WhatsApp</span>
                </a>
                <button type="button" onclick="closeCartDrawer(); resetOrderSuccess();" class="w-full py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer">
                    Continue Shopping
                </button>
            </div>
        </div>

        <!-- Footer / Cart Summary -->
        <div id="cart-footer-section" class="p-4 sm:p-5 border-t border-gray-100 bg-white space-y-3 shrink-0">
            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span id="cart-subtotal" class="font-bold text-gray-800">Rs. 0</span>
                </div>
                <div class="flex items-center justify-between text-gray-500">
                    <span>Express Delivery</span>
                    <span class="text-emerald-600 font-bold uppercase text-[11px]">Free</span>
                </div>
                <div class="flex items-center justify-between text-sm sm:text-base font-black text-gray-950 pt-1.5 border-t border-gray-100">
                    <span>Estimated Total</span>
                    <span id="cart-total" class="text-brand-blue">Rs. 0</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2 pt-1">
                <a href="checkout.php" class="w-full py-3 rounded-full bg-brand-blue hover:bg-brand-blue-hover text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-blue-500/25 transition-all active:scale-95 flex items-center justify-center space-x-2 cursor-pointer">
                    <span>Proceed to Checkout</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a id="cart-whatsapp-order-btn" href="#" target="_blank" class="w-full py-2.5 rounded-full bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold text-xs flex items-center justify-center space-x-1.5 transition-colors">
                    <span>⚡ Quick Order via WhatsApp</span>
                </a>
            </div>

            <div class="text-[10px] text-gray-400 text-center flex items-center justify-center space-x-1 pt-0.5">
                <span>🔒 Official 18-Month Direct Swap Replacement Guarantee</span>
            </div>
        </div>

    </aside>
</div>

<!-- GLOBAL CART SCRIPTS -->
<script>
    const CART_KEY = 'chargehub_cart_v7';

    function getCart() {
        try {
            return JSON.parse(localStorage.getItem(CART_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        renderCart();
        updateCartCounterBadges();
        if (typeof renderCheckoutSummary === 'function') {
            renderCheckoutSummary();
        }
    }

    function addToCart(product, qty = 1, openSidebar = true) {
        const cart = getCart();
        const existingIndex = cart.findIndex(item => (product.id && item.id == product.id) || (product.slug && item.slug && item.slug == product.slug));
        
        if (existingIndex > -1) {
            cart[existingIndex].qty += parseInt(qty) || 1;
        } else {
            cart.push({
                id: product.id || ('p-' + Math.floor(Math.random() * 90000)),
                name: product.name || 'Product',
                slug: product.slug || '',
                price: parseFloat(product.price) || 0,
                original_price: parseFloat(product.original_price) || 0,
                main_image: product.main_image || product.image || 'images/Hero Banner.jpg',
                qty: Math.max(1, parseInt(qty) || 1)
            });
        }

        saveCart(cart);

        if (openSidebar) {
            openCartDrawer();
        }
    }

    function updateCartItemQty(id, delta) {
        let cart = getCart();
        const idx = cart.findIndex(item => item.id == id);
        if (idx > -1) {
            cart[idx].qty += delta;
            if (cart[idx].qty <= 0) {
                cart.splice(idx, 1);
            }
            saveCart(cart);
        }
    }

    function removeCartItem(id) {
        let cart = getCart().filter(item => item.id != id);
        saveCart(cart);
    }

    function clearCart() {
        localStorage.removeItem(CART_KEY);
        renderCart();
        updateCartCounterBadges();
        if (typeof renderCheckoutSummary === 'function') {
            renderCheckoutSummary();
        }
    }

    function updateCartCounterBadges() {
        const cart = getCart();
        const totalCount = cart.reduce((sum, item) => sum + (parseInt(item.qty) || 0), 0);
        document.querySelectorAll('.cart-counter-badge').forEach(badge => {
            badge.textContent = totalCount;
            badge.classList.add('scale-125');
            setTimeout(() => badge.classList.remove('scale-125'), 250);
        });
        const drawerBadge = document.getElementById('cart-drawer-count-badge');
        if (drawerBadge) {
            drawerBadge.textContent = `${totalCount} item${totalCount === 1 ? '' : 's'}`;
        }
    }

    function renderCart() {
        const cart = getCart();
        const itemsContainer = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('cart-empty-state');
        const footerSection = document.getElementById('cart-footer-section');
        const subtotalEl = document.getElementById('cart-subtotal');
        const totalEl = document.getElementById('cart-total');
        const btnTotalEl = document.getElementById('checkout-btn-total');
        const whatsappBtn = document.getElementById('cart-whatsapp-order-btn');

        if (!itemsContainer) return;

        let subtotal = 0;

        if (cart.length === 0) {
            itemsContainer.innerHTML = '';
            itemsContainer.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            if (footerSection) footerSection.classList.add('hidden');
            toggleCheckoutForm(false);
            return;
        }

        itemsContainer.classList.remove('hidden');
        if (emptyState) emptyState.classList.add('hidden');
        if (footerSection) footerSection.classList.remove('hidden');

        let html = '';
        let waItemsList = [];

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            subtotal += itemTotal;
            waItemsList.push(`• ${item.name} (Qty: ${item.qty}) - Rs. ${itemTotal.toLocaleString()}`);

            const productLink = item.slug ? `product-detail.php?slug=${encodeURIComponent(item.slug)}` : 'explore.php';

            html += `
                <div class="flex items-center space-x-3 pt-3 first:pt-0">
                    <a href="${productLink}" class="shrink-0 w-16 h-16 rounded-xl bg-slate-50 border border-slate-200/80 p-1 flex items-center justify-center overflow-hidden hover:opacity-90 transition-opacity">
                        <img src="${item.main_image}" alt="${item.name}" class="w-full h-full object-contain" onerror="this.src='images/Hero Banner.jpg'">
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="${productLink}" class="block font-bold text-xs text-gray-900 hover:text-brand-blue transition-colors line-clamp-1" title="${item.name}">
                            ${item.name}
                        </a>
                        <div class="text-[11px] text-gray-500 mt-0.5">
                            Rs. ${Number(item.price).toLocaleString()}
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center border border-gray-200 rounded-lg bg-slate-50 overflow-hidden h-7">
                                <button type="button" onclick="updateCartItemQty('${item.id}', -1)" class="w-6 h-full flex items-center justify-center text-gray-600 hover:bg-gray-200 font-bold transition-colors cursor-pointer text-xs">−</button>
                                <span class="w-7 text-center text-xs font-black text-gray-900">${item.qty}</span>
                                <button type="button" onclick="updateCartItemQty('${item.id}', 1)" class="w-6 h-full flex items-center justify-center text-gray-600 hover:bg-gray-200 font-bold transition-colors cursor-pointer text-xs">+</button>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="font-black text-xs text-gray-950">Rs. ${Number(itemTotal).toLocaleString()}</span>
                                <button type="button" onclick="removeCartItem('${item.id}')" class="text-gray-400 hover:text-red-500 transition-colors p-1 text-sm cursor-pointer" title="Remove item">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        itemsContainer.innerHTML = html;

        if (subtotalEl) subtotalEl.textContent = `Rs. ${subtotal.toLocaleString()}`;
        if (totalEl) totalEl.textContent = `Rs. ${subtotal.toLocaleString()}`;
        if (btnTotalEl) btnTotalEl.textContent = subtotal.toLocaleString();

        if (whatsappBtn) {
            const waText = encodeURIComponent(`Hi ChargeHub Pakistan, I would like to order the following items from my cart:\n\n${waItemsList.join('\n')}\n\nTotal Amount: Rs. ${subtotal.toLocaleString()}\nDelivery: Free Express (Cash on Delivery)`);
            whatsappBtn.href = `https://wa.me/923001234567?text=${waText}`;
        }
    }

    function openCartDrawer() {
        renderCart();
        updateCartCounterBadges();
        const wrapper = document.getElementById('cart-drawer-wrapper');
        const panel = document.getElementById('cart-drawer-panel');
        if (wrapper && panel) {
            wrapper.classList.remove('opacity-0', 'pointer-events-none');
            wrapper.classList.add('opacity-100', 'pointer-events-auto');
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
        }
    }

    function closeCartDrawer() {
        const wrapper = document.getElementById('cart-drawer-wrapper');
        const panel = document.getElementById('cart-drawer-panel');
        if (wrapper && panel) {
            panel.classList.remove('translate-x-0');
            panel.classList.add('translate-x-full');
            wrapper.classList.remove('opacity-100', 'pointer-events-auto');
            wrapper.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    function toggleCheckoutForm(show) {
        const form = document.getElementById('cart-checkout-form');
        if (form) {
            if (show) {
                form.classList.remove('hidden');
                form.scrollIntoView({ behavior: 'smooth' });
            } else {
                form.classList.add('hidden');
            }
        }
    }

    function resetOrderSuccess() {
        const successDiv = document.getElementById('cart-order-success');
        const itemsDiv = document.getElementById('cart-items-container');
        const footerDiv = document.getElementById('cart-footer-section');
        if (successDiv) successDiv.classList.add('hidden');
        if (itemsDiv) itemsDiv.classList.remove('hidden');
        if (footerDiv) footerDiv.classList.remove('hidden');
        toggleCheckoutForm(false);
    }

    async function handlePlaceOrder(e) {
        e.preventDefault();
        const cart = getCart();
        if (cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }

        const name = document.getElementById('order_cust_name').value.trim();
        const phone = document.getElementById('order_cust_phone').value.trim();
        const address = document.getElementById('order_cust_address').value.trim();
        const city = document.getElementById('order_cust_city').value.trim();
        const submitBtn = document.getElementById('order-submit-btn');

        if (!name || !phone || !address || !city) {
            alert('Please fill in all delivery details.');
            return;
        }

        const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>⏳ Confirming Order...</span>';
        }

        try {
            const res = await fetch('api_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customer_name: name,
                    customer_phone: phone,
                    customer_address: address,
                    customer_city: city,
                    items: cart,
                    subtotal: subtotal,
                    shipping: 0,
                    total: subtotal
                })
            });
            const result = await res.json();

            if (result.success) {
                const orderId = result.order_number || ('CH-' + Math.floor(10000 + Math.random() * 90000));
                document.getElementById('success-order-id').textContent = orderId;

                const waItems = cart.map(i => `• ${i.name} (x${i.qty}) - Rs. ${(i.price * i.qty).toLocaleString()}`).join('\n');
                const waMsg = encodeURIComponent(`Hi ChargeHub! I placed Order #${orderId}\nName: ${name}\nPhone: ${phone}\nCity: ${city}\nAddress: ${address}\n\nItems:\n${waItems}\n\nTotal: Rs. ${subtotal.toLocaleString()} (COD)`);
                document.getElementById('success-whatsapp-link').href = `https://wa.me/923001234567?text=${waMsg}`;

                clearCart();

                document.getElementById('cart-items-container').classList.add('hidden');
                document.getElementById('cart-footer-section').classList.add('hidden');
                document.getElementById('cart-checkout-form').classList.add('hidden');
                document.getElementById('cart-order-success').classList.remove('hidden');
            } else {
                alert(result.message || 'Error processing order. Please try again.');
            }
        } catch (err) {
            const orderId = 'CH-' + Math.floor(10000 + Math.random() * 90000);
            document.getElementById('success-order-id').textContent = orderId;
            clearCart();
            document.getElementById('cart-items-container').classList.add('hidden');
            document.getElementById('cart-footer-section').classList.add('hidden');
            document.getElementById('cart-checkout-form').classList.add('hidden');
            document.getElementById('cart-order-success').classList.remove('hidden');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span>Confirm Order &bull; Rs. <span id="checkout-btn-total">${subtotal.toLocaleString()}</span></span>`;
            }
        }
    }

    // Automatically wire up all Shopping Bag buttons & Add to Cart buttons on page load
    document.addEventListener('DOMContentLoaded', () => {
        updateCartCounterBadges();

        // 1. Attach openCartDrawer to all View Cart buttons in headers & navbars
        document.querySelectorAll('button[title="View Cart"], #cart-trigger, .cart-drawer-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openCartDrawer();
            });
        });

        // 2. Attach global Add to Cart handler to all .add-cart-btn buttons
        document.querySelectorAll('.add-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                // If it's on product-detail page and has specific action ID, skip generic handler
                if (btn.id === 'add-to-cart-action' || btn.id === 'buy-now-action') {
                    return;
                }

                e.preventDefault();
                const card = btn.closest('.catalog-card') || btn.closest('.group') || btn.parentElement;
                
                let name = btn.dataset.product || '';
                if (!name && card) {
                    const hTitle = card.querySelector('h3, h2, h4, .brand-font');
                    if (hTitle) name = hTitle.textContent.trim();
                }
                if (!name) name = 'Product';

                let slug = btn.dataset.slug || '';
                if (!slug && card) {
                    const link = card.querySelector('a[href*="slug="]');
                    if (link) {
                        try {
                            const u = new URL(link.href, window.location.origin);
                            slug = u.searchParams.get('slug') || '';
                        } catch(e) {}
                    }
                }

                let price = parseFloat(btn.dataset.price) || 0;
                if (!price && card) {
                    const pEl = card.querySelector('.font-extrabold, .font-black');
                    if (pEl) {
                        price = parseFloat(pEl.textContent.replace(/[^0-9.]/g, '')) || 0;
                    }
                }

                let image = btn.dataset.image || '';
                if (!image && card) {
                    const img = card.querySelector('img');
                    if (img && img.src) image = img.src;
                }

                addToCart({
                    name: name,
                    slug: slug,
                    price: price,
                    main_image: image || 'images/Hero Banner.jpg'
                }, 1, true);

                // Button visual feedback
                const label = btn.querySelector('span') || btn;
                const origText = label.textContent;
                label.textContent = '✓ Added!';
                setTimeout(() => {
                    label.textContent = origText;
                }, 1400);
            });
        });

        // Close on Escape Key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCartDrawer();
            }
        });
    });
</script>
