@extends('layouts.app')
@section('title', 'Daftar Retur')
@section('top_actions')@endsection
@section('content')
<div class="panel">
    <div class="filters"><a class="btn primary push" href="{{ route('retur.form') }}">+ Retur baru</a></div>
    <div class="tbl-wrap"><table class="tbl">
        <thead><tr><th>No. retur</th><th>Tanggal</th><th>Transaksi</th><th>Petugas</th><th>Metode refund</th><th class="r">Total refund</th></tr></thead>
        <tbody>
        @forelse ($rows as $r)
            <tr><td>{{ $r->return_no }}</td><td>{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                <td><a class="lnk" href="{{ route('transaksi.show', $r->transaction_id) }}">{{ $r->invoice_no }}</a></td><td>{{ $r->user_name }}</td>
                <td>{{ $r->refund_method ? \App\Support\Fmt::label($r->refund_method) : '-' }}</td><td class="r">{{ \App\Support\Fmt::money($r->total_refund) }}</td></tr>
        @empty<tr><td colspan="6" class="empty-cell">Belum ada retur.</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $rows->links('partials.pager') }}
</div>
@endsection
