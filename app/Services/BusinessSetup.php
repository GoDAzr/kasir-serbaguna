<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Menyiapkan data awal satu usaha (cabang, gudang, role, kas, metode bayar, kategori keuangan). */
class BusinessSetup
{
    public static function create(string $name, string $type = 'Toko Kelontong'): int
    {
        $id = DB::table('businesses')->insertGetId([
            'name' => $name, 'business_type' => $type, 'created_at' => now(), 'updated_at' => now(),
        ]);
        self::defaults($id);

        return $id;
    }

    public static function defaults(int $b): void
    {
        $now = now();

        if (! DB::table('branches')->where('business_id', $b)->exists()) {
            DB::table('branches')->insert([
                'business_id' => $b, 'name' => 'Cabang Utama', 'code' => 'PST', 'is_active' => 1,
                'created_at' => $now, 'updated_at' => now(),
            ]);
        }
        self::ensureWarehouse($b);

        foreach (config('permissions.default_roles') as $slug => [$nm, $desc, $perms, $allBr]) {
            DB::table('roles')->updateOrInsert(['business_id' => $b, 'slug' => $slug], [
                'name' => $nm, 'description' => $desc, 'permissions' => json_encode($perms),
                'all_branches' => $allBr, 'is_active' => 1, 'is_system' => 1,
                'cashier_ui' => in_array($slug, config('permissions.cashier_ui_roles', []), true),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        if (! DB::table('cash_accounts')->where('business_id', $b)->exists()) {
            foreach ([['Kas Toko', 'kas'], ['Rekening Bank', 'bank'], ['E-Wallet', 'ewallet']] as [$n, $t]) {
                DB::table('cash_accounts')->insert(['business_id' => $b, 'name' => $n, 'type' => $t,
                    'created_at' => $now, 'updated_at' => $now]);
            }
        }
        $kas = DB::table('cash_accounts')->where('business_id', $b)->orderBy('id')->pluck('id', 'type');

        if (! DB::table('payment_methods')->where('business_id', $b)->exists()) {
            foreach ([
                ['Tunai', 'cash', 'tunai', 'kas'], ['Transfer Bank', 'transfer', 'transfer', 'bank'],
                ['QRIS', 'qris', 'qris', 'bank'], ['E-Wallet', 'ewallet', 'ewallet', 'ewallet'],
            ] as [$n, $c, $t, $k]) {
                DB::table('payment_methods')->insert(['business_id' => $b, 'name' => $n, 'code' => $c, 'type' => $t,
                    'cash_account_id' => $kas[$k] ?? null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (! DB::table('finance_categories')->where('business_id', $b)->exists()) {
            foreach ([['Gaji', 'out'], ['Listrik & Air', 'out'], ['Sewa Tempat', 'out'], ['Transportasi', 'out'],
                ['Operasional Lain', 'out'], ['Penjualan Lain', 'in'], ['Bunga / Bonus', 'in'], ['Lainnya', 'in']] as [$n, $d]) {
                DB::table('finance_categories')->insert(['business_id' => $b, 'name' => $n, 'direction' => $d,
                    'created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (! DB::table('operating_hours')->where('business_id', $b)->exists()) {
            for ($d = 1; $d <= 7; $d++) {
                DB::table('operating_hours')->insert(['business_id' => $b, 'day' => $d, 'open_time' => '08:00',
                    'close_time' => '21:00', 'is_closed' => $d === 7, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public static function ensureWarehouse(int $b): int
    {
        $w = DB::table('warehouses')->where('business_id', $b)->orderBy('id')->value('id');
        if ($w) {
            return $w;
        }
        $br = DB::table('branches')->where('business_id', $b)->orderBy('id')->value('id');

        return DB::table('warehouses')->insertGetId(['business_id' => $b, 'branch_id' => $br, 'name' => 'Gudang Utama',
            'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Data lama (sebelum multi usaha): bungkus ke satu usaha, isi stok gudang dari products.stock.
     * Aman dijalankan berulang.
     */
    public static function backfillLegacy(): void
    {
        $orphanUsers = DB::table('users')->whereNull('business_id')->get();
        if ($orphanUsers->isEmpty()) {
            return;
        }
        $owner = $orphanUsers->firstWhere('role', 'admin') ?? $orphanUsers->first();
        $b = self::create($owner->name ? ($owner->name . ' - Toko') : 'Toko Saya');

        DB::table('users')->whereNull('business_id')->update(['business_id' => $b]);
        foreach (['categories', 'products', 'customers', 'transactions', 'stock_movements'] as $t) {
            DB::table($t)->whereNull('business_id')->update(['business_id' => $b]);
        }
        $br = DB::table('branches')->where('business_id', $b)->value('id');
        DB::table('transactions')->where('business_id', $b)->whereNull('branch_id')->update(['branch_id' => $br]);

        foreach (DB::table('users')->where('business_id', $b)->pluck('id') as $uid) {
            DB::table('branch_user')->updateOrInsert(['user_id' => $uid, 'branch_id' => $br]);
        }

        $wh = self::ensureWarehouse($b);
        foreach (DB::table('products')->where('business_id', $b)->get() as $p) {
            DB::table('stocks')->updateOrInsert(['product_id' => $p->id, 'warehouse_id' => $wh], [
                'business_id' => $b, 'branch_id' => $br, 'quantity' => $p->stock, 'min_stock' => $p->min_stock,
                'unit' => $p->unit ?: 'pcs', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public static function token(): string
    {
        return Str::random(32);
    }
}
