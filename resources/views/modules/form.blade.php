@extends('layouts.app')
@section('title', ($row ? 'Ubah ' : 'Tambah ') . $m['title'])
@section('subtitle', $m['title'])
@section('own_errors', '1')

@section('content')
    @if ($errors->any())
        <div class="flash bad" role="alert">
            <b>Periksa lagi isian Anda:</b>
            <ul>@foreach ($errors->all() as $er)<li>{{ $er }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" novalidate class="panel form"
          action="{{ $row ? route('modules.update', [$m['key'], $row->id]) : route('modules.store', $m['key']) }}">
        @csrf
        @if ($row) @method('PUT') @endif

        <div class="grid">
            @foreach ($fields as $f)
                @php
                    $name = $f['name'];
                    $val = old($name, $extra['values'][$name] ?? ($row->$name ?? ($f['default'] ?? null)));
                    if ($f['type'] === 'password') { $val = null; }
                    if (! $row && ! old($name) && ! isset($extra['values'][$name]) && $f['type'] === 'date' && in_array($name, ['entry_date', 'pay_date', 'movement_date', 'purchase_date', 'debt_date', 'opname_date', 'transfer_date'])) { $val = now()->toDateString(); }
                    if (! $row && $val === null && $name === 'is_active') { $val = 1; }
                @endphp
                @include('modules._field', ['f' => $f, 'name' => $name, 'val' => $val])
            @endforeach
        </div>

        @if ($children)
            @php
                $rowsOld = old($children['name'], $childRows);
                if (! $rowsOld) { $rowsOld = [[]]; }
            @endphp
            <h3 class="sub-title">{{ $children['label'] }}<i class="req">*</i></h3>
            <div class="rep" data-rep data-name="{{ $children['name'] }}">
                <div class="rep-rows" data-rows>
                    @foreach (array_values($rowsOld) as $i => $cr)
                        <div class="rep-row" data-row>
                            @foreach ($children['fields'] as $cf)
                                @include('modules._field', ['f' => $cf, 'name' => $children['name'] . '[' . $i . '][' . $cf['name'] . ']', 'val' => $cr[$cf['name']] ?? null])
                            @endforeach
                            <button type="button" class="btn ghost danger sm rep-del" data-del title="Hapus baris">&times;</button>
                        </div>
                    @endforeach
                </div>
                <template data-tpl>
                    <div class="rep-row" data-row>
                        @foreach ($children['fields'] as $cf)
                            @include('modules._field', ['f' => $cf, 'name' => $children['name'] . '[__i__][' . $cf['name'] . ']', 'val' => null])
                        @endforeach
                        <button type="button" class="btn ghost danger sm rep-del" data-del title="Hapus baris">&times;</button>
                    </div>
                </template>
                <button type="button" class="btn ghost sm" data-add>+ Tambah baris</button>
            </div>
            @error($children['name'])<small class="err">{{ $message }}</small>@enderror
        @endif

        @if (! empty($m['note']))<p class="hint">{{ $m['note'] }}</p>@endif

        <div class="actions">
            <a class="btn ghost" href="{{ route('modules.index', $m['key']) }}">Batal</a>
            <button class="btn primary" type="submit">Simpan</button>
        </div>
    </form>
@endsection
