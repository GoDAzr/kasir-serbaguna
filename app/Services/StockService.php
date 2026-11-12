<?php

namespace App\Services;

use App\Support\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semua perubahan stok lewat sini: stok per gudang (tabel stocks) + total di products.stock
 * + catatan di stock_movements. Panggil di dalam DB::transaction.
 */
class StockService
{
    public static function defaultWarehouse(?int $branchId = null, ?int $businessId = null): int
    {
        $businessId ??= Tenant::id();
        $q = DB::table('warehouses')->where('business_id', $businessId)->where('is_active', 1);
        if ($branchId) {
            $w = (clone $q)->where('branch_id', $branchId)->orderBy('id')->value('id');
            if ($w) {
                return $w;
            }
        }

        return (int) ($q->orderBy('id')->value('id') ?? BusinessSetup::ensureWarehouse($businessId));
    }

    public static function qty(int $productId, int $warehouseId): int
    {
        return (int) DB::table('stocks')->where('product_id', $productId)->where('warehouse_id', $warehouseId)->value('quantity');
    }

    /**
     * @param int $delta  + menambah, - mengurangi
     * @param string $type in|out|adjust|sale|void|return|transfer_in|transfer_out|purchase
     */
    public static function move(int $productId, int $warehouseId, int $delta, string $type, array $meta = []): int
    {
        $wh = DB::table('warehouses')->where('id', $warehouseId)->first();
        $bizId = $wh->business_id;

        $row = DB::table('stocks')->where('product_id', $productId)->where('warehouse_id', $warehouseId)->lockForUpdate()->first();
        if (! $row) {
            DB::table('stocks')->insert([
                'business_id' => $bizId, 'product_id' => $productId, 'warehouse_id' => $warehouseId,
                'branch_id' => $wh->branch_id, 'quantity' => 0, 'unit' => 'pcs',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $row = (object) ['quantity' => 0];
        }

        $new = $row->quantity + $delta;
        if ($new < 0) {
            $name = DB::table('products')->where('id', $productId)->value('name');
            throw ValidationException::withMessages([
                'stok' => ["Stok {$name} di gudang tidak cukup (sisa {$row->quantity})."],
            ]);
        }

        DB::table('stocks')->where('product_id', $productId)->where('warehouse_id', $warehouseId)
            ->update(['quantity' => $new, 'updated_at' => now()]);

        $total = (int) DB::table('stocks')->where('product_id', $productId)->sum('quantity');
        DB::table('products')->where('id', $productId)->update(['stock' => $total]);

        DB::table('stock_movements')->insert([
            'business_id' => $bizId,
            'branch_id' => $wh->branch_id,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'user_id' => $meta['user_id'] ?? Tenant::user()?->id,
            'type' => $type,
            'qty' => $delta,
            'stock_after' => $total,
            'reference' => $meta['reference'] ?? null,
            'note' => $meta['note'] ?? null,
            'movement_date' => $meta['date'] ?? now()->toDateString(),
            'source' => $meta['source'] ?? null,
            'cost' => $meta['cost'] ?? null,
            'proof_path' => $meta['proof'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $new;
    }

    /** Hanya produk bertipe "barang" yang memakai stok (sesuai PDF). */
    public static function usesStock(object $product): bool
    {
        return $product->type === 'barang';
    }
}
