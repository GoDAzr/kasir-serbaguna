@extends('layouts.app')
@section('title', 'Transaksi')
@section('subtitle', $rows->total() . ' transaksi')

@section('content')
<div class="panel">
    <form method="GET" class="filters">
        <input type="search" name="invoice" value="{{ request('invoice') }}" placeholder="Nomor invoice">
        <label class="inl">Dari <input type="date" name="from" value="{{ request('from') }}"></label>
        <label class="inl">Sampai <input type="date" name="to" value="{{ request('to') }}"></label>
        <select name="branch_id"><option value="">Semua cabang</option>@foreach ($branches as $id => $n)<option value="{{ $id }}" @selected(request('branch_id') == $id)>{{ $n }}</option>@endforeach</select>
        <select name="user_id"><option value="">Semua kasir</option>@foreach ($users as $id => $n)<option value="{{ $id }}" @selected(request('user_id') == $id)>{{ $n }}</option>@endforeach</select>
        <select name="status"><option value="">Semua status</option><option value="paid" @selected(request('status') === 'paid')>Selesai</option><option value="void" @selected(request('status') === 'void')>Dibatalkan</option></select>
        <select name="payment_method"><option value="">Semua metode</option>@foreach ($methods as $c => $n)<option value="{{ $c }}" @selected(request('payment_method') === $c)>{{ $n }}</option>@endforeach</select>
        <button class="btn ghost" type="submit">Terapkan</button>
        @if (\App\Support\Tenant::can('pos'))<a class="btn primary push" href="{{ route('pos') }}">+ Transaksi baru</a>@endif
    </form>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Invoice</th><th>Waktu</th><th>Kasir</th><th>Pelanggan</th><th>Metode</th><th>Pembayaran</th><th>Status</th><th class="r">Total</th><th class="r">Aksi</th></tr></thead>
            <tbody>
            @forelse ($rows as $t)
                <tr>
                    <td data-l="Invoice"><a class="lnk" href="{{ route('transaksi.show', $t->id) }}">{{ $t->invoice_no }}</a></td>
                    <td data-l="Waktu">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <td data-l="Kasir">{{ $t->cashier->name ?? '-' }}</td>
                    <td data-l="Pelanggan">{{ $t->customer->name ?? '-' }}</td>
                    <td data-l="Metode">{{ $methods[$t->payment_method] ?? $t->payment_method }}</td>
                    <td data-l="Pembayaran"><span class="badge {{ \App\Support\Fmt::BADGE[$t->payment_status] ?? 'gray' }}">{{ \App\Support\Fmt::label($t->payment_status) }}</span></td>
                    <td data-l="Status"><span class="badge {{ $t->status === 'void' ? 'bad' : 'ok' }}">{{ $t->status === 'void' ? 'Dibatalkan' : 'Selesai' }}</span></td>
                    <td data-l="Total" class="r"><b>{{ \App\Support\Fmt::money($t->total) }}</b></td>
                    <td class="r acts"><a class="lnk" href="{{ route('transaksi.show', $t->id) }}">Detail</a> <a class="lnk" href="{{ route('transaksi.struk', $t->id) }}" target="_blank" rel="noopener">Struk</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty-cell">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->links('partials.pager') }}
</div>
@endsection
