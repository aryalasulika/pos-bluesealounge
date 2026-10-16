@extends('layouts.app')

@section('title', 'Koreksi Faktur Pembelian ' . $purchase->purchase_number . ' — Bluesea Lounge POS')

@section('content')
<div class="max-w-3xl mx-auto py-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Detail Faktur
            </a>
            <h1 class="text-xl font-bold text-slate-800 mt-2">Koreksi Faktur Pembelian</h1>
            <p class="text-xs text-slate-500 font-mono">{{ $purchase->purchase_number }}</p>
        </div>
    </div>

    <!-- Audit Info -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-start space-x-3 text-amber-900 text-xs">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.062 18h13.876c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.33 15c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <span class="font-bold">Jejak Audit (FR-12):</span> Setiap perubahan pada catatan pembelian ini akan dicatat ke <strong>Audit Log</strong> beserta nilai sebelum dan sesudah koreksi.
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
        <form action="{{ route('purchases.update', $purchase) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembelian <span class="text-rose-500">*</span></label>
                    <input type="date" name="purchase_date" required value="{{ old('purchase_date', $purchase->purchase_date->toDateString()) }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemasok / Supplier</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name', $purchase->supplier_name) }}" placeholder="Contoh: PT Boga Nusantara"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Nomor faktur supplier, keterangan pembayaran..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">{{ old('notes', $purchase->notes) }}</textarea>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Barang yang Dibeli</span>
                    <button type="button" onclick="addPurchaseRow()" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center">
                        + Tambah Baris
                    </button>
                </div>

                <div id="purchase-rows-container" class="space-y-3">
                    @foreach ($purchase->items as $index => $item)
                    <div class="purchase-row grid grid-cols-12 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 items-end relative">
                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                        <div class="col-span-12 sm:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Nama Barang / Produk <span class="text-rose-500">*</span></label>
                            <input type="text" name="items[{{ $index }}][product_name]" required value="{{ $item->product_name_snapshot }}"
                                class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div class="col-span-3 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Qty <span class="text-rose-500">*</span></label>
                            <input type="number" name="items[{{ $index }}][quantity]" required min="1" value="{{ $item->quantity }}" oninput="calcRowSubtotals()"
                                class="row-qty w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono">
                        </div>
                        <div class="col-span-3 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="items[{{ $index }}][unit]" required value="{{ $item->unit }}"
                                class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Harga Beli <span class="text-rose-500">*</span></label>
                            <input type="number" name="items[{{ $index }}][purchase_price]" required min="0" step="500" value="{{ (float) $item->purchase_price }}" oninput="calcRowSubtotals()"
                                class="row-price w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold">
                        </div>
                        <div class="col-span-2 sm:col-span-1 text-right">
                            <button type="button" onclick="this.closest('.purchase-row').remove(); calcRowSubtotals();" class="p-1.5 text-rose-500 hover:text-rose-700">✕</button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 p-3 rounded-xl bg-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700">Total Pembelian:</span>
                    <span id="purchase-grand-total" class="font-mono text-base font-black text-brand-700">Rp 0</span>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('purchases.show', $purchase) }}" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Simpan Koreksi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let rowIndex = {{ $purchase->items->count() }};
    function addPurchaseRow() {
        const container = document.getElementById('purchase-rows-container');
        const row = document.createElement('div');
        row.className = 'purchase-row grid grid-cols-12 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 items-end relative';
        row.innerHTML = `
            <input type="hidden" name="items[${rowIndex}][product_id]" value="">
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

    calcRowSubtotals();
</script>
@endpush
@endsection
