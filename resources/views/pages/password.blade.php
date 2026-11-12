@extends('layouts.app')
@section('title', 'Ubah Password')
@section('own_errors', '1')
@section('content')
<form method="POST" class="panel form narrow" novalidate>
    @csrf
    @foreach ([['old_password', 'Password lama'], ['password', 'Password baru (min. 8 karakter)'], ['password_confirmation', 'Konfirmasi password baru']] as [$n, $l])
        <div class="fld {{ $errors->has($n) ? 'has-err' : '' }}"><label>{{ $l }}<i class="req">*</i></label><input type="password" name="{{ $n }}" autocomplete="{{ $n === 'old_password' ? 'current-password' : 'new-password' }}"><small class="err">{{ $errors->first($n) }}</small></div>
    @endforeach
    <div class="actions"><button class="btn primary" type="submit">Ubah password</button></div>
</form>
@endsection
