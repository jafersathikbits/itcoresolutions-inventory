@props(['path' => null, 'size' => 'h-12 w-12', 'alt' => ''])
@if($path)
    <img src="{{ asset('storage/'.$path) }}" alt="{{ $alt }}" loading="lazy" {{ $attributes->merge(['class' => "thumb $size"]) }}>
@else
    <div {{ $attributes->merge(['class' => "thumb $size flex items-center justify-center text-slate-400"]) }}>
        <x-icon name="photo" class="h-1/3 w-1/3 min-h-4 min-w-4" />
    </div>
@endif
