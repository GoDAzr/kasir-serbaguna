@extends('layouts.app')
@section('title', $m['title'])
@section('subtitle', 'Detail')

@section('content')
    <div class="panel">
        <dl class="dl">
            @foreach ($fields as $f)
                @php $v = $row->{$f['name']} ?? null; @endphp
                @continue(in_array($f['type'], ['password']))
                <dt>{{ $f['label'] }}</dt>
                <dd>
                    @if ($v === null || $v === '') -
                    @elseif (in_array($f['type'], ['select', 'ref'])) {{ $f['options'][$v] ?? $v }}
                    @elseif ($f['type'] === 'money') {{ \App\Support\Fmt::money($v) }}
                    @elseif ($f['type'] === 'file') <a href="{{ asset('storage/' . $v) }}" target="_blank" rel="noopener">Lihat file</a>
                    @else {{ $v }}
                    @endif
                </dd>
            @endforeach
        </dl>
    </div>

    @if ($children)
        <div class="panel">
            <h3 class="sub-title">{{ $children['label'] }}</h3>
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead><tr>@foreach ($children['fields'] as $cf)<th>{{ $cf['label'] }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach ($childRows as $cr)
                            <tr>
                                @foreach ($children['fields'] as $cf)
                                    @php $v = $cr[$cf['name']] ?? null; @endphp
                                    <td>{{ in_array($cf['type'], ['select', 'ref']) ? ($cf['options'][$v] ?? $v) : ($cf['type'] === 'money' ? \App\Support\Fmt::money($v) : $v) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <p><a class="btn ghost" href="{{ route('modules.index', $m['key']) }}">&larr; Kembali</a></p>
@endsection
