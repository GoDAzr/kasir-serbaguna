<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $t) {
            if (! Schema::hasColumn('roles', 'cashier_ui')) {
                $t->boolean('cashier_ui')->default(false);   // true = pengguna role ini memakai tampilan kasir
            }
        });

        DB::table('roles')->where('slug', 'kasir')->update(['cashier_ui' => 1]);

        // Izin baru yang lebih rinci: role lama tetap bisa melakukan hal yang sama seperti sebelumnya
        $more = [
            'transaksi' => ['transaksi_batal', 'retur'],
            'pos' => ['pos_diskon', 'pos_piutang'],
            'laporan' => ['lihat_omzet'],
        ];
        foreach (DB::table('roles')->get() as $r) {
            $p = json_decode($r->permissions ?? '[]', true) ?: [];
            if (in_array('*', $p, true)) {
                continue;
            }
            $new = $p;
            foreach ($more as $have => $add) {
                if (in_array($have, $p, true)) {
                    $new = array_merge($new, $add);
                }
            }
            $new = array_values(array_unique($new));
            if ($new !== $p) {
                DB::table('roles')->where('id', $r->id)->update(['permissions' => json_encode($new)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $t) {
            $t->dropColumn('cashier_ui');
        });
    }
};
