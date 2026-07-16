<?php
// views/cashier/index.php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

check_access(['admin', 'cashier']);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$menus = $pdo->query("SELECT * FROM menus WHERE status = 'available' ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>POS Kasir - SsyyCoffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coffee: { 50: '#FDFBF7', 100: '#F4EFE6', 200: '#E6DCC8', 600: '#8C6239', 800: '#5C3A21', 950: '#2B1704' }
                    },
                    boxShadow: { 'soft': '0 -4px 20px -2px rgba(43, 23, 4, 0.05)' }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        /* Animasi transisi filter */
        .menu-card { transition: opacity 0.3s ease, transform 0.3s ease; }
    </style>
</head>
<body class="bg-coffee-50 text-coffee-950 font-sans h-screen overflow-hidden flex flex-col relative">

    <!-- HEADER POS -->
    <header class="h-16 bg-white border-b border-coffee-100 flex items-center justify-between px-4 md:px-6 shrink-0 z-20">
        <div class="flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-coffee-950"><path d="M4 19V6.2C4 5.09543 4.89543 4 6 4H15C16.1046 4 17 4.89543 17 6.2V19H4ZM6 2H15C17.2091 2 19 3.79086 19 6.2V15C20.6569 15 22 13.6569 22 12C22 10.3431 20.6569 9 19 9V7C21.7614 7 24 9.23858 24 12C24 14.7614 21.7614 17 19 17H17V19C17 20.6569 15.6569 22 14 22H7C5.34315 22 4 20.6569 4 19V21H2V19H0V17H2V6.2C2 3.79086 3.79086 2 6 2Z"/></svg>
            <div>
                <h1 class="text-lg font-extrabold text-coffee-950 leading-none">SsyyCoffee POS</h1>
                <p class="text-[10px] text-coffee-600 font-medium uppercase mt-0.5">Terminal Kasir</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="hidden md:flex flex-col items-end mr-2">
                <span class="text-xs font-bold text-coffee-950"><?= htmlspecialchars($_SESSION['name']); ?></span>
                <span class="text-[10px] text-coffee-600">Aktif</span>
            </div>
            <a href="../../logout.php" title="Tutup Shift (Logout)" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" /></svg>
            </a>
        </div>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
        
        <!-- ZONA KIRI: DAFTAR MENU -->
        <main class="flex-1 flex flex-col h-[50vh] lg:h-auto overflow-hidden bg-coffee-50 relative">
            
            <!-- FILTER KATEGORI -->
            <div class="bg-white/60 backdrop-blur-md border-b border-coffee-100 p-4 shrink-0 overflow-x-auto whitespace-nowrap">
                <div class="flex gap-2">
                    <!-- Tombol 'Semua' aktif secara default -->
                    <button onclick="filterCategory('all', this)" class="cat-btn px-5 py-2 bg-coffee-950 text-white text-xs font-bold rounded-full shadow-sm transition-colors">Semua</button>
                    <?php foreach($categories as $cat): ?>
                        <button onclick="filterCategory(<?= $cat['id'] ?>, this)" class="cat-btn px-5 py-2 bg-white text-coffee-800 border border-coffee-200 text-xs font-bold rounded-full hover:bg-coffee-100 transition-colors">
                            <?= htmlspecialchars($cat['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 md:p-6 content-start">
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" id="menu-grid">
                    <?php foreach($menus as $m): ?>
                        <!-- Tambahkan data-category dan class menu-card -->
                        <div class="menu-card bg-white rounded-2xl p-3 border border-coffee-100 shadow-sm hover:border-coffee-600 hover:shadow-md transition-all cursor-pointer group block" 
                             data-category="<?= $m['category_id'] ?>"
                             onclick="checkVariants(<?= $m['id'] ?>, '<?= addslashes($m['name']) ?>', <?= $m['base_price'] ?>)">
                            <div class="w-full aspect-square rounded-xl bg-coffee-100 mb-3 overflow-hidden relative">
                                <?php if($m['image']): ?>
                                    <img src="../../public/uploads/<?= htmlspecialchars($m['image']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-8 h-8 text-coffee-200"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-sm font-bold text-coffee-950 leading-tight mb-1 truncate"><?= htmlspecialchars($m['name']) ?></h3>
                            <p class="text-xs font-extrabold text-coffee-600">Rp <?= number_format($m['base_price'], 0, ',', '.') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <!-- ZONA KANAN: KERANJANG BELANJA (Tidak ada perubahan HTML) -->
        <aside class="w-full lg:w-96 bg-white border-l border-coffee-200 flex flex-col h-[50vh] lg:h-auto shadow-soft lg:shadow-none z-10 shrink-0">
            <div class="px-5 py-4 border-b border-coffee-100 flex justify-between items-center bg-white shrink-0">
                <h2 class="font-extrabold text-coffee-950 text-lg">Pesanan Saat Ini</h2>
                <button onclick="clearCart()" class="text-xs font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">Kosongkan</button>
            </div>
            <div id="cart-items" class="flex-1 overflow-y-auto p-3 bg-coffee-50/30 space-y-2"></div>
            <div class="bg-white p-5 border-t border-coffee-200 shrink-0 rounded-t-3xl shadow-soft">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm text-coffee-800">
                        <span>Subtotal</span>
                        <span id="cart-subtotal" class="font-bold">Rp 0</span>
                    </div>
                </div>
                <div class="flex justify-between items-end mb-5 pt-3 border-t border-coffee-100">
                    <span class="text-sm font-bold text-coffee-950 uppercase">Total</span>
                    <span id="cart-total" class="text-2xl font-extrabold text-coffee-950 leading-none">Rp 0</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button class="py-3.5 bg-coffee-100 text-coffee-950 font-extrabold text-sm rounded-2xl hover:bg-coffee-200 flex items-center justify-center gap-2">Hold</button>
                    <button onclick="openCheckoutModal()" class="py-3.5 bg-coffee-800 text-white font-extrabold text-sm rounded-2xl hover:bg-coffee-950 shadow-md flex items-center justify-center gap-2">Bayar</button>
                </div>
            </div>
        </aside>
    </div>

    <!-- MODAL VARIAN -->
    <div id="variant-modal" class="hidden fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <!-- (Isi modal varian sama seperti sebelumnya) -->
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden flex flex-col max-h-full">
            <div class="px-6 py-4 border-b border-coffee-100 flex justify-between items-center bg-coffee-50">
                <div>
                    <h3 id="modal-menu-name" class="font-bold text-lg text-coffee-950">Nama Menu</h3>
                    <p id="modal-menu-price" class="text-xs font-bold text-coffee-600">Rp 0</p>
                </div>
                <button onclick="closeModal()" class="text-coffee-600 hover:text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div id="modal-variants-container" class="p-6 overflow-y-auto space-y-5"></div>
            <div class="p-5 border-t border-coffee-100 bg-white">
                <button onclick="addToCartFromModal()" class="w-full py-3 bg-coffee-800 text-white text-sm font-bold rounded-xl shadow-md hover:bg-coffee-950">
                    Tambahkan ke Pesanan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL CHECKOUT -->
    <div id="checkout-modal" class="hidden fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <!-- (Isi modal checkout sama seperti sebelumnya) -->
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-coffee-100 flex justify-between items-center bg-coffee-50">
                <h3 class="font-bold text-lg text-coffee-950">Konfirmasi Pembayaran</h3>
                <button onclick="closeCheckoutModal()" class="text-coffee-600 hover:text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div class="text-center p-4 bg-coffee-50 rounded-2xl border border-coffee-100">
                    <p class="text-xs font-bold text-coffee-600 uppercase mb-1">Total Tagihan</p>
                    <h2 id="checkout-total-display" class="text-3xl font-extrabold text-coffee-950">Rp 0</h2>
                </div>
                <div>
                    <label class="block text-xs font-bold text-coffee-950 uppercase mb-3">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="cash" class="peer hidden" checked onchange="toggleCashInput()">
                            <div class="p-4 border border-coffee-200 rounded-xl text-center peer-checked:bg-coffee-800 peer-checked:text-white peer-checked:border-coffee-800 transition-colors">
                                <span class="font-bold text-sm">Tunai (Cash)</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="qris" class="peer hidden" onchange="toggleCashInput()">
                            <div class="p-4 border border-coffee-200 rounded-xl text-center peer-checked:bg-coffee-800 peer-checked:text-white peer-checked:border-coffee-800 transition-colors">
                                <span class="font-bold text-sm">QRIS</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div id="cash-input-section" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Uang Diterima (Rp)</label>
                        <input type="number" id="cash-tendered" oninput="calculateChange()" class="w-full px-4 py-3 bg-coffee-50 border border-coffee-200 rounded-xl text-lg font-bold text-coffee-950 focus:outline-none focus:border-coffee-600">
                    </div>
                    <div class="flex justify-between items-center px-4 py-3 bg-green-50 rounded-xl border border-green-100">
                        <span class="text-sm font-bold text-green-800">Kembalian:</span>
                        <span id="cash-change" class="text-lg font-extrabold text-green-800">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="p-5 border-t border-coffee-100 bg-white">
                <button onclick="processCheckout()" class="w-full py-3.5 bg-coffee-800 text-white text-sm font-bold rounded-xl shadow-md hover:bg-coffee-950">
                    Proses Transaksi & Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LENGKAP -->
    <script>
        // ----------------------------------------------------
        // FITUR BARU: FILTER KATEGORI MENU
        // ----------------------------------------------------
        function filterCategory(categoryId, btnElement) {
            // 1. Reset desain semua tombol kategori jadi putih (tidak aktif)
            const allBtns = document.querySelectorAll('.cat-btn');
            allBtns.forEach(btn => {
                btn.classList.remove('bg-coffee-950', 'text-white', 'border-transparent');
                btn.classList.add('bg-white', 'text-coffee-800', 'border-coffee-200');
            });

            // 2. Jadikan tombol yang diklik warna gelap (aktif)
            btnElement.classList.remove('bg-white', 'text-coffee-800', 'border-coffee-200');
            btnElement.classList.add('bg-coffee-950', 'text-white', 'border-transparent');

            // 3. Saring Card Produk di Grid
            const allCards = document.querySelectorAll('.menu-card');
            allCards.forEach(card => {
                if (categoryId === 'all' || card.getAttribute('data-category') == categoryId) {
                    card.style.display = 'block'; // Tampilkan
                } else {
                    card.style.display = 'none';  // Sembunyikan
                }
            });
        }
        // ----------------------------------------------------

        let currentMenuId = null;
        let currentGrandTotal = 0;

        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        };

        async function checkVariants(menuId, menuName, basePrice) {
            try {
                const response = await fetch(`../../api/get-variants.php?menu_id=${menuId}`);
                const res = await response.json();
                if (res.status === 'success') {
                    if (res.data.length > 0) openModal(menuId, menuName, basePrice, res.data);
                    else processAddToCart(menuId, 1, []);
                }
            } catch (error) { console.error("Gagal load varian", error); }
        }

        function openModal(menuId, menuName, basePrice, groups) {
            currentMenuId = menuId;
            document.getElementById('modal-menu-name').innerText = menuName;
            document.getElementById('modal-menu-price').innerText = formatRupiah(basePrice);
            
            let container = document.getElementById('modal-variants-container');
            container.innerHTML = ''; 
            groups.forEach(group => {
                let groupHTML = `<div><h4 class="text-xs font-bold text-coffee-950 uppercase mb-2">${group.group_name}</h4><div class="space-y-2">`;
                group.options.forEach((opt, index) => {
                    let isChecked = index === 0 ? 'checked' : '';
                    let priceTag = opt.additional_price > 0 ? `<span class="text-xs font-bold text-coffee-600">+ ${formatRupiah(opt.additional_price)}</span>` : '';
                    groupHTML += `
                        <label class="flex items-center justify-between p-3 border border-coffee-200 rounded-xl cursor-pointer hover:bg-coffee-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="variant_group_${group.group_id}" value="${opt.id}" ${isChecked} class="w-4 h-4 text-coffee-800">
                                <span class="text-sm font-semibold text-coffee-950">${opt.option_name}</span>
                            </div>
                            ${priceTag}
                        </label>`;
                });
                groupHTML += `</div></div>`;
                container.innerHTML += groupHTML;
            });
            document.getElementById('variant-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('variant-modal').classList.add('hidden');
            currentMenuId = null;
        }

        function addToCartFromModal() {
            let selectedVariants = [];
            document.querySelectorAll('#modal-variants-container input[type="radio"]:checked').forEach(radio => {
                selectedVariants.push(parseInt(radio.value));
            });
            processAddToCart(currentMenuId, 1, selectedVariants);
            closeModal();
        }

        async function processAddToCart(menuId, qty, variants) {
            try {
                const response = await fetch('../../api/cart-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', menu_id: menuId, qty: qty, variants: variants })
                });
                const data = await response.json();
                if (data.status === 'success') renderCart(data.cart);
            } catch (error) { console.error("Gagal", error); }
        }

        async function updateQty(cartId, newQty) {
            try {
                const response = await fetch('../../api/cart-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'update_qty', cart_id: cartId, qty: newQty })
                });
                const data = await response.json();
                if (data.status === 'success') renderCart(data.cart);
            } catch (error) { console.error("Gagal", error); }
        }

        async function clearCart() {
            if(!confirm('Kosongkan semua pesanan?')) return;
            try {
                const response = await fetch('../../api/cart-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'clear' })
                });
                const data = await response.json();
                if (data.status === 'success') renderCart(data.cart);
            } catch (error) { console.error("Gagal", error); }
        }

        async function fetchCartInitial() {
            try {
                const response = await fetch('../../api/cart-handler.php');
                const data = await response.json();
                if (data.status === 'success') renderCart(data.cart);
            } catch (error) { console.error("Gagal", error); }
        }

        function renderCart(cartArray) {
            const container = document.getElementById('cart-items');
            container.innerHTML = ''; 
            currentGrandTotal = 0;

            if (cartArray.length === 0) {
                container.innerHTML = '<div class="text-center text-coffee-600 text-sm mt-10">Keranjang kosong.</div>';
            } else {
                cartArray.forEach(item => {
                    let subtotalItem = item.qty * item.price_at_sale;
                    currentGrandTotal += subtotalItem;
                    let variantText = item.variant_details && item.variant_details.length > 0 
                        ? `<p class="text-[10px] text-coffee-600 mt-0.5 leading-snug">${item.variant_details.map(v => v.name).join(', ')}</p>` : '';

                    container.innerHTML += `
                        <div class="bg-white p-3 rounded-xl border border-coffee-100 shadow-sm flex flex-col gap-2">
                            <div class="flex justify-between items-start">
                                <div class="pr-4"><h4 class="text-sm font-bold text-coffee-950">${item.name}</h4>${variantText}</div>
                                <p class="text-sm font-extrabold text-coffee-950">${formatRupiah(subtotalItem)}</p>
                            </div>
                            <div class="flex justify-between items-center mt-1 pt-2 border-t border-coffee-50">
                                <div class="flex items-center bg-coffee-50 rounded-lg border border-coffee-200 overflow-hidden">
                                    <button onclick="updateQty('${item.cart_id}', ${item.qty - 1})" class="px-2.5 py-1 text-coffee-800 hover:bg-coffee-200 font-bold">-</button>
                                    <span class="px-2 text-xs font-bold text-coffee-950 min-w-[24px] text-center">${item.qty}</span>
                                    <button onclick="updateQty('${item.cart_id}', ${item.qty + 1})" class="px-2.5 py-1 text-coffee-800 hover:bg-coffee-200 font-bold">+</button>
                                </div>
                                <button onclick="updateQty('${item.cart_id}', 0)" class="text-red-500 hover:text-red-700 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>`;
                });
            }
            document.getElementById('cart-subtotal').innerText = formatRupiah(currentGrandTotal);
            document.getElementById('cart-total').innerText = formatRupiah(currentGrandTotal);
        }

        function openCheckoutModal() {
            if (currentGrandTotal === 0) { alert("Keranjang kosong!"); return; }
            document.getElementById('checkout-total-display').innerText = formatRupiah(currentGrandTotal);
            document.getElementById('cash-tendered').value = '';
            document.getElementById('cash-change').innerText = 'Rp 0';
            document.getElementById('checkout-modal').classList.remove('hidden');
        }

        function closeCheckoutModal() { document.getElementById('checkout-modal').classList.add('hidden'); }

        function toggleCashInput() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById('cash-input-section').classList.toggle('hidden', method !== 'cash');
        }

        function calculateChange() {
            const tendered = parseFloat(document.getElementById('cash-tendered').value) || 0;
            const change = tendered - currentGrandTotal;
            const el = document.getElementById('cash-change');
            if (change >= 0) {
                el.innerText = formatRupiah(change);
                el.className = "text-lg font-extrabold text-green-800";
            } else {
                el.innerText = "- " + formatRupiah(Math.abs(change));
                el.className = "text-lg font-extrabold text-red-600";
            }
        }

        async function processCheckout() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            if (method === 'cash') {
                const tendered = parseFloat(document.getElementById('cash-tendered').value) || 0;
                if (tendered < currentGrandTotal) { alert("Uang tunai tidak cukup!"); return; }
            }
            try {
                const response = await fetch('../../api/order-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'checkout', payment_method: method })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    closeCheckoutModal();
                    fetchCartInitial(); 
                    window.open(`receipt.php?id=${data.order_id}`, 'Struk', 'width=400,height=600');
                } else alert(data.message);
            } catch (error) { console.error("Gagal", error); }
        }

        window.addEventListener('DOMContentLoaded', fetchCartInitial);
    </script>
</body>
</html>