<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BusinessSetup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login / daftar / keluar untuk halaman web (memakai session).
 * Aplikasi Android tetap memakai token lewat Api\AuthController.
 */
class WebAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validateWithBag('login', [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // is_active: akun yang dinonaktifkan admin tidak bisa masuk
        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah, atau akun nonaktif.'], 'login')
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request)
    {
        $data = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan Sign In.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Pendaftar lewat halaman ini adalah pemilik usaha (owner): dibuatkan usaha baru + data awalnya
        // (cabang, gudang, role, akun kas, metode bayar). Password di-hash oleh cast 'hashed'.
        $user = DB::transaction(function () use ($data) {
            $businessId = BusinessSetup::create('Toko ' . explode(' ', trim($data['name']))[0]);

            $base = Str::slug(Str::before($data['email'], '@'), '_') ?: 'user';
            $username = $base;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . Str::lower(Str::random(3));
            }

            $user = User::create([
                'business_id' => $businessId,
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'owner',
            ]);
            DB::table('branch_user')->insert([
                'user_id' => $user->id,
                'branch_id' => DB::table('branches')->where('business_id', $businessId)->value('id'),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}