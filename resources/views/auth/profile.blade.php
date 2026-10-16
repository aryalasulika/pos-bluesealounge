@extends('layouts.app')

@section('title', 'Profil Pengguna — Bluesea Lounge POS')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">{{ $user->isAdmin() ? 'Profil & Keamanan Akun' : 'Profil Akun' }}</h1>
        <p class="text-xs sm:text-sm text-slate-500">
            {{ $user->isAdmin() ? 'Kelola informasi kredensial dan perbarui kata sandi akun Anda.' : 'Informasi akun Anda. Perubahan kata sandi dilakukan oleh Administrator.' }}
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Account Info Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 text-white text-2xl font-bold flex items-center justify-center shadow-glow mb-4">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <h2 class="text-base font-bold text-slate-800">{{ $user->name }}</h2>
            <p class="text-xs text-slate-500 font-mono">@<span>{{ $user->username }}</span></p>
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $user->isAdmin() ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                {{ $user->isAdmin() ? 'Administrator' : 'Kasir Lounge' }}
            </div>

            <div class="w-full border-t border-slate-100 mt-6 pt-4 text-left text-xs space-y-2 text-slate-600">
                <div class="flex justify-between">
                    <span class="text-slate-400">Email:</span>
                    <span class="font-medium">{{ $user->email ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Status Akun:</span>
                    <span class="font-semibold text-emerald-600">Aktif</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Bergabung:</span>
                    <span class="font-medium">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Change Password (Admin) / Info (Kasir) -->
        <div class="md:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            @if ($user->isAdmin())
            <h3 class="text-base font-bold text-slate-800 mb-1">Perbarui Kata Sandi</h3>
            <p class="text-xs text-slate-500 mb-6">Pastikan kata sandi baru Anda aman dan minimal memiliki 6 karakter.</p>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('current_password') border-rose-500 @enderror">
                    @error('current_password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru</label>
                    <input type="password" name="new_password" required minlength="6"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('new_password') border-rose-500 @enderror">
                    @error('new_password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="new_password_confirmation" required minlength="6"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                        Simpan Perubahan Sandi
                    </button>
                </div>
            </form>
            @else
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 mb-1">Kata Sandi Dikelola Administrator</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Demi keamanan, akun <strong>Kasir</strong> tidak dapat mengubah kata sandi sendiri.
                        Perubahan kata sandi hanya dapat dilakukan oleh <strong>Administrator</strong> melalui menu
                        <strong>Manajemen Pengguna</strong>. Silakan hubungi Administrator bila Anda perlu mengganti kata sandi.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
