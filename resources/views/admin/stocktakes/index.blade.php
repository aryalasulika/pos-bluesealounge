@extends('layouts.app')

@section('title', 'Stok Opname (Penyesuaian Fisik) — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Stok Opname (Fisik)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pencatatan rekonsiliasi jumlah fisik barang dengan data sistem secara berkala.</p>
        </div>
        <button type="button" onclick="openStocktakeModal()" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Input Stok Opname Baru
        </button>
    </div>

    <!-- Stocktake Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Opname</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4 text-center">Stok Sistem</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Selisih</th>
                        <th class="py-3 px-4">Alasan / Catatan</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($stocktakes as $st)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $st->stocktake_number }}</td>
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $st->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $st->product->name ?? 'Produk Dihapus' }}
                            <span class="block text-[10px] text-slate-400 font-mono">{{ $st->product->sku ?? '' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono">{{ $st->system_stock }}</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">{{ $st->physical_stock }}</td>
                        <td class="py-3.5 px-4 text-center font-bold font-mono">
                            @if ($st->difference > 0)
                                <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $st->difference }} (Lebih)</span>
                            @elseif ($st->difference < 0)
                                <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ $st->difference }} (Kurang)</span>
                            @else
                                <span class="text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">0 (Sesuai)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 max-w-xs text-slate-600 truncate">{{ $st->reason }}</td>
                        <td class="py-3.5 px-4 font-medium text-slate-700 whitespace-nowrap">{{ $st->recordedByUser->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-xs text-slate-400">Belum ada riwayat pelaksanaan stok opname.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stocktakes->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $stocktakes->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Input Stok Opname -->
<div id="stocktake-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold">Input Hasil Stok Opname Fisik</h3>
            <button type="button" onclick="closeStocktakeModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form action="{{ route('stocktakes.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Product Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk <span class="text-rose-500">*</span></label>
                <select id="opname-product" name="product_id" required onchange="handleProductSelect()"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">-- Pilih Produk Menu --</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}" data-stock="{{ $prod->current_stock }}" data-unit="{{ $prod->unit }}">
                            {{ $prod->name }} (SKU: {{ $prod->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Comparison Preview -->
            <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <div>
                    <span class="text-[10px] text-slate-400 block font-semibold uppercase">Stok Sistem</span>
                    <span id="opname-system-stock" class="text-lg font-black text-slate-700 font-mono">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block font-semibold uppercase">Stok Fisik</span>
                    <span id="opname-preview-physical" class="text-lg font-black text-brand-700 font-mono">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block font-semibold uppercase">Selisih</span>
                    <span id="opname-diff-display" class="text-lg font-black text-slate-500 font-mono">-</span>
                </div>
            </div>

            <!-- Physical Stock Input -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Fisik Ditemukan di Lounge <span class="text-rose-500">*</span></label>
                <input type="number" id="opname-physical-input" name="physical_stock" required min="0" oninput="calculateOpnameDiff()" placeholder="0"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <!-- Reason Input -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penyesuaian / Keterangan <span class="text-rose-500">*</span></label>
                <textarea name="reason" required rows="3" placeholder="Contoh: Selisih 2 cup karena tumpah saat operasional shift pagi..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeStocktakeModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Konfirmasi & Sesuaikan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStocktakeModal() { document.getElementById('stocktake-modal').classList.remove('hidden'); }
    function closeStocktakeModal() { document.getElementById('stocktake-modal').classList.add('hidden'); }

    let currentSystemStock = 0;
    function handleProductSelect() {
        const select = document.getElementById('opname-product');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption.value) {
            currentSystemStock = parseInt(selectedOption.getAttribute('data-stock'));
            const unit = selectedOption.getAttribute('data-unit');
            document.getElementById('opname-system-stock').innerText = currentSystemStock + ' ' + unit;
        } else {
            currentSystemStock = 0;
            document.getElementById('opname-system-stock').innerText = '-';
        }
        calculateOpnameDiff();
    }

    function calculateOpnameDiff() {
        const physicalVal = document.getElementById('opname-physical-input').value;
        const previewPhysical = document.getElementById('opname-preview-physical');
        const diffDisplay = document.getElementById('opname-diff-display');

        if (physicalVal === '') {
            previewPhysical.innerText = '-';
            diffDisplay.innerText = '-';
            diffDisplay.className = 'text-lg font-black text-slate-500 font-mono';
            return;
        }

        const physical = parseInt(physicalVal) || 0;
        previewPhysical.innerText = physical;

        const diff = physical - currentSystemStock;
        if (diff > 0) {
            diffDisplay.innerText = '+' + diff;
            diffDisplay.className = 'text-lg font-black text-emerald-600 font-mono';
        } else if (diff < 0) {
            diffDisplay.innerText = diff;
            diffDisplay.className = 'text-lg font-black text-rose-600 font-mono';
        } else {
            diffDisplay.innerText = '0';
            diffDisplay.className = 'text-lg font-black text-slate-700 font-mono';
        }
    }
</script>
@endsection
