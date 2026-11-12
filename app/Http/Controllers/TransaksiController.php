<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\StockService;
use App\Support\ModuleHooks;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    private function scoped()
    {
        $q = Transaction::query();
        if (($ids = Tenant::branchIds()) !== null) {
            $q->whereIn('branch_id', $ids ?: [0]);
        }
        // Kasir biasa hanya melihat transaksinya sendiri
        if (! Tenant::isAdmin() && ! Tenant::can('laporan')) {
            $q->where('user_id', Tenant::user()->id);
        }

        return $q;
    }

    public function index(Request $r)
    {
        $q = $this->scoped()->with(['cashier:id,name', 'customer:id,name']);
        if ($v = trim((string) $r->query('invoice'))) {
            $q->where('invoice_no', 'like', "%$v%");
        }
        if ($v = $r->query('from')) {
            $q->whereDate('created_at', '>=', $v);
        }
        if ($v = $r->query('to')) {
            $q->whereDate('created_at', '<=', $v);
        }
        foreach (['branch_id', 'user_id', 'status', 'payment_method', 'customer_id'] as $f) {
            if ($r->filled($f)) {
                $q->where($f, $r->query($f));
            }
        }
        $rows = $q->latest()->paginate(20)->withQueryString();
        $branches = DB::table('branches')->where('business_id', Tenant::id())->pluck('name', 'id');
        $users = DB::table('users')->where('business_id', Tenant::id())->pluck('name', 'id');
        $methods = DB::table('payment_methods')->where('business_id', Tenant::id())->pluck('name', 'code');

        return view('pages.transaksi', compact('rows', 'branches', 'users', 'methods'));
    }

    public function show(int $id)
    {
        $tx = $this->scoped()->with(['items', 'cashier:id,name', 'customer'])->findOrFail($id);
        $branch = DB::table('branches')->where('id', $tx->branch_id)->value('name');
        $returns = DB::table('sales_returns')->where('transaction_id', $id)->get();
        $method = DB::table('payment_methods')->where('business_id', Tenant::id())->where('code', $tx->payment_method)->value('name') ?? $tx->payment_method;

        return view('pages.transaksi-detail', compact('tx', 'branch', 'returns', 'method'));
    }

    public function struk(int $id)
    {
        $tx = $this->scoped()->with(['items', 'cashier:id,name', 'customer'])->findOrFail($id);
        $biz = DB::table('businesses')->find(Tenant::id());
        $branch = DB::table('branches')->where('id', $tx->branch_id)->first();
        $printer = DB::table('printers')->where('business_id', Tenant::id())->where('type', 'struk')->where('is_active', 1)->first();
        $method = DB::table('payment_methods')->where('business_id', Tenant::id())->where('code', $tx->payment_method)->value('name') ?? $tx->payment_method;

        return view('pages.struk', compact('tx', 'biz', 'branch', 'printer', 'method'));
    }

    /** Pembatalan: transaksi yang sudah selesai tidak diedit, hanya dibatalkan dengan alasan. */
    public function batal(Request $r, int $id)
    {
        $data = $r->validate([
            'cancel_reason' => 'required|string|max:255',
            'cancel_note' => 'nullable|string|max:1000',
        ], ['cancel_reason.required' => 'Alasan pembatalan wajib diisi.']);

        DB::transaction(function () use ($id, $data) {
            $tx = $this->scoped()->whereKey($id)->lockForUpdate()->firstOrFail();
            if ($tx->status === 'void') {
                throw ValidationException::withMessages(['cancel_reason' => 'Transaksi sudah dibatalkan.']);
            }
            if (DB::table('sales_returns')->where('transaction_id', $id)->exists()) {
                throw ValidationException::withMessages(['cancel_reason' => 'Transaksi ini sudah punya retur, tidak bisa dibatalkan.']);
            }
            $wh = StockService::defaultWarehouse($tx->branch_id);
            foreach ($tx->items as $it) {
                $p = DB::table('products')->where('id', $it->product_id)->first();
                if (! $p) {
                    continue;
                }
                if ($p->type === 'barang') {
                    StockService::move($p->id, $wh, $it->qty, 'void', ['reference' => $tx->invoice_no, 'note' => 'Pembatalan transaksi']);
                } elseif ($p->type === 'paket') {
                    foreach (DB::table('bundle_items')->where('bundle_id', $p->id)->get() as $bi) {
                        if (DB::table('products')->where('id', $bi->product_id)->value('type') === 'barang') {
                            StockService::move($bi->product_id, $wh, $bi->qty * $it->qty, 'void', ['reference' => $tx->invoice_no]);
                        }
                    }
                }
                if ($it->variation_id) {
                    DB::table('product_variations')->where('id', $it->variation_id)->whereNotNull('stock')->increment('stock', $it->qty);
                }
            }
            // Uang yang sudah masuk dikembalikan di catatan kas
            if ($tx->paid > 0) {
                $in = DB::table('cash_entries')->where('ref_type', 'transaction')->where('ref_id', $tx->id)->where('type', 'sale')->first();
                if ($in) {
                    ModuleHooks::cash('refund', now()->toDateString(), $in->cash_account_id, $tx->paid, 'Pembatalan ' . $tx->invoice_no, 'transaction', $tx->id, $tx->branch_id);
                }
            }
            DB::table('debts')->where('business_id', Tenant::id())->where('kind', 'piutang')
                ->where('description', 'Sisa pembayaran ' . $tx->invoice_no)->update(['status' => 'lunas', 'paid_amount' => DB::raw('amount'), 'description' => 'Dibatalkan: ' . $tx->invoice_no]);

            $tx->update([
                'status' => 'void', 'voided_at' => now(), 'voided_by' => Tenant::user()->id,
                'cancel_reason' => $data['cancel_reason'], 'cancel_note' => $data['cancel_note'] ?? null,
            ]);
            Tenant::audit('batal', 'Transaksi', ['invoice' => $tx->invoice_no, 'alasan' => $data['cancel_reason']]);
        });

        return redirect()->route('transaksi.show', $id)->with('ok', 'Transaksi dibatalkan, stok dikembalikan.');
    }

    // ---------- Retur ----------
    public function returList()
    {
        $rows = DB::table('sales_returns')->where('sales_returns.business_id', Tenant::id())
            ->join('transactions', 'transactions.id', '=', 'sales_returns.transaction_id')
            ->leftJoin('users', 'users.id', '=', 'sales_returns.user_id')
            ->select('sales_returns.*', 'transactions.invoice_no', 'users.name as user_name')
            ->orderByDesc('sales_returns.id')->paginate(20);

        return view('pages.retur-list', compact('rows'));
    }

    public function returForm(Request $r)
    {
        $tx = null;
        if ($no = trim((string) $r->query('invoice'))) {
            $tx = $this->scoped()->with('items')->where('invoice_no', $no)->first();
            if (! $tx) {
                session()->now('err', 'Nomor transaksi tidak ditemukan.');
            } elseif ($tx->status === 'void') {
                session()->now('err', 'Transaksi ini sudah dibatalkan.');
                $tx = null;
            }
        }
        $methods = DB::table('payment_methods')->where('business_id', Tenant::id())->where('is_active', 1)->pluck('name', 'code');

        return view('pages.retur-form', compact('tx', 'methods'));
    }

    public function returStore(Request $r)
    {
        $data = $r->validate([
            'transaction_id' => 'required|integer',
            'refund_method' => 'nullable|string',
            'lines' => 'required|array',
            'lines.*.qty' => 'nullable|integer|min:0',
            'lines.*.reason' => 'nullable|string|max:255',
            'lines.*.condition' => 'nullable|in:baik,rusak',
            'lines.*.proof' => 'nullable|file|max:4096|mimes:jpg,jpeg,png,webp,pdf',
        ]);

        $id = DB::transaction(function () use ($data, $r) {
            $tx = $this->scoped()->with('items')->whereKey($data['transaction_id'])->lockForUpdate()->firstOrFail();
            if ($tx->status === 'void') {
                throw ValidationException::withMessages(['transaction_id' => 'Transaksi sudah dibatalkan.']);
            }
            $wh = StockService::defaultWarehouse($tx->branch_id);
            $total = 0;
            $rows = [];
            foreach ($data['lines'] as $itemId => $l) {
                $qty = (int) ($l['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $it = $tx->items->firstWhere('id', (int) $itemId);
                if (! $it) {
                    continue;
                }
                if ($qty > $it->qty - $it->returned_qty) {
                    throw ValidationException::withMessages(['lines' => "Jumlah retur {$it->product_name} melebihi yang bisa diretur."]);
                }
                if (empty($l['reason'])) {
                    throw ValidationException::withMessages(['lines' => "Alasan retur {$it->product_name} wajib diisi."]);
                }
                $unit = ($it->subtotal) / max(1, $it->qty);     // harga bersih per unit (setelah diskon item)
                $amount = round($unit * $qty, 2);
                $total += $amount;
                $rows[] = [$it, $qty, $l, $amount];
            }
            if (! $rows) {
                throw ValidationException::withMessages(['lines' => 'Pilih minimal satu produk yang diretur (jumlah > 0).']);
            }
            // Diskon transaksi dibagi rata proporsional supaya refund tidak melebihi yang dibayar
            if ($tx->subtotal > 0 && $tx->discount > 0) {
                $total = round($total * ($tx->subtotal - $tx->discount) / $tx->subtotal, 2);
            }
            $total = min($total, (float) $tx->paid);
            if ($total > 0 && empty($data['refund_method'])) {
                throw ValidationException::withMessages(['refund_method' => 'Metode pengembalian dana wajib dipilih.']);
            }

            $retId = DB::table('sales_returns')->insertGetId([
                'business_id' => Tenant::id(), 'branch_id' => $tx->branch_id, 'transaction_id' => $tx->id, 'user_id' => Tenant::user()->id,
                'return_no' => 'RTR-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(3)),
                'refund_method' => $data['refund_method'] ?? null, 'total_refund' => $total, 'created_at' => now(), 'updated_at' => now(),
            ]);

            foreach ($rows as [$it, $qty, $l, $amount]) {
                $proof = isset($l['proof']) ? $l['proof']->store('uploads/' . Tenant::id(), 'public') : null;
                DB::table('sales_return_items')->insert([
                    'sales_return_id' => $retId, 'transaction_item_id' => $it->id, 'product_id' => $it->product_id, 'qty' => $qty,
                    'reason' => $l['reason'], 'condition' => $l['condition'] ?? 'baik', 'proof_path' => $proof, 'amount' => $amount,
                ]);
                DB::table('transaction_items')->where('id', $it->id)->increment('returned_qty', $qty);
                $p = DB::table('products')->where('id', $it->product_id)->first();
                // Barang kondisi baik masuk stok lagi; yang rusak tidak
                if ($p && $p->type === 'barang' && ($l['condition'] ?? 'baik') === 'baik') {
                    StockService::move($p->id, $wh, $qty, 'return', ['reference' => 'Retur ' . $tx->invoice_no, 'note' => $l['reason']]);
                }
            }

            if ($total > 0) {
                $pm = DB::table('payment_methods')->where('business_id', Tenant::id())->where('code', $data['refund_method'])->first();
                $acc = $pm->cash_account_id ?? DB::table('cash_accounts')->where('business_id', Tenant::id())->orderBy('id')->value('id');
                ModuleHooks::cash('refund', now()->toDateString(), $acc, $total, 'Retur ' . $tx->invoice_no, 'sales_return', $retId, $tx->branch_id);
            }
            Tenant::audit('retur', 'Retur', ['invoice' => $tx->invoice_no, 'refund' => $total]);

            return $retId;
        });

        return redirect()->route('retur.list')->with('ok', 'Retur tersimpan.');
    }
}
