@extends('layouts.app')
@section('title', $m['title'])
@section('subtitle', $rows->total() . ' data')

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            @if (! empty($m['search']))
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari...">
            @endif
            @foreach ($filters as $f)
                @if ($f['type'] === 'date')
                    <label class="inl">{{ $f['label'] }} <input type="date" name="{{ $f['name'] }}" value="{{ request($f['name']) }}"></label>
                @else
                    <select name="{{ $f['name'] }}">
                        <option value="">{{ $f['label'] }}: semua</option>
                        @foreach ($f['options'] as $k => $label)
                            <option value="{{ $k }}" @selected((string) request($f['name']) === (string) $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            @endforeach
            <button class="btn ghost" type="submit">Terapkan</button>
            @if (($m['create'] ?? true) !== false)
                <a class="btn primary push" href="{{ route('modules.create', $m['key']) }}">+ Tambah</a>
            @endif
        </form>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        @foreach ($m['cols'] as $c)<th>{{ $c[1] }}</th>@endforeach
                        <th class="r">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($m['cols'] as $c)
                                <td data-l="{{ $c[1] }}">{!! \App\Support\Fmt::cell($c, $row, $maps) !!}</td>
                            @endforeach
                            <td class="r acts">
                                @if (! empty($m['children']))
                                    <a class="lnk" href="{{ route('modules.show', [$m['key'], $row->id]) }}">Lihat</a>
                                @endif
                                @foreach ($m['actions'] ?? [] as $a)
                                    <a class="lnk" href="{{ url(preg_replace_callback('/\{(\w+)\}/', fn ($x) => $row->{$x[1]} ?? '', $a[1])) }}">{{ $a[0] }}</a>
                                @endforeach
                                @if (($m['edit'] ?? true) !== false)
                                    <a class="lnk" href="{{ route('modules.edit', [$m['key'], $row->id]) }}">Ubah</a>
                                @endif
                                @if (($m['delete'] ?? true) !== false)
                                    <form method="POST" action="{{ route('modules.destroy', [$m['key'], $row->id]) }}" data-confirm="Hapus data ini?" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button class="lnk danger" type="submit">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($m['cols']) + 1 }}" class="empty-cell">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rows->links('partials.pager') }}
    </div>
@endsection
