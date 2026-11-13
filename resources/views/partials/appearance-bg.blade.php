{{-- Lapisan background (di belakang seluruh aplikasi) --}}
@php($ap = $ap ?? \App\Support\Appearance::for(\App\Support\Tenant::user()))
@if ($ap['type'] !== 'plain')
    <div class="bg-layer" id="bgLayer" aria-hidden="true" data-type="{{ $ap['type'] }}" style="--veil: {{ $ap['veil'] / 100 }};@if ($ap['type'] === 'img') background-image: url('{{ $ap['url'] }}');@endif">
        @if ($ap['type'] === 'scene')
            <div class="bg-scene">@include('partials.bg-scene')</div>
        @endif
    </div>
    <script>document.body.classList.add('has-bg');</script>
@endif
