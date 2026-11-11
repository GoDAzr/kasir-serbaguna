<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    /**
     * Riwayat pergerakan stok satu produk.
     */
    public function history(Product $product)
    {
        return response()->json(
            $product->stockMovements()->with('user:id,name')->latest()->paginate(30)
        );
    }

    /**
     * Ubah stok.
     *  type=in     -> tambah stok sebanyak qty (barang masuk)
     *  type=out    -> kurangi stok sebanyak qty (rusak/hilang)
     *  type=adjust -> set stok menjadi qty (stok opname)
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'type' => 'required|in:in,out,adjust',
            'qty' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request, $product) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->first();

            $newStock = match ($data['type']) {
                'in' => $locked->stock + $data['qty'],
                'out' => $locked->stock - $data['qty'],
                'adjust' => $data['qty'],
            };

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'qty' => ['Stok tidak mencukupi untuk dikurangi.'],
                ]);
            }

            $delta = $newStock - $locked->stock;
            $locked->update(['stock' => $newStock]);

            StockMovement::create([
                'product_id' => $locked->id,
                'user_id' => $request->user()->id,
                'type' => $data['type'],
                'qty' => $delta,
                'stock_after' => $newStock,
                'note' => $data['note'] ?? null,
            ]);
        });

        return response()->json($product->fresh()->load('category'));
    }
}
