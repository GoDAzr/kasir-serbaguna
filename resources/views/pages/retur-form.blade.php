@extends('layouts.app')
@section('title', 'Retur Penjualan')
@section('own_errors', '1')
@section('content')
@if ($errors->any())<div class="flash bad"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="panel">
    <form method="GET" class="filters">
        <div class="fld grow"><label>Nomor transaksi<i class="req">*</i></label><input type="text" name="invoice" value="{{ request('invoice') }}" placeholder="INV-..."></div>
        <button class="btn primary" type="submit">Cari transaksi</button>
    </form>
</div>

@if ($tx)
<form method="POST" action="{{ route('retur.store') }}" enctype="multipart/form-data" class="panel form" novalidate>
    @csrf
    <input type="hidden" name="transaction_id" value="{{ $tx->id }}">
    <h3 class="sub-title">{{ $tx->invoice_no }} &middot; {{ \App\Support\Fmt::money($tx->total) }}</h3>
    @foreach ($tx->items as $it)
        @php $left = $it->qty - $it->returned_qty; @endphp
        <div class="rep-row ret">
            <div class="fld full"><b>{{ $it->product_name }}</b> <small class="muted">dibeli {{ $it->qty }}, sudah diretur {{ $it->returned_qty }}</small></div>
            @if ($left > 0)
                <div class="fld"><label>Jumlah retur<i class="req">*</i></label><input type="number" name="lines[{{ $it->id }}][qty]" min="0" max="{{ $left }}" value="{{ old("lines.$it->id.qty", 0) }}"></div>
                <div class="fld"><label>Alasan<i class="req">*</i></label><input type="text" name="lines[{{ $it->id }}][reason]" value="{{ old("lines.$it->id.reason") }}"></div>
                <div class="fld"><label>Kondisi barang</label><select name="lines[{{ $it->id }}][condition]"><option value="baik">Baik (kembali ke stok)</option><option value="rusak">Rusak</option></select></div>
                <div class="fld"><label>Bukti</label><input type="file" name="lines[{{ $it->id }}][proof]" accept=".jpg,.jpeg,.png,.webp,.pdf"></div>
            @else
                <div class="fld full muted">Sudah diretur seluruhnya.</div>
            @endif
        </div>
    @endforeach
    <div class="grid">
        <div class="fld"><label>Metode pengembalian dana<i class="req">*</i></label>
            <select name="refund_method"><option value="">- Pilih -</option>@foreach ($methods as $c => $n)<option value="{{ $c }}" @selected(old('refund_method') === $c)>{{ $n }}</option>@endforeach</select></div>
    </div>
    <p class="hint">Total refund dihitung otomatis dari harga bersih barang yang diretur.</p>
    <div class="actions"><a class="btn ghost" href="{{ route('retur.list') }}">Batal</a><button class="btn primary" type="submit">Simpan retur</button></div>
</form>
@endif
@endsection
