<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Struk {{ $tx->invoice_no }}</title>
<style>
    body{font-family:'Courier New',monospace;font-size:12px;margin:0;background:#eee;color:#000}
    .s{width:{{ ($printer->paper_size ?? '58mm') === '80mm' ? '80mm' : '58mm' }};max-width:100%;margin:10px auto;background:#fff;padding:10px}
    .c{text-align:center}.r{text-align:right}hr{border:0;border-top:1px dashed #000;margin:6px 0}
    table{width:100%;border-collapse:collapse}td{vertical-align:top;padding:1px 0}
    .bar{text-align:center;margin:10px}.bar button,.bar a{font:inherit;padding:8px 14px;margin:2px;border:1px solid #999;background:#fff;border-radius:6px;cursor:pointer;color:#000;text-decoration:none}
    @media print{body{background:#fff}.bar{display:none}.s{margin:0;width:100%}}
</style></head>
<body>
<div class="bar"><button onclick="window.print()">Cetak</button> <a href="{{ route('transaksi.show', $tx->id) }}">Kembali</a></div>
<div class="s">
    <div class="c"><b>{{ $biz->name }}</b><br>{{ $branch->name ?? '' }}<br>{{ $branch->address ?? $biz->address }}<br>{{ $biz->phone }}</div>
    @if ($printer && $printer->header)<div class="c">{!! nl2br(e($printer->header)) !!}</div>@endif
    <hr>
    {{ $tx->invoice_no }}<br>{{ $tx->created_at->format('d/m/Y H:i') }}<br>Kasir: {{ $tx->cashier->name ?? '-' }}
    @if ($tx->customer)<br>Pelanggan: {{ $tx->customer->name }}@endif
    @if ($tx->status === 'void')<br><b>*** DIBATALKAN ***</b>@endif
    <hr>
    <table>
    @foreach ($tx->items as $it)
        <tr><td colspan="2">{{ $it->product_name }}</td></tr>
        <tr><td>{{ $it->qty }} x {{ number_format($it->price, 0, ',', '.') }}@if ($it->discount > 0) (-{{ number_format($it->discount, 0, ',', '.') }})@endif</td><td class="r">{{ number_format($it->subtotal, 0, ',', '.') }}</td></tr>
    @endforeach
    </table>
    <hr>
    <table>
        <tr><td>Subtotal</td><td class="r">{{ number_format($tx->subtotal, 0, ',', '.') }}</td></tr>
        @if ($tx->discount > 0)<tr><td>Diskon</td><td class="r">-{{ number_format($tx->discount, 0, ',', '.') }}</td></tr>@endif
        @if ($tx->tax > 0)<tr><td>Pajak/Layanan</td><td class="r">{{ number_format($tx->tax, 0, ',', '.') }}</td></tr>@endif
        <tr><td><b>TOTAL</b></td><td class="r"><b>{{ number_format($tx->total, 0, ',', '.') }}</b></td></tr>
        <tr><td>Bayar ({{ $method }})</td><td class="r">{{ number_format($tx->paid, 0, ',', '.') }}</td></tr>
        <tr><td>Kembali</td><td class="r">{{ number_format($tx->change, 0, ',', '.') }}</td></tr>
        @if ($tx->payment_status !== 'lunas')<tr><td>Sisa</td><td class="r">{{ number_format($tx->total - $tx->paid, 0, ',', '.') }}</td></tr>@endif
    </table>
    <hr>
    @if ($tx->receipt_note)<div class="c">{{ $tx->receipt_note }}</div>@endif
    <div class="c">{!! $printer && $printer->footer ? nl2br(e($printer->footer)) : 'Terima kasih atas kunjungan Anda' !!}</div>
</div>
@if ($printer && $printer->auto_print && request('auto'))<script>window.onload=function(){window.print()}</script>@endif
</body></html>
