<?php

namespace App\Http\Controllers;

use App\Support\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** key => [judul, perm, filter yang dipakai] */
    public const REPORTS = [
        'penjualan' => ['Laporan Penjualan', 'laporan', ['period', 'group', 'branch_id', 'user_id', 'payment_method']],
        'transaksi' => ['Laporan Transaksi', 'laporan', ['period', 'branch_id', 'user_id', 'status', 'payment_method']],
        'produk_terlaris' => ['Produk Terlaris', 'laporan', ['period', 'branch_id', 'category_id']],
        'produk_kurang' => ['Produk Kurang Laku', 'laporan', ['period', 'branch_id', 'category_id']],
        'laba_kotor' => ['Laba Kotor', 'laporan', ['period', 'branch_id']],
        'laba_bersih' => ['Laba Bersih', 'laporan', ['period', 'branch_id']],
        'stok' => ['Stok Tersedia', 'laporan', ['warehouse_id', 'category_id', 'low']],
        'stok_masuk' => ['Stok Masuk', 'laporan', ['period', 'warehouse_id']],
        'stok_keluar' => ['Stok Keluar', 'laporan', ['period', 'warehouse_id']],
        'pelanggan' => ['Pelanggan Terbanyak Belanja', 'laporan', ['period', 'branch_id', 'customer_id']],
        'kasir' => ['Kinerja Kasir', 'laporan', ['period', 'branch_id', 'user_id']],
        'cabang' => ['Perbandingan Cabang', 'laporan', ['period']],
        'laba_rugi' => ['Laba Rugi', 'keuangan', ['period', 'branch_id']],
        'arus_kas' => ['Arus Kas', 'keuangan', ['period', 'cash_account_id']],
        'rekap_keuangan' => ['Rekap Keuangan', 'keuangan', ['period']],
    ];

    private function paid()
    {
        $q = DB::table('transactions')->where('transactions.business_id', Tenant::id())->where('transactions.status', 'paid');
        if (($ids = Tenant::branchIds()) !== null) {
            $q->whereIn('transactions.branch_id', $ids ?: [0]);
        }

        return $q;
    }

    private function range(Request $r): array
    {
        $from = $r->query('from') ?: now()->startOfMonth()->toDateString();
        $to = $r->query('to') ?: now()->toDateString();

        return [$from, $to];
    }

    public function show(Request $r, string $type)
    {
        abort_unless(isset(self::REPORTS[$type]), 404);
        [$title, $perm, $filters] = self::REPORTS[$type];
        abort_unless(Tenant::can($perm), 403, 'Anda tidak punya izin membuka laporan ini.');

        [$from, $to] = $this->range($r);
        if ($from > $to) {
            return back()->withErrors(['from' => 'Tanggal mulai tidak boleh setelah tanggal selesai.'])->withInput();
        }

        $rep = $this->{'build' . str_replace('_', '', ucwords($type, '_'))}($r, $from, $to);
        $rep += ['title' => $title, 'money' => [], 'summary' => [], 'totals' => null];

        $format = $r->query('format', 'html');
        if ($format === 'xls') {
            $html = view('reports.table', ['rep' => $rep, 'from' => $from, 'to' => $to, 'plain' => true])->render();

            return response("\xEF\xBB\xBF" . '<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>', 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $type . '-' . $from . '_' . $to . '.xls"',
            ]);
        }

        $options = [
            'branches' => Tenant::branches(),
            'users' => DB::table('users')->where('business_id', Tenant::id())->pluck('name', 'id')->all(),
            'methods' => DB::table('payment_methods')->where('business_id', Tenant::id())->pluck('name', 'code')->all(),
            'categories' => DB::table('categories')->where('business_id', Tenant::id())->pluck('name', 'id')->all(),
            'warehouses' => DB::table('warehouses')->where('business_id', Tenant::id())->pluck('name', 'id')->all(),
            'customers' => DB::table('customers')->where('business_id', Tenant::id())->pluck('name', 'id')->all(),
            'accounts' => DB::table('cash_accounts')->where('business_id', Tenant::id())->pluck('name', 'id')->all(),
        ];

        return view('reports.show', compact('rep', 'type', 'filters', 'from', 'to', 'options') + ['print' => $format === 'pdf']);
    }

    // ---------- Penjualan ----------
    private function build_filtered(Request $r, string $from, string $to)
    {
        $q = $this->paid()->whereDate('transactions.created_at', '>=', $from)->whereDate('transactions.created_at', '<=', $to);
        foreach (['branch_id', 'user_id', 'payment_method'] as $f) {
            if ($r->filled($f)) {
                $q->where('transactions.' . $f, $r->query($f));
            }
        }

        return $q;
    }

    private function buildPenjualan(Request $r, $from, $to)
    {
        $group = $r->query('group', 'harian');
        $rows = [];
        foreach ($this->build_filtered($r, $from, $to)->get(['created_at', 'subtotal', 'discount', 'tax', 'total']) as $t) {
            $d = Carbon::parse($t->created_at);
            $key = match ($group) {
                'mingguan' => $d->copy()->startOfWeek()->format('Y-m-d'),
                'bulanan' => $d->format('Y-m'),
                default => $d->format('Y-m-d'),
            };
            $rows[$key] ??= [0, 0, 0, 0, 0];
            $rows[$key][0]++;
            $rows[$key][1] += $t->subtotal;
            $rows[$key][2] += $t->discount;
            $rows[$key][3] += $t->tax;
            $rows[$key][4] += $t->total;
        }
        ksort($rows);
        $out = [];
        $tot = [0, 0, 0, 0, 0];
        foreach ($rows as $k => $v) {
            $label = match ($group) {
                'mingguan' => 'Minggu ' . Carbon::parse($k)->format('d/m/Y'),
                'bulanan' => Carbon::parse($k . '-01')->locale('id')->translatedFormat('F Y'),
                default => Carbon::parse($k)->format('d/m/Y'),
            };
            $out[] = [$label, ...$v];
            foreach ($v as $i => $x) {
                $tot[$i] += $x;
            }
        }

        return [
            'headers' => ['Periode', 'Transaksi', 'Penjualan', 'Diskon', 'Pajak', 'Total'], 'rows' => $out,
            'money' => [2, 3, 4, 5], 'totals' => ['Total', ...$tot],
            'chart' => array_map(fn ($o) => [$o[0], $o[5]], $out),
        ];
    }

    private function buildTransaksi(Request $r, $from, $to)
    {
        $q = $this->build_filtered($r, $from, $to);
        if ($r->filled('status')) {
            $q = DB::table('transactions')->where('transactions.business_id', Tenant::id())->where('transactions.status', $r->query('status'))->whereDate('transactions.created_at', '>=', $from)->whereDate('transactions.created_at', '<=', $to);
        }
        $users = DB::table('users')->where('business_id', Tenant::id())->pluck('name', 'id');
        $rows = $q->orderByDesc('created_at')->limit(2000)->get()->map(fn ($t) => [
            $t->invoice_no, Carbon::parse($t->created_at)->format('d/m/Y H:i'), $users[$t->user_id] ?? '-', $t->payment_method, $t->payment_status, (float) $t->total,
        ])->all();

        return ['headers' => ['Invoice', 'Waktu', 'Kasir', 'Metode', 'Pembayaran', 'Total'], 'rows' => $rows, 'money' => [5],
            'totals' => ['Total', '', '', '', '', array_sum(array_column($rows, 5))]];
    }

    private function products(Request $r, $from, $to, string $order)
    {
        $q = DB::table('transaction_items as i')
            ->join('transactions as t', 't.id', '=', 'i.transaction_id')
            ->join('products as p', 'p.id', '=', 'i.product_id')
            ->where('t.business_id', Tenant::id())->where('t.status', 'paid')
            ->whereDate('t.created_at', '>=', $from)->whereDate('t.created_at', '<=', $to);
        if ($r->filled('branch_id')) {
            $q->where('t.branch_id', $r->query('branch_id'));
        }
        if ($r->filled('category_id')) {
            $q->where('p.category_id', $r->query('category_id'));
        }
        $rows = $q->groupBy('p.id', 'p.name')->selectRaw('p.name, SUM(i.qty - i.returned_qty) as qty, SUM(i.subtotal) as omzet')
            ->orderBy('qty', $order)->limit(100)->get()->map(fn ($x) => [$x->name, (int) $x->qty, (float) $x->omzet])->all();

        return ['headers' => ['Produk', 'Terjual', 'Omzet'], 'rows' => $rows, 'money' => [2],
            'totals' => ['Total', array_sum(array_column($rows, 1)), array_sum(array_column($rows, 2))],
            'chart' => array_map(fn ($o) => [$o[0], $o[1]], array_slice($rows, 0, 10))];
    }

    private function buildProdukTerlaris(Request $r, $from, $to) { return $this->products($r, $from, $to, 'desc'); }
    private function buildProdukKurang(Request $r, $from, $to) { return $this->products($r, $from, $to, 'asc'); }

    private function profit(Request $r, $from, $to): array
    {
        $q = DB::table('transaction_items as i')->join('transactions as t', 't.id', '=', 'i.transaction_id')
            ->where('t.business_id', Tenant::id())->where('t.status', 'paid')
            ->whereDate('t.created_at', '>=', $from)->whereDate('t.created_at', '<=', $to);
        if ($r->filled('branch_id')) {
            $q->where('t.branch_id', $r->query('branch_id'));
        }
        if (($ids = Tenant::branchIds()) !== null) {
            $q->whereIn('t.branch_id', $ids ?: [0]);
        }
        $row = $q->selectRaw('SUM(i.subtotal) as omzet, SUM(i.cost_price * (i.qty - i.returned_qty)) as modal, SUM(i.subtotal * i.returned_qty / i.qty) as retur')->first();
        $omzet = (float) $row->omzet - (float) $row->retur;
        $modal = (float) $row->modal;
        $exp = (float) DB::table('cash_entries')->where('business_id', Tenant::id())->where('type', 'expense')
            ->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to)->sum('amount');

        return [$omzet, $modal, $exp];
    }

    private function buildLabaKotor(Request $r, $from, $to)
    {
        [$omzet, $modal] = $this->profit($r, $from, $to);

        return ['headers' => ['Keterangan', 'Nominal'], 'money' => [1], 'rows' => [
            ['Penjualan bersih (setelah retur)', $omzet], ['Harga pokok penjualan', $modal],
        ], 'totals' => ['Laba kotor', $omzet - $modal]];
    }

    private function buildLabaBersih(Request $r, $from, $to)
    {
        [$omzet, $modal, $exp] = $this->profit($r, $from, $to);

        return ['headers' => ['Keterangan', 'Nominal'], 'money' => [1], 'rows' => [
            ['Penjualan bersih (setelah retur)', $omzet], ['Harga pokok penjualan', $modal], ['Laba kotor', $omzet - $modal], ['Pengeluaran operasional', $exp],
        ], 'totals' => ['Laba bersih', $omzet - $modal - $exp]];
    }

    private function buildLabaRugi(Request $r, $from, $to)
    {
        [$omzet, $modal, $exp] = $this->profit($r, $from, $to);
        $income = (float) DB::table('cash_entries')->where('business_id', Tenant::id())->where('type', 'income')
            ->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to)->sum('amount');

        return ['headers' => ['Keterangan', 'Nominal'], 'money' => [1], 'rows' => [
            ['Penjualan bersih', $omzet], ['Pemasukan lain', $income], ['Harga pokok penjualan', -$modal], ['Pengeluaran', -$exp],
        ], 'totals' => ['Laba / (Rugi)', $omzet + $income - $modal - $exp]];
    }

    // ---------- Stok ----------
    private function buildStok(Request $r, $from, $to)
    {
        $q = DB::table('stocks as s')->join('products as p', 'p.id', '=', 's.product_id')->join('warehouses as w', 'w.id', '=', 's.warehouse_id')
            ->where('s.business_id', Tenant::id());
        if (($ids = Tenant::branchIds()) !== null) {
            $q->whereIn('s.branch_id', $ids ?: [0]);
        }
        foreach (['warehouse_id' => 's.warehouse_id', 'category_id' => 'p.category_id'] as $k => $col) {
            if ($r->filled($k)) {
                $q->where($col, $r->query($k));
            }
        }
        if ($r->boolean('low')) {
            $q->whereColumn('s.quantity', '<=', DB::raw('COALESCE(s.min_stock, p.min_stock)'));
        }
        $rows = $q->orderBy('p.name')->get(['p.name', 'w.name as gudang', 's.quantity', 's.min_stock', 's.unit', 'p.cost_price'])
            ->map(fn ($x) => [$x->name, $x->gudang, (int) $x->quantity, $x->min_stock ?? '-', $x->unit, (float) $x->cost_price * $x->quantity])->all();

        return ['headers' => ['Produk', 'Gudang', 'Stok', 'Minimum', 'Satuan', 'Nilai (modal)'], 'rows' => $rows, 'money' => [5],
            'totals' => ['Total', '', array_sum(array_column($rows, 2)), '', '', array_sum(array_column($rows, 5))]];
    }

    private function movements(Request $r, $from, $to, array $types)
    {
        $q = DB::table('stock_movements as m')->join('products as p', 'p.id', '=', 'm.product_id')->leftJoin('warehouses as w', 'w.id', '=', 'm.warehouse_id')
            ->where('m.business_id', Tenant::id())->whereIn('m.type', $types)
            ->whereDate('m.movement_date', '>=', $from)->whereDate('m.movement_date', '<=', $to);
        if ($r->filled('warehouse_id')) {
            $q->where('m.warehouse_id', $r->query('warehouse_id'));
        }
        $rows = $q->orderByDesc('m.id')->limit(2000)->get(['m.movement_date', 'p.name', 'w.name as gudang', 'm.type', 'm.qty', 'm.source', 'm.reference'])
            ->map(fn ($x) => [Carbon::parse($x->movement_date)->format('d/m/Y'), $x->name, $x->gudang, $x->type, abs($x->qty), $x->source ?: $x->reference])->all();

        return ['headers' => ['Tanggal', 'Produk', 'Gudang', 'Jenis', 'Jumlah', 'Sumber / alasan'], 'rows' => $rows,
            'totals' => ['Total', '', '', '', array_sum(array_column($rows, 4)), '']];
    }

    private function buildStokMasuk(Request $r, $from, $to) { return $this->movements($r, $from, $to, ['in', 'purchase', 'transfer_in', 'return']); }
    private function buildStokKeluar(Request $r, $from, $to) { return $this->movements($r, $from, $to, ['out', 'transfer_out']); }

    // ---------- Pelanggan, kasir, cabang ----------
    private function buildPelanggan(Request $r, $from, $to)
    {
        $q = $this->build_filtered($r, $from, $to)->join('customers as c', 'c.id', '=', 'transactions.customer_id');
        if ($r->filled('customer_id')) {
            $q->where('transactions.customer_id', $r->query('customer_id'));
        }
        $rows = $q->groupBy('c.id', 'c.name', 'c.phone')->selectRaw('c.name, c.phone, COUNT(*) as n, SUM(transactions.total) as total')
            ->orderByDesc('total')->limit(100)->get()->map(fn ($x) => [$x->name, $x->phone ?: '-', (int) $x->n, (float) $x->total])->all();

        return ['headers' => ['Pelanggan', 'No. HP', 'Transaksi', 'Total belanja'], 'rows' => $rows, 'money' => [3],
            'totals' => ['Total', '', array_sum(array_column($rows, 2)), array_sum(array_column($rows, 3))]];
    }

    private function buildKasir(Request $r, $from, $to)
    {
        $q = $this->build_filtered($r, $from, $to)->join('users as u', 'u.id', '=', 'transactions.user_id');
        $rows = $q->groupBy('u.id', 'u.name')->selectRaw('u.name, COUNT(*) as n, SUM(transactions.total) as total')
            ->orderByDesc('total')->get()->map(fn ($x) => [$x->name, (int) $x->n, (float) $x->total, $x->n ? (float) $x->total / $x->n : 0])->all();

        return ['headers' => ['Kasir', 'Jumlah transaksi', 'Total penjualan', 'Rata-rata / transaksi'], 'rows' => $rows, 'money' => [2, 3],
            'totals' => ['Total', array_sum(array_column($rows, 1)), array_sum(array_column($rows, 2)), ''],
            'chart' => array_map(fn ($o) => [$o[0], $o[2]], $rows)];
    }

    private function buildCabang(Request $r, $from, $to)
    {
        $rows = [];
        foreach (DB::table('branches')->where('business_id', Tenant::id())->get() as $b) {
            if (($ids = Tenant::branchIds()) !== null && ! in_array($b->id, $ids)) {
                continue;
            }
            $req = new Request(['branch_id' => $b->id]);
            $t = $this->paid()->where('branch_id', $b->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            [$omzet, $modal] = $this->profit($req, $from, $to);
            $rows[] = [$b->name, (clone $t)->count(), (float) (clone $t)->sum('total'), $omzet - $modal];
        }

        return ['headers' => ['Cabang', 'Transaksi', 'Penjualan', 'Laba kotor'], 'rows' => $rows, 'money' => [2, 3],
            'totals' => ['Total', array_sum(array_column($rows, 1)), array_sum(array_column($rows, 2)), array_sum(array_column($rows, 3))],
            'chart' => array_map(fn ($o) => [$o[0], $o[2]], $rows)];
    }

    // ---------- Keuangan ----------
    private const IN = ['cash_in', 'capital', 'income', 'sale', 'receivable_payment'];

    private function buildArusKas(Request $r, $from, $to)
    {
        $q = DB::table('cash_entries as e')->join('cash_accounts as a', 'a.id', '=', 'e.cash_account_id')->where('e.business_id', Tenant::id())
            ->whereDate('e.entry_date', '>=', $from)->whereDate('e.entry_date', '<=', $to);
        if ($r->filled('cash_account_id')) {
            $q->where('e.cash_account_id', $r->query('cash_account_id'));
        }
        $rows = []; $in = 0; $out = 0;
        foreach ($q->orderBy('e.entry_date')->orderBy('e.id')->get(['e.entry_date', 'e.type', 'e.name', 'e.description', 'e.amount', 'a.name as akun']) as $e) {
            $isIn = in_array($e->type, self::IN, true);
            $isIn ? $in += $e->amount : $out += $e->amount;
            $rows[] = [Carbon::parse($e->entry_date)->format('d/m/Y'), $e->akun, $e->type, $e->name ?: $e->description, $isIn ? (float) $e->amount : 0, $isIn ? 0 : (float) $e->amount];
        }

        return ['headers' => ['Tanggal', 'Akun', 'Jenis', 'Keterangan', 'Masuk', 'Keluar'], 'rows' => $rows, 'money' => [4, 5],
            'totals' => ['Total', '', '', 'Saldo periode: ' . Tenant::rupiah($in - $out), $in, $out]];
    }

    private function buildRekapKeuangan(Request $r, $from, $to)
    {
        $base = fn () => DB::table('cash_entries')->where('business_id', Tenant::id())->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to);
        $sum = fn (string $t) => (float) $base()->where('type', $t)->sum('amount');
        $hutang = (float) DB::table('debts')->where('business_id', Tenant::id())->where('kind', 'hutang')->selectRaw('SUM(amount - paid_amount) s')->value('s');
        $piutang = (float) DB::table('debts')->where('business_id', Tenant::id())->where('kind', 'piutang')->selectRaw('SUM(amount - paid_amount) s')->value('s');
        $rows = [
            ['Penjualan (kas masuk)', $sum('sale')], ['Pemasukan lain', $sum('income')], ['Kas masuk', $sum('cash_in')], ['Tambahan modal', $sum('capital')],
            ['Pengeluaran', $sum('expense')], ['Kas keluar', $sum('cash_out')], ['Pembelian barang', $sum('purchase')], ['Refund / retur', $sum('refund')],
            ['Sisa hutang (saat ini)', $hutang], ['Sisa piutang (saat ini)', $piutang],
        ];

        return ['headers' => ['Keterangan', 'Nominal'], 'rows' => $rows, 'money' => [1]];
    }
}
