<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (! Schema::hasColumn('users', 'theme')) {
                $t->string('theme', 10)->default('light');      // light | dark | auto
            }
            if (! Schema::hasColumn('users', 'bg_key')) {
                $t->string('bg_key', 120)->default('plain');    // plain | scene:malam-bintang | img:nama-file | photo
            }
            if (! Schema::hasColumn('users', 'bg_photo')) {
                $t->string('bg_photo')->nullable();             // foto milik sendiri (storage publik)
            }
            if (! Schema::hasColumn('users', 'bg_veil')) {
                $t->unsignedTinyInteger('bg_veil')->default(20); // selubung 0-80 (%)
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['theme', 'bg_key', 'bg_photo', 'bg_veil']);
        });
    }
};
