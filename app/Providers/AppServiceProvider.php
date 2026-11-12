<?php

namespace App\Providers;

use App\Support\Tenant;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Data akun untuk menu samping & bar atas di semua halaman
        View::composer(['partials.sidebar', 'partials.header', 'layouts.app'], function ($view) {
            $data = $view->getData();
            $user = Tenant::user();
            $full = $user?->name ?? 'Pengguna';
            $view->with([
                'fullName' => $data['fullName'] ?? $full,
                'firstName' => $data['firstName'] ?? explode(' ', trim($full))[0],
                'roleLabel' => $data['roleLabel'] ?? (Tenant::role()->name ?? ucfirst($user?->role ?? '')),
                'userPhoto' => $user?->photo_path ? asset('storage/' . $user->photo_path) : null,
                'bizName' => $user?->business_id ? DB::table('businesses')->where('id', $user->business_id)->value('name') : null,
            ]);
        });

        // Riwayat login
        $log = function (?object $user, ?string $email, string $status) {
            try {
                DB::table('login_histories')->insert([
                    'business_id' => $user->business_id ?? null, 'user_id' => $user->id ?? null, 'email' => $email,
                    'device' => mb_substr((string) request()->userAgent(), 0, 250), 'ip' => request()->ip(),
                    'status' => $status, 'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // riwayat login tidak boleh menggagalkan login
            }
        };
        Event::listen(Login::class, fn (Login $e) => $e->guard === 'web' ? $log($e->user, $e->user->email, 'berhasil') : null);
        Event::listen(Failed::class, fn (Failed $e) => $e->guard === 'web' ? $log($e->user, $e->credentials['email'] ?? null, 'gagal') : null);
        Event::listen(Logout::class, fn (Logout $e) => $e->guard === 'web' && $e->user ? $log($e->user, $e->user->email, 'keluar') : null);
    }
}
