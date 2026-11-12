<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fondasi multi usaha & multi cabang + kolom tambahan pada tabel yang sudah ada.
 * Semua kolom baru boleh kosong, jadi data lama (dan API Android) tetap aman.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Tabel baru ----------
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_type', 60)->default('Toko Kelontong');
            $table->string('logo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('pic_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'code']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 40);
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('all_branches')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['business_id', 'slug']);
        });

        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'branch_id']);
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('pic_name')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Stok per produk per gudang. products.stock = jumlah seluruh gudang (disalin otomatis).
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->nullable();
            $table->string('unit', 20)->default('pcs');
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });

        // ---------- Kolom tambahan di tabel lama ----------
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->index()->after('id');
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->string('photo_path')->nullable();
            $table->string('pin')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unique(['business_id', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->dropUnique('products_barcode_unique');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->change();
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->string('type', 30)->default('barang');        // barang|sewa|jasa|makanan_minuman|digital|paket
            $table->text('description')->nullable();
            $table->unsignedInteger('rental_period')->nullable();  // sewa: lama
            $table->string('rental_unit', 20)->nullable();         // sewa: jam|hari|minggu|bulan
            $table->unsignedInteger('service_duration')->nullable(); // jasa: menit
            $table->string('digital_code', 60)->nullable();        // digital: kode
            $table->decimal('digital_nominal', 15, 2)->nullable(); // digital: nominal
            $table->decimal('bundle_discount', 15, 2)->default(0); // paket: diskon
            $table->decimal('normal_price', 15, 2)->nullable();    // paket: harga normal (otomatis)
            $table->date('promo_start')->nullable();
            $table->date('promo_end')->nullable();
            $table->unique(['business_id', 'sku']);
            $table->unique(['business_id', 'barcode']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 12)->nullable();
            $table->text('note')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('shift', 20)->nullable();
            $table->string('type', 30)->default('langsung');
            $table->string('payment_status', 20)->default('lunas');
            $table->string('payment_ref')->nullable();
            $table->string('payment_proof')->nullable();
            $table->text('receipt_note')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->text('cancel_note')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->string('variation_label')->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->string('note')->nullable();
            $table->integer('returned_qty')->default(0);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->date('movement_date')->nullable();
            $table->string('source')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('proof_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('businesses');
    }
};
