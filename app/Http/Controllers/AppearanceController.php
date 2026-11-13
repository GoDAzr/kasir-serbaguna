<?php

namespace App\Http\Controllers;

use App\Support\Appearance;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AppearanceController extends Controller
{
    public function show()
    {
        $u = Tenant::user();

        return view('pages.tampilan', [
            'u' => $u,
            'ap' => Appearance::for($u),
            'presets' => Appearance::presets(),
            'themes' => Appearance::THEMES,
        ]);
    }

    public function save(Request $r)
    {
        $u = Tenant::user();
        $keys = array_column(Appearance::presets(), 'key');
        $keys[] = 'photo';

        $data = $r->validate([
            'theme' => ['required', Rule::in(array_keys(Appearance::THEMES))],
            'bg_key' => ['required', Rule::in($keys)],
            'bg_veil' => 'required|integer|min:0|max:80',
            'bg_photo' => 'nullable|image|max:6144',
        ], [
            'bg_photo.image' => 'File harus berupa gambar (jpg, png, webp).',
            'bg_photo.max' => 'Ukuran foto maksimal 6 MB.',
            'bg_key.in' => 'Pilihan background tidak dikenal.',
        ]);

        $photo = $u->bg_photo;
        if ($r->hasFile('bg_photo')) {
            if ($photo) {
                Storage::disk('public')->delete($photo);
            }
            $photo = $r->file('bg_photo')->store('backgrounds/' . $u->business_id, 'public');
            $data['bg_key'] = 'photo';
        }
        if ($data['bg_key'] === 'photo' && ! $photo) {
            return back()->withInput()->withErrors(['bg_photo' => 'Pilih foto dulu untuk dipakai sebagai background.']);
        }
        if ($r->boolean('hapus_foto') && $photo) {
            Storage::disk('public')->delete($photo);
            $photo = null;
            if ($data['bg_key'] === 'photo') {
                $data['bg_key'] = 'plain';
            }
        }

        $u->forceFill([
            'theme' => $data['theme'],
            'bg_key' => $data['bg_key'],
            'bg_veil' => $data['bg_veil'],
            'bg_photo' => $photo,
        ])->save();

        return redirect()->route('akun.tampilan')->with('ok', 'Tampilan disimpan.');
    }

    /** Tombol cepat terang/gelap di menu pengaturan (AJAX). */
    public function quickTheme(Request $r)
    {
        $data = $r->validate(['theme' => ['required', Rule::in(array_keys(Appearance::THEMES))]]);
        Tenant::user()->forceFill(['theme' => $data['theme']])->save();

        return response()->json(['ok' => true, 'theme' => $data['theme']]);
    }
}
