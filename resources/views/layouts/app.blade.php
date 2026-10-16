<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bluesea Lounge POS') — Airport Lounge POS & Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .admin-scroll::-webkit-scrollbar { width: 8px; }
        .admin-scroll::-webkit-scrollbar-track { background: transparent; }
        .admin-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        .admin-scroll::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>

    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-800 bg-slate-50/50 selection:bg-brand-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-slate-900 border-b border-slate-800 text-white shadow-md">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    @if(auth()->user()->isAdmin())
                    <button type="button" onclick="openAdminNav()"
                        class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition"
                        aria-label="Buka menu navigasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    @endif

                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('pos.index') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-sky-400 flex items-center justify-center shadow-glow group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-lg font-bold tracking-tight bg-gradient-to-r from-white via-sky-100 to-sky-300 bg-clip-text text-transparent block">BLUESEA LOUNGE</span>
                            <span class="text-[10px] tracking-widest text-sky-400 uppercase font-semibold block -mt-1">Airport POS & Operational</span>
                        </div>
                    </a>

                    <!-- Current Mode Badge -->
                    <div class="hidden lg:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ auth()->user()->isAdmin() ? 'bg-indigo-900/60 text-indigo-300 border border-indigo-700/50' : 'bg-emerald-900/60 text-emerald-300 border border-emerald-700/50' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ auth()->user()->isAdmin() ? 'bg-indigo-400' : 'bg-emerald-400' }} animate-pulse"></span>
                        {{ auth()->user()->isAdmin() ? 'Administrator Panel' : 'Kasir Terminal' }}
                    </div>
                </div>

                <!-- Center Clock & Date (Lounge Airport Time) -->
                <div class="hidden lg:flex items-center space-x-2 text-xs text-slate-400 bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700/60">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="live-datetime" class="font-mono text-slate-200 font-medium">Memuat waktu...</span>
                    <span class="text-[10px] text-slate-400 font-mono">(WITA)</span>
                </div>

                <!-- Right Nav / User Profile -->
                <div class="flex items-center space-x-3">
                    @if(auth()->user()->isCashier())
                    <!-- Cashier: Riwayat Transaksi (menu kasir) -->
                    <a href="{{ route('sales.index') }}" class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all duration-150">
                        <svg class="w-4 h-4 mr-1.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat
                    </a>
                    @endif

                    <!-- Quick Link: Open POS terminal (disembunyikan saat sudah di halaman POS) -->
                    @unless (request()->routeIs('pos.*'))
                    <a href="{{ route('pos.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-600 hover:bg-brand-500 text-white shadow-sm transition-all duration-150">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Buka POS
                    </a>
                    @endunless

                    <!-- User dropdown -->
                    <div class="relative" x-data="{ open: false }" id="user-menu-container">
                        <button onclick="toggleUserMenu()" type="button" class="flex items-center space-x-2 text-sm rounded-lg p-1.5 hover:bg-slate-800 text-slate-200 focus:outline-none transition">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center text-xs font-bold text-sky-300 border border-slate-700">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="text-left hidden lg:block">
                                <div class="text-xs font-semibold text-slate-200 leading-tight">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] text-sky-400 capitalize">{{ auth()->user()->role }}</div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50 text-slate-700 border border-slate-100">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs text-slate-500">Masuk sebagai</p>
                                <p class="text-xs font-bold text-slate-800 truncate">@<span>{{ auth()->user()->username }}</span></p>
                            </div>
                            <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ auth()->user()->isAdmin() ? 'Profil & Kata Sandi' : 'Profil Saya' }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-xs text-red-600 hover:bg-red-50 transition border-t border-slate-100">
                                    <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- App Body with Sidebar & Content -->
    <div class="flex-1 flex overflow-hidden">
        @if(auth()->user()->isAdmin())
        <!-- Backdrop untuk laci navigasi (mobile & tablet) -->
        <div id="admin-nav-backdrop" class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm hidden lg:hidden" onclick="closeAdminNav()"></div>

        <!-- Admin Navigation: sidebar tetap di lg+, laci geser di bawah lg -->
        <aside id="admin-sidebar"
               class="fixed inset-y-0 left-0 z-50 w-72 max-w-[82vw] bg-slate-900 border-r border-slate-800 flex flex-col -translate-x-full transition-transform duration-300 ease-out shadow-2xl
                      lg:static lg:z-auto lg:translate-x-0 lg:w-64 lg:flex-shrink-0 lg:shadow-none">
            <!-- Header laci (mobile/tablet) -->
            <div class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-sky-400 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-white">Menu Admin</span>
                </div>
                <button type="button" onclick="closeAdminNav()" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition" aria-label="Tutup menu">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 admin-scroll">
                <div class="px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu Utama</div>
                
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('sales.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('sales.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Riwayat Transaksi
                </a>

                <div class="pt-4 px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Katalog & Persediaan</div>

                <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('products.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Manajemen Produk
                </a>

                <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('categories.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Kategori Produk
                </a>

                <a href="{{ route('stocks.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('stocks.index') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Stok & Peringatan
                </a>

                <a href="{{ route('stocks.movements') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('stocks.movements') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Pergerakan Stok
                </a>

                <a href="{{ route('stocktakes.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('stocktakes.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Stok Opname
                </a>

                <div class="pt-4 px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Operasional & Keuangan</div>

                <a href="{{ route('purchases.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('purchases.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Pembelian Barang
                </a>

                <a href="{{ route('expenses.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('expenses.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Pengeluaran Operasional
                </a>

                <div class="pt-4 px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Laporan & Analitik</div>

                <a href="{{ route('reports.sales') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('reports.sales') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Laporan Omzet
                </a>

                <a href="{{ route('reports.top_products') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('reports.top_products') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    Produk Terlaris
                </a>

                <a href="{{ route('reports.stock') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('reports.stock') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Laporan Stok
                </a>

                <a href="{{ route('reports.purchases') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('reports.purchases') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Laporan Pembelian
                </a>

                <div class="pt-4 px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Sistem & Keamanan</div>

                <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('users.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen Pengguna
                </a>

                <a href="{{ route('audit_logs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('audit_logs.*') ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Audit Log
                </a>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-slate-800">
                <div class="rounded-lg bg-slate-800/60 p-2.5 text-center">
                    <p class="text-[11px] font-semibold text-slate-300">Bluesea Lounge POS v1.0</p>
                    <p class="text-[9px] text-slate-400 mt-0.5">Terminal Bandara • Online</p>
                </div>
            </div>
        </aside>
        @endif

        <!-- Main Workspace Area -->
        <main class="flex-1 overflow-y-auto bg-slate-100/60 flex flex-col">
            <!-- Flash Notifications -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if (session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-start space-x-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
                </div>
                @endif

                @if (session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800 flex items-start space-x-3 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800 shadow-sm">
                    <div class="font-semibold text-sm mb-1">Perhatian: Terjadi kesalahan validasi</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <!-- Page Specific Content -->
            <div class="flex-1 px-4 sm:px-6 lg:px-8 pb-8">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Live Makassar Clock (WITA)
        function updateDateTime() {
            const el = document.getElementById('live-datetime');
            if (!el) return;
            const now = new Date();
            const options = {
                timeZone: 'Asia/Makassar',
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            el.innerText = new Intl.DateTimeFormat('id-ID', options).format(now).replace(/\./g, ':');
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // User dropdown menu toggle
        function toggleUserMenu() {
            const dropdown = document.getElementById('user-menu-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const container = document.getElementById('user-menu-container');
            const dropdown = document.getElementById('user-menu-dropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Laci navigasi admin (mobile & tablet)
        function openAdminNav() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-nav-backdrop');
            if (sidebar) sidebar.classList.remove('-translate-x-full');
            if (backdrop) backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeAdminNav() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-nav-backdrop');
            if (sidebar) sidebar.classList.add('-translate-x-full');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Tutup otomatis saat layar kembali ke ukuran desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) closeAdminNav();
        });

        // Tutup dengan tombol Esc
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAdminNav();
        });
    </script>
    @stack('scripts')
</body>
</html>
