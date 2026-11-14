<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use App\Support\ModuleHooks;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public const SHIFTS = ['pagi' => 'Pagi', 'siang' => 'Siang', 'malam' => 'Malam'];
    public const TYPES = ['langsung' => 'Penjualan langsung', 'pesanan' => 'Pesanan', 'online' => 'Online'];

    public function index()
    {
        $b = Tenant::id();
        $products = Product::where('is_active', true)->with('category:id,name')->orderBy('name')->get();
        $vars = DB::table('product_variations')->where('business_id', $b)->get()->groupBy('product_id');
        $bundleMap = DB::table('bundle_items')->where('business_id', $b)->get()->groupBy('bundle_id');

        $items = $products->map(fn ($p) => [
            'id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'type' => $p->type,
            'sku' => $p->sku, 'barcode' => $p->barcode, 'stock' => $p->stock, 'cat' => $p->category?->name,
            'img' => $p->image_path ? asset('storage/' . $p->image_path) : null,
            'uses_stock' => $p->type === 'barang',
            'vars' => ($vars[$p->id] ?? collect())->map(fn ($v) => [
                'id' => $v->id, 'label' => $v->name . ': ' . $v->value, 'extra' => (float) $v->extra_price,
            ])->values(),
            'bundle' => ($bundleMap[$p->id] ?? collect())->count(),
        ])->values();

        $methods = DB::table('payment_methods')->where('business_id', $b)->where('is_active', 1)->get(['code', 'name', 'type']);
        $taxes = DB::table('taxes')->where('business_id', $b)->where('is_active', 1)->get()
            ->map(fn ($t) => ['name' => $t->name, 'pct' => (float) $t->percentage, 'products' => json_decode($t->product_ids ?: '[]', true)]);
        $customers = DB::table('customers')->where('business_id', $b)->orderBy('name')->get(['id', 'name', 'phone']);

        return view('pages.pos', [
            'items' => $items, 'methods' => $methods, 'taxes' => $taxes, 'customers' => $customers,
            'branches' => Tenant::branches(), 'shifts' => self::SHIFTS, 'types' => self::TYPES,
            'canPrice' => Tenant::isAdmin(),
            'canDisc' => Tenant::can('pos_diskon'),
            'canNewProduct' => Tenant::can('produk_cepat') || Tenant::can('produk'),
        ]);
    }

    /**
     * Daftarkan produk baru langsung dari kasir (barcode hasil scan belum terdaftar).
     * Hanya untuk yang diizinkan owner (izin "produk_cepat") atau yang memang boleh mengelola produk.
     */
    public function quickProduct(Request $r)
    {
        abort_unless(Tenant::can('produk_cepat') || Tenant::can('produk'), 403, 'Anda belum diizinkan mendaftarkan produk baru. Minta owner mengaktifkannya.');

        $data = $r->validate([
            'barcode' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:1|max:1000000',
            'branch_id' => 'nullable|integer',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.',
            'stock.required' => 'Jumlah stok wajib diisi (minimal 1).',
            'stock.min' => 'Jumlah stok minimal 1.',
        ]);

        if (Product::withTrashed()->where('barcode', $data['barcode'])->exists()) {
            throw ValidationException::withMessages(['barcode' => 'Barcode ini sudah terdaftar. Cari produknya di daftar produk.']);
        }
        $branches = Tenant::branches();
        $branchId = isset($data['branch_id']) && isset($branches[$data['branch_id']]) ? (int) $data['branch_id'] : null;

        $p = DB::transaction(function () use ($data, $branchId) {
            $p = Product::create([
                'type' => 'barang', 'name' => trim($data['name']), 'barcode' => $data['barcode'],
                'price' => $data['price'], 'cost_price' => $data['cost_price'] ?? 0,
                'stock' => 0, 'min_stock' => 0, 'unit' => 'pcs', 'is_active' => true,
            ]);
            StockService::move($p->id, StockService::defaultWarehouse($branchId), (int) $data['stock'], 'in', [
                'note' => 'Produk didaftarkan dari kasir (scan barcode)', 'reference' => $data['barcode'], 'source' => 'kasir',
            ]);

            return $p->fresh();
        });
        Tenant::audit('tambah', 'Produk (dari kasir)', ['id' => $p->id, 'name' => $p->name, 'barcode' => $p->barcode]);

        return response()->json(['ok' => true, 'item' => [
            'id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'type' => 'barang',
            'sku' => null, 'barcode' => $p->barcode, 'stock' => (int) $p->stock, 'cat' => null, 'img' => null,
            'uses_stock' => true, 'vars' => [], 'bundle' => 0,
        ]]);
    }

    public function checkout(Request $r)
    {
        $b = Tenant::id();
        $user = Tenant::user();

        $data = $r->validate([
            'branch_id' => 'required|integer',
            'shift' => 'required|in:' . implode(',', array_keys(self::SHIFTS)),
            'type' => 'required|in:' . implode(',', array_keys(self::TYPES)),
            'customer_id' => 'nullable|integer',
            'note' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variation_id' => 'nullable|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.note' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'received' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'proof' => 'nullable|file|max:4096|mimes:jpg,jpeg,png,webp,pdf',
            'receipt_note' => 'nullable|string|max:500',
        ], [
            'items.required' => 'Keranjang masih kosong.', 'items.min' => 'Keranjang masih kosong.',
            'items.*.qty.min' => 'Jumlah harus lebih dari 0.', 'shift.required' => 'Pilih shift kasir.',
            'branch_id.required' => 'Pilih cabang.', 'amount.required' => 'Nominal pembayaran wajib diisi.',
        ]);

        $branches = Tenant::branches();
        if (! isset($branches[$data['branch_id']])) {
            throw ValidationException::withMessages(['branch_id' => 'Cabang tidak valid.']);
        }
        $method = DB::table('payment_methods')->where('business_id', $b)->where('code', $data['payment_method'])->where('is_active', 1)->first();
        if (! $method) {
            throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran tidak valid.']);
        }
        $customerId = $data['customer_id'] ?? null;
        if ($customerId && ! DB::table('customers')->where('id', $customerId)->where('business_id', $b)->exists()) {
            throw ValidationException::withMessages(['customer_id' => 'Pelanggan tidak valid.']);
        }
        $isCash = $method->type === 'tunai';
        if ($isCash && ! isset($data['received'])) {
            throw ValidationException::withMessages(['received' => 'Uang diterima wajib diisi untuk pembayaran tunai.']);
        }

        $canDisc = Tenant::can('pos_diskon');   // izin dari owner
        $canDebt = Tenant::can('pos_piutang');

        $proofPath = $r->hasFile('proof') ? $r->file('proof')->store("uploads/$b", 'public') : null;

        $tx = DB::transaction(function () use ($data, $b, $user, $method, $customerId, $isCash, $proofPath, $canDisc, $canDebt) {
            $warehouse = StockService::defaultWarehouse((int) $data['branch_id']);
            $merged = [];
            foreach ($data['items'] as $it) {
                $k = $it['product_id'] . ':' . ($it['variation_id'] ?? 0) . ':' . ($it['note'] ?? '');
                if (isset($merged[$k])) {
                    $merged[$k]['qty'] += $it['qty'];
                } else {
                    $merged[$k] = $it;
                }
            }

            $subtotal = 0;
            $lines = [];
            foreach ($merged as $it) {
                $p = Product::where('id', $it['product_id'])->where('is_active', true)->lockForUpdate()->first();
                if (! $p) {
                    throw ValidationException::withMessages(['items' => 'Ada produk yang tidak tersedia.']);
                }
                $var = null;
                if (! empty($it['variation_id'])) {
                    $var = DB::table('product_variations')->where('id', $it['variation_id'])->where('product_id', $p->id)->first();
                    if (! $var) {
                        throw ValidationException::withMessages(['items' => "Variasi {$p->name} tidak valid."]);
                    }
                }
                $price = (float) $p->price + ($var ? (float) $var->extra_price : 0);
                if (Tenant::isAdmin() && isset($it['price'])) {
                    $price = (float) $it['price'];
                }
                $qty = (int) $it['qty'];
                $disc = $canDisc ? min((float) ($it['discount'] ?? 0), $price * $qty) : 0;
                $line = $price * $qty - $disc;
                $subtotal += $line;

                // Stok: barang langsung, paket lewat komponennya
                if ($p->type === 'barang') {
                    StockService::move($p->id, $warehouse, -$qty, 'sale', ['reference' => 'POS']);
                } elseif ($p->type === 'paket') {
                    foreach (DB::table('bundle_items')->where('bundle_id', $p->id)->get() as $bi) {
                        $comp = DB::table('products')->where('id', $bi->product_id)->first();
                        if ($comp && $comp->type === 'barang') {
                            StockService::move($comp->id, $warehouse, -$bi->qty * $qty, 'sale', ['reference' => 'POS paket ' . $p->name]);
                        }
                    }
                }
                if ($var && $var->stock !== null && $var->uses_stock) {
                    if ($var->stock < $qty) {
                        throw ValidationException::withMessages(['items' => "Stok variasi {$p->name} tidak cukup."]);
                    }
                    DB::table('product_variations')->where('id', $var->id)->decrement('stock', $qty);
                }

                $lines[] = [
                    'product' => $p, 'var' => $var, 'qty' => $qty, 'price' => $price, 'discount' => $disc,
                    'line' => $line, 'note' => $it['note'] ?? null,
                ];
            }

            $discount = $canDisc ? min((float) ($data['discount'] ?? 0), $subtotal) : 0;
            $base = $subtotal - $discount;

            // Pajak / biaya layanan yang aktif
            $tax = 0;
            foreach (DB::table('taxes')->where('business_id', $b)->where('is_active', 1)->get() as $t) {
                $ids = json_decode($t->product_ids ?: '[]', true) ?: [];
                if (! $ids) {
                    $part = $base;
                } else {
                    $sel = collect($lines)->filter(fn ($l) => in_array((string) $l['product']->id, array_map('strval', $ids), true))->sum('line');
                    $part = $subtotal > 0 ? $base * $sel / $subtotal : 0;
                }
                $tax += round($part * $t->percentage / 100, 2);
            }
            $total = $base + $tax;
            $paid = (float) $data['amount'];

            if ($paid > $total + 0.001) {
                throw ValidationException::withMessages(['amount' => 'Nominal melebihi total belanja.']);
            }
            $received = $isCash ? (float) $data['received'] : $paid;
            if ($isCash && $received + 0.001 < $paid) {
                throw ValidationException::withMessages(['received' => 'Uang diterima kurang dari nominal pembayaran.']);
            }
            if ($paid + 0.001 < $total && ! $canDebt) {
                throw ValidationException::withMessages(['amount' => 'Anda tidak punya izin menjual belum lunas. Nominal harus sama dengan total.']);
            }
            if ($paid + 0.001 < $total && ! $customerId) {
                throw ValidationException::withMessages(['customer_id' => 'Pembayaran belum lunas: pilih pelanggan agar tercatat sebagai piutang.']);
            }
            $status = $paid + 0.001 >= $total ? 'lunas' : ($paid > 0 ? 'sebagian' : 'belum_lunas');
            $invoice = 'INV-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(3));

            $tx = \App\Models\Transaction::create([
                'invoice_no' => $invoice, 'user_id' => $user->id, 'customer_id' => $customerId,
                'branch_id' => $data['branch_id'], 'shift' => $data['shift'], 'type' => $data['type'],
                'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $total,
                'paid' => $paid, 'change' => $isCash ? max(0, $received - $paid) : 0,
                'payment_method' => $method->code, 'status' => 'paid', 'payment_status' => $status,
                'payment_ref' => $data['reference'] ?? null, 'payment_proof' => $proofPath,
                'note' => $data['note'] ?? null, 'receipt_note' => $data['receipt_note'] ?? null,
            ]);

            foreach ($lines as $l) {
                $tx->items()->create([
                    'product_id' => $l['product']->id,
                    'product_name' => $l['product']->name . ($l['var'] ? ' (' . $l['var']->value . ')' : ''),
                    'variation_id' => $l['var']->id ?? null, 'variation_label' => $l['var'] ? $l['var']->name . ': ' . $l['var']->value : null,
                    'price' => $l['price'], 'cost_price' => $l['product']->cost_price, 'qty' => $l['qty'],
                    'discount' => $l['discount'], 'note' => $l['note'], 'subtotal' => $l['line'],
                ]);
            }

            if ($paid > 0) {
                $acc = $method->cash_account_id ?: DB::table('cash_accounts')->where('business_id', $b)->orderBy('id')->value('id');
                ModuleHooks::cash('sale', now()->toDateString(), $acc, $paid, 'Penjualan ' . $invoice, 'transaction', $tx->id, $data['branch_id']);
            }
            if ($status !== 'lunas') {
                DB::table('debts')->insert([
                    'business_id' => $b, 'kind' => 'piutang', 'customer_id' => $customerId,
                    'party_name' => DB::table('customers')->where('id', $customerId)->value('name'),
                    'amount' => $total - $paid, 'debt_date' => now()->toDateString(),
                    'description' => 'Sisa pembayaran ' . $invoice, 'status' => 'belum_lunas',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            // Poin member: 1 poin tiap Rp10.000
            if ($customerId && ($mem = DB::table('members')->where('customer_id', $customerId)->where('status', 'aktif')->first())) {
                $pts = (int) floor($total / 10000);
                if ($pts > 0) {
                    DB::table('members')->where('id', $mem->id)->increment('points', $pts);
                    DB::table('point_logs')->insert(['business_id' => $b, 'member_id' => $mem->id, 'type' => 'earn', 'points' => $pts,
                        'reference' => $invoice, 'created_at' => now(), 'updated_at' => now()]);
                }
            }

            return $tx;
        });

        Tenant::audit('tambah', 'Transaksi', ['invoice' => $tx->invoice_no, 'total' => $tx->total]);

        return response()->json([
            'ok' => true, 'invoice' => $tx->invoice_no, 'total' => (float) $tx->total, 'change' => (float) $tx->change,
            'status' => $tx->payment_status, 'receipt' => route('transaksi.struk', $tx->id),
        ]);
    }
}
