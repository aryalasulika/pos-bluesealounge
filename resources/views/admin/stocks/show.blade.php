@extends('layouts.app')

@section('title', 'Riwayat Stok ' . $product->name . ' — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('stocks.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Manajemen Stok
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 mt-2">Riwayat Stok Produk</h1>
            <p class="text-xs sm:text-sm text-slate-500">Jejak lengkap perubahan kuantitas persediaan produk ini.</p>
        </div>
        <a href="{{ route('stocks.movements', ['product_id' => $product->id]) }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
            Lihat di Pergerakan Stok Global
        </a>
    </div>

    <!-- Product Summary Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-start space-x-4">
                @if ($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 flex items-center justify-center text-white font-black text-xl shadow-glow">
                        {{ strtoupper(substr($product->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $product->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px]">
                        <span class="font-mono font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">SKU: {{ $product->sku }}</span>
                        @if ($product->barcode)
                            <span class="font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">Barcode: {{ $product->barcode }}</span>
                        @endif
                        <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-100 font-semibold">{{ $product->category->name }}</span>
                        @if (!$product->is_active)
                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-100 font-semibold">Nonaktif</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Harga Jual: <span class="font-bold text-slate-700">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 lg:gap-4">
                <div class="text-center px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Stok Sistem</span>
                    <span class="block text-xl font-black font-mono {{ $product->current_stock <= 0 ? 'text-rose-600' : ($product->current_stock <= $product->minimum_stock ? 'text-amber-600' : 'text-slate-800') }}">
                        {{ $product->current_stock }}
                    </span>
                    <span class="block text-[10px] text-slate-400">{{ $product->unit }}</span>
                </div>
                <div class="text-center px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Batas Minimum</span>
                    <span class="block text-xl font-black font-mono text-slate-800">{{ $product->minimum_stock }}</span>
                    <span class="block text-[10px] text-slate-400">{{ $product->unit }}</span>
                </div>
                <div class="text-center px-4 py-3 rounded-2xl border {{ $product->current_stock <= 0 ? 'bg-rose-50 border-rose-200' : ($product->current_stock <= $product->minimum_stock ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200') }}">
                    <span class="block text-[10px] uppercase text-slate-400 font-bold">Status</span>
                    <span class="block text-sm font-black mt-1 {{ $product->current_stock <= 0 ? 'text-rose-700' : ($product->current_stock <= $product->minimum_stock ? 'text-amber-800' : 'text-emerald-700') }}">
                        {{ $product->stock_status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Movement Totals + Quick Add -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Stok Masuk</span>
                <div class="text-xl font-black text-emerald-600">+{{ $totalIn }} <span class="text-xs font-normal text-slate-500">{{ $product->unit }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium">Total Stok Keluar</span>
                <div class="text-xl font-black text-rose-600">{{ $totalOut }} <span class="text-xs font-normal text-slate-500">{{ $product->unit }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <form action="{{ route('stocks.add') }}" method="POST" class="flex items-end space-x-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="reason" value="Penambahan stok manual dari halaman detail produk">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Tambah Stok Manual</label>
                    <input type="number" name="quantity" required min="1" placeholder="Jumlah {{ $product->unit }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition whitespace-nowrap">+ Simpan</button>
            </form>
        </div>
    </div>

    <!-- Movement History -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Pergerakan Stok</h3>
            <span class="text-[11px] text-slate-400">{{ $movements->total() }} catatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Jenis Pergerakan</th>
                        <th class="py-3 px-4 text-center">Perubahan</th>
                        <th class="py-3 px-4 text-center">Sebelum</th>
                        <th class="py-3 px-4 text-center">Sesudah</th>
                        <th class="py-3 px-4">Alasan / Referensi</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $m)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap font-mono text-[11px]">{{ $m->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="py-3.5 px-4">
                            @if ($m->movement_type === 'sale')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100">Penjualan</span>
                            @elseif ($m->movement_type === 'manual_in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Stok Masuk</span>
                            @elseif ($m->movement_type === 'cancellation_return')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">Retur Pembatalan</span>
                            @elseif ($m->movement_type === 'stocktake_adjustment')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Penyesuaian Opname</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">{{ $m->movement_type_label }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold font-mono">
                            @if ($m->quantity_change > 0)
                                <span class="text-emerald-600">+{{ $m->quantity_change }}</span>
                            @elseif ($m->quantity_change < 0)
                                <span class="text-rose-600">{{ $m->quantity_change }}</span>
                            @else
                                <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-slate-500">{{ $m->stock_before }}</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">{{ $m->stock_after }}</td>
                        <td class="py-3.5 px-4 max-w-xs text-slate-600 text-[11px]">{{ $m->reason ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium whitespace-nowrap">{{ $m->user->name ?? 'Sistem' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-xs text-slate-400">Belum ada pergerakan stok untuk produk ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    <!-- Stocktake History -->
    @if ($stocktakes->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Stok Opname</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Opname</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4 text-center">Stok Sistem</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Selisih</th>
                        <th class="py-3 px-4">Alasan</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($stocktakes as $st)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-700">{{ $st->stocktake_number }}</td>
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap font-mono text-[11px]">{{ $st->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="py-3.5 px-4 text-center font-mono text-slate-500">{{ $st->system_stock }}</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">{{ $st->physical_stock }}</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold">
                            @if ($st->difference > 0)
                                <span class="text-emerald-600">+{{ $st->difference }}</span>
                            @elseif ($st->difference < 0)
                                <span class="text-rose-600">{{ $st->difference }}</span>
                            @else
                                <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 max-w-xs text-slate-600 text-[11px]">{{ $st->reason }}</td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium whitespace-nowrap">{{ $st->recordedByUser->name ?? 'Sistem' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
