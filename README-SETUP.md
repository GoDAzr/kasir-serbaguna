# Kasir Serbaguna - Backend Laravel (Tahap 1: API untuk Android)

Folder ini adalah **overlay**: berisi file-file kasir yang ditimpa ke instalasi Laravel baru.

## Langkah setup (di komputer kamu)

Kebutuhan: PHP 8.2+, Composer, database (SQLite bawaan Laravel sudah cukup, atau MySQL).

```bash
# 1. Buat proyek Laravel baru
composer create-project laravel/laravel kasir-serbaguna
cd kasir-serbaguna

# 2. Pasang API + Sanctum (membuat routes/api.php dan migration token)
php artisan install:api

# 3. Salin isi folder overlay ini ke dalam proyek, timpa file yang sama
#    (app/, database/, routes/api.php)

# 4. Migrasi + data contoh
php artisan migrate:fresh --seed

# 5. Jalankan server (0.0.0.0 supaya bisa diakses HP/emulator)
php artisan serve --host=0.0.0.0 --port=8000
```

## Akun contoh (dari seeder)

| Role  | Email            | Password |
|-------|------------------|----------|
| Admin | admin@kasir.test | password |
| Kasir | kasir@kasir.test | password |

## Alamat API dari Android

| Perangkat            | Base URL                          |
|----------------------|-----------------------------------|
| Emulator Android     | http://10.0.2.2:8000/api/         |
| HP asli (satu WiFi)  | http://IP-LAPTOP-KAMU:8000/api/   |

Semua request wajib memakai header `Accept: application/json`.
Endpoint selain `/login` dan `/ping` butuh header `Authorization: Bearer <token>`.

Catatan: Android 9+ memblokir HTTP biasa. Saat development tambahkan
`android:usesCleartextTraffic="true"` di AndroidManifest, atau pakai HTTPS di produksi.

## Isi overlay

- `database/migrations/` : users(role), categories, products, customers, transactions, transaction_items, stock_movements
- `app/Models/` : model + relasi
- `app/Http/Controllers/Api/` : Auth, Category, Product, Stock, Customer, Transaction, Report
- `app/Http/Middleware/EnsureRole.php` : pembatas akses admin/kasir
- `routes/api.php` : daftar endpoint
- `database/seeders/DatabaseSeeder.php` : akun & produk contoh
- `docs/API.md` : dokumentasi endpoint

## Roadmap tahap berikutnya

1. Dashboard web admin (Blade/Livewire atau Filament)
2. Cetak struk (PDF / thermal printer Bluetooth)
3. Manajemen user/kasir oleh admin
4. Upload gambar produk
5. Laporan PDF/Excel
