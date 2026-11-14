@extends('layouts.app')
@section('title', $tx->invoice_no)
@section('subtitle', 'Detail transaksi')

@section('content')
<div class="panel">
    <div class="detail-head">
        <div>
            <span class="badge {{ $tx->status === 'void' ? 'bad' : 'ok' }}">{{ $tx->status === 'void' ? 'Dibatalkan' : 'Selesai' }}</span>
            <span class="badge {{ \App\Support\Fmt::BADGE[$tx->payment_status] ?? 'gray' }}">{{ \App\Support\Fmt::label($tx->payment_status) }}</span>
        </div>
        <div class="actions">
            <a class="btn ghost" href="{{ route('transaksi.struk', $tx->id) }}" target="_blank" rel="noopener">Cetak struk</a>
            @if ($tx->status !== 'void' && \App\Support\Tenant::can('retur'))<a class="btn ghost" href="{{ route('retur.form', ['invoice' => $tx->invoice_no]) }}">Retur</a>@endif
        </div>
    </div>
    <dl class="dl">
        <dt>Tanggal</dt><dd>{{ $tx->created_at->format('d/m/Y H:i') }}</dd>
        <dt>Cabang</dt><dd>{{ $branch ?? '-' }}</dd>
        <dt>Kasir</dt><dd>{{ $tx->cashier->name ?? '-' }}</dd>
        <dt>Shift</dt><dd>{{ ucfirst($tx->shift ?? '-') }}</dd>
        <dt>Jenis transaksi</dt><dd>{{ \App\Support\Fmt::label($tx->type) }}</dd>
        <dt>Pelanggan</dt><dd>{{ $tx->customer->name ?? '-' }}</dd>
        <dt>Metode</dt><dd>{{ $method }}</dd>
        <dt>Referensi</dt><dd>{{ $tx->payment_ref ?: '-' }}</dd>
        @if ($tx->payment_proof)<dt>Bukti</dt><dd><a href="{{ asset('storage/' . $tx->payment_proof) }}" target="_blank" rel="noopener">Lihat bukti</a></dd>@endif
        <dt>Catatan</dt><dd>{{ $tx->note ?: '-' }}</dd>
    </dl>
</div>

<div class="panel">
    <h3 class="sub-title">Barang</h3>
    <div class="tbl-wrap"><table class="tbl">
        <thead><tr><th>Produk</th><th class="r">Harga</th><th class="r">Jumlah</th><th class="r">Diskon</th><th class="r">Diretur</th><th class="r">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($tx->items as $it)
            <tr><td>{{ $it->product_name }}@if ($it->note)<br><small class="muted">{{ $it->note }}</small>@endif</td>
                <td class="r">{{ \App\Support\Fmt::money($it->price) }}</td><td class="r">{{ $it->qty }}</td>
                <td class="r">{{ \App\Support\Fmt::money($it->discount) }}</td><td class="r">{{ $it->returned_qty }}</td>
                <td class="r">{{ \App\Support\Fmt::money($it->subtotal) }}</td></tr>
        @endforeach
        </tbody>
    </table></div>
    <div class="totals right">
        <div><span>Subtotal</span><b>{{ \App\Support\Fmt::money($tx->subtotal) }}</b></div>
        <div><span>Diskon transaksi</span><b>{{ \App\Support\Fmt::money($tx->discount) }}</b></div>
        <div><span>Pajak / biaya layanan</span><b>{{ \App\Support\Fmt::money($tx->tax) }}</b></div>
        <div class="grand"><span>Total</span><b>{{ \App\Support\Fmt::money($tx->total) }}</b></div>
        <div><span>Dibayar</span><b>{{ \App\Support\Fmt::money($tx->paid) }}</b></div>
        <div><span>Kembalian</span><b>{{ \App\Support\Fmt::money($tx->change) }}</b></div>
    </div>
</div>

@if ($returns->count())
<div class="panel">
    <h3 class="sub-title">Retur</h3>
    @foreach ($returns as $rt)<p>{{ $rt->return_no }} &middot; Refund {{ \App\Support\Fmt::money($rt->total_refund) }} &middot; {{ \Illuminate\Support\Carbon::parse($rt->created_at)->format('d/m/Y H:i') }}</p>@endforeach
</div>
@endif

@if ($tx->status === 'void')
    <div class="panel"><h3 class="sub-title">Pembatalan</h3>
        <dl class="dl"><dt>Alasan</dt><dd>{{ $tx->cancel_reason }}</dd><dt>Catatan</dt><dd>{{ $tx->cancel_note ?: '-' }}</dd><dt>Waktu</dt><dd>{{ $tx->voided_at?->format('d/m/Y H:i') }}</dd></dl>
    </div>
@elseif (\App\Support\Tenant::can('transaksi_batal') && $returns->isEmpty())
    <div class="panel">
        <h3 class="sub-title">Batalkan transaksi</h3>
        <p class="muted">Transaksi yang sudah selesai tidak bisa diedit. Jika salah, batalkan dengan alasan (stok dikembalikan dan tercatat di audit).</p>
        <form method="POST" action="{{ route('transaksi.batal', $tx->id) }}" class="grid" data-confirm="Yakin ingin membatalkan transaksi ini? Stok akan dikembalikan." data-confirm-title="Batalkan transaksi" data-confirm-yes="Ya, batalkan" data-confirm-no="Tidak" data-confirm-danger>
            @csrf
            <div class="fld"><label>Alasan pembatalan<i class="req">*</i></label><input type="text" name="cancel_reason" value="{{ old('cancel_reason') }}" maxlength="255"></div>
            <div class="fld"><label>Catatan</label><input type="text" name="cancel_note" value="{{ old('cancel_note') }}"></div>
            @include('partials.pin-confirm')
            <div class="actions full"><button class="btn danger" type="submit">Batalkan transaksi</button></div>
        </form>
    </div>
@endif
<p><a class="btn ghost" href="{{ route('transaksi') }}">&larr; Kembali</a></p>
@endsection
