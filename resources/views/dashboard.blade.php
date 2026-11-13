<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Serbaguna UMKM</title>

    {{-- Ikon tab browser --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tampilan: public/css/dashboard/*.css (urutan jangan ditukar) --}}
    @foreach (['base', 'sidebar', 'hero', 'toggle', 'content', 'menu', 'desktop', 'pages'] as $css)
        <link rel="stylesheet" href="{{ asset('css/dashboard/' . $css . '.css') }}">
    @endforeach
    @include('partials.appearance-head')
</head>
<body>
    @include('partials.appearance-bg')
    @include('partials.icons')

    <div class="app" id="app">
        @include('partials.sidebar')

        <div class="backdrop" id="backdrop"></div>

        <div class="main">
            @include('partials.header')
            @include('partials.content')
        </div>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    {{-- Perilaku: public/js/dashboard/*.js --}}
    @foreach (['sidebar', 'topbar', 'menu', 'toast', 'clock', 'summary', 'theme'] as $js)
        <script src="{{ asset('js/dashboard/' . $js . '.js') }}"></script>
    @endforeach
</body>
</html>
