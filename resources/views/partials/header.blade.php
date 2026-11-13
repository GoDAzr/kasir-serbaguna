{{-- Bar atas (sapaan + tombol) dan kartu pendapatan hari ini --}}
<header class="hero">
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

                <div class="greet" id="greet" data-tz="{{ $tz ?? config('app.timezone') }}">
                    <h1><span id="greetText">{{ $greeting }}</span>, {{ $firstName }}</h1>
                    <p id="dateText">{{ $dateLabel }}</p>
                </div>

                @include('partials.settings-menu')
                <form method="POST" action="{{ route('logout') }}" style="display:contents">
                    @csrf
                    <button class="icon-btn btn-out" type="submit" aria-label="Keluar">
                        <svg class="ico"><use href="#i-logout"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <section class="revenue" aria-label="Pendapatan hari ini">
        <div class="revenue-top">
            <span>Pendapatan hari ini</span>
            <span>{{ $txCount }} transaksi</span>
        </div>
        <div class="revenue-amount">{{ $revenue }}</div>
        <div class="progress"><i style="width: {{ $cashPercent }}%"></i></div>
        <div class="revenue-split">
            <span><i class="dot cash"></i>Tunai <b>{{ $cash }}</b></span>
            <span><i class="dot noncash"></i>Non-tunai <b>{{ $nonCash }}</b></span>
        </div>
    </section>
</header>
