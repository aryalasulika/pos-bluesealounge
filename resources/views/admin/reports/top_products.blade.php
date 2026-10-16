@extends('layouts.app')

@section('title', 'Laporan Produk Terlaris — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Laporan Produk Terlaris</h1>
            <p class="text-xs sm:text-sm text-slate-500">Peringkat menu makanan & minuman terfavorit berdasarkan jumlah unit yang berhasil terjual.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor Excel
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Ekspor PDF
            </a>
        </div>
    </div>

    <!-- Period & Category Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.top_products', ['period' => 'today']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'today' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Hari Ini
                </a>
                <a href="{{ route('reports.top_products', ['period' => 'weekly']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'weekly' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('reports.top_products', ['period' => 'monthly']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'monthly' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Bulan Ini
                </a>
            </div>

            <form method="GET" action="{{ route('reports.top_products') }}" class="flex items-center space-x-2">
                <input type="hidden" name="period" value="{{ $period }}">
                <select name="category_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Banner -->
    <div class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs flex items-center justify-between">
        <span class="font-bold">Periode: {{ $periodLabel }}</span>
        <span class="text-[11px] text-slate-400">Hanya menghitung transaksi valid (exclude cancelled)</span>
    </div>

    <!-- Table Ranking -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 text-center w-16">Peringkat</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 font-mono">Kode SKU</th>
                        <th class="py-3 px-4 text-center">Unit Terjual</th>
                        <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                        <th class="py-3 px-4 text-right">Total Nilai Penjualan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($topProducts as $index => $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 text-center">
                            <span class="w-7 h-7 mx-auto rounded-lg inline-flex items-center justify-center font-bold text-xs {{ $index === 0 ? 'bg-amber-400 text-white shadow-sm' : ($index === 1 ? 'bg-slate-300 text-slate-800' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-600')) }}">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800 text-sm">
                            {{ $item->product_name_snapshot }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-500">{{ $item->sku_snapshot ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-sm text-brand-700">
                            {{ $item->total_quantity }} unit
                        </td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ $item->transactions_count }} transaksi</td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-800 font-mono text-sm">
                            Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-slate-400">Tidak ada data penjualan pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
