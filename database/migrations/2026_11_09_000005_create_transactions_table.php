<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 40)->unique();
            // UUID dari aplikasi Android agar kirim ulang (retry/offline sync) tidak membuat transaksi ganda
            $table->uuid('client_uuid')->nullable()->unique();
            $table->foreignId('user_id')->constrained();            // kasir
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->decimal('paid', 15, 2);
            $table->decimal('change', 15, 2)->default(0);
            $table->string('payment_method', 20)->default('cash'); // cash | transfer | qris | ewallet
            $table->string('status', 20)->default('paid');        // paid | void
            $table->string('note')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
