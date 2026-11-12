@extends('layouts.app')
@section('title', 'Ubah PIN')
@section('subtitle', $hasPin ? 'PIN sudah diatur' : 'Anda belum punya PIN')
@section('own_errors', '1')
@section('content')
<form method="POST" class="panel form narrow" novalidate>
    @csrf
    @if ($hasPin)
        <div class="fld {{ $errors->has('old_pin') ? 'has-err' : '' }}"><label>PIN lama<i class="req">*</i></label><input type="password" name="old_pin" inputmode="numeric" maxlength="6"><small class="err">{{ $errors->first('old_pin') }}</small></div>
    @endif
    <div class="fld {{ $errors->has('pin') ? 'has-err' : '' }}"><label>PIN baru (4-6 angka)<i class="req">*</i></label><input type="password" name="pin" inputmode="numeric" maxlength="6"><small class="err">{{ $errors->first('pin') }}</small></div>
    <div class="fld"><label>Konfirmasi PIN baru<i class="req">*</i></label><input type="password" name="pin_confirmation" inputmode="numeric" maxlength="6"></div>
    <div class="actions"><button class="btn primary" type="submit">Simpan PIN</button></div>
</form>
@endsection
