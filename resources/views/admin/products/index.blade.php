@extends('layouts.app')

@section('title', 'Manajemen Produk — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Manajemen Produk</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola katalog menu makanan, minuman, harga jual, dan batas minimum stok.</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk Baru
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Cari Produk</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, SKU, atau Barcode..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Status Persediaan</label>
                <select name="stock_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Status Stok</option>
                    <option value="safe" {{ request('stock_status') === 'safe' ? 'selected' : '' }}>Aman</option>
                    <option value="minimum" {{ request('stock_status') === 'minimum' ? 'selected' : '' }}>Stok Minimum</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Stok Habis</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Info Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">SKU / Barcode</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                        <th class="py-3 px-4 text-center">Batas Min.</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $prod)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            <div class="flex items-center space-x-3">
                                @if ($prod->image_path)
                                    <img src="{{ asset('storage/' . $prod->image_path) }}" alt="{{ $prod->name }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs border border-sky-100">
                                        {{ strtoupper(substr($prod->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="block text-slate-800 font-bold text-xs">{{ $prod->name }}</span>
                                    <span class="text-[10px] text-slate-400 capitalize">Satuan: {{ $prod->unit }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $prod->category->name }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px]">
                            <span class="font-bold text-slate-700 block">{{ $prod->sku }}</span>
                            <span class="text-slate-400">{{ $prod->barcode ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-brand-700 font-mono text-sm">
                            Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if ($prod->current_stock <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                    0 {{ $prod->unit }} (Habis)
                                </span>
                            @elseif ($prod->current_stock <= $prod->minimum_stock)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    {{ $prod->current_stock }} {{ $prod->unit }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    {{ $prod->current_stock }} {{ $prod->unit }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                            {{ $prod->minimum_stock }} {{ $prod->unit }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if ($prod->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <!-- Edit Button -->
                            <button type="button" onclick="openEditModal({{ json_encode($prod) }})"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                Edit
                            </button>

                            <!-- Toggle Status -->
                            <form action="{{ route('products.toggle', $prod) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg {{ $prod->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' }} text-xs font-semibold transition">
                                    {{ $prod->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <!-- Delete -->
                            <form action="{{ route('products.destroy', $prod) }}" method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-xs text-slate-400">Belum ada produk yang memenuhi kriteria pencarian.</td>
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

<!-- Modal Create Product -->
<div id="create-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            <h3 class="text-sm font-bold">Tambah Produk Menu Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Iced Caramel Macchiato"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode SKU <span class="text-rose-500">* (Unik)</span></label>
                    <input type="text" name="sku" required placeholder="Contoh: KOP-005"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Barcode -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Barcode (Opsional)</label>
                    <input type="text" name="barcode" placeholder="Scan atau ketik barcode..."
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit" required placeholder="Contoh: cup, porsi, pcs" value="pcs"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Selling Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="selling_price" required min="0" step="500" placeholder="0"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Initial Stock -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Stok Awal</label>
                    <input type="number" name="initial_stock" min="0" value="0"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Minimum Stock -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batas Minimum Stok <span class="text-rose-500">*</span></label>
                    <input type="number" name="minimum_stock" required min="0" value="5"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Menu (Opsional)</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Product -->
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            <h3 class="text-sm font-bold">Edit Informasi Produk</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form id="edit-form" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit-name" name="name" required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select id="edit-category-id" name="category_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode SKU <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit-sku" name="sku" required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Barcode -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Barcode (Opsional)</label>
                    <input type="text" id="edit-barcode" name="barcode"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit-unit" name="unit" required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Selling Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" id="edit-selling-price" name="selling_price" required min="0" step="500"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Perubahan harga akan dicatat otomatis di Audit Log.</span>
                </div>

                <!-- Minimum Stock -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batas Minimum Stok <span class="text-rose-500">*</span></label>
                    <input type="number" id="edit-minimum-stock" name="minimum_stock" required min="0"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <!-- Image Upload -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Foto Menu (Opsional)</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() { document.getElementById('create-modal').classList.remove('hidden'); }
    function closeCreateModal() { document.getElementById('create-modal').classList.add('hidden'); }

    function openEditModal(prod) {
        document.getElementById('edit-form').action = "/admin/products/" + prod.id;
        document.getElementById('edit-name').value = prod.name;
        document.getElementById('edit-category-id').value = prod.category_id;
        document.getElementById('edit-sku').value = prod.sku;
        document.getElementById('edit-barcode').value = prod.barcode || '';
        document.getElementById('edit-unit').value = prod.unit;
        document.getElementById('edit-selling-price').value = Math.round(prod.selling_price);
        document.getElementById('edit-minimum-stock').value = prod.minimum_stock;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() { document.getElementById('edit-modal').classList.add('hidden'); }
</script>
@endsection
