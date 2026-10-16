<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Masuk — Bluesea Lounge POS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #020617;
            background-image:
                radial-gradient(1100px 560px at 12% 8%, rgba(2, 132, 199, 0.22), transparent 60%),
                radial-gradient(900px 520px at 88% 92%, rgba(56, 189, 248, 0.16), transparent 60%);
        }
        .brand-grid {
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.07) 1px, transparent 1px);
            background-size: 42px 42px;
            -webkit-mask-image: radial-gradient(80% 70% at 50% 40%, #000 45%, transparent 100%);
            mask-image: radial-gradient(80% 70% at 50% 40%, #000 45%, transparent 100%);
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0) }
            50% { transform: translateY(-14px) }
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(14px) }
            to { opacity: 1; transform: translateY(0) }
        }
        .animate-fade-up { animation: fade-up .6s cubic-bezier(.22,1,.36,1) both; }
        .animate-float-slow { animation: float-slow 7s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-up, .animate-float-slow { animation: none; }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-100">
    <div class="min-h-full grid lg:grid-cols-2">

        <!-- ===================== Panel Kiri: Branding ===================== -->
        <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-slate-900/40 p-12 xl:p-16">
            <!-- Dekorasi -->
            <div class="absolute inset-0 brand-grid pointer-events-none"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-600/30 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-[28rem] h-[28rem] bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Wordmark kiri atas -->
            <div class="relative flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 flex items-center justify-center shadow-glow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-lg font-extrabold tracking-tight text-white leading-none">BLUESEA <span class="text-sky-400">LOUNGE</span></span>
                    <span class="block text-[10px] uppercase tracking-[0.28em] text-slate-400 mt-1">Airport Terminal</span>
                </div>
            </div>

            <!-- Headline & fitur -->
            <div class="relative max-w-md animate-fade-up">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/30 text-[11px] font-semibold text-sky-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                    Point of Sale &amp; Manajemen Operasional
                </span>

                <h1 class="mt-5 text-4xl xl:text-5xl font-black leading-[1.1] text-white">
                    Kelola penjualan lounge dengan
                    <span class="text-sky-400">cepat &amp; akurat</span>.
                </h1>

                <p class="mt-4 text-sm text-slate-400 leading-relaxed">
                    Satu sistem terpadu untuk transaksi kasir, persediaan produk siap jual, pembelian barang, pengeluaran operasional, hingga laporan dan audit.
                </p>

                <ul class="mt-8 space-y-3.5">
                    @foreach ([
                        'Transaksi kasir cepat dengan pembayaran Tunai & QRIS',
                        'Manajemen stok, kategori, dan stok opname',
                        'Laporan omzet, produk terlaris, & audit log',
                    ] as $feature)
                    <li class="flex items-start gap-3 text-sm text-slate-300">
                        <span class="mt-0.5 w-5 h-5 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Footer kiri -->
            <div class="relative text-[11px] text-slate-500">
                Sistem internal • Zona waktu Asia/Makassar • Sesi aman
            </div>
        </aside>

        <!-- ===================== Panel Kanan: Form Login ===================== -->
        <main class="relative flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <!-- Wordmark untuk layar kecil -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 shadow-glow-lg mb-4 animate-float-slow">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        BLUESEA <span class="text-sky-400">LOUNGE</span>
                    </h1>
                    <p class="text-[11px] sm:text-xs text-sky-300/80 font-medium tracking-[0.2em] uppercase mt-2">
                        Airport Lounge POS
                    </p>
                </div>

                <!-- Kartu Form -->
                <div class="rounded-3xl border border-slate-800 bg-slate-900/70 backdrop-blur-xl shadow-2xl p-6 sm:p-8 animate-fade-up">
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-white">Masuk ke Sistem</h2>
                        <p class="text-xs text-slate-400 mt-1">Silakan masukkan username dan password akun Anda.</p>
                    </div>

                    @if (session('error'))
                    <div class="mb-4 rounded-xl bg-rose-950/60 border border-rose-800 p-3 text-rose-200 text-xs flex items-start space-x-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    @if (session('info'))
                    <div class="mb-4 rounded-xl bg-sky-950/60 border border-sky-800 p-3 text-sky-200 text-xs flex items-start space-x-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-sky-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-xs font-semibold text-slate-300 mb-1.5">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                    placeholder="Contoh: admin atau kasir1"
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition @error('username') border-rose-500 @enderror">
                            </div>
                            @error('username')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password" required autocomplete="current-password"
                                    placeholder="Masukkan kata sandi akun"
                                    class="w-full pl-10 pr-11 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition @error('password') border-rose-500 @enderror">
                                <button type="button" onclick="togglePassword()" tabindex="-1"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition"
                                    aria-label="Tampilkan / sembunyikan kata sandi">
                                    <svg id="eye-open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <svg id="eye-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.223-3.592m3.042-2.2A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.02 10.02 0 01-4.293 5.208M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center space-x-2 text-slate-300 cursor-pointer">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-brand-600 focus:ring-brand-500">
                                <span>Ingat sesi saya</span>
                            </label>
                            <span class="text-slate-500 text-[11px]">Sistem Khusus Internal</span>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                            class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-sky-500 hover:from-brand-500 hover:to-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400 shadow-glow transition-all duration-200 mt-2 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Masuk ke Sistem POS
                        </button>
                    </form>
                </div>

                <!-- Wordmark kecil untuk layar besar (di bawah kartu) -->
                <p class="hidden lg:block text-center text-[11px] text-slate-500 mt-6">
                    &copy; {{ date('Y') }} Bluesea Lounge Airport POS. Hak cipta dilindungi.
                </p>
                <p class="lg:hidden text-center text-[11px] text-slate-500 mt-6">
                    &copy; {{ date('Y') }} Bluesea Lounge Airport POS.
                </p>
            </div>
        </main>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const open = document.getElementById('eye-open');
            const off = document.getElementById('eye-off');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            open.classList.toggle('hidden', show);
            off.classList.toggle('hidden', !show);
        }
    </script>
</body>
</html>
