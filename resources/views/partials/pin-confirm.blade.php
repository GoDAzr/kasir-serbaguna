{{-- Kolom PIN untuk tindakan penting. Tampil hanya bila pengguna sudah membuat PIN. --}}
@if (\App\Support\Tenant::user()?->pin)
    <div class="fld {{ $errors->has('pin') ? 'has-err' : '' }}"><label>PIN Anda<i class="req">*</i></label>
        <input type="password" name="pin" inputmode="numeric" maxlength="6" autocomplete="off" placeholder="4-6 angka">
        <small class="hint">Masukkan PIN untuk memastikan ini benar-benar Anda.</small></div>
@else
    <p class="hint full">Tips: buat PIN di <a href="{{ route('akun.pin') }}">Akun &gt; Ubah PIN</a> agar tindakan ini minta konfirmasi PIN.</p>
@endif
