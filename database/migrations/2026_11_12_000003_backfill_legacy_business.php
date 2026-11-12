<?php

use App\Services\BusinessSetup;
use Illuminate\Database\Migrations\Migration;

/** Data lama (sebelum multi usaha) dibungkus ke satu usaha, stok dipindah ke Gudang Utama. Aman dijalankan berulang. */
return new class extends Migration
{
    public function up(): void
    {
        BusinessSetup::backfillLegacy();
    }

    public function down(): void
    {
        //
    }
};
