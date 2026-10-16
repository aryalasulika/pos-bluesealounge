@extends('layouts.app')

@section('title', 'Pencatatan Pembelian Barang — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Pembelian Barang Operasional</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pencatatan faktur dan pengadaan perlengkapan / pasokan lounge dari supplier.</p>
        </div>
        <button type="button" onclick="openCreatePurchaseModal()" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Pembelian Baru
        </button>
    </div>

    <!-- PRD Info Disclaimer -->
    <div class="bg-sky-50 border border-sky-200 rounded-2xl p-3.5 flex items-center space-x-3 text-sky-900 text-xs">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <span class="font-bold">Ketentuan SOP (BR-07):</span> Pencatatan pembelian barang operasional hanya mencatat nilai pengeluaran/faktur dan <strong>tidak otomatis menambah stok produk</strong>. Penambahan stok siap jual dilakukan terpisah melalui menu Manajemen Stok.
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('purchases.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Supplier</label>
                <input type="text" name="supplier_name" value="{{ request('supplier_name') }}" placeholder="Cari supplier..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('purchases.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Pembelian</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Pemasok / Supplier</th>
                        <th class="py-3 px-4">Total Item</th>
                        <th class="py-3 px-4 text-right">Total Biaya</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $p->purchase_number }}</td>
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $p->purchase_date->translatedFormat('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-700">{{ $p->supplier_name ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $p->items->count() }} jenis barang</td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-800 font-mono text-sm">
                            Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-medium whitespace-nowrap">{{ $p->recordedByUser->name }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center space-x-1.5">
                                <a href="{{ route('purchases.show', $p) }}" class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                    Detail Faktur
                                </a>
                                <a href="{{ route('purchases.edit', $p) }}" class="inline-flex items-center px-3 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-semibold transition">
                                    Koreksi
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-xs text-slate-400">Belum ada catatan pembelian barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($purchases->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Create Purchase -->
<div id="create-purchase-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            <h3 class="text-sm font-bold">Catat Pembelian Barang Baru</h3>
            <button type="button" onclick="closeCreatePurchaseModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form action="{{ route('purchases.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembelian <span class="text-rose-500">*</span></label>
                    <input type="date" name="purchase_date" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemasok / Supplier</label>
                    <input type="text" name="supplier_name" placeholder="Contoh: PT Boga Nusantara"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Nomor faktur supplier, keterangan pembayaran..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition"></textarea>
            </div>

            <!-- Dynamic Purchase Items Section -->
            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Barang yang Dibeli</span>
                    <button type="button" onclick="addPurchaseRow()" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center">
                        + Tambah Baris
                    </button>
                </div>

                <div id="purchase-rows-container" class="space-y-3">
                    <!-- Default First Row -->
                    <div class="purchase-row grid grid-cols-12 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 items-end">
                        <div class="col-span-12 sm:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Nama Barang / Produk <span class="text-rose-500">*</span></label>
                            <input type="text" name="items[0][product_name]" required placeholder="Contoh: Biji Kopi Arabika 1kg"
                                class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Qty <span class="text-rose-500">*</span></label>
                            <input type="number" name="items[0][quantity]" required min="1" value="1" oninput="calcRowSubtotals()"
                                class="row-qty w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="items[0][unit]" required value="pack" placeholder="pack/pcs"
                                class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div class="col-span-4 sm:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Harga Beli (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="items[0][purchase_price]" required min="0" step="500" placeholder="0" oninput="calcRowSubtotals()"
                                class="row-price w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold">
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-xl bg-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700">Estimasi Total Pembelian:</span>
                    <span id="purchase-grand-total" class="font-mono text-base font-black text-brand-700">Rp 0</span>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreatePurchaseModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Simpan Catatan Pembelian</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreatePurchaseModal() { document.getElementById('create-purchase-modal').classList.remove('hidden'); }
    function closeCreatePurchaseModal() { document.getElementById('create-purchase-modal').classList.add('hidden'); }

    let rowIndex = 1;
    function addPurchaseRow() {
        const container = document.getElementById('purchase-rows-container');
        const row = document.createElement('div');
        row.className = 'purchase-row grid grid-cols-12 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 items-end relative';
        row.innerHTML = `
            <div class="col-span-12 sm:col-span-5">
                <label class="block text-[10px] font-bold text-slate-600 mb-1">Nama Barang / Produk <span class="text-rose-500">*</span></label>
                <input type="text" name="items[${rowIndex}][product_name]" required placeholder="Nama barang..."
                    class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
            </div>
            <div class="col-span-3 sm:col-span-2">
                <label class="block text-[10px] font-bold text-slate-600 mb-1">Qty <span class="text-rose-500">*</span></label>
                <input type="number" name="items[${rowIndex}][quantity]" required min="1" value="1" oninput="calcRowSubtotals()"
                    class="row-qty w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono">
            </div>
            <div class="col-span-3 sm:col-span-2">
                <label class="block text-[10px] font-bold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                <input type="text" name="items[${rowIndex}][unit]" required value="pcs"
                    class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
            </div>
            <div class="col-span-4 sm:col-span-2">
                <label class="block text-[10px] font-bold text-slate-600 mb-1">Harga Beli <span class="text-rose-500">*</span></label>
                <input type="number" name="items[${rowIndex}][purchase_price]" required min="0" step="500" placeholder="0" oninput="calcRowSubtotals()"
                    class="row-price w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold">
            </div>
            <div class="col-span-2 sm:col-span-1 text-right">
                <button type="button" onclick="this.closest('.purchase-row').remove(); calcRowSubtotals();" class="p-1.5 text-rose-500 hover:text-rose-700">✕</button>
            </div>
        `;
        container.appendChild(row);
        rowIndex++;
        calcRowSubtotals();
    }

    function calcRowSubtotals() {
        let total = 0;
        document.querySelectorAll('.purchase-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
            const price = parseFloat(row.querySelector('.row-price').value) || 0;
            total += (qty * price);
        });
        document.getElementById('purchase-grand-total').innerText = 'Rp ' + Math.round(total).toLocaleString('id-ID');
    }
</script>
@endsection
