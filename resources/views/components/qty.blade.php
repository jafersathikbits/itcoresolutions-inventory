{{-- Quantity without trailing zeros: 12.500 -> 12.5, 3.000 -> 3 --}}
@props(['value', 'unit' => null])
@php
    $formatted = $value === null || $value === '' ? '-' : rtrim(rtrim(number_format((float) $value, 3, '.', ','), '0'), '.');
@endphp
<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>{{ $formatted }}@if($unit && $formatted !== '-') <span class="text-slate-400">{{ $unit }}</span>@endif</span>
