@php($plain = $plain ?? false)
<h3>{{ $rep['title'] }}</h3>
<p>Periode {{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}</p>
<div class="tbl-wrap">
<table class="tbl" @if ($plain) border="1" cellpadding="4" @endif>
    <thead><tr>@foreach ($rep['headers'] as $i => $h)<th class="{{ in_array($i, $rep['money']) ? 'r' : '' }}">{{ $h }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse ($rep['rows'] as $row)
        <tr>@foreach ($row as $i => $c)<td class="{{ in_array($i, $rep['money']) ? 'r' : '' }}">{{ in_array($i, $rep['money']) ? \App\Support\Fmt::money($c) : $c }}</td>@endforeach</tr>
    @empty
        <tr><td colspan="{{ count($rep['headers']) }}" class="empty-cell">Tidak ada data pada periode ini.</td></tr>
    @endforelse
    </tbody>
    @if ($rep['totals'])
        <tfoot><tr>@foreach ($rep['totals'] as $i => $c)<th class="{{ in_array($i, $rep['money']) ? 'r' : '' }}">{{ is_numeric($c) && in_array($i, $rep['money']) ? \App\Support\Fmt::money($c) : $c }}</th>@endforeach</tr></tfoot>
    @endif
</table>
</div>
