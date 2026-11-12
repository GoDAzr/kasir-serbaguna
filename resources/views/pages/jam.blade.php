@extends('layouts.app')
@section('title', 'Jam Operasional')
@section('own_errors', '1')
@section('content')
@if ($errors->any())<div class="flash bad"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<form method="POST" class="panel form" novalidate>
    @csrf
    @foreach ($days as $d => $name)
        @php $r = $rows[$d] ?? null; $closed = old("h.$d.is_closed", $r->is_closed ?? false); @endphp
        <div class="hours-row">
            <b>{{ $name }}</b>
            <label class="inl">Buka <input type="time" name="h[{{ $d }}][open]" value="{{ old("h.$d.open", $r->open_time ?? '') }}"></label>
            <label class="inl">Tutup <input type="time" name="h[{{ $d }}][close]" value="{{ old("h.$d.close", $r->close_time ?? '') }}"></label>
            <label class="chk"><input type="checkbox" name="h[{{ $d }}][is_closed]" value="1" @checked($closed)> Libur</label>
        </div>
    @endforeach
    <div class="actions"><button class="btn primary" type="submit">Simpan</button></div>
</form>
@endsection
