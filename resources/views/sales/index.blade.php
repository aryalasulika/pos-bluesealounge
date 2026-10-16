@extends('layouts.app')

@section('title', 'Riwayat Transaksi Penjualan — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Riwayat Transaksi Penjualan</h1>
            <p class="text-xs sm:text-sm text-slate-500">Daftar seluruh transaksi kasir yang tercatat dalam sistem.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buka Terminal POS
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Cari No. Transaksi</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="TRX-2026..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Metode</label>
                <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>

            <!-- Cashier (Admin only) -->
            @if (auth()->user()->isAdmin())
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Kasir</label>
                <select name="cashier_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Kasir</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ (string) request('cashier_id') === (string) $cashier->id ? 'selected' : '' }}>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Start Date -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <!-- Filter Buttons -->
            <div class="lg:col-span-7 flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <a href="{{ route('sales.index') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Reset Filter
                </a>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Item Terjual</th>
                        <th class="py-3 px-4">Metode Bayar</th>
                        <th class="py-3 px-4 text-right">Total Transaksi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                    <tr class="hover:bg-slate-50/80 transition {{ $sale->isCancelled() ? 'bg-rose-50/30' : '' }}">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $sale->transaction_number }}</td>
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $sale->transaction_date->translatedFormat('d M Y, H:i') }}</td>
                        <td class="py-3.5 px-4 font-medium">{{ $sale->cashier->name }}</td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $sale->items->sum('quantity') }} unit ({{ $sale->items->count() }} produk)
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $sale->payment_method === 'qris' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $sale->payment_method }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-800 font-mono">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if ($sale->isCancelled())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                                    Dibatalkan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    Selesai
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                Struk Digital
                            </a>
                            <a href="{{ route('sales.print', $sale) }}" target="_blank" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-semibold transition">
                                Cetak Thermal
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-xs text-slate-400">
                            Tidak ditemukan transaksi yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
