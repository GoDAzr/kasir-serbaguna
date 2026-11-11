<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Ringkasan penjualan. Query: from, to (Y-m-d). Default: awal bulan s/d hari ini.
     */
    public function summary(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($request->query('from', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()))->endOfDay();

        $paid = fn () => Transaction::where('status', 'paid')->whereBetween('created_at', [$from, $to]);

        $totals = $paid()
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(discount), 0) as discount')
            ->first();

        $margin = TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.status', 'paid')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM((transaction_items.price - transaction_items.cost_price) * transaction_items.qty), 0) as margin')
            ->value('margin');

        $daily = $paid()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('SUM(total) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        $topProducts = TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.status', 'paid')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->selectRaw('transaction_items.product_id, transaction_items.product_name')
            ->selectRaw('SUM(transaction_items.qty) as qty_sold')
            ->selectRaw('SUM(transaction_items.subtotal) as revenue')
            ->groupBy('transaction_items.product_id', 'transaction_items.product_name')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get();

        $byPayment = $paid()
            ->selectRaw('payment_method')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total_transactions' => (int) $totals->total_transactions,
            'revenue' => (float) $totals->revenue,
            'discount' => (float) $totals->discount,
            // Estimasi laba = (harga jual - modal) x qty - diskon
            'profit_estimate' => (float) $margin - (float) $totals->discount,
            'daily' => $daily,
            'top_products' => $topProducts,
            'by_payment_method' => $byPayment,
            'low_stock_count' => Product::lowStock()->where('is_active', true)->count(),
        ]);
    }
}
