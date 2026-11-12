<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockService;
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

        DB::transaction(function () use ($data, $product) {
            $wh = StockService::defaultWarehouse();
            $cur = StockService::qty($product->id, $wh);

            $delta = match ($data['type']) {
                'in' => $data['qty'],
                'out' => -$data['qty'],
                'adjust' => $data['qty'] - $cur,
            };

            StockService::move($product->id, $wh, $delta, $data['type'], ['note' => $data['note'] ?? null]);
        });

        return response()->json($product->fresh()->load('category'));
    }
}
