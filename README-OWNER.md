# Menu Owner (sesuai PDF Spesifikasi Field Owner) - cara pasang

1. Timpa/tambahkan semua file dari chat ini ke project (path ada di keterangan tiap file).
2. Di terminal project:
   ```
   php artisan storage:link
   php artisan migrate
   php artisan optimize:clear
   ```
   `migrate` otomatis membungkus data lama ke satu usaha + Gudang Utama (stok lama dipindah ke gudang).
   Ingin mulai bersih dengan data contoh: `php artisan migrate:fresh --seed`
   (login: admin@kasir.test / password, kasir@kasir.test / password).
3. Daftar akun baru lewat halaman Sign Up otomatis jadi Owner dengan cabang, gudang, role, kas, metode bayar bawaan.

## Struktur
- `config/modules.php`  : semua menu data (produk, keuangan, inventori, pengaturan, pengguna, sistem). Tambah field = tambah 1 baris.
- `config/menu.php`     : isi menu samping/dashboard + alamat halaman + izin tiap menu.
- `config/permissions.php` : daftar izin untuk Hak Akses (role).
- `app/Support/ModuleHooks.php` : aturan khusus (stok, kas, hutang, member, backup).
- `app/Services/StockService.php` : semua perubahan stok (per gudang) lewat sini.
- Pembatas data: `business_id` (usaha), cabang, role, izin.

## Catatan
- Kedaluwarsa, Restore Database, Pesanan Baru, dan Bantuan tidak ada di PDF -> tetap "dibuat tahap berikutnya".
- Layanan Usaha (nomor 8 PDF) dihapus sesuai permintaan.
- Export: Excel (.xls) dan PDF lewat halaman cetak (Ctrl+P -> Save as PDF).
- Shift kasir: Pagi / Siang / Malam (pilihan tetap).
- Transaksi selesai tidak bisa diedit: pakai Batalkan (alasan wajib) atau Retur.
