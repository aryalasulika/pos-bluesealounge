@extends('layouts.app')

@section('title', 'Laporan Stok Produk & Valuasi — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Laporan Stok & Valuasi Persediaan</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kondisi persediaan barang siap jual di lounge dan estimasi nilai persediaan saat ini.</p>
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

    <!-- Summary Valuation Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Kuantitas Persediaan</span>
                <div class="text-2xl font-black text-slate-800 font-mono mt-0.5">
                    {{ number_format($totalStockUnits, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">unit item</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Estimasi Valuasi Nilai Jual</span>
                <div class="text-2xl font-black text-brand-700 font-mono mt-0.5">
                    Rp {{ number_format($totalStockValue, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.stock') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Filter Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Status Persediaan</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Status</option>
                    <option value="safe" {{ request('status') === 'safe' ? 'selected' : '' }}>Aman</option>
                    <option value="minimum" {{ request('status') === 'minimum' ? 'selected' : '' }}>Stok Minimum</option>
                    <option value="out" {{ request('status') === 'out' ? 'selected' : '' }}>Stok Habis</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('reports.stock') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stock Report Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 font-mono">Kode SKU</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                        <th class="py-3 px-4 text-center">Batas Min.</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Nilai Persediaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $p->name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $p->sku }}</td>
                        <td class="py-3 px-4">{{ $p->category->name }}</td>
                        <td class="py-3 px-4 text-right font-mono">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold">
                            <span class="{{ $p->current_stock <= 0 ? 'text-rose-600' : ($p->current_stock <= $p->minimum_stock ? 'text-amber-600' : 'text-slate-800') }}">
                                {{ $p->current_stock }} {{ $p->unit }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $p->minimum_stock }} {{ $p->unit }}</td>
                        <td class="py-3 px-4 text-center">
                            @if ($p->current_stock <= 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Habis</span>
                            @elseif ($p->current_stock <= $p->minimum_stock)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Minimum</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">Aman</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                            Rp {{ number_format($p->current_stock * $p->selling_price, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-xs text-slate-400">Tidak ada produk ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
