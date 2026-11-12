@extends('layouts.app')
@section('title', 'Profil Toko')
@section('own_errors', '1')
@section('content')
<form method="POST" enctype="multipart/form-data" class="panel form" novalidate>
    @csrf
    <div class="grid">
        <div class="fld {{ $errors->has('name') ? 'has-err' : '' }}"><label>Nama toko<i class="req">*</i></label><input type="text" name="name" value="{{ old('name', $b->name) }}"><small class="err">{{ $errors->first('name') }}</small></div>
        <div class="fld {{ $errors->has('business_type') ? 'has-err' : '' }}"><label>Jenis usaha<i class="req">*</i></label>
            <select name="business_type"><option value="">- Pilih -</option>@foreach ($types as $t)<option @selected(old('business_type', $b->business_type) === $t)>{{ $t }}</option>@endforeach</select><small class="err">{{ $errors->first('business_type') }}</small></div>
        <div class="fld full {{ $errors->has('address') ? 'has-err' : '' }}"><label>Alamat<i class="req">*</i></label><textarea name="address" rows="2">{{ old('address', $b->address) }}</textarea><small class="err">{{ $errors->first('address') }}</small></div>
        <div class="fld"><label>Nomor telepon</label><input type="tel" name="phone" value="{{ old('phone', $b->phone) }}"></div>
        <div class="fld {{ $errors->has('email') ? 'has-err' : '' }}"><label>Email</label><input type="email" name="email" value="{{ old('email', $b->email) }}"><small class="err">{{ $errors->first('email') }}</small></div>
        <div class="fld"><label>Website</label><input type="text" name="website" value="{{ old('website', $b->website) }}"></div>
        <div class="fld"><label>Logo toko</label><input type="file" name="logo" accept="image/*">@if ($b->logo_path)<img class="thumb big" src="{{ asset('storage/' . $b->logo_path) }}" alt="">@endif<small class="err">{{ $errors->first('logo') }}</small></div>
        <div class="fld full"><label>Deskripsi</label><textarea name="description" rows="3">{{ old('description', $b->description) }}</textarea></div>
    </div>
    <div class="actions"><button class="btn primary" type="submit">Simpan</button></div>
</form>
@endsection
