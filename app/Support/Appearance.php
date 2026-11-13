<?php

namespace App\Support;

use App\Models\User;

/**
 * Pengaturan tampilan per pengguna: tema (terang/gelap/ikuti sistem) dan background.
 *
 * Background bawaan:
 *  - plain               : polos (default)
 *  - scene:malam-bintang : ilustrasi CSS malam bintang
 *  - img:<nama-file>     : gambar apa pun yang ditaruh di public/backgrounds/
 *  - photo               : foto yang diunggah pengguna sendiri
 *
 * Menambah background baru = cukup taruh file gambar (jpg/png/webp) di public/backgrounds/.
 * Nama file jadi nama pilihan ("langit-senja.jpg" -> "Langit Senja").
 */
class Appearance
{
    public const THEMES = ['light' => 'Terang', 'dark' => 'Gelap', 'auto' => 'Ikuti perangkat'];

    public static function presets(): array
    {
        $list = [
            ['key' => 'plain', 'label' => 'Polos', 'type' => 'plain', 'url' => null],
            ['key' => 'scene:malam-bintang', 'label' => 'Malam Bintang', 'type' => 'scene', 'url' => null],
        ];

        $files = [];
        $dir = public_path('backgrounds');
        if (is_dir($dir)) {
            foreach (scandir($dir) ?: [] as $f) {
                if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $files[] = $dir . '/' . $f;
                }
            }
        }
        sort($files);
        foreach ($files as $f) {
            $name = basename($f);
            $list[] = [
                'key' => 'img:' . $name,
                'label' => ucwords(str_replace(['-', '_'], ' ', pathinfo($name, PATHINFO_FILENAME))),
                'type' => 'img',
                'url' => asset('backgrounds/' . rawurlencode($name)),
            ];
        }

        return $list;
    }

    public static function find(string $key): ?array
    {
        foreach (self::presets() as $p) {
            if ($p['key'] === $key) {
                return $p;
            }
        }

        return null;
    }

    /** Data tampilan siap pakai untuk layout. */
    public static function for(?User $u): array
    {
        $theme = $u?->theme ?: 'light';
        if (! isset(self::THEMES[$theme])) {
            $theme = 'light';
        }
        $key = $u?->bg_key ?: 'plain';
        $type = 'plain';
        $url = null;

        if ($key === 'photo' && $u?->bg_photo) {
            $type = 'img';
            $url = asset('storage/' . $u->bg_photo);
        } elseif ($key !== 'plain' && $key !== 'photo') {
            $p = self::find($key);
            if ($p) {
                $type = $p['type'];
                $url = $p['url'];
            } else {
                $key = 'plain';
            }
        } else {
            $key = 'plain';
        }

        return [
            'theme' => $theme,
            'key' => $key,
            'type' => $type,
            'url' => $url,
            'veil' => max(0, min(80, (int) ($u?->bg_veil ?? 20))),
        ];
    }
}
