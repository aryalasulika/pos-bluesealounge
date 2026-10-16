@extends('layouts.app')

@section('title', 'Pengeluaran Operasional — Bluesea Lounge POS')

@section('content')
<div class="space-y-6 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Pengeluaran Operasional Lounge</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pencatatan beban operasional harian lounge (kebersihan, utilitas, pemeliharaan, dll).</p>
        </div>
        <button type="button" onclick="openCreateExpenseModal()" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Pengeluaran Baru
        </button>
    </div>

    <!-- Summary Box & Filter Card -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Summary Card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 border border-slate-800 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pengeluaran (Tersaring)</span>
            <div class="my-2">
                <span class="text-2xl font-black text-rose-400 font-mono">Rp {{ number_format($totalFilteredAmount, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-slate-400">Dipisahkan dari omzet penjualan sesuai aturan pembukuan (FR-13).</p>
        </div>

        <!-- Filter Form -->
        <div class="lg:col-span-3 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Kategori Pengeluaran</label>
                    <input type="text" name="category" value="{{ request('category') }}" placeholder="Contoh: Listrik, Kebersihan..."
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                </div>

                <div class="sm:col-span-3 flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="text-[11px] font-bold uppercase text-slate-400 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Kategori Biaya</th>
                        <th class="py-3 px-4">Deskripsi / Keterangan</th>
                        <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($expenses as $exp)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-medium text-slate-700 whitespace-nowrap">
                            {{ $exp->expense_date->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-800">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 max-w-sm truncate">{{ $exp->description }}</td>
                        <td class="py-3.5 px-4 text-right font-black text-rose-600 font-mono text-sm whitespace-nowrap">
                            Rp {{ number_format($exp->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-600 whitespace-nowrap">{{ $exp->recordedByUser->name }}</td>
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <button type="button" onclick="openEditExpenseModal({{ json_encode($exp) }})"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                Edit
                            </button>
                            <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="inline" onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-slate-400">Belum ada pengeluaran operasional yang dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($expenses->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Create Expense -->
<div id="create-expense-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold">Catat Pengeluaran Operasional</h3>
            <button type="button" onclick="closeCreateExpenseModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form action="{{ route('expenses.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Pengeluaran <span class="text-rose-500">*</span></label>
                <input type="text" name="category" required placeholder="Contoh: Listrik Lounge, Kebersihan, Transport..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Biaya (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="amount" required min="1" step="1000" placeholder="0"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Deskripsi <span class="text-rose-500">*</span></label>
                <textarea name="description" required rows="3" placeholder="Rincian tujuan pengeluaran..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateExpenseModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Expense -->
<div id="edit-expense-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold">Edit Catatan Pengeluaran</h3>
            <button type="button" onclick="closeEditExpenseModal()" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form id="edit-expense-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                <input type="date" id="edit-exp-date" name="expense_date" required
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Pengeluaran <span class="text-rose-500">*</span></label>
                <input type="text" id="edit-exp-category" name="category" required
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Biaya (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" id="edit-exp-amount" name="amount" required min="1" step="1000"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold font-mono focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Deskripsi <span class="text-rose-500">*</span></label>
                <textarea id="edit-exp-description" name="description" required rows="3"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:bg-white transition"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditExpenseModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-sm transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateExpenseModal() { document.getElementById('create-expense-modal').classList.remove('hidden'); }
    function closeCreateExpenseModal() { document.getElementById('create-expense-modal').classList.add('hidden'); }

    function openEditExpenseModal(exp) {
        document.getElementById('edit-expense-form').action = "/admin/expenses/" + exp.id;
        document.getElementById('edit-exp-date').value = exp.expense_date.split('T')[0];
        document.getElementById('edit-exp-category').value = exp.category;
        document.getElementById('edit-exp-amount').value = Math.round(exp.amount);
        document.getElementById('edit-exp-description').value = exp.description;
        document.getElementById('edit-expense-modal').classList.remove('hidden');
    }
    function closeEditExpenseModal() { document.getElementById('edit-expense-modal').classList.add('hidden'); }
</script>
@endsection
