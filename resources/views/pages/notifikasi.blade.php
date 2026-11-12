@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<div class="panel"><h3 class="sub-title">Stok menipis <span class="badge {{ $low->count() ? 'warn' : 'ok' }}">{{ $low->count() }}</span></h3>
    @forelse ($low as $x)<p class="nrow"><b>{{ $x->name }}</b> <span class="muted">{{ $x->gudang }}</span> <span class="badge warn">sisa {{ $x->quantity }}</span></p>@empty<p class="muted">Semua stok aman.</p>@endforelse
</div>
<div class="panel"><h3 class="sub-title">Hutang &amp; piutang jatuh tempo <span class="badge {{ $due->count() ? 'warn' : 'ok' }}">{{ $due->count() }}</span></h3>
    @forelse ($due as $d)
        <p class="nrow"><b>{{ $d->party_name }}</b> <span class="badge gray">{{ $d->kind }}</span> sisa {{ \App\Support\Fmt::money($d->amount - $d->paid_amount) }}
            <span class="badge {{ $d->due_date < now()->toDateString() ? 'bad' : 'warn' }}">jatuh tempo {{ \Illuminate\Support\Carbon::parse($d->due_date)->format('d/m/Y') }}</span></p>
    @empty<p class="muted">Tidak ada yang mendekati jatuh tempo (7 hari).</p>@endforelse
</div>
<div class="panel"><h3 class="sub-title">Pesan dari admin</h3>
    @forelse ($custom as $n)<div class="nrow"><b>{{ $n->title }}</b> <span class="badge gray">{{ \App\Support\Fmt::label($n->type) }}</span><br><span>{{ $n->body }}</span><br><small class="muted">{{ \Illuminate\Support\Carbon::parse($n->created_at)->format('d/m/Y H:i') }}</small></div>@empty<p class="muted">Tidak ada pesan.</p>@endforelse
</div>
@endsection
