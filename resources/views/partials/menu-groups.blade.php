{{--
    Menu bertingkat (kiri & kanan selalu sinkron):
      Kiri  ($variant = 'side') : kategori yang bisa dibuka, isinya tampil sebagai shortcut
      Kanan ($variant = 'wide') : kartu kategori -> isi kategori -> pilihan di dalam menu (mis. Kasir / POS)
    Isi menu diatur di config/menu.php
--}}
@if ($variant === 'side')
<div class="dd side">
    <div class="mg-list">
        @foreach (config('menu.groups') as $gi => $g)
            <div class="mg g-{{ $g['color'] }}" data-group="g{{ $gi }}">
                <button type="button" class="mg-head" data-go="g{{ $gi }}" aria-expanded="false" title="{{ $g['title'] }}">
                    <span class="chip"><svg class="ico"><use href="#i-{{ $g['icon'] }}"/></svg></span>
                    <span class="mg-title">{{ $g['title'] }}<small>{{ count($g['items']) }} menu</small></span>
                    <svg class="ico mg-chev" data-toggle><use href="#i-chev"/></svg>
                </button>

                <div class="mg-body">
                    <div class="mg-inner">
                        @foreach ($g['items'] as $ii => $it)
                            @if (count($it[2]) > 0)
                                <button type="button" class="mi-head" data-go="g{{ $gi }}i{{ $ii }}" title="{{ $it[0] }}">
                                    <span class="mi-ico"><svg class="ico"><use href="#i-{{ $it[1] }}"/></svg></span>
                                    <span class="mi-label">{{ $it[0] }}</span>
                                </button>
                            @else
                                <a href="#" class="mi-head" data-soon="{{ $it[0] }}" title="{{ $it[0] }}">
                                    <span class="mi-ico"><svg class="ico"><use href="#i-{{ $it[1] }}"/></svg></span>
                                    <span class="mi-label">{{ $it[0] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="dd wide">

    {{-- Awal: daftar kategori --}}
    <div class="dd-view is-on" data-view="root" data-title="Menu">
        <div class="mg-list">
            @foreach (config('menu.groups') as $gi => $g)
                <div class="mg g-{{ $g['color'] }}">
                    <button type="button" class="mg-head" data-go="g{{ $gi }}">
                        <span class="chip"><svg class="ico"><use href="#i-{{ $g['icon'] }}"/></svg></span>
                        <span class="mg-title">{{ $g['title'] }}<small>{{ count($g['items']) }} menu</small></span>
                        <svg class="ico mg-chev"><use href="#i-chev"/></svg>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    @foreach (config('menu.groups') as $gi => $g)
        {{-- Isi kategori --}}
        <div class="dd-view g-{{ $g['color'] }}" data-view="g{{ $gi }}" data-title="{{ $g['title'] }}">
            <div class="dd-head">
                <button type="button" class="dd-back" data-back aria-label="Kembali"><svg class="ico"><use href="#i-chev"/></svg></button>
                <span class="chip"><svg class="ico"><use href="#i-{{ $g['icon'] }}"/></svg></span>
                <span class="dd-title">{{ $g['title'] }}</span>
            </div>
            <div class="dd-list">
                @foreach ($g['items'] as $ii => $it)
                    @if (count($it[2]) > 0)
                        <button type="button" class="sub-card" data-go="g{{ $gi }}i{{ $ii }}">
                            <span class="mi-ico"><svg class="ico"><use href="#i-{{ $it[1] }}"/></svg></span>
                            <span class="sub-text">
                                <b>{{ $it[0] }}</b>
                                <small>{{ count($it[2]) }} menu</small>
                            </span>
                        </button>
                    @else
                        <a href="#" class="sub-card" data-soon="{{ $it[0] }}">
                            <span class="mi-ico"><svg class="ico"><use href="#i-{{ $it[1] }}"/></svg></span>
                            <span class="sub-text"><b>{{ $it[0] }}</b></span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Pilihan di dalam menu (mis. Kasir / POS -> Transaksi Baru, Scan Barcode, ...) --}}
        @foreach ($g['items'] as $ii => $it)
            @if (count($it[2]) > 0)
                <div class="dd-view g-{{ $g['color'] }}" data-view="g{{ $gi }}i{{ $ii }}" data-title="{{ $it[0] }}">
                    <div class="dd-head">
                        <button type="button" class="dd-back" data-back aria-label="Kembali"><svg class="ico"><use href="#i-chev"/></svg></button>
                        <span class="chip"><svg class="ico"><use href="#i-{{ $it[1] }}"/></svg></span>
                        <span class="dd-title">{{ $it[0] }}</span>
                    </div>
                    <div class="dd-list">
                        @foreach ($it[2] as $sub)
                            <a href="#" class="sub-card" data-soon="{{ $sub[0] }}">
                                <span class="mi-ico"><svg class="ico"><use href="#i-{{ $sub[2] }}"/></svg></span>
                                <span class="sub-text">
                                    <b>{{ $sub[0] }}</b>
                                    <small>{{ $sub[1] }}</small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach
</div>
@endif
