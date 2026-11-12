@extends('layouts.app')
@section('title', 'Saldo Kas')
@section('content')
<div class="cards">
    @foreach ($rows as $r)
        <div class="stat"><small>{{ $r['name'] }} <span class="badge gray">{{ \App\Support\Fmt::label($r['type']) }}</span></small><b>{{ \App\Support\Fmt::money($r['saldo']) }}</b>
            <span class="muted">Awal {{ \App\Support\Fmt::money($r['awal']) }} &middot; Masuk {{ \App\Support\Fmt::money($r['masuk']) }} &middot; Keluar {{ \App\Support\Fmt::money($r['keluar']) }}</span></div>
    @endforeach
    <div class="stat total"><small>Total semua akun</small><b>{{ \App\Support\Fmt::money($rows->sum('saldo')) }}</b></div>
</div>
<p><a class="btn ghost" href="{{ url('/m/riwayat_kas') }}">Lihat riwayat kas</a></p>
@endsection
