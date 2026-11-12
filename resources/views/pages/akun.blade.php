@extends('layouts.app')
@section('title', 'Edit Akun')
@section('subtitle', 'Data akun Anda')
@section('own_errors', '1')
@section('content')
<form method="POST" enctype="multipart/form-data" class="panel form" novalidate>
    @csrf
    <div class="grid">
        <div class="fld {{ $errors->has('name') ? 'has-err' : '' }}"><label>Nama<i class="req">*</i></label><input type="text" name="name" value="{{ old('name', $u->name) }}"><small class="err">{{ $errors->first('name') }}</small></div>
        <div class="fld {{ $errors->has('username') ? 'has-err' : '' }}"><label>Username<i class="req">*</i></label><input type="text" name="username" value="{{ old('username', $u->username) }}"><small class="err">{{ $errors->first('username') }}</small></div>
        <div class="fld {{ $errors->has('email') ? 'has-err' : '' }}"><label>Email<i class="req">*</i></label><input type="email" name="email" value="{{ old('email', $u->email) }}"><small class="err">{{ $errors->first('email') }}</small></div>
        <div class="fld"><label>Nomor HP</label><input type="tel" name="phone" value="{{ old('phone', $u->phone) }}"><small class="err">{{ $errors->first('phone') }}</small></div>
        <div class="fld full"><label>Foto profil</label><input type="file" name="photo" accept="image/*">
            @if ($u->photo_path)<img class="thumb big" src="{{ asset('storage/' . $u->photo_path) }}" alt="">@endif<small class="err">{{ $errors->first('photo') }}</small></div>
    </div>
    <div class="actions"><button class="btn primary" type="submit">Simpan</button></div>
</form>
@endsection
