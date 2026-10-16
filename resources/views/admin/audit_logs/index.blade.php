@extends('layouts.app')

@section('title', 'Audit Log Sistem — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Audit Log & Rekam Jejak Sistem</h1>
            <p class="text-xs sm:text-sm text-slate-500">Catatan aktivitas penting operasional, pembatalan transaksi, stok opname, dan perubahan keamanan.</p>
        </div>
        <div class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-900 text-sky-400 text-xs font-mono font-bold">
            🛡️ Protected Audit Trail
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('audit_logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Pengguna / Petugas</label>
                <select name="user_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Petugas</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} (@<span>{{ $u->username }}</span>)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Aktivitas</label>
                <select name="action" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <option value="">Semua Aktivitas</option>
                    @foreach ($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('audit_logs.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu Kejadian</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Aktivitas</th>
                        <th class="py-3 px-4">Keterangan / Alasan</th>
                        <th class="py-3 px-4">Alamat IP</th>
                        <th class="py-3 px-4 text-right">Data Log</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                            {{ $log->user->name ?? 'Sistem / Anonim' }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                {{ str_contains($log->action, 'cancelled') || str_contains($log->action, 'deleted') ? 'bg-rose-100 text-rose-700' :
                                   (str_contains($log->action, 'login') ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-800') }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 max-w-md text-slate-700 text-xs leading-relaxed">
                            {{ $log->reason ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            @if ($log->before_data || $log->after_data)
                            <button type="button" onclick="showLogDetails({{ json_encode($log) }})"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                Lihat Data JSON
                            </button>
                            @else
                            <span class="text-slate-300 text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-slate-400">Belum ada catatan log aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Inspect JSON Log -->
<div id="log-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            <h3 class="text-sm font-bold">Rincian Data Audit Log</h3>
            <button type="button" onclick="closeLogModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <div class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Aktivitas</span>
                <span id="modal-log-action" class="font-mono font-bold text-slate-800 text-sm"></span>
            </div>

            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Data Sebelum Perubahan (Before)</span>
                <pre id="modal-log-before" class="p-3 bg-slate-900 text-sky-300 rounded-xl overflow-x-auto font-mono text-[11px] mt-1"></pre>
            </div>

            <div>
                <span class="text-slate-400 block uppercase font-bold text-[10px]">Data Sesudah Perubahan (After)</span>
                <pre id="modal-log-after" class="p-3 bg-slate-900 text-emerald-300 rounded-xl overflow-x-auto font-mono text-[11px] mt-1"></pre>
            </div>
        </div>

        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-right">
            <button type="button" onclick="closeLogModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function showLogDetails(log) {
        document.getElementById('modal-log-action').innerText = log.action;
        document.getElementById('modal-log-before').innerText = log.before_data ? JSON.stringify(log.before_data, null, 2) : '(Kosong / Tidak Ada)';
        document.getElementById('modal-log-after').innerText = log.after_data ? JSON.stringify(log.after_data, null, 2) : '(Kosong / Tidak Ada)';
        document.getElementById('log-modal').classList.remove('hidden');
    }
    function closeLogModal() {
        document.getElementById('log-modal').classList.add('hidden');
    }
</script>
@endsection
