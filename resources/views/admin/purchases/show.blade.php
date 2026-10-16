@extends('layouts.app')

@section('title', 'Detail Faktur Pembelian ' . $purchase->purchase_number . ' — Bluesea Lounge POS')

@section('content')
<div class="max-w-3xl mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pembelian
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('purchases.edit', $purchase) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-xl border border-sky-300 text-xs font-semibold text-sky-700 hover:bg-sky-50 transition">
                <svg class="w-4 h-4 mr-1.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Koreksi
            </a>
            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" onsubmit="return confirm('Hapus catatan pembelian {{ $purchase->purchase_number }}? Data historis tetap tersimpan untuk audit.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3.5 py-1.5 rounded-xl border border-rose-300 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                    <svg class="w-4 h-4 mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
            <button type="button" onclick="window.print()" class="inline-flex items-center px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Faktur
            </button>
        </div>
    </div>

    <!-- Invoice Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-200 gap-4">
            <div>
                <span class="text-xs uppercase tracking-wider text-sky-600 font-bold">Faktur Pembelian Barang</span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 font-mono mt-0.5">{{ $purchase->purchase_number }}</h1>
                <p class="text-xs text-slate-500 mt-1">Tanggal: {{ $purchase->purchase_date->translatedFormat('d F Y') }}</p>
            </div>
            <div class="text-left sm:text-right text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400">Pemasok:</span> <strong class="text-slate-800">{{ $purchase->supplier_name ?? '-' }}</strong></div>
                <div><span class="text-slate-400">Dicatat oleh:</span> <span class="font-medium">{{ $purchase->recordedByUser->name }}</span></div>
            </div>
        </div>

        @if ($purchase->notes)
        <div class="mt-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-600 border border-slate-100">
            <strong class="text-slate-700">Catatan Pengadaan:</strong> {{ $purchase->notes }}
        </div>
        @endif

        <!-- Items Table -->
        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-y border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">Nama Barang</th>
                        <th class="py-2.5 px-3 text-center">Jumlah</th>
                        <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($purchase->items as $item)
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-800">{{ $item->product_name_snapshot }}</td>
                        <td class="py-3 px-3 text-center font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="py-3 px-3 text-right font-mono">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200">
                        <td colspan="3" class="py-4 px-3 font-bold text-slate-800 text-sm">TOTAL BIAYA PEMBELIAN:</td>
                        <td class="py-4 px-3 text-right font-mono font-black text-brand-700 text-base">
                            Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
