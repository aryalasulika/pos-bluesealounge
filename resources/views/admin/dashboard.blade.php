@extends('layouts.app')

@section('title', 'Admin Dashboard — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header Welcome & Quick Stats -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 rounded-2xl text-white shadow-lg border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-500/20 text-sky-300 border border-sky-400/30 mb-2">
                Terminal Operasional Bluesea Lounge
            </span>
            <h1 class="text-2xl font-black tracking-tight text-white">Ringkasan Operasional & POS</h1>
            <p class="text-xs text-slate-300 mt-1">Pemantauan real-time penjualan, persediaan stok, dan arus kas operasional.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-sky-500 hover:from-brand-500 hover:to-sky-400 text-white text-xs font-bold shadow-glow transition duration-150">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Mulai Transaksi Kasir
            </a>
            <a href="{{ route('reports.sales') }}" class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition">
                <svg class="w-4 h-4 mr-1.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Laporan Omzet
            </a>
        </div>
    </div>

    <!-- 4 Key Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Omzet Hari Ini -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Omzet Hari Ini</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-800">Rp {{ number_format($todayOmzet, 0, ',', '.') }}</div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="font-semibold text-emerald-600 mr-1">{{ $todaySalesCount }}</span> transaksi valid hari ini
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
        </div>

        <!-- 2. Omzet Bulan Berjalan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Omzet Bulan Ini</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-800">Rp {{ number_format($monthlyOmzet, 0, ',', '.') }}</div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="font-semibold text-sky-600 mr-1">{{ $monthlySalesCount }}</span> transaksi valid bulan berjalan
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-500 to-sky-400"></div>
        </div>

        <!-- 3. Peringatan Stok Minimum -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Stok Kritis / Habis</span>
                <div class="w-9 h-9 rounded-xl {{ ($lowStockCount > 0 || $outOfStockCount > 0) ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black {{ ($lowStockCount > 0 || $outOfStockCount > 0) ? 'text-amber-600' : 'text-slate-800' }}">
                    {{ $lowStockCount }} <span class="text-xs font-normal text-slate-500">item</span>
                </div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="font-semibold text-rose-500 mr-1">{{ $outOfStockCount }}</span> item telah habis total
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-rose-400"></div>
        </div>

        <!-- 4. Ringkasan Pengeluaran & Pembelian -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengeluaran & Pembelian</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-black text-slate-800">Rp {{ number_format($monthlyExpensesTotal + $monthlyPurchasesTotal, 0, ',', '.') }}</div>
                <div class="mt-1 text-[11px] text-slate-500 flex justify-between">
                    <span>Beli: Rp {{ number_format($monthlyPurchasesTotal, 0, ',', '.') }}</span>
                    <span>Biaya: Rp {{ number_format($monthlyExpensesTotal, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-400"></div>
        </div>
    </div>

    <!-- Alert Box: Low Stock Products (if any) -->
    @if ($lowStockProducts->count() > 0)
    <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 sm:p-5 text-amber-900 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900">Peringatan: Persediaan Produk di Bawah Batas Minimum</h3>
                    <p class="text-xs text-amber-700 mt-0.5">Terdapat {{ $lowStockProducts->count() }} produk yang memerlukan penambahan stok segera untuk menjaga kelancaran lounge.</p>
                </div>
            </div>
            <a href="{{ route('stocks.index', ['status' => 'minimum']) }}" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline flex-shrink-0">
                Kelola Stok →
            </a>
        </div>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
            @foreach ($lowStockProducts as $lp)
            <div class="bg-white/90 border border-amber-200/80 rounded-xl px-3 py-2 flex items-center justify-between text-xs">
                <div class="truncate mr-2">
                    <span class="font-bold text-slate-800 block truncate">{{ $lp->name }}</span>
                    <span class="text-[10px] text-slate-500 font-mono">SKU: {{ $lp->sku }}</span>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold {{ $lp->current_stock <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                        {{ $lp->current_stock }} {{ $lp->unit }}
                    </span>
                    <span class="block text-[9px] text-slate-400 mt-0.5">Min: {{ $lp->minimum_stock }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 2 Column Section: Top Products & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Selling Products (Bulan Ini) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Produk Terlaris</h2>
                    <p class="text-[11px] text-slate-400">Peringkat penjualan bulan berjalan</p>
                </div>
                <a href="{{ route('reports.top_products') }}" class="text-xs text-brand-600 hover:text-brand-700 font-semibold">Semua →</a>
            </div>

            <div class="flex-1 space-y-3">
                @forelse ($topProducts as $index => $top)
                <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-7 h-7 rounded-lg {{ $index === 0 ? 'bg-amber-500 text-white' : ($index === 1 ? 'bg-slate-300 text-slate-800' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-600')) }} flex items-center justify-center font-bold text-xs">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 leading-snug">{{ $top->product_name_snapshot }}</p>
                            <p class="text-[10px] text-slate-400 font-mono">Rp {{ number_format($top->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100">
                            {{ $top->total_qty }} terjual
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-xs text-slate-400">
                    Belum ada data penjualan tercatat pada bulan ini.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Transaksi Terbaru</h2>
                    <p class="text-[11px] text-slate-400">Aktivitas penjualan kasir terkini</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-semibold">Lihat Semua →</a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50/70 border-y border-slate-100">
                        <tr>
                            <th class="py-2.5 px-3">No. Transaksi</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Kasir</th>
                            <th class="py-2.5 px-3">Metode</th>
                            <th class="py-2.5 px-3 text-right">Total</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentSales as $sale)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">{{ $sale->transaction_number }}</td>
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $sale->transaction_date->translatedFormat('d M H:i') }}</td>
                            <td class="py-3 px-3">{{ $sale->cashier->name }}</td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sale->payment_method === 'qris' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-800">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center">
                                @if ($sale->isCancelled())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700">Dibatalkan</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">Selesai</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('sales.show', $sale) }}" class="text-brand-600 hover:text-brand-700 font-semibold text-xs">Struk</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-slate-400">Belum ada riwayat transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
