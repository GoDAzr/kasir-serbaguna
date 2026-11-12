<?php

namespace App\Support;

use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Aturan khusus tiap modul (data otomatis, stok, kas, hutang). Dipanggil oleh ModuleController
 * dengan nama: form_*, prepare_*, child_*, store_*, after_*, delete_*
 */
class ModuleHooks
{
    public static function has(string $name): bool
    {
        return method_exists(static::class, $name);
    }

    public static function call(string $name, ...$args)
    {
        return self::has($name) ? static::$name(...$args) : null;
    }

    private static function fail(string $field, string $msg): never
    {
        throw ValidationException::withMessages([$field => $msg]);
    }

    private static function biz(): int
    {
        return Tenant::id();
    }

    // ---------- Produk / paket ----------
    public static function after_paket($id, $data, $children)
    {
        $normal = 0;
        foreach ($children as $c) {
            $normal += (float) DB::table('products')->where('id', $c['product_id'])->value('price') * $c['qty'];
        }
        DB::table('products')->where('id', $id)->update(['normal_price' => $normal, 'unit' => 'paket']);
    }

    // ---------- Member ----------
    public static function prepare_member($d, $row)
    {
        if (! $row) {
            $n = DB::table('members')->where('business_id', self::biz())->count() + 1;
            do {
                $no = 'MBR-' . str_pad((string) $n++, 5, '0', STR_PAD_LEFT);
            } while (DB::table('members')->where('business_id', self::biz())->where('member_no', $no)->exists());
            $d['member_no'] = $no;
            $d['joined_at'] = now()->toDateString();
        }

        return $d;
    }

    // ---------- Pembelian ----------
    public static function prepare_pembelian($d, $row, Request $r, $children)
    {
        $wh = DB::table('warehouses')->where('id', $d['warehouse_id'])->where('business_id', self::biz())->first();
        if (! $wh || (int) $wh->branch_id !== (int) $d['branch_id']) {
            self::fail('warehouse_id', 'Gudang tujuan harus milik cabang yang dipilih.');
        }
        $sum = 0;
        foreach ($children as $c) {
            $sum += $c['qty'] * $c['cost'];
        }
        $total = $sum - (float) ($d['discount'] ?? 0) + (float) ($d['tax'] ?? 0);
        if ($total < 0) {
            self::fail('discount', 'Diskon melebihi total pembelian.');
        }
        $d['total'] = $total;
        $d['purchase_no'] = 'PB-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));

        return $d;
    }

    public static function child_pembelian($r)
    {
        $r['subtotal'] = $r['qty'] * $r['cost'];

        return $r;
    }

    public static function after_pembelian($id, $d, $children, Request $r)
    {
        $p = DB::table('purchases')->find($id);
        $supplier = DB::table('suppliers')->where('id', $p->supplier_id)->value('name');
        foreach ($children as $c) {
            StockService::move($c['product_id'], $p->warehouse_id, (int) $c['qty'], 'purchase', [
                'reference' => $p->purchase_no, 'cost' => $c['cost'], 'source' => $supplier, 'date' => $p->purchase_date,
            ]);
            DB::table('products')->where('id', $c['product_id'])->update(['cost_price' => $c['cost']]);
        }
        if ($p->payment_status === 'lunas') {
            self::cash('purchase', $p->purchase_date, $r->input('cash_account_id'), $p->total, 'Pembelian ' . $p->purchase_no, 'purchase', $id, $p->branch_id);
        } else {
            DB::table('debts')->insert([
                'business_id' => self::biz(), 'kind' => 'hutang', 'party_name' => $supplier, 'amount' => $p->total,
                'debt_date' => $p->purchase_date, 'description' => 'Pembelian ' . $p->purchase_no,
                'status' => 'belum_lunas', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /** Catat mutasi kas otomatis. */
    public static function cash(string $type, $date, $accountId, $amount, string $name, ?string $refType = null, ?int $refId = null, $branchId = null): void
    {
        DB::table('cash_entries')->insert([
            'business_id' => self::biz(), 'branch_id' => $branchId, 'cash_account_id' => $accountId, 'type' => $type,
            'entry_date' => $date, 'name' => $name, 'amount' => $amount, 'ref_type' => $refType, 'ref_id' => $refId,
            'user_id' => Tenant::user()?->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // ---------- Hutang & piutang ----------
    public static function prepare_hutang($d, $row)
    {
        $d['status'] = 'belum_lunas';

        return $d;
    }

    public static function prepare_piutang($d, $row)
    {
        $d['status'] = 'belum_lunas';
        $d['party_name'] = DB::table('customers')->where('id', $d['customer_id'])->value('name');

        return $d;
    }

    private static function prepPay($d, string $kind)
    {
        $debt = DB::table('debts')->where('id', $d['debt_id'])->where('business_id', self::biz())->where('kind', $kind)->first();
        if (! $debt) {
            self::fail('debt_id', 'Data tidak ditemukan.');
        }
        $remaining = (float) $debt->amount - (float) $debt->paid_amount;
        if ($d['amount'] > $remaining + 0.001) {
            self::fail('amount', 'Nominal melebihi sisa (' . Tenant::rupiah($remaining) . ').');
        }
        $d['remaining'] = $remaining - $d['amount'];

        return $d;
    }

    private static function afterPay($id, $d, string $cashType)
    {
        $debt = DB::table('debts')->where('id', $d['debt_id'])->first();
        $paid = (float) $debt->paid_amount + $d['amount'];
        DB::table('debts')->where('id', $debt->id)->update([
            'paid_amount' => $paid, 'status' => $paid >= $debt->amount - 0.001 ? 'lunas' : 'sebagian', 'updated_at' => now(),
        ]);
        self::cash($cashType, $d['pay_date'], $d['cash_account_id'], $d['amount'],
            ($cashType === 'debt_payment' ? 'Bayar hutang ' : 'Terima piutang ') . $debt->party_name, 'debt_payment', $id);
    }

    public static function prepare_bayar_hutang($d, $row) { return self::prepPay($d, 'hutang'); }
    public static function prepare_terima_piutang($d, $row) { return self::prepPay($d, 'piutang'); }
    public static function after_bayar_hutang($id, $d) { self::afterPay($id, $d, 'debt_payment'); }
    public static function after_terima_piutang($id, $d) { self::afterPay($id, $d, 'receivable_payment'); }

    // ---------- Inventori ----------
    public static function prepare_stok($d, $row)
    {
        $wh = DB::table('warehouses')->where('id', $d['warehouse_id'])->where('business_id', self::biz())->first();
        if (! $wh || (int) $wh->branch_id !== (int) $d['branch_id']) {
            self::fail('warehouse_id', 'Gudang harus milik cabang yang dipilih.');
        }
        $dup = DB::table('stocks')->where('product_id', $d['product_id'])->where('warehouse_id', $d['warehouse_id'])
            ->when($row, fn ($q) => $q->where('id', '!=', $row->id))->exists();
        if ($dup) {
            self::fail('product_id', 'Produk ini sudah terdaftar di gudang tersebut.');
        }

        return $d;
    }

    private static function singleMove(array $d, int $sign, string $type, ?string $source)
    {
        $wh = DB::table('warehouses')->where('id', $d['warehouse_id'])->where('business_id', self::biz())->first();
        if (! $wh) {
            self::fail('warehouse_id', 'Gudang tidak valid.');
        }
        StockService::move((int) $d['product_id'], $wh->id, $sign * (int) $d['qty'], $type, [
            'date' => $d['movement_date'], 'source' => $source, 'cost' => $d['cost'] ?? null,
            'proof' => $d['proof_path'] ?? null, 'note' => $d['note'] ?? null,
        ]);
        if (! empty($d['cost']) && $sign > 0) {
            DB::table('products')->where('id', $d['product_id'])->update(['cost_price' => $d['cost']]);
        }

        return (int) DB::table('stock_movements')->where('business_id', self::biz())->max('id');
    }

    public static function store_stok_masuk($d, $children, Request $r)
    {
        return self::singleMove($d, 1, 'in', $d['source'] ?? null);
    }

    public static function store_stok_keluar($d, $children, Request $r)
    {
        return self::singleMove($d, -1, 'out', $d['source'] ?? null);
    }

    public static function after_opname($id, $d, $children)
    {
        foreach (DB::table('stock_opname_items')->where('stock_opname_id', $id)->get() as $it) {
            $sys = StockService::qty($it->product_id, $d['warehouse_id']);
            $diff = $it->physical_qty - $sys;
            DB::table('stock_opname_items')->where('id', $it->id)->update(['system_qty' => $sys, 'diff' => $diff]);
            if ($diff !== 0) {
                StockService::move($it->product_id, $d['warehouse_id'], $diff, 'adjust', [
                    'reference' => 'OPN-' . $id, 'note' => $it->reason ?: 'Stok opname', 'date' => $d['opname_date'],
                ]);
            }
        }
    }

    public static function prepare_transfer($d)
    {
        if ((int) $d['from_warehouse_id'] === (int) $d['to_warehouse_id']) {
            self::fail('to_warehouse_id', 'Gudang tujuan harus berbeda dari gudang asal.');
        }
        $d['status'] = 'selesai';

        return $d;
    }

    public static function after_transfer($id, $d, $children)
    {
        foreach ($children as $c) {
            $meta = ['reference' => 'TRF-' . $id, 'date' => $d['transfer_date'], 'note' => $d['note'] ?? null];
            StockService::move($c['product_id'], $d['from_warehouse_id'], -(int) $c['qty'], 'transfer_out', $meta);
            StockService::move($c['product_id'], $d['to_warehouse_id'], (int) $c['qty'], 'transfer_in', $meta);
        }
    }

    // ---------- Pengaturan ----------
    public static function prepare_cabang($d)
    {
        $d['code'] = strtoupper(preg_replace('/\s+/', '', $d['code']));

        return $d;
    }

    public static function prepare_metode_bayar($d, $row)
    {
        if (! $row) {
            $base = Str::slug($d['name'], '_') ?: 'metode';
            $code = $base;
            while (DB::table('payment_methods')->where('business_id', self::biz())->where('code', $code)->exists()) {
                $code = $base . '_' . Str::lower(Str::random(3));
            }
            $d['code'] = $code;
        }

        return $d;
    }

    public static function prepare_qr_meja($d, $row)
    {
        if (! $row) {
            $d['token'] = BusinessSetupToken::make();
        }

        return $d;
    }

    public static function prepare_qr_takeaway($d, $row)
    {
        return self::prepare_qr_meja($d, $row);
    }

    // ---------- Pengguna & role ----------
    public static function form_pengguna($row)
    {
        return ['values' => ['branches' => $row ? DB::table('branch_user')->where('user_id', $row->id)->pluck('branch_id')->all() : []]];
    }

    public static function prepare_pengguna($d, $row, Request $r)
    {
        $me = Tenant::user();
        if ($row && $row->id === $me->id && isset($d['is_active']) && (int) $d['is_active'] === 0) {
            self::fail('is_active', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }
        if (($d['role'] ?? null) === 'owner' && $me->role !== 'owner') {
            self::fail('role', 'Hanya Owner yang boleh memberi role Owner.');
        }

        return $d;
    }

    public static function after_pengguna($id, $d, $children, Request $r)
    {
        $branches = array_map('intval', (array) $r->input('branches', []));
        $valid = DB::table('branches')->where('business_id', self::biz())->whereIn('id', $branches)->pluck('id')->all();
        DB::table('branch_user')->where('user_id', $id)->delete();
        foreach ($valid as $b) {
            DB::table('branch_user')->insert(['user_id' => $id, 'branch_id' => $b]);
        }
    }

    public static function prepare_role($d, $row)
    {
        if ($row && $row->is_system) {
            if (in_array($row->slug, ['owner', 'admin'], true)) {
                $d['permissions'] = json_encode(['*']);
                $d['is_active'] = 1;
            }
        } elseif (! $row) {
            $base = Str::slug($d['name'], '_') ?: 'role';
            $slug = $base;
            while (DB::table('roles')->where('business_id', self::biz())->where('slug', $slug)->exists()) {
                $slug = $base . '_' . Str::lower(Str::random(3));
            }
            $d['slug'] = $slug;
        }

        return $d;
    }

    public static function delete_role($row)
    {
        if ($row->is_system) {
            self::fail('role', 'Role bawaan tidak boleh dihapus.');
        }
        if (DB::table('users')->where('business_id', self::biz())->where('role', $row->slug)->exists()) {
            self::fail('role', 'Role masih dipakai pengguna.');
        }
    }

    // ---------- Backup ----------
    public static function store_backup($d, $children, Request $r)
    {
        $b = self::biz();
        $direct = ['transactions', 'cash_entries', 'debts', 'debt_payments', 'stock_movements', 'purchases', 'sales_returns',
            'stock_opnames', 'stock_transfers', 'stocks'];
        if ($d['type'] === 'lengkap') {
            $direct = array_merge($direct, ['businesses_self', 'branches', 'warehouses', 'roles', 'categories', 'products', 'product_variations',
                'bundle_items', 'customers', 'members', 'suppliers', 'cash_accounts', 'finance_categories', 'operating_hours',
                'payment_methods', 'taxes', 'printers', 'qr_tables', 'qr_takeaways', 'app_notifications']);
        }
        $dump = ['business_id' => $b, 'type' => $d['type'], 'created_at' => now()->toDateTimeString(), 'tables' => []];
        foreach ($direct as $t) {
            if ($t === 'businesses_self') {
                $dump['tables']['businesses'] = DB::table('businesses')->where('id', $b)->get();
                continue;
            }
            $dump['tables'][$t] = DB::table($t)->where('business_id', $b)->get();
        }
        $children = [
            'transaction_items' => ['transactions', 'transaction_id'], 'purchase_items' => ['purchases', 'purchase_id'],
            'sales_return_items' => ['sales_returns', 'sales_return_id'], 'stock_opname_items' => ['stock_opnames', 'stock_opname_id'],
            'stock_transfer_items' => ['stock_transfers', 'stock_transfer_id'],
        ];
        foreach ($children as $t => [$parent, $fk]) {
            if (isset($dump['tables'][$parent])) {
                $dump['tables'][$t] = DB::table($t)->whereIn($fk, $dump['tables'][$parent]->pluck('id'))->get();
            }
        }
        $name = 'backup-' . now()->format('Ymd-His') . '.json';
        $path = "backups/$b/$name";
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, json_encode($dump, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return DB::table('backups')->insertGetId([
            'business_id' => $b, 'name' => $name, 'type' => $d['type'], 'note' => $d['note'] ?? null, 'file_path' => $path,
            'size' => \Illuminate\Support\Facades\Storage::disk('local')->size($path), 'user_id' => Tenant::user()->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

class BusinessSetupToken
{
    public static function make(): string
    {
        return Str::random(32);
    }
}
