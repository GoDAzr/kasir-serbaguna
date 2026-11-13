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
