<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('recordedByUser')->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        $totalFilteredAmount = (clone $query)->sum('amount');
        $expenses = $query->paginate(15)->withQueryString();

        $categories = Expense::select('category')->distinct()->pluck('category');

        return view('admin.expenses.index', compact('expenses', 'categories', 'totalFilteredAmount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $expense = Expense::create([
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'recorded_by' => Auth::id(),
        ]);

        AuditLog::record(
            'expense_recorded',
            $expense,
            null,
            $expense->toArray(),
            "Pengeluaran operasional dicatat: Rp " . number_format($expense->amount, 0, ',', '.') . " ({$expense->category}) - {$expense->description}"
        );

        return back()->with('success', 'Pengeluaran operasional berhasil dicatat.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $before = $expense->toArray();

        $expense->update([
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
        ]);

        AuditLog::record(
            'expense_updated',
            $expense,
            $before,
            $expense->toArray(),
            "Pengeluaran operasional ID #{$expense->id} diperbarui"
        );

        return back()->with('success', 'Pengeluaran operasional berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $before = $expense->toArray();
        $desc = "Rp " . number_format($expense->amount, 0, ',', '.') . " ({$expense->category})";

        $expense->delete();

        AuditLog::record(
            'expense_deleted',
            null,
            $before,
            null,
            "Pengeluaran operasional dihapus: {$desc}"
        );

        return back()->with('success', 'Pengeluaran operasional berhasil dihapus.');
    }
}
