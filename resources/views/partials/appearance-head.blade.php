{{-- Tema & background pengguna: dipasang di <head> supaya tidak berkedip saat halaman dimuat --}}
@php($ap = \App\Support\Appearance::for(\App\Support\Tenant::user()))
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">
@if ($ap['type'] === 'scene')
    <link rel="stylesheet" href="{{ asset('css/bg-scene.css') }}">
@endif
<script>
(function () {
    var t = @json($ap['theme']);
    var dark = t === 'dark' || (t === 'auto' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
    var h = document.documentElement;
    h.setAttribute('data-theme', dark ? 'dark' : 'light');
    h.setAttribute('data-theme-pref', t);
    window.APP_THEME = t;
    window.CSRF = @json(csrf_token());
    window.THEME_URL = @json(route('akun.tema'));
})();
</script>
