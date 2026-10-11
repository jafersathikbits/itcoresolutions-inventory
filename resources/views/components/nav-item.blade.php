@props(['href', 'active' => false, 'icon'])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'nav-item' . ($active ? ' active' : '')]) }} @if($active) aria-current="page" @endif>
    <x-icon :name="$icon" />
    <span class="flex-1">{{ $slot }}</span>
    {{ $badge ?? '' }}
</a>
