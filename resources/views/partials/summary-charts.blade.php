{{-- Grafik penjualan + aktivitas terbaru: ditaruh di bawah menu --}}
<section class="sec-charts" id="grafik-aktivitas">
    {{-- Grafik penjualan: pilih jenis grafik, tombol > untuk buka / tutup, panjang -> scroll di tempat --}}
    <div class="card card-pad fold is-open" id="grafik" data-fold="grafik"
         data-chart="{{ json_encode(collect($chart)->map(fn ($v, $k) => [(string) $k, (float) $v])->values()) }}">
        <div class="fold-head">
            <button type="button" class="fold-btn" aria-expanded="true" aria-label="Buka / tutup grafik"><svg class="ico"><use href="#i-chev"/></svg></button>
            <h3 class="sub-title">Grafik penjualan</h3>
            <div class="chart-tabs">
                <button type="button" class="chip-btn on" data-type="bar">Batang</button>
                <button type="button" class="chip-btn" data-type="line">Garis</button>
                <button type="button" class="chip-btn" data-type="area">Area</button>
                <button type="button" class="chip-btn" data-type="donut">Donat</button>
            </div>
        </div>
        <div class="fold-body">
            @if (count($chart))
                <div class="chart-host"></div>
            @else
                <p class="muted center">Belum ada penjualan pada periode ini.</p>
            @endif
        </div>
    </div>

    {{-- Aktivitas terbaru: tombol > untuk buka / tutup, cari + filter tanggal, scroll di tempat --}}
    <div class="card card-pad fold is-open" id="aktivitas" data-fold="aktivitas">
        <div class="fold-head">
            <button type="button" class="fold-btn" aria-expanded="true" aria-label="Buka / tutup aktivitas"><svg class="ico"><use href="#i-chev"/></svg></button>
            <h3 class="sub-title">Aktivitas terbaru</h3>
            <small class="muted act-count" id="actCount"></small>
        </div>
        <div class="fold-body">
            <div class="act-filter">
                <input type="search" id="actQ" placeholder="Cari aktivitas..." autocomplete="off">
                <label class="inl">Dari <input type="date" id="actFrom"></label>
                <label class="inl">Sampai <input type="date" id="actTo"></label>
                <button type="button" class="btn ghost sm" id="actReset">Reset</button>
            </div>
            <div class="fold-scroll">
                @foreach ($logs as $l)
                    @php $at = \Illuminate\Support\Carbon::parse($l->created_at); @endphp
                    <p class="nrow" data-date="{{ $at->toDateString() }}" data-text="{{ mb_strtolower(($l->user_name ?? 'Sistem') . ' ' . $l->action . ' ' . $l->menu) }}"><b>{{ $l->user_name ?? 'Sistem' }}</b> <span class="badge {{ \App\Support\Fmt::BADGE[$l->action] ?? 'gray' }}">{{ $l->action }}</span> {{ $l->menu }}
                        <small class="muted" title="{{ $at->format('d/m/Y H:i') }}">{{ $at->format('d/m H:i') }} &middot; {{ $at->locale('id')->diffForHumans() }}</small></p>
                @endforeach
                <p class="muted center" id="actEmpty" @if ($logs->count()) hidden @endif>{{ $logs->count() ? 'Tidak ada aktivitas yang cocok.' : 'Belum ada aktivitas.' }}</p>
            </div>
        </div>
    </div>
</section>
