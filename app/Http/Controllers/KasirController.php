<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\Tenant;
use Illuminate\Support\Facades\DB;

/** Beranda khusus kasir: ringkas, fokus ke transaksi milik kasir itu sendiri. */
class KasirController extends Controller
{
    public function home()
    {
        if (! Tenant::cashierUi()) {
            return redirect()->route('dashboard');
        }

        $u = Tenant::user();
        $b = Tenant::id();
        $now = now();
        $hour = (int) $now->format('G');
        $greeting = match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };

        // Hanya transaksi milik kasir ini, hari ini, yang tidak dibatalkan
        $mine = fn () => Transaction::where('user_id', $u->id)->where('status', '!=', 'void')->whereDate('created_at', $now->toDateString());

        $canSum = Tenant::can('lihat_omzet');
        $cashCodes = DB::table('payment_methods')->where('business_id', $b)->where('type', 'tunai')->pluck('code')->all();

        $stats = [
            'count' => $mine()->count(),
            'total' => $canSum ? (float) $mine()->sum('total') : null,
            'cash' => $canSum ? (float) $mine()->whereIn('payment_method', $cashCodes)->sum('total') : null,
        ];

        $recent = Transaction::where('user_id', $u->id)->latest()
            ->limit(6)->get(['id', 'invoice_no', 'total', 'payment_method', 'status', 'created_at']);

        return view('pages.kasir-home', [
            'greeting' => $greeting,
            'dateLabel' => $now->locale('id')->translatedFormat('l, j F Y'),
            'tz' => config('app.timezone'),
            'firstName' => explode(' ', trim($u->name))[0],
            'role' => Tenant::role($u)?->name ?? 'Kasir',
            'branches' => implode(', ', Tenant::branches()),
            'stats' => $stats,
            'recent' => $recent,
            'canSum' => $canSum,
        ]);
    }
}
