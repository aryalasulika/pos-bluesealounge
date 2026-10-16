@extends('layouts.app')

@section('title', 'Laporan Pembelian Barang — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Laporan Riwayat Pembelian Barang</h1>
            <p class="text-xs sm:text-sm text-slate-500">Rekapitulasi biaya pengadaan barang dan pasokan operasional lounge dari supplier.</p>
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

    <!-- Period & Filter Card -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.purchases', ['period' => 'today']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'today' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Hari Ini
                </a>
                <a href="{{ route('reports.purchases', ['period' => 'weekly']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'weekly' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('reports.purchases', ['period' => 'monthly']) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'monthly' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Bulan Ini
                </a>
            </div>

            <form method="GET" action="{{ route('reports.purchases') }}" class="flex items-center space-x-2">
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="text" name="supplier_name" value="{{ request('supplier_name') }}" placeholder="Cari Supplier..."
                    class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl shadow-sm">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <!-- Period & Summary Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-slate-900 text-white rounded-2xl p-5 flex flex-col justify-between">
            <span class="text-xs text-sky-400 font-bold uppercase tracking-wider">Periode Laporan</span>
            <div class="text-lg font-bold mt-1">{{ $periodLabel }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Total {{ $purchases->count() }} faktur pengadaan barang</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Biaya Pembelian</span>
            <div class="text-2xl font-black text-slate-800 font-mono mt-1">
                Rp {{ number_format($totalCost, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Akumulasi seluruh pengeluaran pengadaan periode ini</p>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Pemasok / Supplier</th>
                        <th class="py-3 px-4">Rincian Barang</th>
                        <th class="py-3 px-4 text-right">Total Biaya (Rp)</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $p->purchase_number }}</td>
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $p->purchase_date->translatedFormat('d M Y') }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-700">{{ $p->supplier_name ?? '-' }}</td>
                        <td class="py-3.5 px-4 max-w-xs truncate">
                            @foreach ($p->items as $it)
                                <span class="inline-block mr-1 text-slate-700">{{ $it->product_name_snapshot }} ({{ $it->quantity }} {{ $it->unit }}),</span>
                            @endforeach
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-800 font-mono text-sm whitespace-nowrap">
                            Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-medium whitespace-nowrap">{{ $p->recordedByUser->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-slate-400">Tidak ada catatan pembelian pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
