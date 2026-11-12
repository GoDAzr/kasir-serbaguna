<?php

/*
 |--------------------------------------------------------------------------
 | Modul data (CRUD otomatis) - sesuai "Spesifikasi Field Owner SERBAGUNA UMKM"
 |--------------------------------------------------------------------------
 | Field   : ['nama_kolom', 'Label', 'tipe', opsi...]
 | Tipe    : text textarea number money date datetime select ref check file password email tel multi perms
 | Opsi    : 'r'=>1 wajib | 'rc'=>1 wajib saat tambah | 'u'=>1 unik | 'gt'=>0 harus lebih dari
 |           'opts'=>[...] (select) | 'ref'=>'tabel','label_col'=>'kolom','where'=>[..],'where_not'=>[..],'key'=>'slug'
 |           'show'=>['kolom'=>'nilai'] tampil hanya jika | 'req_if'=>['kolom'=>'nilai'] wajib jika | 'hint'=>'teks'
 | Modul   : title, perm, table, cols, fields, search, filters, where, where_not, fixed, children, branch,
 |           create/edit/delete=>false, virtual (field bukan kolom), actions
 | Kolom   : [kolom, 'Judul', format] format: money date datetime status rel(+ 'tabel.kolom') badge
 */

$aktif = [1 => 'Aktif', 0 => 'Nonaktif'];

return [

    // ================= PRODUK =================
    'kategori' => [
        'title' => 'Kategori Produk', 'perm' => 'produk', 'table' => 'categories', 'icon' => 'tag',
        'cols' => [['name', 'Nama'], ['description', 'Deskripsi'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama kategori', 'text', 'r' => 1, 'u' => 1],
            ['description', 'Deskripsi', 'textarea'],
            ['photo_path', 'Foto kategori', 'file'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'produk' => [
        'title' => 'Produk', 'perm' => 'produk', 'table' => 'products', 'icon' => 'box',
        'where_not' => ['type' => 'paket'],
        'search' => ['name', 'sku', 'barcode'],
        'cols' => [['image_path', 'Foto', 'img'], ['name', 'Nama'], ['type', 'Jenis', 'badge'], ['category_id', 'Kategori', 'rel', 'categories.name'],
            ['price', 'Harga jual', 'money'], ['stock', 'Stok'], ['is_active', 'Status', 'status']],
        'filters' => [
            ['type', 'Jenis', 'select', 'opts' => ['barang' => 'Barang', 'sewa' => 'Sewa', 'jasa' => 'Jasa', 'makanan_minuman' => 'Makanan & Minuman', 'digital' => 'Produk Digital']],
            ['category_id', 'Kategori', 'ref', 'ref' => 'categories'],
        ],
        'fields' => [
            ['name', 'Nama produk', 'text', 'r' => 1],
            ['type', 'Jenis produk', 'select', 'r' => 1, 'opts' => ['barang' => 'Barang (pakai stok)', 'sewa' => 'Sewa', 'jasa' => 'Jasa', 'makanan_minuman' => 'Makanan & Minuman', 'digital' => 'Produk Digital']],
            ['category_id', 'Kategori', 'ref', 'r' => 1, 'ref' => 'categories'],
            ['price', 'Harga jual', 'money', 'r' => 1],
            ['cost_price', 'Harga modal', 'money'],
            ['sku', 'SKU', 'text', 'u' => 1],
            ['barcode', 'Barcode', 'text', 'u' => 1],
            ['unit', 'Satuan', 'text', 'show' => ['type' => 'barang'], 'hint' => 'mis. pcs, kg, dus'],
            ['min_stock', 'Stok minimum', 'number', 'show' => ['type' => 'barang']],
            ['rental_period', 'Lama sewa', 'number', 'show' => ['type' => 'sewa'], 'req_if' => ['type' => 'sewa'], 'gt' => 0],
            ['rental_unit', 'Satuan sewa', 'select', 'show' => ['type' => 'sewa'], 'req_if' => ['type' => 'sewa'], 'opts' => ['jam' => 'Jam', 'hari' => 'Hari', 'minggu' => 'Minggu', 'bulan' => 'Bulan']],
            ['service_duration', 'Durasi layanan (menit)', 'number', 'show' => ['type' => 'jasa']],
            ['digital_code', 'Kode produk digital', 'text', 'show' => ['type' => 'digital'], 'req_if' => ['type' => 'digital']],
            ['digital_nominal', 'Nominal produk digital', 'money', 'show' => ['type' => 'digital'], 'req_if' => ['type' => 'digital']],
            ['description', 'Deskripsi', 'textarea'],
            ['image_path', 'Foto produk', 'file'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'variasi' => [
        'title' => 'Variasi Produk', 'perm' => 'produk', 'table' => 'product_variations',
        'search' => ['name', 'value'],
        'cols' => [['product_id', 'Produk', 'rel', 'products.name'], ['name', 'Variasi'], ['value', 'Nilai'], ['extra_price', 'Tambahan harga', 'money'], ['sku', 'SKU'], ['stock', 'Stok']],
        'fields' => [
            ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where_not' => ['type' => 'paket']],
            ['name', 'Nama variasi', 'text', 'r' => 1, 'hint' => 'mis. Ukuran, Rasa, Warna'],
            ['value', 'Nilai variasi', 'text', 'r' => 1, 'hint' => 'mis. Besar, Pedas, Merah'],
            ['extra_price', 'Tambahan harga', 'money'],
            ['sku', 'SKU variasi', 'text'],
            ['barcode', 'Barcode variasi', 'text'],
            ['stock', 'Stok variasi', 'number'],
        ],
    ],

    'paket' => [
        'title' => 'Paket / Bundling', 'perm' => 'produk', 'table' => 'products', 'icon' => 'box',
        'where' => ['type' => 'paket'], 'fixed' => ['type' => 'paket'], 'search' => ['name'],
        'cols' => [['name', 'Nama paket'], ['normal_price', 'Harga normal', 'money'], ['price', 'Harga paket', 'money'], ['bundle_discount', 'Diskon', 'money'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama paket', 'text', 'r' => 1],
            ['category_id', 'Kategori', 'ref', 'r' => 1, 'ref' => 'categories'],
            ['price', 'Harga paket', 'money', 'r' => 1],
            ['bundle_discount', 'Diskon', 'money'],
            ['promo_start', 'Mulai promo', 'date'],
            ['promo_end', 'Akhir promo', 'date'],
            ['description', 'Deskripsi', 'textarea'],
            ['image_path', 'Foto paket', 'file'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
        'children' => [
            'name' => 'items', 'label' => 'Produk dalam paket', 'table' => 'bundle_items', 'fk' => 'bundle_id', 'min' => 1,
            'fields' => [
                ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where_not' => ['type' => 'paket']],
                ['qty', 'Jumlah', 'number', 'r' => 1, 'gt' => 0],
            ],
        ],
    ],

    // ================= PELANGGAN =================
    'pelanggan' => [
        'title' => 'Pelanggan', 'perm' => 'pelanggan', 'table' => 'customers', 'icon' => 'users',
        'search' => ['name', 'phone', 'email'],
        'cols' => [['name', 'Nama'], ['phone', 'No. HP'], ['email', 'Email'], ['address', 'Alamat']],
        'fields' => [
            ['name', 'Nama pelanggan', 'text', 'r' => 1],
            ['phone', 'Nomor HP', 'tel'],
            ['email', 'Email', 'email'],
            ['address', 'Alamat', 'textarea'],
            ['birth_date', 'Tanggal lahir', 'date'],
            ['gender', 'Jenis kelamin', 'select', 'opts' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            ['note', 'Catatan', 'textarea'],
        ],
    ],

    'member' => [
        'title' => 'Member', 'perm' => 'pelanggan', 'table' => 'members', 'search' => ['member_no'],
        'cols' => [['member_no', 'No. member'], ['customer_id', 'Pelanggan', 'rel', 'customers.name'], ['joined_at', 'Bergabung', 'date'], ['status', 'Status', 'badge'], ['points', 'Poin']],
        'fields' => [
            ['customer_id', 'Pelanggan', 'ref', 'r' => 1, 'u' => 1, 'ref' => 'customers'],
            ['status', 'Status member', 'select', 'r' => 1, 'opts' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']],
        ],
        'actions' => [['Riwayat poin', '/m/poin?member={id}']],
    ],

    'poin' => [
        'title' => 'Riwayat Poin', 'perm' => 'pelanggan', 'table' => 'point_logs', 'create' => false, 'edit' => false, 'delete' => false,
        'cols' => [['created_at', 'Tanggal', 'datetime'], ['member_id', 'Member', 'rel', 'members.member_no'], ['type', 'Jenis', 'badge'], ['points', 'Poin'], ['reference', 'Referensi'], ['note', 'Catatan']],
        'filters' => [['member_id', 'Member', 'ref', 'ref' => 'members', 'label_col' => 'member_no']],
        'fields' => [],
    ],

    // ================= SUPPLIER =================
    'supplier' => [
        'title' => 'Supplier', 'perm' => 'supplier', 'table' => 'suppliers', 'icon' => 'truck',
        'search' => ['name', 'phone', 'email'],
        'cols' => [['name', 'Nama'], ['contact_name', 'Kontak'], ['phone', 'No. HP'], ['email', 'Email'], ['address', 'Alamat']],
        'fields' => [
            ['name', 'Nama supplier', 'text', 'r' => 1],
            ['phone', 'Nomor HP', 'tel'],
            ['email', 'Email', 'email'],
            ['address', 'Alamat', 'textarea'],
            ['contact_name', 'Nama kontak', 'text'],
            ['note', 'Catatan', 'textarea'],
        ],
    ],

    'pembelian' => [
        'title' => 'Pembelian', 'perm' => 'supplier', 'table' => 'purchases', 'edit' => false, 'delete' => false, 'branch' => 'branch_id',
        'search' => ['purchase_no'],
        'cols' => [['purchase_no', 'No.'], ['purchase_date', 'Tanggal', 'date'], ['supplier_id', 'Supplier', 'rel', 'suppliers.name'], ['branch_id', 'Cabang', 'rel', 'branches.name'], ['total', 'Total', 'money'], ['payment_status', 'Pembayaran', 'badge']],
        'filters' => [
            ['supplier_id', 'Supplier', 'ref', 'ref' => 'suppliers'],
            ['payment_status', 'Pembayaran', 'select', 'opts' => ['lunas' => 'Lunas', 'belum_lunas' => 'Belum lunas']],
        ],
        'virtual' => ['cash_account_id'],
        'fields' => [
            ['supplier_id', 'Supplier', 'ref', 'r' => 1, 'ref' => 'suppliers'],
            ['branch_id', 'Cabang', 'ref', 'r' => 1, 'ref' => 'branches'],
            ['warehouse_id', 'Gudang tujuan', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['purchase_date', 'Tanggal pembelian', 'date', 'r' => 1],
            ['discount', 'Diskon (Rp)', 'money'],
            ['tax', 'Pajak (Rp)', 'money'],
            ['payment_status', 'Status pembayaran', 'select', 'r' => 1, 'opts' => ['lunas' => 'Lunas', 'belum_lunas' => 'Belum lunas (jadi hutang)']],
            ['cash_account_id', 'Dibayar dari akun kas', 'ref', 'ref' => 'cash_accounts', 'show' => ['payment_status' => 'lunas'], 'req_if' => ['payment_status' => 'lunas']],
            ['proof_path', 'Bukti pembelian', 'file'],
            ['note', 'Catatan', 'textarea'],
        ],
        'children' => [
            'name' => 'items', 'label' => 'Produk yang dibeli', 'table' => 'purchase_items', 'fk' => 'purchase_id', 'min' => 1,
            'fields' => [
                ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where' => ['type' => 'barang']],
                ['qty', 'Jumlah', 'number', 'r' => 1, 'gt' => 0],
                ['cost', 'Harga beli', 'money', 'r' => 1],
            ],
        ],
    ],

    // ================= KEUANGAN =================
    'akun_kas' => [
        'title' => 'Akun Kas', 'perm' => 'keuangan', 'table' => 'cash_accounts',
        'cols' => [['name', 'Nama akun'], ['type', 'Jenis', 'badge'], ['opening_balance', 'Saldo awal', 'money'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama akun kas', 'text', 'r' => 1],
            ['type', 'Jenis', 'select', 'r' => 1, 'opts' => ['kas' => 'Kas tunai', 'bank' => 'Bank', 'ewallet' => 'E-Wallet']],
            ['opening_balance', 'Saldo awal', 'money'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'kat_keuangan' => [
        'title' => 'Kategori Keuangan', 'perm' => 'keuangan', 'table' => 'finance_categories',
        'cols' => [['name', 'Nama kategori'], ['direction', 'Untuk', 'badge']],
        'fields' => [
            ['name', 'Nama kategori', 'text', 'r' => 1],
            ['direction', 'Jenis', 'select', 'r' => 1, 'opts' => ['out' => 'Pengeluaran', 'in' => 'Pemasukan']],
        ],
    ],

    'kas_masuk' => [
        'title' => 'Kas Masuk', 'perm' => 'keuangan', 'table' => 'cash_entries', 'where' => ['type' => 'cash_in'], 'fixed' => ['type' => 'cash_in'],
        'search' => ['description'],
        'cols' => [['entry_date', 'Tanggal', 'date'], ['cash_account_id', 'Akun kas', 'rel', 'cash_accounts.name'], ['category_id', 'Kategori', 'rel', 'finance_categories.name'], ['amount', 'Nominal', 'money'], ['description', 'Keterangan']],
        'filters' => [['entry_date', 'Mulai tanggal', 'date', 'op' => '>='], ['cash_account_id', 'Akun kas', 'ref', 'ref' => 'cash_accounts']],
        'fields' => [
            ['entry_date', 'Tanggal', 'date', 'r' => 1],
            ['cash_account_id', 'Akun kas', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
            ['category_id', 'Kategori', 'ref', 'r' => 1, 'ref' => 'finance_categories', 'where' => ['direction' => 'in']],
            ['amount', 'Nominal', 'money', 'r' => 1, 'gt' => 0],
            ['description', 'Keterangan', 'textarea'],
            ['proof_path', 'Bukti', 'file'],
        ],
    ],

    'kas_keluar' => [
        'title' => 'Kas Keluar', 'perm' => 'keuangan', 'table' => 'cash_entries', 'where' => ['type' => 'cash_out'], 'fixed' => ['type' => 'cash_out'],
        'search' => ['description'],
        'cols' => [['entry_date', 'Tanggal', 'date'], ['cash_account_id', 'Akun kas', 'rel', 'cash_accounts.name'], ['category_id', 'Kategori', 'rel', 'finance_categories.name'], ['amount', 'Nominal', 'money'], ['description', 'Keterangan']],
        'filters' => [['entry_date', 'Mulai tanggal', 'date', 'op' => '>='], ['cash_account_id', 'Akun kas', 'ref', 'ref' => 'cash_accounts']],
        'fields' => [
            ['entry_date', 'Tanggal', 'date', 'r' => 1],
            ['cash_account_id', 'Akun kas', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
            ['category_id', 'Kategori', 'ref', 'r' => 1, 'ref' => 'finance_categories', 'where' => ['direction' => 'out']],
            ['amount', 'Nominal', 'money', 'r' => 1, 'gt' => 0],
            ['description', 'Keterangan', 'textarea'],
            ['proof_path', 'Bukti', 'file'],
        ],
    ],

    'modal' => [
        'title' => 'Tambah Modal', 'perm' => 'keuangan', 'table' => 'cash_entries', 'where' => ['type' => 'capital'], 'fixed' => ['type' => 'capital'],
        'cols' => [['entry_date', 'Tanggal', 'date'], ['source', 'Sumber modal'], ['cash_account_id', 'Akun kas tujuan', 'rel', 'cash_accounts.name'], ['amount', 'Nominal', 'money']],
        'fields' => [
            ['entry_date', 'Tanggal', 'date', 'r' => 1],
            ['amount', 'Nominal modal', 'money', 'r' => 1, 'gt' => 0],
            ['source', 'Sumber modal', 'select', 'r' => 1, 'opts' => ['Pribadi' => 'Pribadi', 'Pinjaman' => 'Pinjaman', 'Investor' => 'Investor', 'Lainnya' => 'Lainnya']],
            ['cash_account_id', 'Akun kas tujuan', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
        ],
    ],

    'pengeluaran' => [
        'title' => 'Pengeluaran', 'perm' => 'keuangan', 'table' => 'cash_entries', 'where' => ['type' => 'expense'], 'fixed' => ['type' => 'expense'],
        'search' => ['name'],
        'cols' => [['entry_date', 'Tanggal', 'date'], ['name', 'Nama'], ['category_id', 'Kategori', 'rel', 'finance_categories.name'], ['cash_account_id', 'Akun kas', 'rel', 'cash_accounts.name'], ['amount', 'Nominal', 'money']],
        'filters' => [['entry_date', 'Mulai tanggal', 'date', 'op' => '>='], ['category_id', 'Kategori', 'ref', 'ref' => 'finance_categories', 'where' => ['direction' => 'out']]],
        'fields' => [
            ['name', 'Nama pengeluaran', 'text', 'r' => 1],
            ['category_id', 'Kategori', 'ref', 'r' => 1, 'ref' => 'finance_categories', 'where' => ['direction' => 'out']],
            ['entry_date', 'Tanggal', 'date', 'r' => 1],
            ['amount', 'Nominal', 'money', 'r' => 1, 'gt' => 0],
            ['cash_account_id', 'Akun kas', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
            ['description', 'Keterangan', 'textarea'],
            ['proof_path', 'Foto struk / bukti', 'file'],
        ],
    ],

    'pemasukan' => [
        'title' => 'Pemasukan', 'perm' => 'keuangan', 'table' => 'cash_entries', 'where' => ['type' => 'income'], 'fixed' => ['type' => 'income'],
        'search' => ['name'],
        'cols' => [['entry_date', 'Tanggal', 'date'], ['name', 'Nama'], ['source', 'Sumber'], ['category_id', 'Kategori', 'rel', 'finance_categories.name'], ['amount', 'Nominal', 'money']],
        'filters' => [['entry_date', 'Mulai tanggal', 'date', 'op' => '>=']],
        'fields' => [
            ['name', 'Nama pemasukan', 'text', 'r' => 1],
            ['category_id', 'Kategori', 'ref', 'r' => 1, 'ref' => 'finance_categories', 'where' => ['direction' => 'in']],
            ['entry_date', 'Tanggal', 'date', 'r' => 1],
            ['amount', 'Nominal', 'money', 'r' => 1, 'gt' => 0],
            ['cash_account_id', 'Akun kas', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
            ['source', 'Sumber pemasukan', 'text'],
            ['description', 'Keterangan', 'textarea'],
            ['proof_path', 'Foto bukti', 'file'],
        ],
    ],

    'riwayat_kas' => [
        'title' => 'Riwayat Kas', 'perm' => 'keuangan', 'table' => 'cash_entries', 'create' => false, 'edit' => false, 'delete' => false,
        'search' => ['name', 'description'],
        'cols' => [['entry_date', 'Tanggal', 'date'], ['type', 'Jenis', 'badge'], ['cash_account_id', 'Akun kas', 'rel', 'cash_accounts.name'], ['name', 'Nama'], ['amount', 'Nominal', 'money'], ['description', 'Keterangan']],
        'filters' => [
            ['type', 'Jenis', 'select', 'opts' => ['cash_in' => 'Kas masuk', 'cash_out' => 'Kas keluar', 'capital' => 'Modal', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran', 'sale' => 'Penjualan', 'refund' => 'Refund', 'purchase' => 'Pembelian', 'debt_payment' => 'Bayar hutang', 'receivable_payment' => 'Terima piutang']],
            ['cash_account_id', 'Akun kas', 'ref', 'ref' => 'cash_accounts'],
            ['entry_date', 'Mulai tanggal', 'date', 'op' => '>='],
        ],
        'fields' => [],
    ],

    'hutang' => [
        'title' => 'Hutang', 'perm' => 'keuangan', 'table' => 'debts', 'where' => ['kind' => 'hutang'], 'fixed' => ['kind' => 'hutang'], 'edit' => false,
        'search' => ['party_name'],
        'cols' => [['party_name', 'Pemberi hutang'], ['debt_date', 'Tanggal', 'date'], ['due_date', 'Jatuh tempo', 'date'], ['amount', 'Nominal', 'money'], ['paid_amount', 'Dibayar', 'money'], ['status', 'Status', 'badge']],
        'filters' => [['status', 'Status', 'select', 'opts' => ['belum_lunas' => 'Belum lunas', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas']]],
        'fields' => [
            ['party_name', 'Nama pemberi hutang', 'text', 'r' => 1],
            ['amount', 'Nominal hutang', 'money', 'r' => 1, 'gt' => 0],
            ['debt_date', 'Tanggal hutang', 'date', 'r' => 1],
            ['due_date', 'Jatuh tempo', 'date'],
            ['description', 'Keterangan', 'textarea'],
            ['proof_path', 'Bukti', 'file'],
        ],
    ],

    'bayar_hutang' => [
        'title' => 'Pembayaran Hutang', 'perm' => 'keuangan', 'table' => 'debt_payments', 'where' => ['kind' => 'hutang'], 'fixed' => ['kind' => 'hutang'], 'edit' => false, 'delete' => false,
        'cols' => [['pay_date', 'Tanggal', 'date'], ['debt_id', 'Hutang', 'rel', 'debts.party_name'], ['amount', 'Dibayar', 'money'], ['method', 'Metode', 'badge'], ['remaining', 'Sisa', 'money']],
        'fields' => [
            ['debt_id', 'Data hutang', 'ref', 'r' => 1, 'ref' => 'debts', 'label_col' => 'party_name', 'where' => ['kind' => 'hutang'], 'where_not' => ['status' => 'lunas'], 'all' => 1],
            ['pay_date', 'Tanggal bayar', 'date', 'r' => 1],
            ['amount', 'Nominal bayar', 'money', 'r' => 1, 'gt' => 0],
            ['cash_account_id', 'Akun kas', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
            ['method', 'Metode pembayaran', 'select', 'r' => 1, 'opts' => ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet', 'lainnya' => 'Lainnya']],
            ['proof_path', 'Bukti', 'file'],
        ],
    ],

    'piutang' => [
        'title' => 'Piutang', 'perm' => 'keuangan', 'table' => 'debts', 'where' => ['kind' => 'piutang'], 'fixed' => ['kind' => 'piutang'], 'edit' => false,
        'search' => ['party_name'],
        'cols' => [['party_name', 'Pelanggan'], ['debt_date', 'Tanggal', 'date'], ['due_date', 'Jatuh tempo', 'date'], ['amount', 'Nominal', 'money'], ['paid_amount', 'Diterima', 'money'], ['status', 'Status', 'badge']],
        'filters' => [['status', 'Status', 'select', 'opts' => ['belum_lunas' => 'Belum lunas', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas']]],
        'fields' => [
            ['customer_id', 'Pelanggan', 'ref', 'r' => 1, 'ref' => 'customers'],
            ['amount', 'Nominal piutang', 'money', 'r' => 1, 'gt' => 0],
            ['debt_date', 'Tanggal piutang', 'date', 'r' => 1],
            ['due_date', 'Jatuh tempo', 'date'],
            ['description', 'Keterangan', 'textarea'],
            ['proof_path', 'Bukti', 'file'],
        ],
    ],

    'terima_piutang' => [
        'title' => 'Pembayaran Piutang', 'perm' => 'keuangan', 'table' => 'debt_payments', 'where' => ['kind' => 'piutang'], 'fixed' => ['kind' => 'piutang'], 'edit' => false, 'delete' => false,
        'cols' => [['pay_date', 'Tanggal', 'date'], ['debt_id', 'Piutang', 'rel', 'debts.party_name'], ['amount', 'Diterima', 'money'], ['method', 'Metode', 'badge'], ['remaining', 'Sisa', 'money']],
        'fields' => [
            ['debt_id', 'Data piutang', 'ref', 'r' => 1, 'ref' => 'debts', 'label_col' => 'party_name', 'where' => ['kind' => 'piutang'], 'where_not' => ['status' => 'lunas'], 'all' => 1],
            ['pay_date', 'Tanggal terima', 'date', 'r' => 1],
            ['amount', 'Nominal diterima', 'money', 'r' => 1, 'gt' => 0],
            ['cash_account_id', 'Akun kas', 'ref', 'r' => 1, 'ref' => 'cash_accounts'],
            ['method', 'Metode pembayaran', 'select', 'r' => 1, 'opts' => ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet', 'lainnya' => 'Lainnya']],
            ['proof_path', 'Bukti', 'file'],
        ],
    ],

    // ================= INVENTORI =================
    'gudang' => [
        'title' => 'Gudang', 'perm' => 'inventori', 'table' => 'warehouses', 'branch' => 'branch_id', 'search' => ['name'],
        'cols' => [['name', 'Nama gudang'], ['branch_id', 'Cabang', 'rel', 'branches.name'], ['pic_name', 'Penanggung jawab'], ['address', 'Alamat'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama gudang', 'text', 'r' => 1],
            ['branch_id', 'Cabang', 'ref', 'r' => 1, 'ref' => 'branches'],
            ['address', 'Alamat', 'textarea'],
            ['pic_name', 'Penanggung jawab', 'text'],
            ['note', 'Keterangan', 'textarea'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'stok' => [
        'title' => 'Stok Barang', 'perm' => 'inventori', 'table' => 'stocks', 'branch' => 'branch_id',
        'cols' => [['product_id', 'Produk', 'rel', 'products.name'], ['warehouse_id', 'Gudang', 'rel', 'warehouses.name'], ['branch_id', 'Cabang', 'rel', 'branches.name'], ['quantity', 'Jumlah', 'stock'], ['min_stock', 'Minimum'], ['unit', 'Satuan']],
        'filters' => [['warehouse_id', 'Gudang', 'ref', 'ref' => 'warehouses'], ['product_id', 'Produk', 'ref', 'ref' => 'products', 'where' => ['type' => 'barang']]],
        'virtual' => [],
        'fields' => [
            ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where' => ['type' => 'barang']],
            ['branch_id', 'Cabang', 'ref', 'r' => 1, 'ref' => 'branches'],
            ['warehouse_id', 'Gudang', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['min_stock', 'Stok minimum', 'number'],
            ['unit', 'Satuan', 'text', 'r' => 1, 'hint' => 'mis. pcs, kg, dus'],
        ],
        'note' => 'Jumlah stok dihitung otomatis dari stok masuk, keluar, penjualan, opname, dan transfer.',
    ],

    'stok_masuk' => [
        'title' => 'Stok Masuk', 'perm' => 'inventori', 'table' => 'stock_movements', 'where' => ['type' => ['in', 'purchase', 'transfer_in', 'return']],
        'edit' => false, 'delete' => false, 'branch' => 'branch_id',
        'cols' => [['movement_date', 'Tanggal', 'date'], ['product_id', 'Produk', 'rel', 'products.name'], ['warehouse_id', 'Gudang', 'rel', 'warehouses.name'], ['qty', 'Jumlah'], ['type', 'Jenis', 'badge'], ['source', 'Sumber'], ['cost', 'Harga modal', 'money']],
        'filters' => [['warehouse_id', 'Gudang', 'ref', 'ref' => 'warehouses'], ['movement_date', 'Mulai tanggal', 'date', 'op' => '>=']],
                'fields' => [
            ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where' => ['type' => 'barang']],
            ['warehouse_id', 'Gudang tujuan', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['qty', 'Jumlah masuk', 'number', 'r' => 1, 'gt' => 0],
            ['movement_date', 'Tanggal', 'date', 'r' => 1],
            ['source', 'Sumber barang', 'text', 'hint' => 'mis. nama supplier'],
            ['cost', 'Harga modal', 'money'],
            ['proof_path', 'Bukti', 'file'],
            ['note', 'Catatan', 'textarea'],
        ],
    ],

    'stok_keluar' => [
        'title' => 'Stok Keluar', 'perm' => 'inventori', 'table' => 'stock_movements', 'where' => ['type' => ['out', 'transfer_out']],
        'edit' => false, 'delete' => false, 'branch' => 'branch_id',
        'cols' => [['movement_date', 'Tanggal', 'date'], ['product_id', 'Produk', 'rel', 'products.name'], ['warehouse_id', 'Gudang', 'rel', 'warehouses.name'], ['qty', 'Jumlah'], ['source', 'Alasan'], ['note', 'Catatan']],
        'filters' => [['warehouse_id', 'Gudang', 'ref', 'ref' => 'warehouses'], ['movement_date', 'Mulai tanggal', 'date', 'op' => '>=']],
        'fields' => [
            ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where' => ['type' => 'barang']],
            ['warehouse_id', 'Gudang asal', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['qty', 'Jumlah keluar', 'number', 'r' => 1, 'gt' => 0],
            ['movement_date', 'Tanggal', 'date', 'r' => 1],
            ['source', 'Alasan pengeluaran', 'select', 'r' => 1, 'opts' => ['Rusak' => 'Rusak', 'Hilang' => 'Hilang', 'Kedaluwarsa' => 'Kedaluwarsa', 'Pemakaian sendiri' => 'Pemakaian sendiri', 'Lainnya' => 'Lainnya']],
            ['proof_path', 'Bukti', 'file'],
            ['note', 'Catatan', 'textarea'],
        ],
    ],

    'opname' => [
        'title' => 'Stok Opname', 'perm' => 'inventori', 'table' => 'stock_opnames', 'edit' => false, 'delete' => false,
        'cols' => [['opname_date', 'Tanggal', 'date'], ['warehouse_id', 'Gudang', 'rel', 'warehouses.name'], ['user_id', 'Petugas', 'rel', 'users.name'], ['note', 'Alasan / catatan']],
        'fields' => [
            ['warehouse_id', 'Gudang', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['opname_date', 'Tanggal opname', 'date', 'r' => 1],
            ['note', 'Alasan selisih / catatan', 'textarea'],
        ],
        'children' => [
            'name' => 'items', 'label' => 'Produk yang dihitung', 'table' => 'stock_opname_items', 'fk' => 'stock_opname_id', 'min' => 1,
            'fields' => [
                ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where' => ['type' => 'barang']],
                ['physical_qty', 'Stok fisik', 'number', 'r' => 1, 'min' => 0],
                ['reason', 'Alasan selisih', 'text'],
            ],
        ],
    ],

    'transfer' => [
        'title' => 'Transfer Gudang', 'perm' => 'inventori', 'table' => 'stock_transfers', 'edit' => false, 'delete' => false,
        'cols' => [['transfer_date', 'Tanggal', 'date'], ['from_warehouse_id', 'Dari', 'rel', 'warehouses.name'], ['to_warehouse_id', 'Ke', 'rel', 'warehouses.name'], ['status', 'Status', 'badge'], ['note', 'Catatan']],
        'fields' => [
            ['from_warehouse_id', 'Gudang asal', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['to_warehouse_id', 'Gudang tujuan', 'ref', 'r' => 1, 'ref' => 'warehouses'],
            ['transfer_date', 'Tanggal transfer', 'date', 'r' => 1],
            ['note', 'Catatan', 'textarea'],
        ],
        'children' => [
            'name' => 'items', 'label' => 'Produk yang dipindah', 'table' => 'stock_transfer_items', 'fk' => 'stock_transfer_id', 'min' => 1,
            'fields' => [
                ['product_id', 'Produk', 'ref', 'r' => 1, 'ref' => 'products', 'where' => ['type' => 'barang']],
                ['qty', 'Jumlah', 'number', 'r' => 1, 'gt' => 0],
            ],
        ],
    ],

    // ================= PENGATURAN TOKO =================
    'cabang' => [
        'title' => 'Cabang', 'perm' => 'pengaturan', 'table' => 'branches', 'search' => ['name', 'code'],
        'cols' => [['code', 'Kode'], ['name', 'Nama cabang'], ['address', 'Alamat'], ['phone', 'No. HP'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama cabang', 'text', 'r' => 1],
            ['code', 'Kode cabang', 'text', 'r' => 1, 'u' => 1, 'hint' => 'unik, mis. PST / BDG'],
            ['address', 'Alamat', 'textarea', 'r' => 1],
            ['phone', 'Nomor telepon', 'tel'],
            ['email', 'Email', 'email'],
            ['pic_name', 'Penanggung jawab', 'text'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'metode_bayar' => [
        'title' => 'Metode Pembayaran', 'perm' => 'pengaturan', 'table' => 'payment_methods',
        'cols' => [['name', 'Nama'], ['type', 'Jenis', 'badge'], ['bank_name', 'Bank'], ['account_no', 'No. rekening'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama metode', 'text', 'r' => 1],
            ['type', 'Jenis', 'select', 'r' => 1, 'opts' => ['tunai' => 'Tunai', 'transfer' => 'Transfer bank', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet', 'lainnya' => 'Lainnya']],
            ['bank_name', 'Nama bank', 'text', 'show' => ['type' => 'transfer']],
            ['account_no', 'Nomor rekening', 'text', 'show' => ['type' => ['transfer', 'ewallet']]],
            ['account_holder', 'Atas nama', 'text', 'show' => ['type' => ['transfer', 'ewallet']]],
            ['qris_no', 'Nomor / ID QRIS', 'text', 'show' => ['type' => 'qris']],
            ['qris_image_path', 'Gambar QRIS', 'file', 'show' => ['type' => 'qris']],
            ['cash_account_id', 'Akun kas penerima', 'ref', 'ref' => 'cash_accounts'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'pajak' => [
        'title' => 'Pajak & Biaya Layanan', 'perm' => 'pengaturan', 'table' => 'taxes',
        'cols' => [['name', 'Nama'], ['percentage', 'Persentase', 'pct'], ['kind', 'Jenis', 'badge'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama pajak', 'text', 'r' => 1, 'hint' => 'mis. PPN 11%'],
            ['percentage', 'Persentase (%)', 'number', 'r' => 1, 'min' => 0, 'x' => ['max:100']],
            ['kind', 'Jenis', 'select', 'r' => 1, 'opts' => ['pajak' => 'Pajak', 'biaya_layanan' => 'Biaya layanan']],
            ['product_ids', 'Berlaku untuk produk', 'multi', 'ref' => 'products', 'hint' => 'Kosongkan = berlaku untuk semua produk'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'printer' => [
        'title' => 'Printer', 'perm' => 'pengaturan', 'table' => 'printers',
        'cols' => [['name', 'Nama'], ['type', 'Jenis', 'badge'], ['connection', 'Koneksi', 'badge'], ['paper_size', 'Kertas'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama printer', 'text', 'r' => 1],
            ['type', 'Jenis printer', 'select', 'r' => 1, 'opts' => ['struk' => 'Printer struk', 'barcode' => 'Printer barcode']],
            ['connection', 'Koneksi', 'select', 'r' => 1, 'opts' => ['usb' => 'USB', 'bluetooth' => 'Bluetooth', 'jaringan' => 'Jaringan (LAN/WiFi)']],
            ['paper_size', 'Ukuran kertas', 'select', 'r' => 1, 'opts' => ['58mm' => '58 mm', '80mm' => '80 mm', 'A4' => 'A4', 'label' => 'Label']],
            ['auto_print', 'Cetak otomatis setelah transaksi', 'check'],
            ['header', 'Header struk', 'textarea'],
            ['footer', 'Footer struk', 'textarea'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'qr_meja' => [
        'title' => 'QR Meja', 'perm' => 'pengaturan', 'table' => 'qr_tables', 'branch' => 'branch_id',
        'cols' => [['branch_id', 'Cabang', 'rel', 'branches.name'], ['table_no', 'Nomor meja'], ['capacity', 'Kapasitas'], ['token', 'Token'], ['is_active', 'Status', 'status']],
        'actions' => [['Lihat QR', '/qr/{token}']],
        'fields' => [
            ['branch_id', 'Cabang', 'ref', 'r' => 1, 'ref' => 'branches'],
            ['table_no', 'Nomor meja', 'text', 'r' => 1],
            ['capacity', 'Kapasitas', 'number'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'qr_takeaway' => [
        'title' => 'QR Takeaway', 'perm' => 'pengaturan', 'table' => 'qr_takeaways', 'branch' => 'branch_id',
        'cols' => [['branch_id', 'Cabang', 'rel', 'branches.name'], ['name', 'Nama'], ['token', 'Token'], ['is_active', 'Status', 'status']],
        'actions' => [['Lihat QR', '/qr/{token}']],
        'fields' => [
            ['branch_id', 'Cabang', 'ref', 'r' => 1, 'ref' => 'branches'],
            ['name', 'Nama QR', 'text', 'r' => 1],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    // ================= AKUN & PENGGUNA =================
    'pengguna' => [
        'title' => 'Pengguna', 'perm' => 'pengguna', 'table' => 'users', 'delete' => false, 'search' => ['name', 'username', 'email'],
        'cols' => [['name', 'Nama'], ['username', 'Username'], ['email', 'Email'], ['role', 'Role', 'badge'], ['is_active', 'Status', 'status']],
        'virtual' => ['branches'],
        'fields' => [
            ['name', 'Nama lengkap', 'text', 'r' => 1],
            ['username', 'Username', 'text', 'r' => 1, 'u' => 1, 'global' => 1],
            ['email', 'Email', 'email', 'r' => 1, 'u' => 1, 'global' => 1],
            ['phone', 'Nomor HP', 'tel'],
            ['photo_path', 'Foto profil', 'file'],
            ['password', 'Password', 'password', 'rc' => 1, 'hint' => 'Minimal 8 karakter. Saat ubah data, kosongkan jika tidak diganti.'],
            ['pin', 'PIN', 'password', 'minlen' => 4, 'x' => ['digits_between:4,6'], 'hint' => '4-6 angka. Kosongkan jika tidak diganti.'],
            ['role', 'Role', 'ref', 'r' => 1, 'ref' => 'roles', 'key' => 'slug'],
            ['branches', 'Cabang yang boleh diakses', 'multi', 'r' => 1, 'ref' => 'branches'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    'role' => [
        'title' => 'Hak Akses (Role)', 'perm' => 'pengguna', 'table' => 'roles', 'search' => ['name'],
        'cols' => [['name', 'Nama role'], ['description', 'Deskripsi'], ['all_branches', 'Semua cabang', 'yesno'], ['is_active', 'Status', 'status']],
        'fields' => [
            ['name', 'Nama role', 'text', 'r' => 1],
            ['description', 'Deskripsi', 'textarea'],
            ['permissions', 'Daftar izin', 'perms', 'r' => 1],
            ['all_branches', 'Boleh akses semua cabang', 'check'],
            ['is_active', 'Status', 'select', 'r' => 1, 'opts' => $aktif],
        ],
    ],

    // ================= SISTEM =================
    'notifikasi' => [
        'title' => 'Notifikasi', 'perm' => 'sistem', 'table' => 'app_notifications', 'edit' => false, 'search' => ['title'],
        'cols' => [['created_at', 'Dibuat', 'datetime'], ['title', 'Judul'], ['recipient', 'Penerima', 'badge'], ['type', 'Jenis', 'badge'], ['scheduled_at', 'Jadwal', 'datetime']],
        'fields' => [
            ['title', 'Judul', 'text', 'r' => 1],
            ['body', 'Isi notifikasi', 'textarea', 'r' => 1],
            ['recipient', 'Penerima', 'select', 'r' => 1, 'opts' => ['semua' => 'Semua pengguna', 'peran' => 'Role tertentu', 'pengguna' => 'Pengguna tertentu']],
            ['recipient_value', 'Role (kasir/admin/...) atau email pengguna', 'text', 'show' => ['recipient' => ['peran', 'pengguna']], 'req_if' => ['recipient' => ['peran', 'pengguna']]],
            ['type', 'Jenis', 'select', 'r' => 1, 'opts' => ['info' => 'Info', 'peringatan' => 'Peringatan', 'stok' => 'Stok', 'jatuh_tempo' => 'Jatuh tempo']],
            ['scheduled_at', 'Jadwal kirim', 'datetime', 'hint' => 'Kosongkan = langsung tampil'],
        ],
    ],

    'backup' => [
        'title' => 'Backup Data', 'perm' => 'sistem', 'table' => 'backups', 'edit' => false,
        'cols' => [['name', 'Nama file'], ['type', 'Jenis', 'badge'], ['size', 'Ukuran', 'bytes'], ['created_at', 'Waktu', 'datetime'], ['note', 'Catatan']],
        'actions' => [['Unduh', '/sistem/backup/{id}/unduh']],
        'fields' => [
            ['type', 'Jenis backup', 'select', 'r' => 1, 'opts' => ['data' => 'Data transaksi saja', 'lengkap' => 'Lengkap (semua data usaha)']],
            ['note', 'Catatan', 'textarea'],
        ],
    ],

    'audit' => [
        'title' => 'Audit Log', 'perm' => 'sistem', 'table' => 'audit_logs', 'create' => false, 'edit' => false, 'delete' => false, 'search' => ['menu', 'changes'],
        'cols' => [['created_at', 'Waktu', 'datetime'], ['user_id', 'Pengguna', 'rel', 'users.name'], ['action', 'Aksi', 'badge'], ['menu', 'Menu'], ['changes', 'Perubahan', 'long'], ['ip', 'IP']],
        'filters' => [['action', 'Aksi', 'select', 'opts' => ['tambah' => 'Tambah', 'ubah' => 'Ubah', 'hapus' => 'Hapus', 'batal' => 'Batal', 'retur' => 'Retur', 'bayar' => 'Bayar']]],
        'fields' => [],
    ],

    'riwayat_login' => [
        'title' => 'Riwayat Login', 'perm' => 'sistem', 'table' => 'login_histories', 'create' => false, 'edit' => false, 'delete' => false, 'search' => ['email'],
        'cols' => [['created_at', 'Waktu', 'datetime'], ['email', 'Email'], ['status', 'Status', 'badge'], ['device', 'Perangkat'], ['ip', 'IP']],
        'fields' => [],
    ],
];
