@extends('layouts.app')

@section('title', 'Struk Transaksi ' . $sale->transaction_number . ' — Bluesea Lounge')

@section('content')
<div class="max-w-2xl mx-auto py-6 space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Riwayat
        </a>

        <div class="flex items-center space-x-2">
            <a href="{{ route('sales.print', $sale) }}" target="_blank"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Struk Thermal
            </a>

            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-glow transition">
                Transaksi Baru
            </a>
        </div>
    </div>

    <!-- Cancellation Banner if Cancelled -->
    @if ($sale->isCancelled())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-rose-900 shadow-sm flex items-start space-x-3">
        <svg class="w-6 h-6 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-xs space-y-1">
            <h3 class="font-bold text-sm text-rose-800">Transaksi Ini Telah Dibatalkan</h3>
            <p><span class="font-semibold text-rose-700">Dibatalkan oleh:</span> {{ $sale->cancelledByUser->name ?? 'Admin' }} pada {{ $sale->cancelled_at ? $sale->cancelled_at->translatedFormat('d F Y, H:i') : '-' }}</p>
            <p><span class="font-semibold text-rose-700">Alasan:</span> "{{ $sale->cancellation_reason }}"</p>
            <p class="text-[11px] text-rose-600 italic">Stok item transaksi ini telah otomatis dikembalikan ke sistem persediaan.</p>
        </div>
    </div>
    @endif

    <!-- Digital Receipt Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden">
        <!-- Receipt Top Header -->
        <div class="bg-slate-900 text-white p-6 sm:p-8 text-center relative">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 flex items-center justify-center shadow-glow mb-3">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight">BLUESEA LOUNGE</h2>
            <p class="text-[11px] text-sky-400 font-medium tracking-wider uppercase mt-0.5">Airport Terminal Food & Beverage</p>
            <p class="text-[10px] text-slate-400 mt-1">Struk Resmi Pembelian Lounge Bandara</p>

            <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <div class="text-left">
                    <span class="block text-[10px] uppercase text-slate-500">No. Transaksi</span>
                    <span class="font-mono font-bold text-white text-sm">{{ $sale->transaction_number }}</span>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] uppercase text-slate-500">Waktu Transaksi</span>
                    <span class="font-mono text-slate-200">{{ $sale->transaction_date->translatedFormat('d/m/Y H:i') }} WITA</span>
                </div>
            </div>
        </div>

        <!-- Receipt Body Details -->
        <div class="p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-2 text-xs text-slate-600 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase">Kasir Bertugas</span>
                    <span class="font-semibold text-slate-800">{{ $sale->cashier->name }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[10px] uppercase">Metode Pembayaran</span>
                    <span class="font-bold text-slate-800 uppercase inline-flex items-center">
                        <span class="w-2 h-2 rounded-full mr-1.5 {{ $sale->payment_method === 'qris' ? 'bg-purple-500' : 'bg-emerald-500' }}"></span>
                        {{ $sale->payment_method }}
                    </span>
                </div>
            </div>

            <!-- Items Table -->
            <div class="space-y-3">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex justify-between pb-1 border-b border-slate-100">
                    <span>Menu / Item</span>
                    <span>Subtotal</span>
                </div>

                @foreach ($sale->items as $item)
                <div class="flex items-start justify-between text-xs py-1">
                    <div class="pr-4">
                        <span class="font-bold text-slate-800 block">{{ $item->product_name_snapshot }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">
                            {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </span>
                    </div>
                    <span class="font-mono font-bold text-slate-800 whitespace-nowrap">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>

            <!-- Totals & Payment Summary -->
            <div class="pt-4 border-t-2 border-dashed border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-mono font-semibold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-100">
                    <span>Total Pembayaran</span>
                    <span class="text-base text-brand-700 font-mono">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                </div>

                @if ($sale->payment_method === 'cash')
                <div class="flex justify-between text-slate-600 pt-1">
                    <span>Uang Diterima</span>
                    <span class="font-mono">Rp {{ number_format($sale->payment_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600 font-semibold">
                    <span>Kembalian</span>
                    <span class="font-mono text-emerald-600">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
                @else
                <div class="flex justify-between text-slate-600 pt-1">
                    <span>Status QRIS</span>
                    <span class="font-semibold text-purple-700">Lunas Terverifikasi</span>
                </div>
                @endif
            </div>

            <!-- Footer Message -->
            <div class="pt-6 border-t border-slate-100 text-center text-xs text-slate-400 space-y-1">
                <p class="font-semibold text-slate-600">Terima kasih atas kunjungan Anda di Bluesea Lounge</p>
                <p class="text-[10px]">Semoga perjalanan Anda menyenangkan & aman sampai tujuan.</p>
            </div>
        </div>

        <!-- Admin Action Footer: Cancel Transaction -->
        @if (auth()->user()->isAdmin() && !$sale->isCancelled())
        <div class="p-4 bg-rose-50/50 border-t border-rose-100 flex items-center justify-between">
            <span class="text-xs text-rose-700 font-medium">Kewenangan Khusus Administrator</span>
            <button type="button" onclick="openCancelModal()"
                class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition">
                Batalkan Transaksi Ini
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Modal Pembatalan Transaksi (Admin Only) -->
@if (auth()->user()->isAdmin() && !$sale->isCancelled())
<div id="cancel-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-rose-600 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-sm font-bold">Batalkan Transaksi {{ $sale->transaction_number }}</h3>
            </div>
            <button type="button" onclick="closeCancelModal()" class="text-white/80 hover:text-white">✕</button>
        </div>

        <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="text-xs text-slate-600 space-y-2">
                <p class="font-semibold text-slate-800">Apakah Anda yakin ingin membatalkan transaksi ini?</p>
                <p>Tindakan ini akan:</p>
                <ul class="list-disc list-inside space-y-1 text-slate-500 pl-1">
                    <li>Mengubah status transaksi menjadi <strong>Dibatalkan</strong>.</li>
                    <li>Mengembalikan seluruh stok produk ke database persediaan secara otomatis.</li>
                    <li>Mengecualikan transaksi ini dari perhitungan laporan omzet & produk terlaris.</li>
                    <li>Mencatat tindakan pembatalan ini ke dalam <strong>Audit Log</strong> sistem.</li>
                </ul>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Alasan Pembatalan <span class="text-rose-500">* (Wajib)</span>
                </label>
                <textarea name="cancellation_reason" required rows="3" minlength="5" maxlength="500"
                    placeholder="Contoh: Tamu membatalkan pesanan karena penerbangan boarding mendadak..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 focus:bg-white transition"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeCancelModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Kembali
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition">
                    Konfirmasi Pembatalan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCancelModal() {
        document.getElementById('cancel-modal').classList.remove('hidden');
    }
    function closeCancelModal() {
        document.getElementById('cancel-modal').classList.add('hidden');
    }
</script>
@endif
@endsection
