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
    </div>
</div>
