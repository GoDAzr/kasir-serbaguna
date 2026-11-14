@extends('layouts.app')
@section('title', 'Kasir / POS')
@section('subtitle', 'Transaksi baru')
@section('page_class', 'page-pos')
@section('own_errors', '1')

@section('content')
<div class="pos" id="pos">
    {{-- ===== Kiri: pilih produk ===== --}}
    <section class="pos-products">
        <div class="pos-search">
            <input type="search" id="posSearch" placeholder="Cari produk / SKU..." autocomplete="off">
            <div class="scan-row">
                <input type="text" id="posScan" placeholder="Scan barcode lalu Enter" autocomplete="off" inputmode="numeric" {{ request('scan') ? 'autofocus' : '' }}>
                <button type="button" class="btn ghost" id="posCam" title="Scan pakai kamera" aria-label="Scan pakai kamera"><svg class="ico" style="width:20px;height:20px"><use href="#i-scan"/></svg>Kamera</button>
            </div>
        </div>
        <div class="chips" id="posCats"></div>
        <div class="pgrid" id="posGrid"></div>
    </section>

    {{-- ===== Kanan: keranjang & pembayaran ===== --}}
    <section class="pos-cart">
        <div class="panel" id="shift">
            <h3 class="sub-title">Info transaksi</h3>
            <div class="grid g2">
                <div class="fld"><label>Cabang<i class="req">*</i></label>
                    <select id="fBranch">@foreach ($branches as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
                <div class="fld"><label>Shift kasir<i class="req">*</i></label>
                    <select id="fShift">@foreach ($shifts as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach</select></div>
                <div class="fld"><label>Jenis transaksi<i class="req">*</i></label>
                    <select id="fType">@foreach ($types as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach</select></div>
                <div class="fld"><label>Pelanggan</label>
                    <select id="fCustomer"><option value="">Umum (tanpa pelanggan)</option>
                        @foreach ($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}{{ $c->phone ? ' - ' . $c->phone : '' }}</option>@endforeach</select></div>
                <div class="fld full"><label>Catatan</label><input type="text" id="fNote" maxlength="255"></div>
            </div>
        </div>

        <div class="panel" id="keranjang">
            <h3 class="sub-title">Keranjang <span class="badge gray" id="cartCount">0</span></h3>
            <div id="cartLines" class="cart-lines"><p class="muted center">Belum ada produk. Klik produk di sebelah kiri atau scan barcode.</p></div>

            <div class="totals">
                <div><span>Subtotal</span><b id="tSub">Rp 0</b></div>
                <div class="inl-field" @unless ($canDisc ?? true) hidden @endunless><span>Diskon transaksi (Rp)</span><input type="text" id="fDisc" data-money inputmode="numeric" autocomplete="off" value="0"></div>
                <div><span>Pajak / biaya layanan</span><b id="tTax">Rp 0</b></div>
                <div class="grand"><span>Total</span><b id="tTotal">Rp 0</b></div>
            </div>
        </div>

        <div class="panel" id="bayar">
            <h3 class="sub-title">Pembayaran</h3>
            <div class="grid g2">
                <div class="fld full"><label>Metode pembayaran<i class="req">*</i></label>
                    <select id="fMethod">@foreach ($methods as $m)<option value="{{ $m->code }}" data-type="{{ $m->type }}">{{ $m->name }}</option>@endforeach</select></div>
                <div class="fld"><label>Nominal dibayar<i class="req">*</i></label><input type="text" id="fAmount" data-money inputmode="numeric" autocomplete="off" placeholder="0"></div>
                <div class="fld" id="wrapRecv"><label>Uang diterima<i class="req">*</i></label><input type="text" id="fRecv" data-money inputmode="numeric" autocomplete="off" placeholder="0"></div>
                <div class="fld" id="wrapRef"><label>Referensi pembayaran</label><input type="text" id="fRef" maxlength="255"></div>
                <div class="fld" id="wrapProof"><label>Bukti pembayaran</label><input type="file" id="fProof" accept=".jpg,.jpeg,.png,.webp,.pdf"></div>
                <div class="fld full"><label>Catatan struk</label><input type="text" id="fReceiptNote" maxlength="500"></div>
            </div>
            <div class="totals">
                <div><span>Kembalian</span><b id="tChange">Rp 0</b></div>
                <div><span>Status</span><b id="tStatus">-</b></div>
            </div>
            <div id="posErr" class="flash bad" hidden></div>
            <button class="btn primary big" id="btnPay" type="button">Bayar &amp; simpan transaksi</button>
        </div>
    </section>
</div>

{{-- Pilih variasi --}}
<div class="modal" id="varModal" hidden>
    <div class="modal-box">
        <h3 id="varTitle">Pilih variasi</h3>
        <div id="varList" class="var-list"></div>
        <button class="btn ghost" type="button" data-close>Batal</button>
    </div>
</div>

{{-- Transaksi berhasil --}}
<div class="modal" id="doneModal" hidden>
    <div class="modal-box center">
        <div class="ok-ico">&#10003;</div>
        <h3>Transaksi berhasil</h3>
        <p id="doneInfo"></p>
        <div class="actions c">
            <a class="btn ghost" id="doneStruk" target="_blank" rel="noopener">Cetak struk</a>
            <button class="btn primary" type="button" id="doneNew">Transaksi baru</button>
        </div>
    </div>
</div>

{{-- Barcode belum terdaftar: daftarkan produk baru (hanya bila diizinkan owner) --}}
<div class="modal" id="newProdModal" hidden>
    <div class="modal-box">
        <h3>Barcode belum terdaftar</h3>
        @if ($canNewProduct ?? false)
            <p class="muted" style="margin:0">Daftarkan sebagai produk baru, lalu otomatis masuk ke keranjang.</p>
            <form id="newProdForm" class="np-form" novalidate>
                <div class="fld"><label>Barcode</label><input type="text" id="npBarcode" readonly></div>
                <div class="fld"><label>Nama produk<i class="req">*</i></label><input type="text" id="npName" maxlength="255" autocomplete="off"></div>
                <div class="np-two">
                    <div class="fld"><label>Harga jual<i class="req">*</i></label><input type="text" id="npPrice" data-money inputmode="numeric" autocomplete="off" placeholder="0"></div>
                    <div class="fld"><label>Harga modal</label><input type="text" id="npCost" data-money inputmode="numeric" autocomplete="off" placeholder="0"></div>
                </div>
                <div class="fld"><label>Jumlah stok saat ini<i class="req">*</i></label><input type="text" id="npStock" inputmode="numeric" autocomplete="off" value="1"></div>
                <div id="npErr" class="flash bad" hidden></div>
                <div class="actions">
                    <button type="button" class="btn ghost" id="npCancel">Batal</button>
                    <button type="submit" class="btn primary" id="npSave">Simpan &amp; tambahkan</button>
                </div>
            </form>
        @else
            <p>Barcode <b id="npCodeOnly"></b> belum ada di daftar produk.</p>
            <p class="muted" style="margin:0">Akun Anda belum diizinkan mendaftarkan produk baru. Minta owner untuk mendaftarkannya atau mengaktifkan izin <b>"Mendaftarkan produk baru dari kasir"</b> di menu Akses Kasir.</p>
            <div class="actions"><button type="button" class="btn primary" id="npCancel">Mengerti</button></div>
        @endif
    </div>
</div>

<div class="modal" id="camModal" hidden>
    <div class="modal-box">
        <h3>Scan barcode</h3>
        <div class="cam-box"><video id="camVideo" playsinline muted></video><i class="cam-line"></i></div>
        <p class="muted center" id="camMsg">Membuka kamera...</p>
        <button class="btn ghost" type="button" id="camClose">Tutup</button>
    </div>
</div>

@php $posData = ['items' => $items, 'taxes' => $taxes, 'canPrice' => $canPrice, 'canDisc' => $canDisc ?? true, 'canNewProduct' => $canNewProduct ?? false, 'url' => route('pos.checkout'), 'newUrl' => route('pos.produk')]; @endphp
<script type="application/json" id="posData">{!! json_encode($posData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@push('scripts')
    <script>window.ZXING_URL = "{{ asset('js/vendor/zxing.min.js') }}";</script>
    <script src="{{ asset('js/dashboard/scan.js') }}?v={{ @filemtime(public_path('js/dashboard/scan.js')) }}"></script>
    <script src="{{ asset('js/dashboard/pos.js') }}?v={{ @filemtime(public_path('js/dashboard/pos.js')) }}"></script>
@endpush
