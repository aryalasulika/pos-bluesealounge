<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('pos.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // FR-20: catat percobaan login yang gagal untuk keperluan keamanan.
            AuditLog::create([
                'user_id' => $user?->id,
                'action' => 'login_failed',
                'entity_type' => $user ? User::class : null,
                'entity_id' => $user?->id,
                'reason' => 'Percobaan login gagal untuk username: ' . $credentials['username'],
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'username' => 'Username atau password yang Anda masukkan salah.',
            ]);
        }

        if (!$user->is_active) {
            return back()->with('error', 'Akun Anda telah dinonaktifkan. Hubungi Administrator Bluesea Lounge.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditLog::record('user_login', $user, null, null, 'User berhasil login ke sistem');

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang kembali, ' . $user->name);
        }

        return redirect()->intended(route('pos.index'))->with('success', 'Selamat bertugas, ' . $user->name);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('user_logout', Auth::user(), null, null, 'User keluar dari sistem');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil logout.');
    }

    public function profile()
    {
        return view('auth.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        // Kebijakan keamanan: perubahan kata sandi mandiri hanya untuk Administrator.
        // Kata sandi akun Kasir dikelola Admin melalui menu Manajemen Pengguna.
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Perubahan kata sandi hanya dapat dilakukan oleh Administrator.');
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        AuditLog::record('password_changed', $user, null, null, 'Administrator memperbarui kata sandi mandiri');

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
