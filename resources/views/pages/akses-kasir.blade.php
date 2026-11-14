@extends('layouts.app')
@section('title', 'Akses Kasir')
@section('subtitle', 'Atur apa saja yang boleh dibuka dan dilakukan kasir')
@section('content')
@if (! $cur)
    <div class="panel">
        <h3 class="sub-title" style="margin-top:0">Belum ada role kasir</h3>
        <p class="muted">Role yang memakai <b>tampilan kasir</b> belum ada. Buka <a href="{{ url('/m/role') }}">Hak Akses</a>, ubah role Kasir atau buat role baru, lalu centang <b>Pakai tampilan kasir</b>.</p>
    </div>
@else
    <form method="POST" action="{{ route('akses.kasir') }}" class="panel tampilan-kasir">
        @csrf
        <div class="fld">
            <label for="roleSel">Role kasir</label>
            <select name="role" id="roleSel" data-url="{{ route('akses.kasir') }}">
                @foreach ($roles as $r)<option value="{{ $r->slug }}" @selected($r->slug === $cur->slug)>{{ $r->name }}</option>@endforeach
            </select>
            <small class="hint">
                @if ($users) Dipakai oleh: {{ implode(', ', $users) }}. @else Belum ada pengguna dengan role ini. @endif
                Kasir memakai tampilan khusus: beranda sederhana tanpa menu owner. <a href="{{ url('/m/pengguna/create') }}">Tambah kasir baru</a>
            </small>
        </div>

        @if ($full)
            <div class="flash bad">Role ini punya akses penuh, jadi pengaturan di bawah tidak berlaku. Ubah izinnya di <a href="{{ url('/m/role') }}">Hak Akses</a>.</div>
        @endif

        <h3 class="sub-title">Halaman yang boleh dibuka</h3>
        @foreach ($pages as $key => [$label, $desc])
            <div class="ak-row">
                <div><b>{{ $label }}</b><small>{{ $desc }}</small></div>
                <label class="switch"><input type="checkbox" name="perm[]" value="{{ $key }}" @checked($full || in_array($key, $perms, true)) @disabled($full)><span></span></label>
            </div>
        @endforeach

        <h3 class="sub-title">Tindakan yang boleh dilakukan</h3>
        @foreach ($actions as $key => [$label, $desc])
            <div class="ak-row">
                <div><b>{{ $label }}</b><small>{{ $desc }}</small></div>
                <label class="switch"><input type="checkbox" name="perm[]" value="{{ $key }}" @checked($full || in_array($key, $perms, true)) @disabled($full)><span></span></label>
            </div>
        @endforeach

        <div class="actions">
            <button class="btn primary" type="submit" @disabled($full)>Simpan</button>
            <a class="btn ghost" href="{{ url('/m/role') }}">Pengaturan role lengkap</a>
        </div>
    </form>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var sel = document.getElementById('roleSel');
    if (sel) { sel.addEventListener('change', function () { location.href = sel.getAttribute('data-url') + '?role=' + encodeURIComponent(sel.value); }); }
})();
</script>
@endpush
