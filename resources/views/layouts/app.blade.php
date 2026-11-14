{{-- Pengguna dengan role "tampilan kasir" memakai kerangka halaman yang berbeda (layouts/kasir) --}}
@if (\App\Support\Tenant::cashierUi())
    @include('layouts.kasir')
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Serbaguna UMKM') - Serbaguna UMKM</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @foreach (['base', 'sidebar', 'hero', 'toggle', 'content', 'menu', 'desktop', 'pages'] as $css)
        <link rel="stylesheet" href="{{ asset('css/dashboard/' . $css . '.css') }}?v={{ @filemtime(public_path('css/dashboard/' . $css . '.css')) }}">
    @endforeach
    <script>window.DASH_URL = "{{ route('dashboard') }}";</script>
    @stack('head')
    @include('partials.appearance-head')
</head>
<body>
    @include('partials.appearance-bg')
    @include('partials.icons')

    <div class="app" id="app">
        @include('partials.sidebar')
        <div class="backdrop" id="backdrop"></div>

        <div class="main">
            @include('partials.header-lite')

            <main class="page @yield('page_class')">
                @if (session('ok'))<div class="flash ok" role="status">{{ session('ok') }}</div>@endif
                @if (session('err'))<div class="flash bad" role="alert">{{ session('err') }}</div>@endif
                @if ($errors->any() && ! View::hasSection('own_errors'))
                    <div class="flash bad" role="alert">
                        <b>Periksa lagi isian Anda:</b>
                        <ul>@foreach ($errors->all() as $er)<li>{{ $er }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    @foreach (['sidebar', 'topbar', 'menu', 'toast'] as $js)
        <script src="{{ asset('js/dashboard/' . $js . '.js') }}?v={{ @filemtime(public_path('js/dashboard/' . $js . '.js')) }}"></script>
    @endforeach
    <script src="{{ asset('js/dashboard/money.js') }}?v={{ @filemtime(public_path('js/dashboard/money.js')) }}"></script>
    <script src="{{ asset('js/dashboard/pages.js') }}?v={{ @filemtime(public_path('js/dashboard/pages.js')) }}"></script>
    <script src="{{ asset('js/dashboard/theme.js') }}?v={{ @filemtime(public_path('js/dashboard/theme.js')) }}"></script>
    @stack('scripts')
</body>
</html>
@endif
