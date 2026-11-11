<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Riwayat transaksi. Kasir hanya melihat miliknya, admin melihat semua.
     * Query: from, to (Y-m-d), status, per_page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::with(['cashier:id,name', 'customer:id,name'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->latest()
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($transactions);
    }

    public function show(Request $request, Transaction $transaction)
    {
        $this->authorizeView($request, $transaction);

        return response()->json($transaction->load(['items', 'cashier:id,name', 'customer:id,name,phone']));
    }

    /**
     * Buat transaksi (checkout).
     * Harga diambil dari database, BUKAN dari kiriman aplikasi, supaya tidak bisa dimanipulasi.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_uuid' => 'nullable|uuid',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,qris,ewallet',
            'note' => 'nullable|string|max:255',
        ]);

        // Retry dari Android dengan client_uuid yang sama -> kembalikan transaksi yang sudah ada
        if (! empty($data['client_uuid'])) {
            $existing = Transaction::with('items')->where('client_uuid', $data['client_uuid'])->first();
            if ($existing) {
                return response()->json($existing, 200);
            }
        }

        // Gabungkan item dengan product_id yang sama
        $lines = collect($data['items'])
            ->groupBy('product_id')
            ->map(fn ($rows) => (int) $rows->sum('qty'));

        $transaction = DB::transaction(function () use ($data, $lines, $request) {
            $products = Product::whereIn('id', $lines->keys())
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $rows = [];

            foreach ($lines as $productId => $qty) {
                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Produk #{$productId} tidak tersedia."],
                    ]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => ["Stok {$product->name} tidak cukup (sisa {$product->stock})."],
                    ]);
                }

                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                $rows[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = min((float) ($data['discount'] ?? 0), $subtotal);
            $tax = round(($subtotal - $discount) * ((float) ($data['tax_percent'] ?? 0)) / 100, 2);
            $total = $subtotal - $discount + $tax;
            $paid = (float) $data['paid'];

            if ($paid < $total) {
                throw ValidationException::withMessages([
                    'paid' => ['Uang yang dibayarkan kurang dari total.'],
                ]);
            }

            $invoiceNo = 'INV-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(3));

            $transaction = Transaction::create([
                'invoice_no' => $invoiceNo,
                'client_uuid' => $data['client_uuid'] ?? null,
                'user_id' => $request->user()->id,
                'customer_id' => $data['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid' => $paid,
                'change' => $paid - $total,
                'payment_method' => $data['payment_method'],
                'status' => 'paid',
                'note' => $data['note'] ?? null,
            ]);

            foreach ($rows as $row) {
                /** @var Product $product */
                $product = $row['product'];

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'qty' => $row['qty'],
                    'subtotal' => $row['line_total'],
                ]);

                $product->decrement('stock', $row['qty']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => 'sale',
                    'qty' => -$row['qty'],
                    'stock_after' => $product->stock,
                    'reference' => $invoiceNo,
                ]);
            }

            return $transaction;
        });

        return response()->json(
            $transaction->load(['items', 'cashier:id,name', 'customer:id,name']),
            201
        );
    }

    /**
     * Batalkan transaksi (admin). Stok dikembalikan.
     */
    public function void(Request $request, Transaction $transaction)
    {
        if ($transaction->status === 'void') {
            return response()->json(['message' => 'Transaksi sudah dibatalkan.'], 422);
        }

        DB::transaction(function () use ($transaction, $request) {
            foreach ($transaction->items as $item) {
                $product = Product::withTrashed()->whereKey($item->product_id)->lockForUpdate()->first();

                if (! $product) {
                    continue;
                }

                $product->increment('stock', $item->qty);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => 'void',
                    'qty' => $item->qty,
                    'stock_after' => $product->stock,
                    'reference' => $transaction->invoice_no,
                    'note' => 'Pembatalan transaksi',
                ]);
            }

            $transaction->update(['status' => 'void', 'voided_at' => now()]);
        });

        return response()->json($transaction->fresh()->load('items'));
    }

    private function authorizeView(Request $request, Transaction $transaction): void
    {
        $user = $request->user();

        if (! $user->isAdmin() && $transaction->user_id !== $user->id) {
            abort(403, 'Anda tidak punya akses ke transaksi ini.');
        }
    }
}
