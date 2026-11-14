{{-- Ringkasan Owner: total otomatis, filter periode / cabang / jenis laporan, grafik, aktivitas --}}
<section class="sec-summary" id="ringkasan">
    <h2 class="sec-title">Ringkasan</h2>
    <form method="GET" action="{{ route('dashboard') }}" class="filters card-pad card">
        <label class="inl">Dari <input type="date" name="from" value="{{ $f['from'] }}"></label>
        <label class="inl">Sampai <input type="date" name="to" value="{{ $f['to'] }}"></label>
        @if (count($branchOptions) > 1)
            <select name="branch_id"><option value="">Semua cabang</option>@foreach ($branchOptions as $id => $n)<option value="{{ $id }}" @selected((string) $f['branch_id'] === (string) $id)>{{ $n }}</option>@endforeach</select>
        @endif
        <select name="group">@foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $k => $l)<option value="{{ $k }}" @selected($f['group'] === $k)>Laporan {{ strtolower($l) }}</option>@endforeach</select>
        <button class="btn primary" type="submit">Terapkan</button>
    </form>

    <div class="cards">
        <div class="stat g-hijau"><small>Omzet</small><b>{{ $sum['omzet'] }}</b></div>
        <div class="stat g-kuning"><small>Keuntungan</small><b>{{ $sum['profit'] }}</b></div>
        <div class="stat g-biru"><small>Jumlah transaksi</small><b>{{ $sum['count'] }}</b></div>
        <div class="stat g-hitam"><small>Total produk</small><b>{{ $sum['products'] }}</b></div>
        <div class="stat g-toska"><small>Total pelanggan</small><b>{{ $sum['customers'] }}</b></div>
        <a class="stat g-merah" href="{{ route('notifikasi') }}"><small>Stok menipis</small><b>{{ $sum['low'] }}</b></a>
    </div>

</section>
