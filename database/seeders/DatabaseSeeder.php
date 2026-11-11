<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun contoh (ganti password sebelum dipakai sungguhan!)
        User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@kasir.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@kasir.test',
            'password' => 'password',
            'role' => 'kasir',
        ]);

        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);
        $lainnya = Category::create(['name' => 'Lainnya']);

        $products = [
            [$makanan, 'MKN-001', '8999999000011', 'Mie Instan Goreng', 3500, 2800, 100],
            [$makanan, 'MKN-002', '8999999000028', 'Roti Tawar', 16000, 13000, 30],
            [$makanan, 'MKN-003', '8999999000035', 'Biskuit Cokelat', 9000, 7000, 50],
            [$minuman, 'MNM-001', '8999999000042', 'Air Mineral 600ml', 4000, 2800, 120],
            [$minuman, 'MNM-002', '8999999000059', 'Teh Botol 450ml', 5500, 4200, 80],
            [$minuman, 'MNM-003', '8999999000066', 'Kopi Sachet', 2000, 1500, 200],
            [$lainnya, 'LNY-001', '8999999000073', 'Sabun Mandi', 4500, 3500, 40],
            [$lainnya, 'LNY-002', '8999999000080', 'Tisu Pack', 8000, 6000, 3], // sengaja stok tipis
        ];

        foreach ($products as [$category, $sku, $barcode, $name, $price, $cost, $stock]) {
            Product::create([
                'category_id' => $category->id,
                'sku' => $sku,
                'barcode' => $barcode,
                'name' => $name,
                'price' => $price,
                'cost_price' => $cost,
                'stock' => $stock,
                'min_stock' => 5,
                'unit' => 'pcs',
            ]);
        }

        Customer::create(['name' => 'Pelanggan Umum', 'phone' => null]);
    }
}
