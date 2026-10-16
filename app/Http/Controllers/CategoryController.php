<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        AuditLog::record('category_created', $category, null, $category->toArray(), "Kategori '{$category->name}' dibuat");

        return back()->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $before = $category->toArray();

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $category->is_active,
        ]);

        AuditLog::record('category_updated', $category, $before, $category->toArray(), "Kategori '{$category->name}' diperbarui");

        return back()->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function toggle(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        $statusStr = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        AuditLog::record('category_status_toggled', $category, null, ['is_active' => $category->is_active], "Kategori '{$category->name}' {$statusStr}");

        return back()->with('success', "Kategori '{$category->name}' berhasil {$statusStr}.");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh produk. Anda dapat menonaktifkannya.");
        }

        $before = $category->toArray();
        $name = $category->name;
        $category->delete();

        AuditLog::record('category_deleted', null, $before, null, "Kategori '{$name}' dihapus");

        return back()->with('success', "Kategori '{$name}' berhasil dihapus.");
    }
}
