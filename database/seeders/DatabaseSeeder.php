<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Services\BusinessSetup;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $b = BusinessSetup::create('Toko Serbaguna Contoh');
        $branch = DB::table('branches')->where('business_id', $b)->value('id');

        // Akun contoh (ganti password sebelum dipakai sungguhan!)
        $admin = User::create(['business_id' => $b, 'name' => 'Admin Toko', 'username' => 'admin', 'email' => 'admin@kasir.test',
            'password' => 'password', 'pin' => '123456', 'role' => 'owner']);
        $kasir = User::create(['business_id' => $b, 'name' => 'Kasir 1', 'username' => 'kasir1', 'email' => 'kasir@kasir.test',
            'password' => 'password', 'pin' => '1234', 'role' => 'kasir']);
        DB::table('branch_user')->insert([['user_id' => $admin->id, 'branch_id' => $branch], ['user_id' => $kasir->id, 'branch_id' => $branch]]);

        // Data bawaan ini dibuat atas nama usaha contoh
        auth()->setUser($admin);

        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);
        $lainnya = Category::create(['name' => 'Lainnya']);

        $wh = StockService::defaultWarehouse($branch, $b);
        foreach ([
            [$makanan, 'MKN-001', '8999999000011', 'Mie Instan Goreng', 3500, 2800, 100],
            [$makanan, 'MKN-002', '8999999000028', 'Roti Tawar', 16000, 13000, 30],
            [$makanan, 'MKN-003', '8999999000035', 'Biskuit Cokelat', 9000, 7000, 50],
            [$minuman, 'MNM-001', '8999999000042', 'Air Mineral 600ml', 4000, 2800, 120],
            [$minuman, 'MNM-002', '8999999000059', 'Teh Botol 450ml', 5500, 4200, 80],
            [$minuman, 'MNM-003', '8999999000066', 'Kopi Sachet', 2000, 1500, 200],
            [$lainnya, 'LNY-001', '8999999000073', 'Sabun Mandi', 4500, 3500, 40],
            [$lainnya, 'LNY-002', '8999999000080', 'Tisu Pack', 8000, 6000, 3], // sengaja stok tipis
        ] as [$cat, $sku, $barcode, $name, $price, $cost, $stock]) {
            $p = Product::create([
                'category_id' => $cat->id, 'sku' => $sku, 'barcode' => $barcode, 'name' => $name, 'price' => $price,
                'cost_price' => $cost, 'stock' => 0, 'min_stock' => 5, 'unit' => 'pcs', 'type' => 'barang',
            ]);
            StockService::move($p->id, $wh, $stock, 'in', ['note' => 'Stok awal', 'user_id' => $admin->id]);
        }
        Product::create(['category_id' => $lainnya->id, 'sku' => 'JSA-001', 'name' => 'Jasa Antar', 'price' => 10000, 'type' => 'jasa', 'service_duration' => 30]);

        Customer::create(['name' => 'Pelanggan Umum', 'phone' => null]);

        auth()->forgetUser();
    }
}
