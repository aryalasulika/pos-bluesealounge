@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Riwayat Pergerakan Stok</h1>
            <p class="text-xs sm:text-sm text-slate-500">Jejak audit seluruh perubahan kuantitas persediaan produk (penjualan, stok masuk, retur, & opname).</p>
        </div>
        <a href="{{ route('stocks.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
            ← Kembali ke Manajemen Stok
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('stocks.movements') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Produk</label>
                <select name="product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>{{ $prod->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Pergerakan</label>
                <select name="movement_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Jenis</option>
                    <option value="manual_in" {{ request('movement_type') === 'manual_in' ? 'selected' : '' }}>Stok Masuk Manual</option>
                    <option value="sale" {{ request('movement_type') === 'sale' ? 'selected' : '' }}>Penjualan</option>
                    <option value="cancellation_return" {{ request('movement_type') === 'cancellation_return' ? 'selected' : '' }}>Retur Pembatalan</option>
                    <option value="stocktake_adjustment" {{ request('movement_type') === 'stocktake_adjustment' ? 'selected' : '' }}>Penyesuaian Opname</option>
                </select>
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
                <a href="{{ route('stocks.movements') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Produk</th>
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
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap font-mono text-[11px]">
                            {{ $m->created_at->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            @if ($m->product)
                                <a href="{{ route('stocks.show', $m->product) }}" class="hover:text-brand-600 transition">{{ $m->product->name }}</a>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $m->product->sku }}</span>
                            @else
                                Produk Dihapus
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if ($m->movement_type === 'sale')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                    Penjualan
                                </span>
                            @elseif ($m->movement_type === 'manual_in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Stok Masuk
                                </span>
                            @elseif ($m->movement_type === 'cancellation_return')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                    Retur Pembatalan
                                </span>
                            @elseif ($m->movement_type === 'stocktake_adjustment')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                    Penyesuaian Opname
                                </span>
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
                        <td class="py-3.5 px-4 max-w-xs text-slate-600 text-[11px] truncate">
                            {{ $m->reason ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium whitespace-nowrap">
                            {{ $m->user->name ?? 'Sistem' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-xs text-slate-400">Belum ada catatan pergerakan stok.</td>
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
</div>
@endsection
