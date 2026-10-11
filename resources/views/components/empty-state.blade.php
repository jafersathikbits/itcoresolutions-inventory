@props(['icon' => 'inbox', 'title' => 'Nothing here yet'])
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-icon :name="$icon" class="h-6 w-6" />
    </div>
    <div class="font-medium text-slate-700">{{ $title }}</div>
    @if(trim($slot) !== '')
        <div class="max-w-sm">{{ $slot }}</div>
    @endif
</div>
