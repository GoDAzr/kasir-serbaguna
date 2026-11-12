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
    ],

    // Role bawaan yang dibuat otomatis untuk setiap usaha baru
    'default_roles' => [
        'owner' => ['Owner', 'Akses penuh ke seluruh usaha', ['*'], true],
        'admin' => ['Admin', 'Mengelola toko', ['*'], true],
        'kasir' => ['Kasir', 'Hanya transaksi kasir', ['dashboard', 'pos', 'transaksi', 'pelanggan'], false],
        'supervisor' => ['Supervisor', 'Memantau dan menyetujui', ['dashboard', 'pos', 'transaksi', 'produk', 'pelanggan', 'inventori', 'laporan'], true],
    ],
];
