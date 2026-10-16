@extends('layouts.app')

@section('title', 'Manajemen Stok & Peringatan — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Manajemen Stok & Peringatan</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pantau ketersediaan persediaan produk lounge dan tambah stok masuk secara manual.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('stocks.movements') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Riwayat Pergerakan Stok
            </a>
            <button type="button" onclick="openAddStockModal()" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Stok Manual
            </button>
        </div>
    </div>

    <!-- Alert Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium">Stok Kondisi Aman</span>
                <div class="text-xl font-black text-slate-800">
                    {{ $products->total() - $lowStockTotal }} <span class="text-xs font-normal text-slate-500">produk</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium">Mencapai Batas Minimum</span>
                <div class="text-xl font-black text-amber-600">
                    {{ $lowStockTotal }} <span class="text-xs font-normal text-slate-500">produk</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium">Stok Habis Total</span>
                <div class="text-xl font-black text-rose-600">
                    {{ $outOfStockTotal }} <span class="text-xs font-normal text-slate-500">produk</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('stocks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Cari Produk</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / SKU / Barcode..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Filter Status Stok</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Status</option>
                    <option value="safe" {{ request('status') === 'safe' ? 'selected' : '' }}>Aman</option>
                    <option value="minimum" {{ request('status') === 'minimum' ? 'selected' : '' }}>Stok Minimum (Kritis)</option>
                    <option value="out" {{ request('status') === 'out' ? 'selected' : '' }}>Stok Habis</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                    Terapkan
                </button>
                <a href="{{ route('stocks.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4 text-center">Stok Sistem</th>
                        <th class="py-3 px-4 text-center">Batas Minimum</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $prod)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-800 text-sm">
                            <a href="{{ route('stocks.show', $prod) }}" class="hover:text-brand-600 transition">{{ $prod->name }}</a>
                        </td>
                        <td class="py-3.5 px-4">{{ $prod->category->name }}</td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $prod->sku }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-sm">
                            <span class="{{ $prod->current_stock <= 0 ? 'text-rose-600' : ($prod->current_stock <= $prod->minimum_stock ? 'text-amber-600' : 'text-slate-800') }}">
                                {{ $prod->current_stock }} {{ $prod->unit }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                            {{ $prod->minimum_stock }} {{ $prod->unit }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if ($prod->current_stock <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    Stok Habis
                                </span>
                            @elseif ($prod->current_stock <= $prod->minimum_stock)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    Stok Minimum
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    Aman
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center space-x-1.5">
                                <a href="{{ route('stocks.show', $prod) }}"
                                    class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                    Riwayat
                                </a>
                                <button type="button" onclick="quickAddStock({{ $prod->id }}, '{{ addslashes($prod->name) }}', '{{ $prod->unit }}')"
                                    class="inline-flex items-center px-3 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold transition">
                                    + Tambah Stok
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-xs text-slate-400">Tidak ada produk ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Stok Manual -->
<div id="add-stock-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold">Penambahan Stok Manual</h3>
            <button type="button" onclick="closeAddStockModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form action="{{ route('stocks.add') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk <span class="text-rose-500">*</span></label>
                <select id="modal-product-id" name="product_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Pilih Produk Menu</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} (Sisa: {{ $prod->current_stock }} {{ $prod->unit }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Stok Masuk <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input type="number" name="quantity" required min="1" placeholder="Jumlah penambahan"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan / Referensi Masuk <span class="text-rose-500">*</span></label>
                <input type="text" name="reason" required placeholder="Contoh: Penerimaan pasokan dari dapur pusat, restock supplier..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAddStockModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Konfirmasi & Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddStockModal() {
        document.getElementById('add-stock-modal').classList.remove('hidden');
    }
    function closeAddStockModal() {
        document.getElementById('add-stock-modal').classList.add('hidden');
    }

    function quickAddStock(productId, name, unit) {
        document.getElementById('modal-product-id').value = productId;
        openAddStockModal();
    }
</script>
@endsection
