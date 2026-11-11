<?php

/*
 |--------------------------------------------------------------------------
 | Daftar menu aplikasi (satu sumber untuk menu samping & halaman dashboard)
 |--------------------------------------------------------------------------
 | Susunan: kategori -> menu -> pilihan di dalam menu.
 | Menu     : [ 'Nama menu', 'ikon', [ pilihan... ] ]
 | Pilihan  : [ 'Nama pilihan', 'keterangan singkat', 'ikon' ]
 | Menu tanpa pilihan cukup diberi array kosong [].
 |
 | color : hijau | biru | kuning | jingga | hitam | toska | merah | abu | ungu
 | ikon  : nama ikon di dashboard.blade.php (id="i-NAMA")
 |
 | Setelah mengubah file ini jalankan:  php artisan optimize:clear
 */

return [
    'groups' => [
        [
            'title' => 'Manajemen',
            'icon' => 'grid',
            'color' => 'hijau',
            'items' => [
                ['Ringkasan Penjualan Hari Ini', 'trend', []],
                ['Jumlah Transaksi', 'receipt', []],
                ['Pendapatan dan Pengeluaran', 'wallet', []],
                ['Grafik Penjualan', 'chart', []],
                ['Produk Terlaris', 'star', []],
                ['Keuntungan', 'cash', []],
                ['Total Produk dan Pelanggan', 'users', []],
                ['Ringkasan Stok dan Notifikasi', 'bell', []],
                ['Aktivitas Terbaru', 'clock', []],
            ],
        ],

        [
            'title' => 'Operasional',
            'icon' => 'pos',
            'color' => 'biru',
            'items' => [
                ['Kasir / POS', 'pos', [
                    ['Transaksi Baru', 'Mulai penjualan baru', 'pos'],
                    ['Scan Barcode', 'Tambah barang dengan kamera atau scanner', 'tag'],
                    ['Keranjang', 'Lihat dan ubah barang yang dipilih', 'box'],
                    ['Pembayaran', 'Tunai, transfer, QRIS, e-wallet', 'card'],
                    ['Cetak Struk', 'Cetak atau kirim struk pembeli', 'printer'],
                    ['Shift Kasir', 'Buka dan tutup shift kasir', 'clock'],
                ]],
                ['Transaksi', 'receipt', [
                    ['Semua Transaksi', 'Daftar seluruh penjualan', 'list'],
                    ['Detail Transaksi', 'Rincian barang dan pembayaran', 'doc'],
                    ['Pembatalan Transaksi', 'Batalkan transaksi dengan alasan', 'close'],
                    ['Retur', 'Pengembalian barang dari pembeli', 'restore'],
                    ['Riwayat Pembayaran', 'Catatan pembayaran yang masuk', 'clock'],
                ]],
                ['Produk', 'tag', [
                    ['Semua Produk', 'Daftar produk dan stoknya', 'box'],
                    ['Tambah Produk', 'Daftarkan produk baru', 'arrowup'],
                    ['Kategori Produk', 'Kelompokkan produk', 'tag'],
                    ['Variasi Produk', 'Ukuran, rasa, warna, dan lainnya', 'grid'],
                    ['Paket / Bundling', 'Gabungkan beberapa produk jadi satu paket', 'box'],
                    ['Barcode', 'Kode batang produk', 'scan'],
                    ['Foto Produk', 'Gambar produk untuk kasir', 'star'],
                ]],
                ['Pelanggan', 'users', [
                    ['Semua Pelanggan', 'Daftar pelanggan toko', 'users'],
                    ['Tambah Pelanggan', 'Simpan pelanggan baru', 'user'],
                    ['Pendaftaran Member', 'Daftarkan pelanggan jadi member', 'shield'],
                    ['Poin dan Penukaran', 'Poin loyalitas dan riwayat penukaran', 'star'],
                    ['Riwayat Pembelian', 'Apa saja yang pernah dibeli', 'clock'],
                ]],
                ['Supplier', 'truck', [
                    ['Daftar Supplier', 'Pemasok barang toko', 'truck'],
                    ['Tambah Supplier', 'Simpan supplier baru', 'user'],
                    ['Tambah Pembelian', 'Catat pembelian barang dari supplier', 'in'],
                    ['Riwayat Pembelian', 'Barang yang dibeli dari supplier', 'receipt'],
                ]],
            ],
        ],

        [
            'title' => 'Keuangan',
            'icon' => 'wallet',
            'color' => 'kuning',
            'items' => [
                ['Kas & Modal', 'cash', [
                    ['Kas Masuk', 'Catat uang masuk ke kas', 'arrowdown'],
                    ['Kas Keluar', 'Catat uang keluar dari kas', 'arrowup'],
                    ['Tambah Modal', 'Catat modal usaha yang masuk', 'cash'],
                    ['Saldo Kas', 'Jumlah uang di kas sekarang', 'wallet'],
                    ['Riwayat Kas', 'Semua mutasi kas', 'clock'],
                ]],
                ['Hutang & Piutang', 'swap', [
                    ['Hutang', 'Hutang toko ke supplier', 'arrowup'],
                    ['Tambah Hutang', 'Catat hutang baru', 'arrowup'],
                    ['Bayar Hutang', 'Catat pembayaran hutang', 'cash'],
                    ['Riwayat Pembayaran Hutang', 'Cicilan yang sudah dibayar', 'clock'],
                    ['Piutang', 'Uang yang belum dibayar pelanggan', 'arrowdown'],
                    ['Tambah Piutang', 'Catat piutang baru', 'arrowdown'],
                    ['Terima Pembayaran Piutang', 'Catat pembayaran dari pelanggan', 'cash'],
                    ['Riwayat Pembayaran Piutang', 'Cicilan yang sudah diterima', 'clock'],
                ]],
                ['Pengeluaran', 'arrowup', [
                    ['Daftar Pengeluaran', 'Semua biaya yang keluar', 'list'],
                    ['Tambah Pengeluaran', 'Catat biaya baru', 'arrowup'],
                    ['Kategori Pengeluaran', 'Kelompokkan jenis biaya', 'tag'],
                ]],
                ['Pemasukan', 'arrowdown', [
                    ['Daftar Pemasukan', 'Semua pemasukan di luar penjualan', 'list'],
                    ['Tambah Pemasukan', 'Catat pemasukan baru', 'arrowdown'],
                ]],
                ['Laporan Keuangan', 'doc', [
                    ['Laba Rugi', 'Untung dan rugi per periode', 'trend'],
                    ['Arus Kas', 'Uang masuk dan keluar', 'swap'],
                    ['Rekap Keuangan', 'Ringkasan keuangan toko', 'doc'],
                    ['Export Laporan', 'Unduh laporan keuangan', 'download'],
                ]],
            ],
        ],

        [
            'title' => 'Inventori',
            'icon' => 'box',
            'color' => 'jingga',
            'items' => [
                ['Stok Barang', 'box', [
                    ['Daftar Stok', 'Jumlah stok semua produk', 'list'],
                    ['Detail Stok', 'Rincian stok per produk', 'doc'],
                    ['Peringatan Stok Minimum', 'Produk yang hampir habis', 'bell'],
                ]],
                ['Stok Masuk', 'in', [
                    ['Tambah Stok Masuk', 'Catat barang masuk dari supplier', 'in'],
                    ['Riwayat Stok Masuk', 'Catatan barang yang masuk', 'clock'],
                ]],
                ['Stok Keluar', 'out', [
                    ['Tambah Stok Keluar', 'Catat barang rusak atau hilang', 'out'],
                    ['Riwayat Stok Keluar', 'Catatan barang yang keluar', 'clock'],
                ]],
                ['Stok Opname', 'clipboard', [
                    ['Buat Stok Opname', 'Hitung ulang stok fisik', 'clipboard'],
                    ['Riwayat Stok Opname', 'Hasil hitung stok sebelumnya', 'clock'],
                ]],
                ['Gudang', 'warehouse', [
                    ['Daftar Gudang', 'Tempat penyimpanan barang', 'warehouse'],
                    ['Tambah Gudang', 'Tambah lokasi penyimpanan', 'warehouse'],
                    ['Transfer Gudang', 'Pindahkan stok antar gudang', 'swap'],
                    ['Riwayat Transfer', 'Status dan catatan transfer', 'clock'],
                ]],
                ['Kedaluwarsa', 'clock', [
                    ['Daftar Batch', 'Batch produk dan tanggalnya', 'list'],
                    ['Produk Mendekati Kedaluwarsa', 'Segera jual atau tarik', 'bell'],
                ]],
            ],
        ],

        [
            'title' => 'Pengaturan Toko',
            'icon' => 'store',
            'color' => 'hitam',
            'items' => [
                ['Profil Toko', 'store', [
                    ['Nama Toko', 'Nama yang tampil di struk', 'store'],
                    ['Logo', 'Gambar logo toko', 'star'],
                    ['Alamat', 'Alamat lengkap toko', 'branch'],
                    ['Nomor Kontak', 'Telepon atau WhatsApp toko', 'help'],
                ]],
                ['Cabang Toko', 'branch', [
                    ['Daftar Cabang', 'Semua cabang toko', 'branch'],
                    ['Tambah Cabang', 'Buka cabang baru', 'store'],
                    ['Pengaturan Cabang', 'Atur tiap cabang', 'gear'],
                ]],
                ['Jam Operasional', 'clock', [
                    ['Hari dan Jam Buka', 'Atur jadwal buka toko', 'clock'],
                ]],
                ['Metode Pembayaran', 'card', [
                    ['Tunai', 'Pembayaran uang tunai', 'cash'],
                    ['Transfer', 'Transfer bank', 'card'],
                    ['QRIS', 'Bayar lewat kode QR', 'scan'],
                    ['Metode Lain', 'E-wallet dan lainnya', 'wallet'],
                ]],
                ['Pajak', 'percent', [
                    ['Pengaturan Pajak dan Biaya Layanan', 'PPN dan biaya layanan di struk', 'percent'],
                ]],
                ['Printer', 'printer', [
                    ['Printer Struk', 'Printer untuk struk', 'printer'],
                    ['Printer Barcode', 'Printer untuk label barcode', 'printer'],
                    ['Pengaturan Cetak', 'Ukuran kertas dan tampilan', 'gear'],
                ]],
                ['QR Order', 'scan', [
                    ['QR Meja', 'Kode QR untuk tiap meja', 'scan'],
                    ['QR Takeaway', 'Kode QR pesan bawa pulang', 'scan'],
                ]],
            ],
        ],

        [
            'title' => 'Akun & Pengguna',
            'icon' => 'user',
            'color' => 'toska',
            'items' => [
                ['Edit Akun', 'user', [
                    ['Nama', 'Ubah nama akun', 'user'],
                    ['Username', 'Ubah username', 'user'],
                    ['Email', 'Ubah email', 'doc'],
                    ['Nomor HP', 'Ubah nomor telepon', 'help'],
                    ['Foto Profil', 'Ganti foto akun', 'star'],
                ]],
                ['Ubah Password', 'lock', [
                    ['Password Lama', 'Masukkan password saat ini', 'lock'],
                    ['Password Baru', 'Buat password baru', 'key'],
                    ['Konfirmasi Password', 'Ketik ulang password baru', 'shield'],
                ]],
                ['Ubah PIN', 'key', [
                    ['PIN Lama', 'Masukkan PIN saat ini', 'lock'],
                    ['PIN Baru', 'Buat PIN baru', 'key'],
                    ['Konfirmasi PIN', 'Ketik ulang PIN baru', 'shield'],
                ]],
                ['Manajemen Pengguna', 'users', [
                    ['Daftar Pengguna', 'Semua pengguna aplikasi', 'users'],
                    ['Tambah Pengguna', 'Tambah kasir atau admin', 'user'],
                    ['Edit Pengguna', 'Ubah data pengguna', 'gear'],
                    ['Nonaktifkan Pengguna', 'Blokir akses pengguna', 'lock'],
                ]],
                ['Hak Akses', 'shield', [
                    ['Owner', 'Akses penuh', 'shield'],
                    ['Admin', 'Kelola toko', 'gear'],
                    ['Kasir', 'Hanya transaksi', 'pos'],
                    ['Supervisor', 'Pantau dan setujui', 'users'],
                    ['Tambah Role', 'Buat role baru dan pilih izinnya', 'key'],
                    ['Akses Cabang', 'Batasi cabang yang boleh diakses', 'branch'],
                ]],
            ],
        ],

        [
            'title' => 'Laporan',
            'icon' => 'chart',
            'color' => 'merah',
            'items' => [
                ['Laporan Penjualan', 'trend', [
                    ['Harian', 'Penjualan per hari', 'clock'],
                    ['Mingguan', 'Penjualan per minggu', 'chart'],
                    ['Bulanan', 'Penjualan per bulan', 'chart'],
                    ['Berdasarkan Periode', 'Pilih tanggal sendiri', 'clock'],
                ]],
                ['Laporan Transaksi', 'receipt', [
                    ['Daftar Transaksi', 'Semua transaksi', 'list'],
                    ['Detail Transaksi', 'Rincian tiap transaksi', 'doc'],
                ]],
                ['Laporan Produk', 'tag', [
                    ['Produk Terlaris', 'Paling banyak terjual', 'star'],
                    ['Produk Kurang Laku', 'Perlu promo', 'arrowdown'],
                ]],
                ['Laporan Keuntungan', 'cash', [
                    ['Laba Kotor', 'Penjualan dikurangi modal', 'trend'],
                    ['Laba Bersih', 'Setelah semua biaya', 'trend'],
                ]],
                ['Laporan Stok', 'box', [
                    ['Stok Tersedia', 'Stok saat ini', 'box'],
                    ['Stok Masuk', 'Barang yang masuk', 'in'],
                    ['Stok Keluar', 'Barang yang keluar', 'out'],
                ]],
                ['Laporan Pelanggan', 'users', [
                    ['Pelanggan Terbanyak Belanja', 'Pelanggan paling aktif', 'star'],
                    ['Riwayat Pelanggan', 'Pembelian per pelanggan', 'clock'],
                ]],
                ['Kinerja Kasir', 'user', [
                    ['Penjualan per Kasir', 'Total penjualan tiap kasir', 'chart'],
                    ['Jumlah Transaksi Kasir', 'Berapa transaksi tiap kasir', 'receipt'],
                ]],
                ['Perbandingan Cabang', 'branch', [
                    ['Penjualan per Cabang', 'Bandingkan omzet antar cabang', 'chart'],
                    ['Keuntungan per Cabang', 'Bandingkan laba antar cabang', 'trend'],
                ]],
                ['Ekspor', 'download', [
                    ['PDF', 'Unduh sebagai PDF', 'doc'],
                    ['Excel', 'Unduh sebagai Excel', 'download'],
                ]],
            ],
        ],

        [
            'title' => 'Sistem & Bantuan',
            'icon' => 'shield',
            'color' => 'abu',
            'items' => [
                ['Notifikasi', 'bell', [
                    ['Stok Minimum', 'Peringatan stok menipis', 'bell'],
                    ['Pesanan Baru', 'Pesanan yang masuk', 'receipt'],
                    ['Jatuh Tempo', 'Hutang dan piutang jatuh tempo', 'clock'],
                    ['Kirim Notifikasi', 'Buat notifikasi untuk pengguna', 'bell'],
                ]],
                ['Backup Database', 'db', [
                    ['Buat Backup', 'Simpan cadangan data (penuh atau sebagian)', 'db'],
                    ['Riwayat Backup', 'Daftar cadangan data', 'clock'],
                ]],
                ['Restore Database', 'restore', [
                    ['Pemulihan dari Backup', 'Kembalikan data dari cadangan', 'restore'],
                ]],
                ['Audit Log', 'clipboard', [
                    ['Riwayat Aktivitas Pengguna', 'Siapa melakukan apa', 'clipboard'],
                ]],
                ['Riwayat Login', 'login', [
                    ['Perangkat', 'Perangkat yang pernah masuk', 'help'],
                    ['Waktu Login', 'Kapan akun masuk', 'clock'],
                ]],
                ['Bantuan', 'help', [
                    ['Panduan Penggunaan', 'Cara memakai aplikasi', 'help'],
                    ['Tentang Aplikasi', 'Versi dan informasi', 'doc'],
                ]],
            ],
        ],

    ],
];