{{-- Tombol pengaturan (gerigi): membuka daftar Akun & Pengguna. Isi diambil dari config/menu.php --}}
@php
    $akunGroup = collect(config('menu.groups'))->firstWhere('title', 'Akun & Pengguna');
    $akunItems = [];
    foreach (($akunGroup['items'] ?? []) as $it) {
        if (! empty($it[4]) && ! \App\Support\Tenant::can($it[4])) { continue; }
        $url = $it[3] ?? null;
        if (! $url) {
            foreach ($it[2] as $sub) { if (! empty($sub[3])) { $url = $sub[3]; break; } }
        }
        $akunItems[] = [$it[0], $it[1], $url];
    }
@endphp
<div class="gear-wrap">
    <button class="icon-btn btn-gear" type="button" id="gearBtn" aria-haspopup="true" aria-expanded="false" aria-label="Pengaturan" title="Pengaturan">
        <svg class="ico"><use href="#i-gear"/></svg>
    </button>
    <div class="gear-menu g-toska" id="gearMenu" hidden>
        <div class="gear-title">Akun &amp; Pengguna</div>
        @foreach ($akunItems as $it)
            <a href="{{ $it[2] ? url($it[2]) : '#' }}" @if (! $it[2]) data-soon="{{ $it[0] }}" @endif>
                <span class="mi-ico"><svg class="ico"><use href="#i-{{ $it[1] }}"/></svg></span>{{ $it[0] }}
            </a>
        @endforeach
        <div class="gear-sep"></div>
        <button type="button" class="gear-theme" id="themeQuick" aria-label="Ganti mode gelap atau terang">
            <span class="mi-ico"><svg class="ico th-moon" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg><svg class="ico th-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></span>
            <span class="th-label">Mode Gelap</span>
        </button>
    </div>
</div>
