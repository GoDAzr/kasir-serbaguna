<?php

/*
 |--------------------------------------------------------------------------
 | Daftar menu aplikasi (satu sumber untuk menu samping & halaman dashboard)
 |--------------------------------------------------------------------------
 | Menu     : [ 'Nama menu', 'ikon', [ pilihan... ], 'alamat bila tanpa pilihan', 'izin' ]
 | Pilihan  : [ 'Nama pilihan', 'keterangan singkat', 'ikon', 'alamat (null = belum tersedia)' ]
 | izin     : kunci izin di config/permissions.php (null = semua pengguna boleh)
 | color    : hijau | biru | kuning | jingga | hitam | toska | merah | abu | ungu
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
                [
                    'Ringkasan Penjualan Hari Ini',
                    'trend',
                    [],
                    '/dashboard#ringkasan',
                    'dashboard',
                ],
                [
                    'Jumlah Transaksi',
                    'receipt',
                    [],
                    '/dashboard#ringkasan',
                    'dashboard',
                ],
                [
                    'Pendapatan dan Pengeluaran',
                    'wallet',
                    [],
                    '/laporan/rekap_keuangan',
                    'keuangan',
                ],
                [
                    'Grafik Penjualan',
                    'chart',
                    [],
                    '/dashboard#grafik',
                    'dashboard',
                ],
                [
                    'Produk Terlaris',
                    'star',
                    [],
                    '/laporan/produk_terlaris',
                    'laporan',
                ],
                [
                    'Keuntungan',
                    'cash',
                    [],
                    '/laporan/laba_kotor',
                    'laporan',
                ],
                [
                    'Total Produk dan Pelanggan',
                    'users',
                    [],
                    '/dashboard#ringkasan',
                    'dashboard',
                ],
                [
                    'Ringkasan Stok dan Notifikasi',
                    'bell',
                    [],
                    '/notifikasi',
                    'dashboard',
                ],
                [
                    'Aktivitas Terbaru',
                    'clock',
                    [],
                    '/dashboard#aktivitas',
                    'dashboard',
                ],
            ],
        ],
        [
            'title' => 'Operasional',
            'icon' => 'pos',
            'color' => 'biru',
            'items' => [
                [
                    'Kasir / POS',
                    'pos',
                    [
                        ['Transaksi Baru', 'Mulai penjualan baru', 'pos', '/pos'],
                        ['Scan Barcode', 'Tambah barang dengan kamera atau scanner', 'tag', '/pos?scan=1'],
                        ['Keranjang', 'Lihat dan ubah barang yang dipilih', 'box', '/pos#keranjang'],
                        ['Pembayaran', 'Tunai, transfer, QRIS, e-wallet', 'card', '/pos#bayar'],
                        ['Cetak Struk', 'Cetak atau kirim struk pembeli', 'printer', '/transaksi'],
                        ['Shift Kasir', 'Buka dan tutup shift kasir', 'clock', '/pos#shift'],
                    ],
                    null,
                    'pos',
                ],
                [
                    'Transaksi',
                    'receipt',
                    [
                        ['Semua Transaksi', 'Daftar seluruh penjualan', 'list', '/transaksi'],
                        ['Detail Transaksi', 'Rincian barang dan pembayaran', 'doc', '/transaksi'],
                        ['Pembatalan Transaksi', 'Batalkan transaksi dengan alasan', 'close', '/transaksi'],
                        ['Retur', 'Pengembalian barang dari pembeli', 'restore', '/retur/baru'],
                        ['Daftar Retur', 'Retur yang pernah dicatat', 'list', '/retur'],
                        ['Riwayat Pembayaran', 'Catatan pembayaran yang masuk', 'clock', '/transaksi'],
                    ],
                    null,
                    'transaksi',
                ],
                [
                    'Produk',
                    'tag',
                    [
                        ['Semua Produk', 'Daftar produk dan stoknya', 'box', '/m/produk'],
                        ['Tambah Produk', 'Daftarkan produk baru', 'arrowup', '/m/produk/create'],
                        ['Kategori Produk', 'Kelompokkan produk', 'tag', '/m/kategori'],
                        ['Variasi Produk', 'Ukuran, rasa, warna, dan lainnya', 'grid', '/m/variasi'],
                        ['Paket / Bundling', 'Gabungkan beberapa produk jadi satu paket', 'box', '/m/paket'],
                        ['Barcode', 'Kode batang produk', 'scan', '/m/produk'],
                        ['Foto Produk', 'Gambar produk untuk kasir', 'star', '/m/produk'],
                    ],
                    null,
                    'produk',
                ],
                [
                    'Pelanggan',
                    'users',
                    [
                        ['Semua Pelanggan', 'Daftar pelanggan toko', 'users', '/m/pelanggan'],
                        ['Tambah Pelanggan', 'Simpan pelanggan baru', 'user', '/m/pelanggan/create'],
                        ['Pendaftaran Member', 'Daftarkan pelanggan jadi member', 'shield', '/m/member/create'],
                        ['Poin dan Penukaran', 'Poin loyalitas dan riwayat penukaran', 'star', '/m/poin'],
                        ['Riwayat Pembelian', 'Apa saja yang pernah dibeli', 'clock', '/transaksi'],
                    ],
                    null,
                    'pelanggan',
                ],
                [
                    'Supplier',
                    'truck',
                    [
                        ['Daftar Supplier', 'Pemasok barang toko', 'truck', '/m/supplier'],
                        ['Tambah Supplier', 'Simpan supplier baru', 'user', '/m/supplier/create'],
                        ['Tambah Pembelian', 'Catat pembelian barang dari supplier', 'in', '/m/pembelian/create'],
                        ['Riwayat Pembelian', 'Barang yang dibeli dari supplier', 'receipt', '/m/pembelian'],
                    ],
                    null,
                    'supplier',
                ],
            ],
        ],
        [
            'title' => 'Keuangan',
            'icon' => 'wallet',
            'color' => 'kuning',
            'items' => [
                [
                    'Kas & Modal',
                    'cash',
                    [
                        ['Kas Masuk', 'Catat uang masuk ke kas', 'arrowdown', '/m/kas_masuk'],
                        ['Kas Keluar', 'Catat uang keluar dari kas', 'arrowup', '/m/kas_keluar'],
                        ['Tambah Modal', 'Catat modal usaha yang masuk', 'cash', '/m/modal'],
                        ['Saldo Kas', 'Jumlah uang di kas sekarang', 'wallet', '/keuangan/saldo'],
                        ['Riwayat Kas', 'Semua mutasi kas', 'clock', '/m/riwayat_kas'],
                    ],
                    null,
                    'keuangan',
                ],
                [
                    'Hutang & Piutang',
                    'swap',
                    [
                        ['Hutang', 'Hutang toko ke supplier', 'arrowup', '/m/hutang'],
                        ['Tambah Hutang', 'Catat hutang baru', 'arrowup', '/m/hutang/create'],
                        ['Bayar Hutang', 'Catat pembayaran hutang', 'cash', '/m/bayar_hutang/create'],
                        ['Riwayat Pembayaran Hutang', 'Cicilan yang sudah dibayar', 'clock', '/m/bayar_hutang'],
                        ['Piutang', 'Uang yang belum dibayar pelanggan', 'arrowdown', '/m/piutang'],
                        ['Tambah Piutang', 'Catat piutang baru', 'arrowdown', '/m/piutang/create'],
                        ['Terima Pembayaran Piutang', 'Catat pembayaran dari pelanggan', 'cash', '/m/terima_piutang/create'],
                        ['Riwayat Pembayaran Piutang', 'Cicilan yang sudah diterima', 'clock', '/m/terima_piutang'],
                    ],
                    null,
                    'keuangan',
                ],
                [
                    'Pengeluaran',
                    'arrowup',
                    [
                        ['Daftar Pengeluaran', 'Semua biaya yang keluar', 'list', '/m/pengeluaran'],
                        ['Tambah Pengeluaran', 'Catat biaya baru', 'arrowup', '/m/pengeluaran/create'],
                        ['Kategori Pengeluaran', 'Kelompokkan jenis biaya', 'tag', '/m/kat_keuangan'],
                    ],
                    null,
                    'keuangan',
                ],
                [
                    'Pemasukan',
                    'arrowdown',
                    [
                        ['Daftar Pemasukan', 'Semua pemasukan di luar penjualan', 'list', '/m/pemasukan'],
                        ['Tambah Pemasukan', 'Catat pemasukan baru', 'arrowdown', '/m/pemasukan/create'],
                    ],
                    null,
                    'keuangan',
                ],
                [
                    'Laporan Keuangan',
                    'doc',
                    [
                        ['Laba Rugi', 'Untung dan rugi per periode', 'trend', '/laporan/laba_rugi'],
                        ['Arus Kas', 'Uang masuk dan keluar', 'swap', '/laporan/arus_kas'],
                        ['Rekap Keuangan', 'Ringkasan keuangan toko', 'doc', '/laporan/rekap_keuangan'],
                        ['Export Laporan', 'Unduh laporan keuangan', 'download', '/laporan/rekap_keuangan'],
                    ],
                    null,
                    'keuangan',
                ],
            ],
        ],
        [
            'title' => 'Inventori',
            'icon' => 'box',
            'color' => 'jingga',
            'items' => [
                [
                    'Stok Barang',
                    'box',
                    [
                        ['Daftar Stok', 'Jumlah stok semua produk', 'list', '/m/stok'],
                        ['Detail Stok', 'Rincian stok per produk', 'doc', '/m/stok'],
                        ['Peringatan Stok Minimum', 'Produk yang hampir habis', 'bell', '/notifikasi'],
                    ],
                    null,
                    'inventori',
                ],
                [
                    'Stok Masuk',
                    'in',
                    [
                        ['Tambah Stok Masuk', 'Catat barang masuk dari supplier', 'in', '/m/stok_masuk/create'],
                        ['Riwayat Stok Masuk', 'Catatan barang yang masuk', 'clock', '/m/stok_masuk'],
                    ],
                    null,
                    'inventori',
                ],
                [
                    'Stok Keluar',
                    'out',
                    [
                        ['Tambah Stok Keluar', 'Catat barang rusak atau hilang', 'out', '/m/stok_keluar/create'],
                        ['Riwayat Stok Keluar', 'Catatan barang yang keluar', 'clock', '/m/stok_keluar'],
                    ],
                    null,
                    'inventori',
                ],
                [
                    'Stok Opname',
                    'clipboard',
                    [
                        ['Buat Stok Opname', 'Hitung ulang stok fisik', 'clipboard', '/m/opname/create'],
                        ['Riwayat Stok Opname', 'Hasil hitung stok sebelumnya', 'clock', '/m/opname'],
                    ],
                    null,
                    'inventori',
                ],
                [
                    'Gudang',
                    'warehouse',
                    [
                        ['Daftar Gudang', 'Tempat penyimpanan barang', 'warehouse', '/m/gudang'],
                        ['Tambah Gudang', 'Tambah lokasi penyimpanan', 'warehouse', '/m/gudang/create'],
                        ['Transfer Gudang', 'Pindahkan stok antar gudang', 'swap', '/m/transfer/create'],
                        ['Riwayat Transfer', 'Status dan catatan transfer', 'clock', '/m/transfer'],
                    ],
                    null,
                    'inventori',
                ],
                [
                    'Kedaluwarsa',
                    'clock',
                    [
                        ['Daftar Batch', 'Batch produk dan tanggalnya', 'list', null],
                        ['Produk Mendekati Kedaluwarsa', 'Segera jual atau tarik', 'bell', null],
                    ],
                    null,
                    'inventori',
                ],
            ],
        ],
        [
            'title' => 'Pengaturan Toko',
            'icon' => 'store',
            'color' => 'hitam',
            'items' => [
                [
                    'Profil Toko',
                    'store',
                    [
                        ['Nama Toko', 'Nama yang tampil di struk', 'store', '/toko'],
                        ['Logo', 'Gambar logo toko', 'star', '/toko'],
                        ['Alamat', 'Alamat lengkap toko', 'branch', '/toko'],
                        ['Nomor Kontak', 'Telepon atau WhatsApp toko', 'help', '/toko'],
                    ],
                    null,
                    'pengaturan',
                ],
                [
                    'Cabang Toko',
                    'branch',
                    [
                        ['Daftar Cabang', 'Semua cabang toko', 'branch', '/m/cabang'],
                        ['Tambah Cabang', 'Buka cabang baru', 'store', '/m/cabang/create'],
                        ['Pengaturan Cabang', 'Atur tiap cabang', 'gear', '/m/cabang'],
                    ],
                    null,
                    'pengaturan',
                ],
                [
                    'Jam Operasional',
                    'clock',
                    [
                        ['Hari dan Jam Buka', 'Atur jadwal buka toko', 'clock', '/toko/jam'],
                    ],
                    null,
                    'pengaturan',
                ],
                [
                    'Metode Pembayaran',
                    'card',
                    [
                        ['Tunai', 'Pembayaran uang tunai', 'cash', '/m/metode_bayar'],
                        ['Transfer', 'Transfer bank', 'card', '/m/metode_bayar'],
                        ['QRIS', 'Bayar lewat kode QR', 'scan', '/m/metode_bayar'],
                        ['Metode Lain', 'E-wallet dan lainnya', 'wallet', '/m/metode_bayar'],
                    ],
                    null,
                    'pengaturan',
                ],
                [
                    'Pajak',
                    'percent',
                    [
                        ['Pengaturan Pajak dan Biaya Layanan', 'PPN dan biaya layanan di struk', 'percent', '/m/pajak'],
                    ],
                    null,
                    'pengaturan',
                ],
                [
                    'Printer',
                    'printer',
                    [
                        ['Printer Struk', 'Printer untuk struk', 'printer', '/m/printer'],
                        ['Printer Barcode', 'Printer untuk label barcode', 'printer', '/m/printer'],
                        ['Pengaturan Cetak', 'Ukuran kertas dan tampilan', 'gear', '/m/printer'],
                    ],
                    null,
                    'pengaturan',
                ],
                [
                    'QR Order',
                    'scan',
                    [
                        ['QR Meja', 'Kode QR untuk tiap meja', 'scan', '/m/qr_meja'],
                        ['QR Takeaway', 'Kode QR pesan bawa pulang', 'scan', '/m/qr_takeaway'],
                    ],
                    null,
                    'pengaturan',
                ],
            ],
        ],
        [
            'title' => 'Akun & Pengguna',
            'icon' => 'user',
            'color' => 'toska',
            'items' => [
                [
                    'Edit Akun',
                    'user',
                    [
                        ['Nama', 'Ubah nama akun', 'user', '/akun'],
                        ['Username', 'Ubah username', 'user', '/akun'],
                        ['Email', 'Ubah email', 'doc', '/akun'],
                        ['Nomor HP', 'Ubah nomor telepon', 'help', '/akun'],
                        ['Foto Profil', 'Ganti foto akun', 'star', '/akun'],
                    ],
                    null,
                    null,
                ],
                [
                    'Ubah Password',
                    'lock',
                    [
                        ['Password Lama', 'Masukkan password saat ini', 'lock', '/akun/password'],
                        ['Password Baru', 'Buat password baru', 'key', '/akun/password'],
                        ['Konfirmasi Password', 'Ketik ulang password baru', 'shield', '/akun/password'],
                    ],
                    null,
                    null,
                ],
                [
                    'Ubah PIN',
                    'key',
                    [
                        ['PIN Lama', 'Masukkan PIN saat ini', 'lock', '/akun/pin'],
                        ['PIN Baru', 'Buat PIN baru', 'key', '/akun/pin'],
                        ['Konfirmasi PIN', 'Ketik ulang PIN baru', 'shield', '/akun/pin'],
                    ],
                    null,
                    null,
                ],
                [
                    'Tampilan',
                    'star',
                    [
                        ['Mode Gelap / Terang', 'Pilih tema aplikasi', 'star', '/akun/tampilan'],
                        ['Ganti Background', 'Polos, malam bintang, atau foto sendiri', 'star', '/akun/tampilan'],
                    ],
                    null,
                    null,
                ],
                [
                    'Manajemen Pengguna',
                    'users',
                    [
                        ['Daftar Pengguna', 'Semua pengguna aplikasi', 'users', '/m/pengguna'],
                        ['Tambah Pengguna', 'Tambah kasir atau admin', 'user', '/m/pengguna/create'],
                        ['Edit Pengguna', 'Ubah data pengguna', 'gear', '/m/pengguna'],
                        ['Nonaktifkan Pengguna', 'Blokir akses pengguna', 'lock', '/m/pengguna'],
                    ],
                    null,
                    'pengguna',
                ],
                [
                    'Hak Akses',
                    'shield',
                    [
                        ['Owner', 'Akses penuh', 'shield', '/m/role'],
                        ['Admin', 'Kelola toko', 'gear', '/m/role'],
                        ['Kasir', 'Hanya transaksi', 'pos', '/m/role'],
                        ['Supervisor', 'Pantau dan setujui', 'users', '/m/role'],
                        ['Tambah Role', 'Buat role baru dan pilih izinnya', 'key', '/m/role/create'],
                        ['Akses Cabang', 'Batasi cabang yang boleh diakses', 'branch', '/m/pengguna'],
                    ],
                    null,
                    'pengguna',
                ],
                [
                    'Akses Kasir',
                    'pos',
                    [],
                    '/akun/akses-kasir',
                    'pengguna',
                ],
            ],
        ],
        [
            'title' => 'Laporan',
            'icon' => 'chart',
            'color' => 'merah',
            'items' => [
                [
                    'Laporan Penjualan',
                    'trend',
                    [
                        ['Harian', 'Penjualan per hari', 'clock', '/laporan/penjualan?group=harian'],
                        ['Mingguan', 'Penjualan per minggu', 'chart', '/laporan/penjualan?group=mingguan'],
                        ['Bulanan', 'Penjualan per bulan', 'chart', '/laporan/penjualan?group=bulanan'],
                        ['Berdasarkan Periode', 'Pilih tanggal sendiri', 'clock', '/laporan/penjualan'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Laporan Transaksi',
                    'receipt',
                    [
                        ['Daftar Transaksi', 'Semua transaksi', 'list', '/laporan/transaksi'],
                        ['Detail Transaksi', 'Rincian tiap transaksi', 'doc', '/laporan/transaksi'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Laporan Produk',
                    'tag',
                    [
                        ['Produk Terlaris', 'Paling banyak terjual', 'star', '/laporan/produk_terlaris'],
                        ['Produk Kurang Laku', 'Perlu promo', 'arrowdown', '/laporan/produk_kurang'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Laporan Keuntungan',
                    'cash',
                    [
                        ['Laba Kotor', 'Penjualan dikurangi modal', 'trend', '/laporan/laba_kotor'],
                        ['Laba Bersih', 'Setelah semua biaya', 'trend', '/laporan/laba_bersih'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Laporan Stok',
                    'box',
                    [
                        ['Stok Tersedia', 'Stok saat ini', 'box', '/laporan/stok'],
                        ['Stok Masuk', 'Barang yang masuk', 'in', '/laporan/stok_masuk'],
                        ['Stok Keluar', 'Barang yang keluar', 'out', '/laporan/stok_keluar'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Laporan Pelanggan',
                    'users',
                    [
                        ['Pelanggan Terbanyak Belanja', 'Pelanggan paling aktif', 'star', '/laporan/pelanggan'],
                        ['Riwayat Pelanggan', 'Pembelian per pelanggan', 'clock', '/laporan/pelanggan'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Kinerja Kasir',
                    'user',
                    [
                        ['Penjualan per Kasir', 'Total penjualan tiap kasir', 'chart', '/laporan/kasir'],
                        ['Jumlah Transaksi Kasir', 'Berapa transaksi tiap kasir', 'receipt', '/laporan/kasir'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Perbandingan Cabang',
                    'branch',
                    [
                        ['Penjualan per Cabang', 'Bandingkan omzet antar cabang', 'chart', '/laporan/cabang'],
                        ['Keuntungan per Cabang', 'Bandingkan laba antar cabang', 'trend', '/laporan/cabang'],
                    ],
                    null,
                    'laporan',
                ],
                [
                    'Ekspor',
                    'download',
                    [
                        ['PDF', 'Unduh sebagai PDF', 'doc', '/laporan/penjualan?format=pdf'],
                        ['Excel', 'Unduh sebagai Excel', 'download', '/laporan/penjualan?format=xls'],
                    ],
                    null,
                    'laporan',
                ],
            ],
        ],
        [
            'title' => 'Sistem & Bantuan',
            'icon' => 'shield',
            'color' => 'abu',
            'items' => [
                [
                    'Notifikasi',
                    'bell',
                    [
                        ['Stok Minimum', 'Peringatan stok menipis', 'bell', '/notifikasi'],
                        ['Pesanan Baru', 'Pesanan yang masuk', 'receipt', null],
                        ['Jatuh Tempo', 'Hutang dan piutang jatuh tempo', 'clock', '/notifikasi'],
                        ['Kirim Notifikasi', 'Buat notifikasi untuk pengguna', 'bell', '/m/notifikasi/create'],
                    ],
                    null,
                    'sistem',
                ],
                [
                    'Backup Database',
                    'db',
                    [
                        ['Buat Backup', 'Simpan cadangan data (penuh atau sebagian)', 'db', '/m/backup/create'],
                        ['Riwayat Backup', 'Daftar cadangan data', 'clock', '/m/backup'],
                    ],
                    null,
                    'sistem',
                ],
                [
                    'Restore Database',
                    'restore',
                    [
                        ['Pemulihan dari Backup', 'Kembalikan data dari cadangan', 'restore', null],
                    ],
                    null,
                    'sistem',
                ],
                [
                    'Audit Log',
                    'clipboard',
                    [
                        ['Riwayat Aktivitas Pengguna', 'Siapa melakukan apa', 'clipboard', '/m/audit'],
                    ],
                    null,
                    'sistem',
                ],
                [
                    'Riwayat Login',
                    'login',
                    [
                        ['Perangkat', 'Perangkat yang pernah masuk', 'help', '/m/riwayat_login'],
                        ['Waktu Login', 'Kapan akun masuk', 'clock', '/m/riwayat_login'],
                    ],
                    null,
                    'sistem',
                ],
                [
                    'Bantuan',
                    'help',
                    [
                        ['Panduan Penggunaan', 'Cara memakai aplikasi', 'help', null],
                        ['Tentang Aplikasi', 'Versi dan informasi', 'doc', null],
                    ],
                    null,
                    null,
                ],
            ],
        ],
    ],
];
