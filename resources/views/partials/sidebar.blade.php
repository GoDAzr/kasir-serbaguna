{{-- Menu samping: logo, gembok, Dashboard, menu bertingkat, akun --}}
<aside class="sidebar" id="sidebar" aria-label="Menu utama">
    <div class="sidebar-head">
        <img src="{{ asset('images/logo4.png') }}" alt="Serbaguna UMKM">
        <div class="head-btns">
            <button class="menu-lock" id="menuLock" type="button" aria-pressed="false" title="Menu menutup otomatis setelah dipilih (klik untuk kunci)">
                <svg class="ico"><use href="#i-unlock"/></svg>
            </button>
            <button class="menu-close" id="menuClose" type="button" aria-label="Tutup menu">
                <svg class="ico"><use href="#i-close"/></svg>
            </button>
        </div>
    </div>

    <nav class="nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" id="navDashboard" title="Dashboard"><span class="chip c-home"><svg class="ico"><use href="#i-home"/></svg></span>Dashboard</a>

        <div class="nav-label">Menu</div>
        @include('partials.menu-groups', ['variant' => 'side'])

        <div class="nav-label">Akun</div>
        <form method="POST" action="{{ route('logout') }}" data-confirm="Yakin ingin keluar dari akun ini?" data-confirm-yes="Ya, keluar" data-confirm-no="Tidak" data-confirm-title="Keluar">
            @csrf
            <button type="submit" class="nav-btn"><span class="chip c-out"><svg class="ico"><use href="#i-logout"/></svg></span>Keluar</button>
        </form>
    </nav>

    <div class="user-card">
        @if (! empty($userPhoto))
            <img class="avatar" src="{{ $userPhoto }}" alt="" style="object-fit:cover">
        @else
            <div class="avatar">{{ mb_strtoupper(mb_substr($firstName, 0, 1)) }}</div>
        @endif
        <div>
            <b>{{ $fullName }}</b>
            <small>{{ $roleLabel }}@if (! empty($bizName)) &middot; {{ $bizName }}@endif</small>
        </div>
    </div>
</aside>
