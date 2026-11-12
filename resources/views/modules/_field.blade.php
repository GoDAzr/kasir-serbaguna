{{-- Satu isian formulir. Variabel: $f (definisi field), $name (nama input), $val (nilai), $idx (untuk repeater) --}}
@php
    $type = $f['type'];
    $req = ! empty($f['r']) || (! empty($f['rc']) && empty($row));
    $err = $errors->first($name);
    $id = 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $name);
@endphp
<div class="fld {{ in_array($type, ['textarea', 'perms', 'multi']) ? 'full' : '' }} {{ $err ? 'has-err' : '' }}"
     @if (! empty($f['show'])) data-show='@json($f['show'])' @endif>
    <label for="{{ $id }}">{{ $f['label'] }}@if ($req)<i class="req">*</i>@endif</label>

    @switch($type)
        @case('textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="3">{{ $val }}</textarea>
            @break

        @case('select')
        @case('ref')
            <select id="{{ $id }}" name="{{ $name }}">
                <option value="">- Pilih -</option>
                @foreach ($f['options'] as $k => $label)
                    <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('check')
            <label class="chk"><input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($val)> Ya</label>
            @break

        @case('file')
            <input type="file" id="{{ $id }}" name="{{ $name }}" accept=".jpg,.jpeg,.png,.webp,.pdf">
            @if ($val)<small class="hint">Sudah ada file: <a href="{{ asset('storage/' . $val) }}" target="_blank" rel="noopener">lihat</a>. Pilih file baru untuk mengganti.</small>@endif
            @break

        @case('multi')
            @php $sel = is_array($val) ? $val : (json_decode($val ?? '[]', true) ?: []); @endphp
            <div class="checks">
                @forelse ($f['options'] ?? [] as $k => $label)
                    <label class="chk"><input type="checkbox" name="{{ $name }}[]" value="{{ $k }}" @checked(in_array((string) $k, array_map('strval', $sel)))> {{ $label }}</label>
                @empty
                    <small class="hint">Belum ada pilihan.</small>
                @endforelse
            </div>
            @break

        @case('perms')
            @php $sel = is_array($val) ? $val : (json_decode($val ?? '[]', true) ?: []); $all = in_array('*', $sel); @endphp
            <div class="checks perms">
                @foreach (config('permissions.groups') as $k => $label)
                    <label class="chk"><input type="checkbox" name="{{ $name }}[]" value="{{ $k }}" @checked($all || in_array($k, $sel))> {{ $label }}</label>
                @endforeach
            </div>
            @if ($all)<small class="hint">Role ini punya akses penuh (semua izin).</small>@endif
            @break

        @case('password')
            <input type="password" id="{{ $id }}" name="{{ $name }}" autocomplete="new-password">
            @break

        @case('money')
            <input type="number" id="{{ $id }}" name="{{ $name }}" value="{{ $val }}" step="any" min="0" inputmode="decimal">
            @break

        @case('number')
            <input type="number" id="{{ $id }}" name="{{ $name }}" value="{{ $val }}" step="1" inputmode="numeric">
            @break

        @case('datetime')
            <input type="datetime-local" id="{{ $id }}" name="{{ $name }}" value="{{ $val ? \Illuminate\Support\Carbon::parse($val)->format('Y-m-d\TH:i') : '' }}">
            @break

        @case('date')
            <input type="date" id="{{ $id }}" name="{{ $name }}" value="{{ $val ? \Illuminate\Support\Carbon::parse($val)->format('Y-m-d') : '' }}">
            @break

        @default
            <input type="{{ in_array($type, ['email', 'tel']) ? $type : 'text' }}" id="{{ $id }}" name="{{ $name }}" value="{{ $val }}">
    @endswitch

    @if (! empty($f['hint']))<small class="hint">{{ $f['hint'] }}</small>@endif
    @if ($err)<small class="err">{{ $err }}</small>@endif
</div>
