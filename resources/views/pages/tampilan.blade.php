@extends('layouts.app')
@section('title', 'Tampilan')
@section('subtitle', 'Mode gelap / terang dan background')
@section('own_errors', '1')
@section('content')
<form method="POST" enctype="multipart/form-data" class="panel form tampilan" id="tmpForm" novalidate>
    @csrf
    @if ($errors->any())<div class="flash bad">@foreach ($errors->all() as $er)<div>{{ $er }}</div>@endforeach</div>@endif

    <h3 class="sub-title" style="margin-top:0">Mode tampilan</h3>
    <div class="opt-row">
        @foreach ($themes as $k => $label)
            <label class="opt">
                <input type="radio" name="theme" value="{{ $k }}" @checked(old('theme', $ap['theme']) === $k)>
                <span class="opt-box">
                    <span class="mini mini-{{ $k }}"><i></i><i></i><i></i></span>
                    <b>{{ $label }}</b>
                </span>
            </label>
        @endforeach
    </div>

    @php($heroOn = old('hero_mode', $ap['hero'] ? 'custom' : 'default') === 'custom')
    <h3 class="sub-title">Warna header</h3>
    <div class="opt-row opt-row-2">
        <label class="opt">
            <input type="radio" name="hero_mode" value="default" @checked(! $heroOn)>
            <span class="opt-box"><span class="hero-swatch" style="background:linear-gradient(160deg,#34E07F,#14B856 45%,#0C8F3F)"></span><b>Bawaan (hijau)</b></span>
        </label>
        <label class="opt">
            <input type="radio" name="hero_mode" value="custom" @checked($heroOn)>
            <span class="opt-box"><span class="hero-swatch" id="heroSw" style="background:{{ old('hero_color', $ap['hero'] ?? '#2563eb') }}"></span><b>Warna sendiri</b></span>
        </label>
    </div>
    <div class="hero-pick" id="heroPick" @unless ($heroOn) hidden @endunless>
        <input type="color" name="hero_color" id="heroColor" value="{{ old('hero_color', $ap['hero'] ?? '#2563eb') }}" aria-label="Pilih warna header">
        <code id="heroHex">{{ old('hero_color', $ap['hero'] ?? '#2563eb') }}</code>
        @foreach (['#2563eb' => 'Biru', '#7c3aed' => 'Ungu', '#e11d48' => 'Merah', '#f97316' => 'Jingga', '#0ea5c6' => 'Toska', '#334155' => 'Abu gelap', '#ffd500' => 'Kuning'] as $hex => $nm)
            <button type="button" class="sw" data-color="{{ $hex }}" title="{{ $nm }}" style="background:{{ $hex }}"></button>
        @endforeach
    </div>

    <h3 class="sub-title">Efek kaca</h3>
    <label class="chk"><input type="checkbox" name="glass" value="1" @checked(old('theme') ? old('glass') : $ap['glass'])> Mode kaca: kartu, menu, dan sidebar jadi tembus pandang dengan blur</label>
    <small class="hint">Paling terlihat kalau memakai background gambar atau Malam Bintang.</small>

    <h3 class="sub-title">Background</h3>
    <div class="bg-grid">
        @foreach ($presets as $p)
            <label class="bgopt">
                <input type="radio" name="bg_key" value="{{ $p['key'] }}" data-type="{{ $p['type'] }}" data-url="{{ $p['url'] }}" @checked(old('bg_key', $ap['key']) === $p['key'])>
                <span class="bgopt-box">
                    <span class="bgthumb t-{{ $p['type'] }}" @if ($p['type'] === 'img') style="background-image:url('{{ $p['url'] }}')" @endif></span>
                    <b>{{ $p['label'] }}</b>
                </span>
            </label>
        @endforeach

        <label class="bgopt">
            <input type="radio" name="bg_key" value="photo" data-type="img" data-url="{{ $u->bg_photo ? asset('storage/' . $u->bg_photo) : '' }}" @checked(old('bg_key', $ap['key']) === 'photo')>
            <span class="bgopt-box">
                <span class="bgthumb t-photo" id="photoThumb" @if ($u->bg_photo) style="background-image:url('{{ asset('storage/' . $u->bg_photo) }}')" @endif>@unless ($u->bg_photo)<em>+ Foto sendiri</em>@endunless</span>
                <b>Foto saya</b>
            </span>
        </label>
    </div>

    <div class="fld" style="margin-top:14px">
        <label>Unggah foto sendiri</label>
        <input type="file" name="bg_photo" id="bgPhoto" accept="image/*">
        <small class="hint">Gambar jpg / png / webp, maksimal 6 MB. Foto langsung dipakai sebagai background setelah disimpan.</small>
        @if ($u->bg_photo)
            <label class="chk"><input type="checkbox" name="hapus_foto" value="1"> Hapus foto saya</label>
        @endif
    </div>

    <div class="fld" style="margin-top:14px">
        <label>Selubung background: <b id="veilVal">{{ old('bg_veil', $ap['veil']) }}</b>%</label>
        <input type="range" name="bg_veil" id="bgVeil" min="0" max="80" step="5" value="{{ old('bg_veil', $ap['veil']) }}">
        <small class="hint">Naikkan kalau tulisan sulit dibaca di atas background; turunkan supaya gambar lebih terang.</small>
    </div>

    <div class="actions"><button class="btn primary" type="submit">Simpan</button></div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tmpForm');
    var cssScene = @json(asset('css/bg-scene.css'));
    var sceneHtml = null;

    function layer() {
        var l = document.getElementById('bgLayer');
        if (!l) {
            l = document.createElement('div'); l.id = 'bgLayer'; l.className = 'bg-layer'; l.setAttribute('aria-hidden', 'true');
            document.body.insertBefore(l, document.body.firstChild);
        }
        return l;
    }

    function preview() {
        var t = form.querySelector('input[name=theme]:checked');
        if (t && window.applyTheme) { window.applyTheme(t.value); }
        var b = form.querySelector('input[name=bg_key]:checked');
        var veil = document.getElementById('bgVeil').value;
        document.getElementById('veilVal').textContent = veil;
        var l = document.getElementById('bgLayer');
        if (!b || b.value === 'plain' || (b.dataset.type === 'img' && !b.dataset.url)) {
            if (l) { l.remove(); }
            document.body.classList.remove('has-bg');
            return;
        }
        l = layer();
        l.style.setProperty('--veil', veil / 100);
        document.body.classList.add('has-bg');
        if (b.dataset.type === 'img') {
            l.innerHTML = ''; l.style.backgroundImage = "url('" + b.dataset.url + "')";
        } else if (b.dataset.type === 'scene') {
            l.style.backgroundImage = '';
            if (!document.getElementById('sceneCss')) {
                var k = document.createElement('link'); k.id = 'sceneCss'; k.rel = 'stylesheet'; k.href = cssScene; document.head.appendChild(k);
            }
            if (!l.querySelector('.bg-scene')) {
                l.innerHTML = '<div class="bg-scene">' + document.getElementById('sceneTpl').innerHTML + '</div>';
            }
        }
    }

    // ----- Warna header: pratinjau langsung (rumus sama dengan Appearance::heroCss di PHP) -----
    function heroCss(hex) {
        var lum = (0.299 * parseInt(hex.substr(1, 2), 16) + 0.587 * parseInt(hex.substr(3, 2), 16) + 0.114 * parseInt(hex.substr(5, 2), 16)) / 255;
        var light = lum > 0.65, fg = light ? '#0B1220' : '#FFFFFF', ink = light ? '#0B1220' : hex;
        var mix = function (p) { return 'color-mix(in srgb,var(--hero-2) ' + p + '%,#000)'; };
        return ':root{--hero-2:' + hex + ';--hero-1:color-mix(in srgb,var(--hero-2) 72%,#fff);--hero-3:color-mix(in srgb,var(--hero-2) 72%,#000);--hero-fg:' + fg + ';--hero-ink:' + ink + ';}'
            + 'html[data-theme="dark"] .hero{background:linear-gradient(160deg,' + mix(86) + ' 0%,' + mix(66) + ' 50%,' + mix(48) + ' 100%);}'
            + 'html[data-theme="dark"] .topbar-slot.is-fixed{background:linear-gradient(90deg,' + mix(86) + ',' + mix(66) + ');}'
            + 'html[data-theme="dark"] .icon-btn.btn-menu{color:var(--hero-2);}';
    }

    function heroPreview() {
        var mode = form.querySelector('input[name=hero_mode]:checked').value;
        var color = document.getElementById('heroColor').value;
        var st = document.getElementById('heroCss');
        document.getElementById('heroPick').hidden = mode !== 'custom';
        document.getElementById('heroHex').textContent = color;
        document.getElementById('heroSw').style.background = color;
        if (mode !== 'custom') { if (st) { st.remove(); } return; }
        if (!st) { st = document.createElement('style'); st.id = 'heroCss'; document.head.appendChild(st); }
        st.textContent = heroCss(color);
    }

    form.querySelectorAll('.sw').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById('heroColor').value = b.getAttribute('data-color');
            form.querySelector('input[name=hero_mode][value=custom]').checked = true;
            heroPreview();
        });
    });
    form.addEventListener('input', function (e) { if (e.target.id === 'heroColor') { form.querySelector('input[name=hero_mode][value=custom]').checked = true; heroPreview(); } });
    form.addEventListener('change', function (e) {
        if (e.target.name === 'hero_mode') { heroPreview(); }
        if (e.target.name === 'glass') { document.documentElement.setAttribute('data-glass', e.target.checked ? 'on' : 'off'); }
    });

    form.addEventListener('change', preview);
    form.addEventListener('input', function (e) { if (e.target.id === 'bgVeil') { preview(); } });

    // foto baru dipilih -> tampilkan langsung sebagai pratinjau
    document.getElementById('bgPhoto').addEventListener('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        var url = URL.createObjectURL(f);
        var radio = form.querySelector('input[name=bg_key][value=photo]');
        radio.dataset.url = url; radio.checked = true;
        var th = document.getElementById('photoThumb');
        th.style.backgroundImage = "url('" + url + "')"; th.innerHTML = '';
        preview();
    });
})();
</script>
<template id="sceneTpl">@include('partials.bg-scene')</template>
@endpush
