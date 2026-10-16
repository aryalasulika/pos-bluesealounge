@extends('layouts.app')

@section('title', 'Terminal Kasir POS — Bluesea Lounge')

@push('styles')
<style>
    /* Tinggi terminal POS yang andal di tablet/iPad (dvh) dengan fallback vh. */
    .pos-shell { height: calc(100vh - 5rem); height: calc(100dvh - 5rem); }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }

    /* Scrollbar halus untuk katalog & keranjang */
    .nice-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .nice-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .nice-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .nice-scroll::-webkit-scrollbar-track { background: transparent; }

    /* Kartu produk */
    .product-tile { transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
    .product-tile:hover { transform: translateY(-2px); }
    .product-tile:active { transform: scale(.975); }

    /* Animasi masuk */
    @keyframes posPopIn { from { opacity: 0; transform: translateY(8px) } to { opacity: 1; transform: none } }
    @keyframes posFadeIn { from { opacity: 0 } to { opacity: 1 } }
    .cart-line { animation: posPopIn .18s ease-out both; }
    .grid-fade { animation: posFadeIn .25s ease-out both; }

    @media (prefers-reduced-motion: reduce) {
        .product-tile, .cart-line, .grid-fade { animation: none !important; transition: none !important; transform: none !important; }
    }
</style>
@endpush

@section('content')
<div class="pos-shell flex flex-col -mx-4 sm:-mx-6 lg:-mx-8 -mb-8 overflow-hidden bg-slate-100">
    <div class="flex-1 flex flex-col md:flex-row overflow-hidden min-h-0">

        <!-- Left / Center: Products Catalog -->
        <div class="flex-1 flex flex-col overflow-hidden min-h-0 p-3 sm:p-4 lg:p-6">
            <!-- Katalog Header -->
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-sky-400 text-white flex items-center justify-center shadow-glow flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h7"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-extrabold text-slate-800 leading-tight">Katalog Produk</h1>
                        <p class="text-[11px] text-slate-400">{{ $products->count() }} produk aktif siap dijual</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-[10px] font-semibold text-slate-500 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Terminal Online
                </span>
            </div>

            <!-- Search & Barcode Bar -->
            <div class="relative mb-3">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="pos-search" autofocus
                    placeholder="Cari nama produk, SKU, atau scan barcode…"
                    class="w-full pl-11 pr-28 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:inline-flex items-center gap-1 text-[10px] bg-slate-100 text-slate-500 px-2.5 py-1 rounded-lg font-mono border border-slate-200">
                    ⏎ Enter / Scan
                </span>
            </div>

            <!-- Category Pills (Scrollable) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-3 scrollbar-none flex-shrink-0">
                <button type="button" onclick="filterCategory('all', this)"
                    class="cat-filter-btn px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap bg-brand-600 text-white shadow-glow">
                    Semua Kategori
                </button>
                @foreach ($categories as $cat)
                <button type="button" onclick="filterCategory('{{ $cat->id }}', this)"
                    class="cat-filter-btn px-4 py-2 rounded-full text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition whitespace-nowrap shadow-sm">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>

            <!-- Products Grid -->
            <div class="flex-1 min-h-0 overflow-y-auto pr-1 nice-scroll">
                @php
                    $tileGradients = [
                        'from-sky-500 to-blue-600',
                        'from-emerald-500 to-teal-600',
                        'from-violet-500 to-purple-600',
                        'from-amber-500 to-orange-600',
                        'from-rose-500 to-pink-600',
                        'from-cyan-500 to-sky-600',
                    ];
                @endphp
                <div id="product-grid" class="grid-fade grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5 sm:gap-3.5">
                    @forelse ($products as $prod)
                    <div class="product-card product-tile group relative flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden cursor-pointer {{ $prod->current_stock <= 0 ? 'opacity-55 pointer-events-none' : '' }}"
                         data-id="{{ $prod->id }}"
                         data-sku="{{ $prod->sku }}"
                         data-barcode="{{ $prod->barcode ?? '' }}"
                         data-name="{{ $prod->name }}"
                         data-price="{{ $prod->selling_price }}"
                         data-stock="{{ $prod->current_stock }}"
                         data-unit="{{ $prod->unit }}"
                         data-category="{{ $prod->category_id }}"
                         onclick="addToCart({{ $prod->id }})">

                        <!-- Thumbnail -->
                        <div class="relative h-24 sm:h-28 w-full overflow-hidden">
                            @if ($prod->image_path)
                                <img src="{{ asset('storage/' . $prod->image_path) }}" alt="{{ $prod->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br {{ $tileGradients[$prod->category_id % count($tileGradients)] }} flex items-center justify-center">
                                    <span class="text-white/90 font-black text-3xl tracking-tight drop-shadow-sm">{{ strtoupper(substr($prod->name, 0, 1)) }}</span>
                                </div>
                            @endif

                            <span class="absolute bottom-1.5 left-1.5 text-[9px] font-mono font-semibold px-1.5 py-0.5 rounded bg-slate-900/70 text-white backdrop-blur-sm">{{ $prod->sku }}</span>

                            @if ($prod->current_stock <= 0)
                                <span class="absolute top-1.5 right-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-600 text-white shadow-sm">Habis</span>
                            @elseif ($prod->current_stock <= $prod->minimum_stock)
                                <span class="absolute top-1.5 right-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500 text-white shadow-sm">Sisa {{ $prod->current_stock }}</span>
                            @else
                                <span class="absolute top-1.5 right-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/90 text-emerald-700 shadow-sm">{{ $prod->current_stock }}</span>
                            @endif
                        </div>

                        <!-- Body -->
                        <div class="flex-1 flex flex-col p-2.5">
                            <span class="text-[10px] font-semibold text-brand-600/80 truncate">{{ $prod->category->name }}</span>
                            <h3 class="text-xs font-bold text-slate-800 leading-snug line-clamp-2 mt-0.5 group-hover:text-brand-700 transition">{{ $prod->name }}</h3>
                            <div class="mt-auto pt-2 flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-900">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                                <span class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center transition group-hover:bg-brand-600 group-hover:text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-slate-400 text-sm">
                        <svg class="w-12 h-12 text-slate-300 mb-3 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Tidak ada produk aktif yang ditemukan.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Backdrop laci keranjang (khusus mobile) -->
        <div id="cart-backdrop" class="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-sm hidden md:hidden" onclick="closeCartPanel()"></div>

        <!-- Right: Cart Panel (panel samping pada md+, laci bawah pada mobile) -->
        <!-- z-30: di atas konten & bilah bawah, tetapi DI BAWAH header (z-40) agar menu user tidak tertutup -->
        <div id="cart-panel"
             class="fixed inset-x-0 bottom-0 z-30 w-full h-[85dvh] translate-y-full transition-transform duration-300 ease-out
                    md:static md:inset-auto md:h-auto md:translate-y-0 md:w-80 lg:w-96
                    bg-white flex flex-col justify-between shadow-2xl md:shadow-xl border-t md:border-t-0 md:border-l border-slate-200">
            <!-- Mobile drawer header -->
            <div class="md:hidden relative flex items-center justify-center px-4 py-3 border-b border-slate-100 bg-slate-50">
                <span class="h-1.5 w-12 rounded-full bg-slate-300"></span>
                <button type="button" onclick="closeCartPanel()" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-800 px-2.5 py-1 rounded-lg hover:bg-slate-200 transition">Tutup ✕</button>
            </div>
            <!-- Cart Header -->
            <div class="p-3 sm:p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-sky-400 text-white flex items-center justify-center shadow-glow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800 leading-tight">Keranjang Pesanan</h2>
                        <span id="cart-item-count" class="text-[11px] text-slate-400">0 item dipilih</span>
                    </div>
                </div>
                <button type="button" onclick="clearCart()" title="Kosongkan Pesanan" class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-white font-semibold px-2.5 py-1.5 rounded-lg hover:bg-rose-500 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Kosongkan
                </button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
                <!-- Empty State (persistent, hanya di-toggle) -->
                <div id="cart-empty" class="h-full flex flex-col items-center justify-center text-center py-12 px-6 text-slate-400">
                    <div class="w-20 h-20 rounded-full bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center mb-3">
                        <svg class="w-9 h-9 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Keranjang masih kosong</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-[220px]">Pilih produk atau scan barcode untuk mulai mencatat pesanan tamu.</p>
                </div>

                <!-- Dynamic Cart Items Rendered Here via JS -->
                <div id="cart-items-container" class="flex-1 min-h-0 overflow-y-auto p-3 space-y-2.5 nice-scroll"></div>
            </div>

            <!-- Cart Footer Summary & Checkout Button -->
            <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal Belanja</span>
                        <span id="cart-subtotal" class="font-semibold text-slate-800 font-mono">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Pajak & Layanan</span>
                        <span class="font-medium text-emerald-600">Termasuk (0%)</span>
                    </div>
                </div>

                <div class="flex items-end justify-between pt-2.5 border-t border-slate-200">
                    <div>
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold">Total Tagihan</span>
                        <span id="cart-total" class="text-xl font-black text-brand-600 font-mono leading-tight">Rp 0</span>
                    </div>
                    <span id="cart-qty-badge" class="text-[11px] font-bold text-slate-500 bg-white border border-slate-200 rounded-full px-2.5 py-1">0 item</span>
                </div>

                <button type="button" id="btn-open-payment" onclick="openPaymentModal()" disabled
                    class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-sky-500 hover:from-brand-500 hover:to-sky-400 disabled:opacity-50 disabled:cursor-not-allowed shadow-glow transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Proses Pembayaran</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile bottom action bar (khusus layar < md) -->
    <div id="mobile-cart-bar" class="md:hidden flex-shrink-0 flex items-center justify-between gap-3 px-3 py-2.5 bg-slate-900 text-white border-t border-slate-800 z-20">
        <button type="button" onclick="openCartPanel()" class="flex items-center space-x-2.5 text-left">
            <span class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-600 shadow-glow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span id="mobile-cart-count" class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">0</span>
            </span>
            <span>
                <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Total Keranjang</span>
                <span id="mobile-cart-total" class="block text-base font-black font-mono leading-tight">Rp 0</span>
            </span>
        </button>
        <button type="button" onclick="openCartPanel()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-sky-500 text-xs font-extrabold shadow-glow flex items-center">
            Lihat Keranjang
            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </div>
</div>

<!-- Modal Pembayaran (Tunai & QRIS) -->
<div id="payment-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-3 sm:p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[92dvh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-scaleIn">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center shadow-glow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold">Konfirmasi Pembayaran</h3>
                    <p class="text-[10px] text-sky-400">Pilih metode pembayaran tamu lounge</p>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">✕</button>
        </div>

        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1">
            <!-- Total Banner -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-4 text-center shadow-inner">
                <span class="text-xs uppercase tracking-wider text-slate-400 block font-semibold">Total Tagihan</span>
                <span id="modal-bill-total" class="text-3xl font-black text-sky-300 font-mono mt-0.5 block">Rp 0</span>
            </div>

            <!-- Payment Method Tabs -->
            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                <button type="button" id="tab-btn-cash" onclick="switchPaymentMethod('cash')"
                    class="py-2.5 text-xs font-bold rounded-lg transition bg-white text-slate-800 shadow-sm flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Tunai (Cash)</span>
                </button>
                <button type="button" id="tab-btn-qris" onclick="switchPaymentMethod('qris')"
                    class="py-2.5 text-xs font-bold rounded-lg transition text-slate-500 hover:text-slate-800 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>QRIS Dinamis</span>
                </button>
            </div>

            <!-- Content Tunai -->
            <div id="content-cash" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Uang Diterima dari Tamu</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" id="cash-received-input" oninput="calculateChange()" min="0" step="1000"
                            placeholder="0"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-lg font-bold text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Quick Cash Presets -->
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setCashPreset('exact')" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">Uang Pas</button>
                    <button type="button" onclick="setCashPreset(50000)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">50.000</button>
                    <button type="button" onclick="setCashPreset(100000)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">100.000</button>
                    <button type="button" onclick="setCashPreset(200000)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">200.000</button>
                    <button type="button" onclick="setCashPreset(500000)" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">500.000</button>
                </div>

                <!-- Kembalian Box -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 block">Kembalian:</span>
                        <span id="cash-change-display" class="text-xl font-black text-slate-800 font-mono">Rp 0</span>
                    </div>
                    <div id="cash-status-indicator" class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        Menunggu Nominal
                    </div>
                </div>
            </div>

            <!-- Content QRIS -->
            <div id="content-qris" class="hidden space-y-4 text-center">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col items-center">
                    <!-- SVG QR Code Mockup -->
                    <div class="w-44 h-44 bg-white p-2 rounded-xl border border-slate-300 shadow-sm flex items-center justify-center">
                        <svg class="w-full h-full text-slate-900" viewBox="0 0 100 100" fill="currentColor">
                            <!-- Outer border / markers -->
                            <rect x="5" y="5" width="30" height="30" rx="3" fill="#0f172a"/>
                            <rect x="9" y="9" width="22" height="22" rx="2" fill="#fff"/>
                            <rect x="13" y="13" width="14" height="14" fill="#0284c7"/>

                            <rect x="65" y="5" width="30" height="30" rx="3" fill="#0f172a"/>
                            <rect x="69" y="9" width="22" height="22" rx="2" fill="#fff"/>
                            <rect x="73" y="13" width="14" height="14" fill="#0284c7"/>

                            <rect x="5" y="65" width="30" height="30" rx="3" fill="#0f172a"/>
                            <rect x="9" y="69" width="22" height="22" rx="2" fill="#fff"/>
                            <rect x="13" y="73" width="14" height="14" fill="#0284c7"/>

                            <!-- Simulated QR bits -->
                            <rect x="42" y="10" width="8" height="8" fill="#0f172a"/>
                            <rect x="53" y="18" width="6" height="6" fill="#0f172a"/>
                            <rect x="45" y="30" width="10" height="8" fill="#0f172a"/>
                            <rect x="10" y="45" width="15" height="8" fill="#0f172a"/>
                            <rect x="30" y="45" width="8" height="15" fill="#0f172a"/>
                            <rect x="45" y="45" width="12" height="12" fill="#0284c7"/>
                            <rect x="65" y="45" width="10" height="8" fill="#0f172a"/>
                            <rect x="80" y="45" width="12" height="14" fill="#0f172a"/>
                            <rect x="45" y="65" width="8" height="15" fill="#0f172a"/>
                            <rect x="60" y="65" width="15" height="8" fill="#0f172a"/>
                            <rect x="80" y="65" width="10" height="10" fill="#0f172a"/>
                            <rect x="65" y="80" width="25" height="10" fill="#0f172a"/>
                            <rect x="40" y="85" width="15" height="8" fill="#0f172a"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-800 mt-3">QRIS Standar Nasional — Bluesea Lounge</span>
                    <span class="text-[11px] text-slate-500">Mendukung BCA, Mandiri, BRI, BNI, GoPay, OVO, Dana, ShopeePay</span>
                </div>

                <label class="flex items-start space-x-2.5 text-left cursor-pointer p-3 bg-purple-50 rounded-xl border border-purple-200">
                    <input type="checkbox" id="qris-confirmed-check" onchange="toggleQrisSubmit()" class="w-4 h-4 mt-0.5 rounded text-purple-600 focus:ring-purple-500 border-purple-300">
                    <span class="text-xs text-purple-900 font-medium">
                        Saya mengonfirmasi bahwa tamu telah menunjukkan bukti pembayaran QRIS yang valid & saldo telah berhasil masuk.
                    </span>
                </label>
            </div>
        </div>

        <!-- Modal Actions -->
        <div class="px-4 sm:px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2 flex-shrink-0">
            <button type="button" onclick="closePaymentModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                Batal
            </button>

            <button type="button" id="btn-submit-transaction" onclick="submitTransaction()"
                class="px-6 py-2.5 rounded-xl text-xs font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition-all flex items-center justify-center space-x-1.5">
                <svg id="submit-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span id="submit-btn-text">Selesaikan & Simpan Transaksi</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State Store
    let cart = [];
    let currentMethod = 'cash'; // 'cash' | 'qris'
    // FR-06 / Kriteria #25: kunci idempotensi mencegah transaksi ganda
    // saat tombol diklik berulang atau permintaan dikirim ulang.
    let idempotencyKey = null;

    function generateIdempotencyKey() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        return 'trx-' + Date.now() + '-' + Math.random().toString(36).slice(2, 12);
    }

    // Setiap perubahan isi keranjang menghasilkan sesi checkout baru.
    function resetIdempotencyKey() {
        idempotencyKey = null;
    }

    // Real-time Search & Barcode Scan Handler
    const searchInput = document.getElementById('pos-search');
    searchInput.addEventListener('input', function() {
        filterProducts();
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim().toLowerCase();
            if (!val) return;

            // Find exact match by barcode or SKU first
            const cards = document.querySelectorAll('.product-card');
            for (let card of cards) {
                const sku = card.getAttribute('data-sku').toLowerCase();
                const barcode = card.getAttribute('data-barcode').toLowerCase();
                if (sku === val || barcode === val) {
                    const id = parseInt(card.getAttribute('data-id'));
                    addToCart(id);
                    this.value = '';
                    filterProducts();
                    return;
                }
            }

            // If only 1 visible card, add it
            const visibleCards = Array.from(cards).filter(c => c.style.display !== 'none');
            if (visibleCards.length === 1) {
                const id = parseInt(visibleCards[0].getAttribute('data-id'));
                addToCart(id);
                this.value = '';
                filterProducts();
            }
        }
    });

    function filterProducts() {
        const query = searchInput.value.toLowerCase().trim();
        const activeCat = document.querySelector('.cat-filter-btn.bg-brand-600').getAttribute('data-cat-id') || 'all';

        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const sku = card.getAttribute('data-sku').toLowerCase();
            const barcode = card.getAttribute('data-barcode').toLowerCase();
            const cat = card.getAttribute('data-category');

            const matchQuery = !query || name.includes(query) || sku.includes(query) || barcode.includes(query);
            const matchCat = (activeCat === 'all' || cat === activeCat);

            if (matchQuery && matchCat) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function filterCategory(catId, btn) {
        document.querySelectorAll('.cat-filter-btn').forEach(b => {
            b.classList.remove('bg-brand-600', 'text-white', 'shadow-glow');
            b.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
            b.removeAttribute('data-cat-id');
        });

        btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        btn.classList.add('bg-brand-600', 'text-white', 'shadow-glow');
        btn.setAttribute('data-cat-id', catId);

        filterProducts();
    }

    // Cart Management
    function addToCart(productId) {
        const card = document.querySelector(`.product-card[data-id="${productId}"]`);
        if (!card) return;

        const stock = parseInt(card.getAttribute('data-stock'));
        if (stock <= 0) {
            alert('Stok produk ini sedang habis.');
            return;
        }

        const existing = cart.find(i => i.id === productId);
        if (existing) {
            if (existing.quantity + 1 > stock) {
                alert(`Maksimal stok yang tersedia untuk ${existing.name} hanya ${stock} ${existing.unit}.`);
                return;
            }
            existing.quantity += 1;
        } else {
            cart.push({
                id: productId,
                name: card.getAttribute('data-name'),
                sku: card.getAttribute('data-sku'),
                price: parseFloat(card.getAttribute('data-price')),
                stock: stock,
                unit: card.getAttribute('data-unit'),
                quantity: 1,
            });
        }

        resetIdempotencyKey();
        renderCart();
    }

    function updateQuantity(productId, delta) {
        const item = cart.find(i => i.id === productId);
        if (!item) return;

        const newQty = item.quantity + delta;
        if (newQty <= 0) {
            removeFromCart(productId);
            return;
        }

        if (newQty > item.stock) {
            alert(`Stok tidak mencukupi. Sisa stok: ${item.stock} ${item.unit}.`);
            return;
        }

        item.quantity = newQty;
        resetIdempotencyKey();
        renderCart();
    }

    function removeFromCart(productId) {
        cart = cart.filter(i => i.id !== productId);
        resetIdempotencyKey();
        renderCart();
    }

    function clearCart() {
        if (cart.length === 0) return;
        if (confirm('Apakah Anda yakin ingin membatalkan dan mengosongkan seluruh isi keranjang ini?')) {
            cart = [];
            resetIdempotencyKey();
            renderCart();
        }
    }

    function calculateCartTotal() {
        return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('cart-empty');
        const itemCountEl = document.getElementById('cart-item-count');
        const subtotalEl = document.getElementById('cart-subtotal');
        const totalEl = document.getElementById('cart-total');
        const btnCheckout = document.getElementById('btn-open-payment');

        const totalQty = cart.reduce((sum, i) => sum + i.quantity, 0);
        const grandTotal = calculateCartTotal();

        itemCountEl.innerText = `${totalQty} item dipilih`;
        subtotalEl.innerText = formatRupiah(grandTotal);
        totalEl.innerText = formatRupiah(grandTotal);

        // Sinkronkan bilah keranjang bawah (mobile) & badge jumlah
        const mobileCount = document.getElementById('mobile-cart-count');
        const mobileTotal = document.getElementById('mobile-cart-total');
        const qtyBadge = document.getElementById('cart-qty-badge');
        if (mobileCount) mobileCount.innerText = totalQty;
        if (mobileTotal) mobileTotal.innerText = formatRupiah(grandTotal);
        if (qtyBadge) qtyBadge.innerText = `${totalQty} item`;

        if (cart.length === 0) {
            container.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            btnCheckout.disabled = true;
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');
        btnCheckout.disabled = false;

        let html = '';
        cart.forEach(item => {
            const lineSubtotal = item.price * item.quantity;
            const initial = (item.name || '?').trim().charAt(0).toUpperCase();
            html += `
                <div class="cart-line bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-sm hover:border-brand-200 hover:shadow-md transition">
                    <div class="flex items-start gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-100 to-slate-200 text-slate-500 font-black text-sm flex items-center justify-center flex-shrink-0">${initial}</div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-800 leading-snug line-clamp-2">${item.name}</h4>
                                <button type="button" onclick="removeFromCart(${item.id})" class="text-slate-300 hover:text-rose-500 transition p-0.5 flex-shrink-0" title="Hapus item">✕</button>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">${item.sku} • ${formatRupiah(item.price)}/${item.unit}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-2.5 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                            <button type="button" onclick="updateQuantity(${item.id}, -1)" class="w-7 h-7 rounded-md bg-white text-slate-700 hover:bg-slate-200 font-bold text-sm flex items-center justify-center shadow-sm transition">−</button>
                            <span class="w-8 text-center font-bold text-xs text-slate-800">${item.quantity}</span>
                            <button type="button" onclick="updateQuantity(${item.id}, 1)" class="w-7 h-7 rounded-md bg-white text-slate-700 hover:bg-slate-200 font-bold text-sm flex items-center justify-center shadow-sm transition">+</button>
                        </div>
                        <span class="text-sm font-black text-slate-900 font-mono">${formatRupiah(lineSubtotal)}</span>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Modal Payment Logic
    function openPaymentModal() {
        if (cart.length === 0) return;

        const total = calculateCartTotal();
        document.getElementById('modal-bill-total').innerText = formatRupiah(total);
        document.getElementById('payment-modal').classList.remove('hidden');

        // Reset inputs
        document.getElementById('cash-received-input').value = '';
        document.getElementById('qris-confirmed-check').checked = false;
        switchPaymentMethod('cash');
        calculateChange();
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
    }

    // Laci keranjang untuk layar < md (ponsel/tablet kecil)
    function openCartPanel() {
        const panel = document.getElementById('cart-panel');
        const backdrop = document.getElementById('cart-backdrop');
        if (panel) panel.classList.remove('translate-y-full');
        if (backdrop) backdrop.classList.remove('hidden');
    }

    function closeCartPanel() {
        const panel = document.getElementById('cart-panel');
        const backdrop = document.getElementById('cart-backdrop');
        if (panel) panel.classList.add('translate-y-full');
        if (backdrop) backdrop.classList.add('hidden');
    }

    function switchPaymentMethod(method) {
        currentMethod = method;
        const btnCash = document.getElementById('tab-btn-cash');
        const btnQris = document.getElementById('tab-btn-qris');
        const contentCash = document.getElementById('content-cash');
        const contentQris = document.getElementById('content-qris');

        if (method === 'cash') {
            btnCash.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
            btnCash.classList.remove('text-slate-500');
            btnQris.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
            btnQris.classList.add('text-slate-500');

            contentCash.classList.remove('hidden');
            contentQris.classList.add('hidden');
            calculateChange();
        } else {
            btnQris.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
            btnQris.classList.remove('text-slate-500');
            btnCash.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
            btnCash.classList.add('text-slate-500');

            contentQris.classList.remove('hidden');
            contentCash.classList.add('hidden');
            toggleQrisSubmit();
        }
    }

    function setCashPreset(val) {
        const total = calculateCartTotal();
        const input = document.getElementById('cash-received-input');
        if (val === 'exact') {
            input.value = total;
        } else {
            input.value = val;
        }
        calculateChange();
    }

    function calculateChange() {
        const total = calculateCartTotal();
        const cashInput = parseFloat(document.getElementById('cash-received-input').value) || 0;
        const display = document.getElementById('cash-change-display');
        const indicator = document.getElementById('cash-status-indicator');
        const btnSubmit = document.getElementById('btn-submit-transaction');

        if (currentMethod !== 'cash') return;

        if (cashInput <= 0) {
            display.innerText = 'Rp 0';
            indicator.innerText = 'Menunggu Pembayaran';
            indicator.className = 'text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg';
            btnSubmit.disabled = true;
        } else if (cashInput < total) {
            const def = total - cashInput;
            display.innerText = '- ' + formatRupiah(def);
            indicator.innerText = 'Kurang ' + formatRupiah(def);
            indicator.className = 'text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200';
            btnSubmit.disabled = true;
        } else {
            const change = cashInput - total;
            display.innerText = formatRupiah(change);
            indicator.innerText = 'Uang Cukup';
            indicator.className = 'text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200';
            btnSubmit.disabled = false;
        }
    }

    function toggleQrisSubmit() {
        const check = document.getElementById('qris-confirmed-check');
        const btnSubmit = document.getElementById('btn-submit-transaction');
        btnSubmit.disabled = !check.checked;
    }

    // Submit Transaction Atomically via Fetch API
    let isSubmitting = false;
    async function submitTransaction() {
        if (isSubmitting || cart.length === 0) return;

        const total = calculateCartTotal();
        if (!idempotencyKey) {
            idempotencyKey = generateIdempotencyKey();
        }
        let payload = {
            items: cart.map(i => ({ id: i.id, quantity: i.quantity })),
            payment_method: currentMethod,
            idempotency_key: idempotencyKey,
        };

        if (currentMethod === 'cash') {
            const cashInput = parseFloat(document.getElementById('cash-received-input').value) || 0;
            if (cashInput < total) {
                alert('Jumlah uang yang diterima belum mencukupi total belanja.');
                return;
            }
            payload.payment_amount = cashInput;
        } else {
            const check = document.getElementById('qris-confirmed-check');
            if (!check.checked) {
                alert('Harap centang konfirmasi verifikasi pembayaran QRIS.');
                return;
            }
            payload.payment_amount = total;
        }

        isSubmitting = true;
        const spinner = document.getElementById('submit-spinner');
        const btnText = document.getElementById('submit-btn-text');
        const btnSubmit = document.getElementById('btn-submit-transaction');
        spinner.classList.remove('hidden');
        btnText.innerText = 'Memproses Transaksi...';
        btnSubmit.disabled = true;

        try {
            const response = await fetch(@json(route('pos.process', [], false)), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            // Baca sebagai teks dahulu, lalu parse JSON secara aman. Dengan begitu
            // respons non-JSON (mis. halaman login atau error HTML) tidak membuat
            // JSON.parse melempar error dan menampilkan pesan "masalah jaringan".
            const raw = await response.text();
            let data = null;
            try {
                data = raw ? JSON.parse(raw) : null;
            } catch (parseError) {
                data = null;
            }

            if (response.ok && data && data.success) {
                cart = [];
                resetIdempotencyKey();
                renderCart();
                closePaymentModal();
                // Redirect langsung ke struk digital dengan opsi cetak
                window.location.href = data.receipt_url + '?new=1';
                return;
            }

            // Sesi habis / dialihkan ke halaman login.
            if (response.redirected && /\/login(\?|$)/.test(response.url)) {
                alert('Sesi Anda telah berakhir. Silakan login kembali, lalu ulangi transaksi.');
                window.location.href = @json(route('login', [], false));
                return;
            }

            let errMsg = (data && data.message) ? data.message : ('Permintaan gagal (HTTP ' + response.status + ').');
            if (data && data.errors) {
                const firstErr = Object.values(data.errors)[0];
                errMsg = Array.isArray(firstErr) ? firstErr[0] : firstErr;
            }
            alert('Gagal: ' + errMsg);
            isSubmitting = false;
            spinner.classList.add('hidden');
            btnText.innerText = 'Selesaikan & Simpan Transaksi';
            btnSubmit.disabled = false;
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan atau server terputus. Silakan periksa koneksi Anda.');
            isSubmitting = false;
            spinner.classList.add('hidden');
            btnText.innerText = 'Selesaikan & Simpan Transaksi';
            btnSubmit.disabled = false;
        }
    }
</script>
@endpush
