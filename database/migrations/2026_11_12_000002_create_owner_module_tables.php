<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk menu Owner sesuai dokumen "Spesifikasi Field Owner".
 * Semua nominal uang memakai DECIMAL(15,2).
 */
return new class extends Migration
{
    private function biz(Blueprint $table): void
    {
        $table->foreignId('business_id')->constrained()->cascadeOnDelete();
    }

    public function up(): void
    {
        // ---------- Produk ----------
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');                              // mis. Ukuran
            $table->string('value');                             // mis. Besar
            $table->decimal('extra_price', 15, 2)->default(0);
            $table->string('sku', 50)->nullable();
            $table->string('barcode', 50)->nullable();
            $table->integer('stock')->nullable();
            $table->boolean('uses_stock')->default(false);
            $table->timestamps();
        });

        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('bundle_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('qty');
            $table->timestamps();
        });

        // ---------- Pelanggan & member ----------
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('member_no', 30);
            $table->date('joined_at');
            $table->string('status', 20)->default('aktif');
            $table->integer('points')->default(0);
            $table->timestamps();

            $table->unique(['business_id', 'member_no']);
            $table->unique('customer_id');
        });

        Schema::create('point_logs', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);          // earn | redeem | adjust
            $table->integer('points');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        // ---------- Supplier & pembelian ----------
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_name')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('purchase_no', 40)->nullable();
            $table->date('purchase_date');
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('payment_status', 20)->default('lunas');   // lunas | belum_lunas
            $table->string('proof_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('qty');
            $table->decimal('cost', 15, 2);
            $table->decimal('subtotal', 15, 2);
        });

        // ---------- Retur penjualan ----------
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreignId('transaction_id')->constrained();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('return_no', 40);
            $table->string('refund_method', 30)->nullable();
            $table->decimal('total_refund', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_item_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->integer('qty');
            $table->string('reason');
            $table->string('condition', 30)->nullable();   // baik | rusak
            $table->string('proof_path')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
        });

        // ---------- Keuangan ----------
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->string('type', 20)->default('kas');    // kas | bank | ewallet
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('finance_categories', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->string('direction', 10);               // in | out
            $table->timestamps();
        });

        Schema::create('cash_entries', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreignId('cash_account_id')->constrained();
            // cash_in | cash_out | capital | income | expense | sale | refund | purchase | debt_payment | receivable_payment
            $table->string('type', 30);
            $table->date('entry_date');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('source')->nullable();          // sumber modal / sumber pemasukan
            $table->text('description')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('ref_type', 30)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'entry_date']);
        });

        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('kind', 10);                    // hutang | piutang
            $table->string('party_name')->nullable();      // pemberi hutang
            $table->unsignedBigInteger('customer_id')->nullable();   // piutang
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('debt_date');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('status', 20)->default('belum_lunas');   // belum_lunas | sebagian | lunas
            $table->timestamps();
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10)->default('hutang');
            $table->date('pay_date');
            $table->decimal('amount', 15, 2);
            $table->foreignId('cash_account_id')->constrained();
            $table->string('method', 30);
            $table->string('proof_path')->nullable();
            $table->decimal('remaining', 15, 2)->default(0);
            $table->timestamps();
        });

        // ---------- Inventori ----------
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('warehouse_id')->constrained();
            $table->date('opname_date');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('system_qty')->default(0);
            $table->integer('physical_qty');
            $table->integer('diff')->default(0);
            $table->string('reason')->nullable();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->date('transfer_date');
            $table->text('note')->nullable();
            $table->string('status', 20)->default('selesai');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('qty');
        });

        // ---------- Pengaturan toko ----------
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->unsignedTinyInteger('day');            // 1 = Senin ... 7 = Minggu
            $table->string('open_time', 5)->nullable();
            $table->string('close_time', 5)->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['business_id', 'day']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->string('code', 40);
            $table->string('type', 20);                    // tunai | transfer | qris | ewallet | lainnya
            $table->string('bank_name')->nullable();
            $table->string('account_no', 60)->nullable();
            $table->string('account_holder')->nullable();
            $table->string('qris_no', 80)->nullable();
            $table->string('qris_image_path')->nullable();
            $table->unsignedBigInteger('cash_account_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'code']);
        });

        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->decimal('percentage', 6, 2);
            $table->string('kind', 20);                    // pajak | biaya_layanan
            $table->json('product_ids')->nullable();       // kosong = semua produk
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->string('type', 20);                    // struk | barcode
            $table->string('connection', 20);              // usb | bluetooth | jaringan
            $table->string('paper_size', 20);
            $table->boolean('auto_print')->default(false);
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('qr_tables', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('table_no', 20);
            $table->unsignedInteger('capacity')->nullable();
            $table->string('token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('qr_takeaways', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ---------- Sistem ----------
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('title');
            $table->text('body');
            $table->string('recipient', 20)->default('semua');   // semua | peran | pengguna
            $table->string('recipient_value', 60)->nullable();
            $table->string('type', 20)->default('info');
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $this->biz($table);
            $table->string('name');
            $table->string('type', 20);                    // data | lengkap
            $table->text('note')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 20);
            $table->string('menu', 60);
            $table->text('changes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('device')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('status', 20);                  // berhasil | gagal | keluar
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach ([
            'login_histories', 'audit_logs', 'backups', 'app_notifications', 'qr_takeaways', 'qr_tables', 'printers',
            'taxes', 'payment_methods', 'operating_hours', 'stock_transfer_items', 'stock_transfers',
            'stock_opname_items', 'stock_opnames', 'debt_payments', 'debts', 'cash_entries', 'finance_categories',
            'cash_accounts', 'sales_return_items', 'sales_returns', 'purchase_items', 'purchases', 'suppliers',
            'point_logs', 'members', 'bundle_items', 'product_variations',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
