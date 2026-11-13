{{-- Ringkasan Owner: total otomatis, filter periode / cabang / jenis laporan, grafik, aktivitas --}}
<section class="sec-summary" id="ringkasan">
    <h2 class="sec-title">Ringkasan</h2>
    <form method="GET" action="{{ route('dashboard') }}" class="filters card-pad card">
        <label class="inl">Dari <input type="date" name="from" value="{{ $f['from'] }}"></label>
        <label class="inl">Sampai <input type="date" name="to" value="{{ $f['to'] }}"></label>
        @if (count($branchOptions) > 1)
            <select name="branch_id"><option value="">Semua cabang</option>@foreach ($branchOptions as $id => $n)<option value="{{ $id }}" @selected((string) $f['branch_id'] === (string) $id)>{{ $n }}</option>@endforeach</select>
        @endif
        <select name="group">@foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $k => $l)<option value="{{ $k }}" @selected($f['group'] === $k)>Laporan {{ strtolower($l) }}</option>@endforeach</select>
        <button class="btn primary" type="submit">Terapkan</button>
    </form>

    <div class="cards">
        <div class="stat g-hijau"><small>Omzet</small><b>{{ $sum['omzet'] }}</b></div>
        <div class="stat g-kuning"><small>Keuntungan</small><b>{{ $sum['profit'] }}</b></div>
        <div class="stat g-biru"><small>Jumlah transaksi</small><b>{{ $sum['count'] }}</b></div>
        <div class="stat g-hitam"><small>Total produk</small><b>{{ $sum['products'] }}</b></div>
        <div class="stat g-toska"><small>Total pelanggan</small><b>{{ $sum['customers'] }}</b></div>
        <a class="stat g-merah" href="{{ route('notifikasi') }}"><small>Stok menipis</small><b>{{ $sum['low'] }}</b></a>
    </div>

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
