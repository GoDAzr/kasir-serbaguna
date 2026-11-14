<?php

/*
 | Grup izin untuk Hak Akses (role). Owner & Admin selalu boleh semuanya.
 | Kunci grup dipakai di route (middleware perm:xxx) dan di menu.
 */
return [
    'groups' => [
        'dashboard' => 'Dashboard',
        'pos' => 'Kasir / POS',
        'transaksi' => 'Transaksi, Pembatalan & Retur',
        'produk' => 'Produk, Kategori, Variasi & Paket',
        'pelanggan' => 'Pelanggan & Member',
        'supplier' => 'Supplier & Pembelian',
        'keuangan' => 'Keuangan',
        'inventori' => 'Inventori & Gudang',
        'pengaturan' => 'Pengaturan Toko',
        'pengguna' => 'Pengguna & Hak Akses',
        'laporan' => 'Laporan',
        'sistem' => 'Notifikasi, Backup & Audit',

        // Izin rinci untuk kasir (dipakai juga di halaman Akses Kasir)
        'pos_diskon' => 'Kasir: boleh memberi diskon',
        'pos_piutang' => 'Kasir: boleh jual belum lunas (piutang)',
        'transaksi_batal' => 'Boleh membatalkan transaksi',
        'retur' => 'Boleh retur penjualan',
        'lihat_omzet' => 'Boleh melihat total penjualan di beranda kasir',
    ],

    // Role yang memakai tampilan kasir (bisa diubah owner di Hak Akses > Pakai tampilan kasir)
    'cashier_ui_roles' => ['kasir'],

    // Role bawaan yang dibuat otomatis untuk setiap usaha baru
    'default_roles' => [
        'owner' => ['Owner', 'Akses penuh ke seluruh usaha', ['*'], true],
        'admin' => ['Admin', 'Mengelola toko', ['*'], true],
        'kasir' => ['Kasir', 'Hanya transaksi kasir', ['dashboard', 'pos', 'transaksi', 'pelanggan'], false],
        'supervisor' => ['Supervisor', 'Memantau dan menyetujui', ['dashboard', 'pos', 'transaksi', 'produk', 'pelanggan', 'inventori', 'laporan', 'pos_diskon', 'pos_piutang', 'transaksi_batal', 'retur', 'lihat_omzet'], true],
    ],
];
