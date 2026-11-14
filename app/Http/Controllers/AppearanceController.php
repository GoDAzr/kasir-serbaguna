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
            'glass' => 'nullable|boolean',
            'hero_mode' => ['required', Rule::in(['default', 'custom'])],
            'hero_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'hero_color.regex' => 'Warna header tidak valid.',
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

        $hero = $data['hero_mode'] === 'custom' ? strtolower((string) ($data['hero_color'] ?? '')) : null;
        if ($data['hero_mode'] === 'custom' && ! $hero) {
            return back()->withInput()->withErrors(['hero_color' => 'Pilih warna header dulu.']);
        }

        $u->forceFill([
            'hero_color' => $hero,
            'glass' => $r->boolean('glass'),
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
