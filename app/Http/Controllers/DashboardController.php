<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $hour = (int) $now->format('G');

        $greeting = match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };

        // Sebelum login dibuat, $request->user() masih null -> pakai nama bawaan
        $user = $request->user();
        $fullName = $user?->name ?? 'Admin Toko';
        $firstName = explode(' ', trim($fullName))[0];
        $roleLabel = Tenant::role()->name ?? ucfirst($user?->role ?? 'Admin');

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

        $summary = $this->summary($request);

        return view('dashboard', $summary + [
            'greeting' => $greeting,
            'firstName' => $firstName,
            'fullName' => $fullName,
            'roleLabel' => $roleLabel,
            'dateLabel' => $now->locale('id')->translatedFormat('l, j F Y'),
            'tz' => config('app.timezone'),
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

    /** Ringkasan Owner: total otomatis + filter periode, cabang, dan jenis laporan. */
    private function summary(Request $r): array
    {
        $b = Tenant::id();
        $from = $r->query('from') ?: now()->startOfMonth()->toDateString();
        $to = $r->query('to') ?: now()->toDateString();
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        $group = in_array($r->query('group'), ['harian', 'mingguan', 'bulanan'], true) ? $r->query('group') : 'harian';
        $branch = $r->query('branch_id');
        $allowed = Tenant::branchIds();

        $tx = function () use ($b, $from, $to, $branch, $allowed) {
            $q = DB::table('transactions')->where('business_id', $b)->where('status', 'paid')
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            if ($branch) {
                $q->where('branch_id', $branch);
            }
            if ($allowed !== null) {
                $q->whereIn('branch_id', $allowed ?: [0]);
            }

            return $q;
        };

        $omzet = (float) $tx()->sum('total');
        $count = $tx()->count();
        $item = DB::table('transaction_items as i')->join('transactions as t', 't.id', '=', 'i.transaction_id')
            ->where('t.business_id', $b)->where('t.status', 'paid')
            ->whereDate('t.created_at', '>=', $from)->whereDate('t.created_at', '<=', $to)
            ->when($branch, fn ($q) => $q->where('t.branch_id', $branch))
            ->when($allowed !== null, fn ($q) => $q->whereIn('t.branch_id', $allowed ?: [0]))
            ->selectRaw('SUM(i.subtotal) as s, SUM(i.cost_price * (i.qty - i.returned_qty)) as c, SUM(i.subtotal * i.returned_qty / i.qty) as r')->first();
        $profit = (float) $item->s - (float) $item->r - (float) $item->c;

        $buckets = [];
        foreach ($tx()->get(['created_at', 'total']) as $t) {
            $d = \Carbon\Carbon::parse($t->created_at);
            $k = match ($group) {
                'mingguan' => $d->copy()->startOfWeek()->format('d/m'),
                'bulanan' => $d->format('m/Y'),
                default => $d->format('d/m'),
            };
            $buckets[$k] = ($buckets[$k] ?? 0) + $t->total;
        }

        $logs = DB::table('audit_logs')->where('audit_logs.business_id', $b)->leftJoin('users', 'users.id', '=', 'audit_logs.user_id')
            ->orderByDesc('audit_logs.id')->limit(200)->get(['audit_logs.*', 'users.name as user_name']);

        return [
            'sum' => [
                'omzet' => $this->rupiah($omzet), 'profit' => $this->rupiah($profit), 'count' => $count,
                'products' => DB::table('products')->where('business_id', $b)->whereNull('deleted_at')->count(),
                'customers' => DB::table('customers')->where('business_id', $b)->count(),
                'low' => DB::table('stocks as s')->join('products as p', 'p.id', '=', 's.product_id')->where('s.business_id', $b)
                    ->whereColumn('s.quantity', '<=', DB::raw('COALESCE(s.min_stock, p.min_stock)'))->count(),
            ],
            'chart' => $buckets, 'logs' => $logs,
            'f' => ['from' => $from, 'to' => $to, 'group' => $group, 'branch_id' => $branch],
            'branchOptions' => Tenant::branches(),
        ];
    }
}
