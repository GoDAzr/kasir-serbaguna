<?php

namespace App\Http\Controllers;

use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagesController extends Controller
{
    // ---------- Akun saya ----------
    public function akun()
    {
        return view('pages.akun', ['u' => Tenant::user()]);
    }

    public function akunSave(Request $r)
    {
        $u = Tenant::user();
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($u->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($u->id)],
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ], [
            'name.required' => 'Nama wajib diisi.', 'username.required' => 'Username wajib diisi.', 'username.unique' => 'Username sudah dipakai.',
            'username.alpha_dash' => 'Username hanya huruf, angka, strip, dan garis bawah.',
            'email.required' => 'Email wajib diisi.', 'email.unique' => 'Email sudah dipakai.', 'email.email' => 'Format email tidak valid.',
        ]);
        if ($r->hasFile('photo')) {
            if ($u->photo_path) {
                Storage::disk('public')->delete($u->photo_path);
            }
            $data['photo_path'] = $r->file('photo')->store('uploads/' . $u->business_id, 'public');
        }
        unset($data['photo']);
        $u->update($data);
        Tenant::audit('ubah', 'Akun', ['user' => $u->id]);

        return back()->with('ok', 'Akun diperbarui.');
    }

    public function password()
    {
        return view('pages.password');
    }

    public function passwordSave(Request $r)
    {
        $r->validate([
            'old_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi.', 'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.', 'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);
        $u = Tenant::user();
        if (! Hash::check($r->old_password, $u->password)) {
            throw ValidationException::withMessages(['old_password' => 'Password lama salah.']);
        }
        $u->forceFill(['password' => $r->password])->save();
        Tenant::audit('ubah', 'Password', ['user' => $u->id]);

        return back()->with('ok', 'Password berhasil diubah.');
    }

    public function pin()
    {
        return view('pages.pin', ['hasPin' => (bool) Tenant::user()->pin]);
    }

    public function pinSave(Request $r)
    {
        $u = Tenant::user();
        $r->validate([
            'old_pin' => $u->pin ? 'required' : 'nullable',
            'pin' => 'required|digits_between:4,6|confirmed',
        ], [
            'old_pin.required' => 'PIN lama wajib diisi.', 'pin.required' => 'PIN baru wajib diisi.',
            'pin.digits_between' => 'PIN harus 4 sampai 6 angka.', 'pin.confirmed' => 'Konfirmasi PIN tidak sama.',
        ]);
        if ($u->pin && ! Hash::check($r->old_pin, $u->pin)) {
            throw ValidationException::withMessages(['old_pin' => 'PIN lama salah.']);
        }
        $u->forceFill(['pin' => $r->pin])->save();
        Tenant::audit('ubah', 'PIN', ['user' => $u->id]);

        return back()->with('ok', 'PIN berhasil diubah.');
    }

    // ---------- Profil toko & jam ----------
    public const BIZ_TYPES = ['Toko Kelontong', 'Minimarket', 'Warung / Kuliner', 'Fashion', 'Elektronik', 'Jasa', 'Penyewaan', 'Produk Digital', 'Lainnya'];

    public function toko()
    {
        return view('pages.toko', ['b' => DB::table('businesses')->find(Tenant::id()), 'types' => self::BIZ_TYPES]);
    }

    public function tokoSave(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'business_type' => ['required', Rule::in(self::BIZ_TYPES)],
            'address' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|max:2048',
        ], ['name.required' => 'Nama toko wajib diisi.', 'business_type.required' => 'Jenis usaha wajib dipilih.', 'address.required' => 'Alamat wajib diisi.']);
        if ($r->hasFile('logo')) {
            $data['logo_path'] = $r->file('logo')->store('uploads/' . Tenant::id(), 'public');
        }
        unset($data['logo']);
        DB::table('businesses')->where('id', Tenant::id())->update($data + ['updated_at' => now()]);
        Tenant::audit('ubah', 'Profil Toko', $data);

        return back()->with('ok', 'Profil toko disimpan.');
    }

    public function jam()
    {
        $rows = DB::table('operating_hours')->where('business_id', Tenant::id())->orderBy('day')->get()->keyBy('day');

        return view('pages.jam', ['rows' => $rows, 'days' => [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu']]);
    }

    public function jamSave(Request $r)
    {
        $in = $r->input('h', []);
        $errors = [];
        $names = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        foreach ($names as $d => $n) {
            $x = $in[$d] ?? [];
            if (! empty($x['is_closed'])) {
                continue;
            }
            if (empty($x['open']) || empty($x['close'])) {
                $errors["h.$d"] = "$n: jam buka dan jam tutup wajib diisi (atau tandai libur).";
            } elseif ($x['close'] <= $x['open']) {
                $errors["h.$d"] = "$n: jam tutup harus setelah jam buka.";
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        foreach ($names as $d => $n) {
            $x = $in[$d] ?? [];
            $closed = ! empty($x['is_closed']);
            DB::table('operating_hours')->updateOrInsert(['business_id' => Tenant::id(), 'day' => $d], [
                'open_time' => $closed ? null : $x['open'], 'close_time' => $closed ? null : $x['close'], 'is_closed' => $closed,
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }
        Tenant::audit('ubah', 'Jam Operasional', $in);

        return back()->with('ok', 'Jam operasional disimpan.');
    }

    // ---------- Info keuangan / stok ----------
    public function saldoKas()
    {
        $b = Tenant::id();
        $in = ['cash_in', 'capital', 'income', 'sale', 'receivable_payment'];
        $rows = DB::table('cash_accounts')->where('business_id', $b)->get()->map(function ($a) use ($in, $b) {
            $e = DB::table('cash_entries')->where('cash_account_id', $a->id)->get(['type', 'amount']);
            $masuk = $e->whereIn('type', $in)->sum('amount');
            $keluar = $e->whereNotIn('type', $in)->sum('amount');

            return ['name' => $a->name, 'type' => $a->type, 'awal' => (float) $a->opening_balance, 'masuk' => (float) $masuk, 'keluar' => (float) $keluar,
                'saldo' => (float) $a->opening_balance + $masuk - $keluar];
        });

        return view('pages.saldo', compact('rows'));
    }

    public function notifikasi()
    {
        $b = Tenant::id();
        $u = Tenant::user();
        $low = DB::table('stocks as s')->join('products as p', 'p.id', '=', 's.product_id')->join('warehouses as w', 'w.id', '=', 's.warehouse_id')
            ->where('s.business_id', $b)->whereColumn('s.quantity', '<=', DB::raw('COALESCE(s.min_stock, p.min_stock)'))
            ->get(['p.name', 'w.name as gudang', 's.quantity']);
        $due = DB::table('debts')->where('business_id', $b)->where('status', '!=', 'lunas')->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDays(7)->toDateString())->orderBy('due_date')->get();
        $custom = DB::table('app_notifications')->where('business_id', $b)
            ->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()))
            ->where(function ($q) use ($u) {
                $q->where('recipient', 'semua')
                    ->orWhere(fn ($w) => $w->where('recipient', 'peran')->where('recipient_value', $u->role))
                    ->orWhere(fn ($w) => $w->where('recipient', 'pengguna')->where('recipient_value', $u->email));
            })->orderByDesc('id')->limit(30)->get();

        return view('pages.notifikasi', compact('low', 'due', 'custom'));
    }

    public function backupDownload(int $id)
    {
        abort_unless(Tenant::can('sistem'), 403);
        $b = DB::table('backups')->where('id', $id)->where('business_id', Tenant::id())->firstOrFail();
        abort_unless($b->file_path && Storage::disk('local')->exists($b->file_path), 404);

        return Storage::disk('local')->download($b->file_path, $b->name);
    }

    // ---------- QR publik ----------
    public function qr(string $token)
    {
        $t = DB::table('qr_tables')->where('token', $token)->where('is_active', 1)->first();
        $kind = 'Meja';
        if ($t) {
            $label = 'Meja ' . $t->table_no;
        } else {
            $t = DB::table('qr_takeaways')->where('token', $token)->where('is_active', 1)->firstOrFail();
            $label = $t->name;
            $kind = 'Takeaway';
        }
        $biz = DB::table('businesses')->find($t->business_id);
        $branch = DB::table('branches')->where('id', $t->branch_id)->value('name');

        return view('pages.qr', compact('label', 'kind', 'biz', 'branch', 'token'));
    }
}
