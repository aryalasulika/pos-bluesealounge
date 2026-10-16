<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'role' => ['required', 'in:admin,cashier'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        AuditLog::record(
            'user_created',
            $user,
            null,
            ['id' => $user->id, 'username' => $user->username, 'role' => $user->role],
            "Akun pengguna baru '{$user->name}' ({$user->username}, role: {$user->role}) berhasil dibuat oleh " . Auth::user()->name
        );

        return back()->with('success', "Akun '{$user->name}' ({$user->username}) berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,cashier'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        // Prevent admin from revoking their own admin role
        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri menjadi Kasir.');
        }

        $before = $user->only(['name', 'username', 'email', 'role', 'is_active']);

        $user->name = $validated['name'];
        $user->username = strtolower($validated['username']);
        $user->email = $validated['email'] ?? null;
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        AuditLog::record(
            'user_updated',
            $user,
            $before,
            $user->only(['name', 'username', 'email', 'role', 'is_active']),
            "Data pengguna '{$user->username}' diperbarui oleh " . Auth::user()->name
        );

        return back()->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    public function toggle(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        AuditLog::record(
            'user_status_toggled',
            $user,
            null,
            ['is_active' => $user->is_active],
            "Akun '{$user->username}' telah {$statusStr} oleh " . Auth::user()->name
        );

        return back()->with('success', "Akun '{$user->name}' ({$user->username}) berhasil {$statusStr}.");
    }
}
