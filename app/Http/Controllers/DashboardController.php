<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $hour = (int) $now->format('G');

        $greeting = match (true) {
            $hour < 11 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };

        // Sebelum login dibuat, $request->user() masih null -> pakai nama bawaan
        $user = $request->user();
        $fullName = $user?->name ?? 'Admin Toko';
        $firstName = explode(' ', trim($fullName))[0];
        $roleLabel = ($user?->role ?? 'admin') === 'admin' ? 'Admin' : 'Kasir';

        // Transaksi hari ini (yang sudah dibayar, bukan void)
        $todayQuery = fn () => Transaction::where('status', 'paid')
            ->whereDate('created_at', $now->toDateString());

        $txCount = $todayQuery()->count();
        $revenue = (float) $todayQuery()->sum('total');
        $cash = (float) $todayQuery()->where('payment_method', 'cash')->sum('total');
        $nonCash = $revenue - $cash;
        $cashPercent = $revenue > 0 ? round($cash / $revenue * 100) : 0;

        $lowStock = Product::lowStock()->where('is_active', true)->count();

        $methodLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
        ];

        $recent = $todayQuery()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'invoice' => $t->invoice_no,
                'time' => $t->created_at->format('H:i'),
                'key' => $t->payment_method,
                'method' => $methodLabels[$t->payment_method] ?? $t->payment_method,
                'total' => $this->rupiah((float) $t->total),
            ])
            ->all();

        return view('dashboard', [
            'greeting' => $greeting,
            'firstName' => $firstName,
            'fullName' => $fullName,
            'roleLabel' => $roleLabel,
            'dateLabel' => $now->locale('id')->translatedFormat('l, j F Y'),
            'txCount' => $txCount,
            'revenue' => $this->rupiah($revenue),
            'cash' => $this->rupiah($cash),
            'nonCash' => $this->rupiah($nonCash),
            'cashPercent' => $cashPercent,
            'lowStock' => $lowStock,
            'recent' => $recent,
        ]);
    }

    private function rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}