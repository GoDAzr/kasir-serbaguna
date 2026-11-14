{{-- Isi halaman: perlu perhatian, menu, transaksi terakhir --}}
<main class="content">
    @include('partials.summary')

    <section class="sec-attention">
        <h2 class="sec-title">Perlu perhatian</h2>
        @if ($lowStock > 0)
            <a href="{{ route('notifikasi') }}" class="card alert">
                <div class="alert-num">{{ $lowStock }}</div>
                <div>
                    <b>Stok rendah</b>
                    <small>{{ $lowStock }} produk perlu diisi ulang</small>
                </div>
                <svg class="ico chev"><use href="#i-chev"/></svg>
            </a>
        @else
            <div class="card alert">
                <div class="alert-num ok">&#10003;</div>
                <div>
                    <b>Semua aman</b>
                    <small>Tidak ada stok yang menipis</small>
                </div>
            </div>
        @endif
    </section>

    <section class="sec-actions">
        <h2 class="sec-title js-menu-title">Menu</h2>
        <div class="menu-list">
            @include('partials.menu-groups', ['variant' => 'wide'])
        </div>
    </section>

    @include('partials.summary-charts')

    <section class="sec-recent">
        <h2 class="sec-title">Transaksi terakhir</h2>
        <div class="card">
            @forelse ($recent as $tx)
                @if ($loop->first)<ul class="recent-list">@endif
                <li>
                    <svg class="ico" style="color:var(--blue)"><use href="#i-receipt"/></svg>
                    <div>
                        <b>{{ $tx['invoice'] }}</b>
                        <div class="meta">
                            <small>{{ $tx['time'] }}</small>
                            <span class="pay pay-{{ $tx['key'] }}">{{ $tx['method'] }}</span>
                        </div>
                    </div>
                    <span class="amt">{{ $tx['total'] }}</span>
                </li>
                @if ($loop->last)</ul>@endif
            @empty
                <div class="empty">
                    <svg class="ico"><use href="#i-receipt"/></svg>
                    <b>Belum ada transaksi hari ini</b>
                    <small>Mulai transaksi dari menu POS</small>
                </div>
            @endforelse
        </div>
    </section>
</main>
