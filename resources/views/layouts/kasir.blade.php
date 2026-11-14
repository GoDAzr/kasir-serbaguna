{{-- Kerangka halaman untuk kasir: bar atas biru + menu atas (desktop) / tab bawah (HP), tanpa sidebar owner --}}
@php
    $T = \App\Support\Tenant::class;
    $bizName = \Illuminate\Support\Facades\DB::table('businesses')->where('id', $T::id())->value('name') ?: 'Serbaguna UMKM';
    $nav = array_values(array_filter([
        ['Beranda', 'home', route('kasir'), request()->is('kasir'), true],
        ['Kasir', 'pos', route('pos'), request()->is('pos*'), $T::can('pos')],
        ['Riwayat', 'receipt', route('transaksi'), request()->is('transaksi*'), $T::can('transaksi')],
        ['Retur', 'swap', route('retur.list'), request()->is('retur*'), $T::can('retur')],
        ['Pelanggan', 'users', url('/m/pelanggan'), request()->is('m/pelanggan*'), $T::can('pelanggan')],
        ['Produk', 'box', url('/m/produk'), request()->is('m/produk*'), $T::can('produk')],
        ['Akun', 'user', route('akun'), request()->is('akun*'), true],
    ], fn ($n) => $n[4]));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir') - {{ $bizName }}</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @foreach (['base', 'hero', 'toggle', 'content', 'menu', 'pages'] as $css)
        <link rel="stylesheet" href="{{ asset('css/dashboard/' . $css . '.css') }}">
    @endforeach
    <script>window.DASH_URL = "{{ route('kasir') }}";</script>
    @stack('head')
    @include('partials.appearance-head')
</head>
<body class="kasir-body">
    @include('partials.appearance-bg')
    @include('partials.icons')

    <header class="k-top">
        <div class="k-top-in">
            <a class="k-brand" href="{{ route('kasir') }}">
                <img src="{{ asset('images/logo4.png') }}" alt="">
                <span><b>{{ $bizName }}</b><small>Mode Kasir</small></span>
            </a>

            <nav class="k-nav" aria-label="Menu kasir">
                @foreach ($nav as [$label, $icon, $href, $on])
                    <a href="{{ $href }}" class="{{ $on ? 'on' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="k-user">
                <div class="k-who"><b>{{ $T::user()->name }}</b><small>{{ $T::role()?->name ?? 'Kasir' }}</small></div>
                @include('partials.settings-menu')
                <form method="POST" action="{{ route('logout') }}" style="display:contents">
                    @csrf
                    <button class="icon-btn btn-out" type="submit" aria-label="Keluar"><svg class="ico"><use href="#i-logout"/></svg></button>
                </form>
            </div>
        </div>
    </header>

    <main class="k-page page @yield('page_class')">
        @if (session('ok'))<div class="flash ok" role="status">{{ session('ok') }}</div>@endif
        @if (session('err'))<div class="flash bad" role="alert">{{ session('err') }}</div>@endif
        @if ($errors->any() && ! View::hasSection('own_errors'))
            <div class="flash bad" role="alert">
                <b>Periksa lagi isian Anda:</b>
                <ul>@foreach ($errors->all() as $er)<li>{{ $er }}</li>@endforeach</ul>
            </div>
        @endif
        @hasSection('subtitle')<p class="k-sub">@yield('subtitle')</p>@endif
        @yield('content')
    </main>

    <nav class="k-tabs" aria-label="Menu kasir">
        @foreach ($nav as [$label, $icon, $href, $on])
            <a href="{{ $href }}" class="{{ $on ? 'on' : '' }}"><svg class="ico"><use href="#i-{{ $icon }}"/></svg>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script src="{{ asset('js/dashboard/toast.js') }}"></script>
    <script src="{{ asset('js/dashboard/pages.js') }}"></script>
    <script src="{{ asset('js/dashboard/theme.js') }}"></script>
    <script src="{{ asset('js/dashboard/clock.js') }}"></script>
    <script src="{{ asset('js/dashboard/kasir.js') }}"></script>
    @stack('scripts')
</body>
</html>
