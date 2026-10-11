@props(['low'])
@if($low)
    <span {{ $attributes->merge(['class' => 'badge badge-red']) }}><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Low</span>
@else
    <span {{ $attributes->merge(['class' => 'badge badge-green']) }}><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>OK</span>
@endif
