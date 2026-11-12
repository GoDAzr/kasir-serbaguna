{{-- Bar atas halaman dalam: tombol menu, judul halaman, tombol keluar --}}
<header class="hero hero-lite">
    <div class="topbar-holder" id="topbarHolder">
        <div class="topbar-slot" id="topbarSlot">
            <div class="topbar">
                <button class="icon-btn btn-menu" id="menuBtn" type="button" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
                    <span class="tg" aria-hidden="true">
                        <span class="tg-bars"><span></span><span></span></span>
                        <svg><use href="#toggle-path"/></svg>
                        <svg><use href="#toggle-path"/></svg>
                    </span>
                </button>

                <div class="greet">
                    <h1>@yield('title')</h1>
                    @hasSection('subtitle')<p>@yield('subtitle')</p>@endif
                </div>

                @yield('top_actions')

                <a class="icon-btn btn-gear" href="{{ route('dashboard') }}" aria-label="Dashboard" title="Dashboard"><svg class="ico"><use href="#i-home"/></svg></a>
                <form method="POST" action="{{ route('logout') }}" style="display:contents">
                    @csrf
                    <button class="icon-btn btn-out" type="submit" aria-label="Keluar"><svg class="ico"><use href="#i-logout"/></svg></button>
                </form>
            </div>
        </div>
    </div>
</header>
