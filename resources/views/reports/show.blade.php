@extends('layouts.app')
@section('title', $rep['title'])
@section('subtitle', 'Laporan')
@section('own_errors', '1')

@section('content')
<div class="panel no-print">
    @if ($errors->any())<div class="flash bad">{{ $errors->first() }}</div>@endif
    <form method="GET" class="filters">
        @if (in_array('period', $filters))
            <label class="inl">Dari <input type="date" name="from" value="{{ $from }}"></label>
            <label class="inl">Sampai <input type="date" name="to" value="{{ $to }}"></label>
        @endif
        @if (in_array('group', $filters))
            <select name="group">@foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $k => $l)<option value="{{ $k }}" @selected(request('group', 'harian') === $k)>{{ $l }}</option>@endforeach</select>
        @endif
        @foreach ([['branch_id', 'Cabang', 'branches'], ['user_id', 'Kasir', 'users'], ['payment_method', 'Metode', 'methods'], ['category_id', 'Kategori', 'categories'], ['warehouse_id', 'Gudang', 'warehouses'], ['customer_id', 'Pelanggan', 'customers'], ['cash_account_id', 'Akun kas', 'accounts']] as [$f, $l, $o])
            @if (in_array($f, $filters))
                <select name="{{ $f }}"><option value="">{{ $l }}: semua</option>@foreach ($options[$o] as $k => $n)<option value="{{ $k }}" @selected((string) request($f) === (string) $k)>{{ $n }}</option>@endforeach</select>
            @endif
        @endforeach
        @if (in_array('status', $filters))
            <select name="status"><option value="">Status: selesai</option><option value="void" @selected(request('status') === 'void')>Dibatalkan</option></select>
        @endif
        @if (in_array('low', $filters))
            <label class="chk"><input type="checkbox" name="low" value="1" @checked(request('low'))> Hanya stok menipis</label>
        @endif
        <select name="format" title="Format">
            <option value="html">Tampilkan di layar</option>
            <option value="xls">Export Excel (.xls)</option>
            <option value="pdf">Export PDF (cetak)</option>
        </select>
        <button class="btn primary" type="submit">Tampilkan</button>
    </form>
</div>

<div class="panel">
    @if (! empty($rep['chart']) && count($rep['chart']) > 1)
        @php $max = max(1, max(array_column($rep['chart'], 1))); @endphp
        <div class="bars no-print">
            @foreach (array_slice($rep['chart'], -31) as [$lbl, $val])
                <div class="bar" title="{{ $lbl }}: {{ is_numeric($val) ? number_format($val, 0, ',', '.') : $val }}"><i style="height:{{ max(3, round($val / $max * 100)) }}%"></i><span>{{ \Illuminate\Support\Str::limit($lbl, 8, '') }}</span></div>
            @endforeach
        </div>
    @endif
    @include('reports.table', ['plain' => false])
</div>
@endsection

@if ($print)
    @push('scripts')<script>window.addEventListener('load', function () { window.print(); });</script>@endpush
@endif
