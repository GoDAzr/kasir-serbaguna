<?php

namespace App\Http\Controllers;

use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Owner mengatur apa saja yang boleh dibuka / dilakukan kasir (disimpan di izin role). */
class CashierAccessController extends Controller
{
    public const PAGES = [
        'pos' => ['Kasir / POS', 'Membuka halaman kasir dan melakukan penjualan'],
        'transaksi' => ['Riwayat transaksi', 'Melihat daftar dan struk transaksi miliknya'],
        'pelanggan' => ['Pelanggan & member', 'Melihat dan mengelola data pelanggan'],
        'produk' => ['Produk', 'Melihat dan mengelola produk (termasuk ubah dan hapus)'],
        'laporan' => ['Laporan', 'Melihat laporan seluruh toko, bukan hanya transaksinya sendiri'],
    ];

    public const ACTIONS = [
        'pos_diskon' => ['Memberi diskon', 'Boleh mengisi diskon per barang dan diskon transaksi di kasir'],
        'pos_piutang' => ['Jual belum lunas (piutang)', 'Boleh menyimpan transaksi yang pembayarannya belum penuh'],
        'produk_cepat' => ['Mendaftarkan produk baru dari kasir', 'Jika barcode hasil scan belum terdaftar, kasir boleh langsung mengisi nama dan harga lalu menyimpannya'],
        'transaksi_batal' => ['Membatalkan transaksi', 'Boleh membatalkan transaksi yang sudah selesai'],
        'retur' => ['Retur penjualan', 'Boleh mencatat pengembalian barang dari pembeli'],
        'lihat_omzet' => ['Melihat total penjualan', 'Beranda kasir menampilkan jumlah rupiah penjualan hari ini'],
    ];

    private function roles()
    {
        return DB::table('roles')->where('business_id', Tenant::id())->where('cashier_ui', 1)
            ->whereNotIn('slug', ['owner', 'admin'])->orderBy('name')->get();
    }

    public function show(Request $r)
    {
        $roles = $this->roles();
        $cur = $roles->firstWhere('slug', $r->query('role')) ?? $roles->first();
        $perms = $cur ? (json_decode($cur->permissions ?? '[]', true) ?: []) : [];

        return view('pages.akses-kasir', [
            'roles' => $roles,
            'cur' => $cur,
            'perms' => $perms,
            'full' => in_array('*', $perms, true),
            'pages' => self::PAGES,
            'actions' => self::ACTIONS,
            'users' => $cur ? DB::table('users')->where('business_id', Tenant::id())->where('role', $cur->slug)->where('is_active', 1)->pluck('name')->all() : [],
        ]);
    }

    public function save(Request $r)
    {
        $slug = $r->validate(['role' => 'required|string|max:40'])['role'];
        $role = $this->roles()->firstWhere('slug', $slug);
        abort_unless($role, 404);

        $perms = json_decode($role->permissions ?? '[]', true) ?: [];
        if (in_array('*', $perms, true)) {
            return back()->with('err', 'Role ini punya akses penuh. Ubah lewat menu Hak Akses.');
        }

        $keys = array_merge(array_keys(self::PAGES), array_keys(self::ACTIONS));
        $on = array_values(array_intersect((array) $r->input('perm', []), $keys));
        // Izin lain di role ini (mis. inventori) tidak disentuh; beranda kasir selalu boleh
        $perms = array_values(array_unique(array_merge(array_diff($perms, $keys), $on, ['dashboard'])));

        DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($perms), 'updated_at' => now()]);
        Tenant::audit('ubah', 'Akses Kasir', ['role' => $slug, 'izin' => $on]);

        return redirect()->route('akses.kasir', ['role' => $slug])->with('ok', 'Akses ' . $role->name . ' disimpan.');
    }
}
