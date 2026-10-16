@extends('layouts.app')

@section('title', 'Laporan Omzet Penjualan — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Laporan Omzet Penjualan</h1>
            <p class="text-xs sm:text-sm text-slate-500">Rekapitulasi pendapatan kotor penjualan makanan & minuman pada periode yang dipilih.</p>
        </div>

        <!-- Export Buttons -->
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

    <!-- Period Selection & Filter -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-4">
        <!-- Preset Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.sales', ['period' => 'today']) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'today' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Hari Ini
            </a>
            <a href="{{ route('reports.sales', ['period' => 'weekly']) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'weekly' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Minggu Ini
            </a>
            <a href="{{ route('reports.sales', ['period' => 'monthly']) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'monthly' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Bulan Ini
            </a>
            <button type="button" onclick="document.getElementById('custom-date-box').classList.toggle('hidden')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'custom' ? 'bg-brand-600 text-white shadow-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Rentang Kustom ▾
            </button>
        </div>

        <!-- Custom Date Range Collapsible -->
        <div id="custom-date-box" class="{{ $period === 'custom' ? '' : 'hidden' }} pt-3 border-t border-slate-100">
            <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>
                <div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                        Terapkan Tanggal Kustom
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Period Label Banner -->
    <div class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-bold">Laporan Aktif:</span>
            <span>{{ $periodLabel }}</span>
        </div>
        <span class="text-[11px] text-slate-400">Asia/Makassar (WITA)</span>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Omzet -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Omzet Valid</span>
            <div class="text-2xl font-black text-brand-700 font-mono mt-2">
                Rp {{ number_format($totalOmzet, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Pendapatan kotor dari transaksi valid</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-brand-600"></div>
        </div>

        <!-- Valid Transactions -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Transaksi</span>
            <div class="text-2xl font-black text-slate-800 font-mono mt-2">
                {{ $totalTransactions }} <span class="text-xs font-normal text-slate-500">struk</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Total {{ $totalItemsSold }} unit produk terjual</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-sky-500"></div>
        </div>

        <!-- Cash vs QRIS -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pembayaran Tunai vs QRIS</span>
            <div class="mt-2 text-xs space-y-1 font-mono">
                <div class="flex justify-between">
                    <span class="text-emerald-700 font-semibold">Tunai ({{ $cashCount }}):</span>
                    <span class="font-bold">Rp {{ number_format($cashOmzet, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-purple-700 font-semibold">QRIS ({{ $qrisCount }}):</span>
                    <span class="font-bold">Rp {{ number_format($qrisOmzet, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500"></div>
        </div>

        <!-- Cancelled Transactions (Excluded) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Transaksi Dibatalkan</span>
            <div class="text-2xl font-black text-rose-600 font-mono mt-2">
                {{ $cancelledCount }} <span class="text-xs font-normal text-slate-500">transaksi</span>
            </div>
            <p class="text-[11px] text-rose-500/80 mt-1">Dikecualikan dari omzet (Rp {{ number_format($cancelledPotential, 0, ',', '.') }})</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-rose-500"></div>
        </div>
    </div>

    <!-- Sales Details Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Rincian Transaksi Selesai</h3>
            <span class="text-xs text-slate-400">{{ $sales->count() }} transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Transaksi</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Item Pesanan</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-right">Total (Rp)</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $s->transaction_number }}</td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">{{ $s->transaction_date->translatedFormat('d M H:i') }}</td>
                        <td class="py-3 px-4 font-medium">{{ $s->cashier->name }}</td>
                        <td class="py-3 px-4 text-slate-600 max-w-xs truncate">
                            @foreach ($s->items as $it)
                                <span class="inline-block mr-1">{{ $it->product_name_snapshot }} ({{ $it->quantity }}x),</span>
                            @endforeach
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $s->payment_method === 'qris' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $s->payment_method }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-black text-slate-800 font-mono text-sm">
                            Rp {{ number_format($s->total, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('sales.show', $s) }}" class="text-brand-600 hover:text-brand-700 font-semibold">Struk</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-xs text-slate-400">Tidak ada transaksi yang tercatat pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
