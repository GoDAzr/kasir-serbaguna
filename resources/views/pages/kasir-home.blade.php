@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
@php
    $T = \App\Support\Tenant::class;
    $tiles = array_values(array_filter([
        ['Kasir / POS', 'Layani pembeli', 'pos', route('pos'), $T::can('pos'), 'g-hijau'],
        ['Scan Barcode', 'Tambah barang dengan kamera', 'scan', route('pos', ['scan' => 1]), $T::can('pos'), 'g-biru'],
        ['Riwayat', 'Transaksi dan struk', 'receipt', route('transaksi'), $T::can('transaksi'), 'g-kuning'],
        ['Retur', 'Barang dikembalikan', 'swap', route('retur.form'), $T::can('retur'), 'g-jingga'],
        ['Pelanggan', 'Data pelanggan', 'users', url('/m/pelanggan'), $T::can('pelanggan'), 'g-toska'],
        ['Produk', 'Daftar dan harga', 'box', url('/m/produk'), $T::can('produk'), 'g-ungu'],
        ['Laporan', 'Penjualan toko', 'chart', url('/laporan/penjualan'), $T::can('laporan'), 'g-merah'],
        ['Tampilan', 'Gelap, terang, warna', 'star', route('akun.tampilan'), true, 'g-abu'],
        ['Akun saya', 'Nama, password, PIN', 'user', route('akun'), true, 'g-hitam'],
    ], fn ($t) => $t[4]));
@endphp

<section class="k-hello" id="greet" data-tz="{{ $tz }}">
    <div>
        <h1><span id="greetText">{{ $greeting }}</span>, {{ $firstName }}</h1>
        <p id="dateText">{{ $dateLabel }}</p>
    </div>
    <div class="k-badges">
        <span class="badge gray">{{ $role }}</span>
        @if ($branches)<span class="badge gray">{{ $branches }}</span>@endif
    </div>
</section>

@if ($T::can('pos'))
    <a class="k-start" href="{{ route('pos') }}">
        <svg class="ico"><use href="#i-pos"/></svg>
        <span>Mulai transaksi<small>Buka kasir dan layani pembeli</small></span>
        <svg class="ico chev"><use href="#i-chev"/></svg>
    </a>
@endif

<div class="k-stats">
    <div class="stat g-biru"><small>Transaksi saya hari ini</small><b>{{ $stats['count'] }}</b></div>
    @if ($canSum)
        <div class="stat g-hijau"><small>Total penjualan saya</small><b>{{ \App\Support\Fmt::money($stats['total']) }}</b></div>
        <div class="stat g-kuning"><small>Penjualan tunai</small><b>{{ \App\Support\Fmt::money($stats['cash']) }}</b></div>
    @endif
</div>

<h2 class="sec-title">Menu kasir</h2>
<div class="k-tiles">
    @foreach ($tiles as [$label, $desc, $icon, $href, $ok, $color])
        <a class="k-tile {{ $color }}" href="{{ $href }}">
            <span class="k-ico"><svg class="ico"><use href="#i-{{ $icon }}"/></svg></span>
            <b>{{ $label }}</b>
            <small>{{ $desc }}</small>
        </a>
    @endforeach
</div>

<h2 class="sec-title" style="margin-top:24px">Transaksi terakhir saya</h2>
<div class="card k-recent">
    @forelse ($recent as $tx)
        @php $linked = $T::can('transaksi'); @endphp
        <a class="k-row" @if ($linked) href="{{ route('transaksi.show', $tx->id) }}" @endif>
            <svg class="ico"><use href="#i-receipt"/></svg>
            <div>
                <b>{{ $tx->invoice_no }}</b>
                <small>{{ $tx->created_at?->format('d/m H:i') }}@if ($tx->status === 'void') &middot; <span class="badge bad">Dibatalkan</span>@endif</small>
            </div>
            <span class="amt">{{ \App\Support\Fmt::money($tx->total) }}</span>
        </a>
    @empty
        <div class="empty"><b>Belum ada transaksi</b><small>Transaksi yang kamu buat akan muncul di sini</small></div>
    @endforelse
</div>
@endsection
