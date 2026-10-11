@props(['status'])
@php
    $map = [
        'Received' => ['badge-blue', 'inbox'],
        'In Progress' => ['badge-amber', 'refresh'],
        'Completed' => ['badge-green', 'check'],
        'Dispatched' => ['badge-purple', 'truck'],
    ];
    [$cls, $icon] = $map[$status] ?? ['badge-gray', 'info'];
@endphp
<span {{ $attributes->merge(['class' => "badge $cls"]) }}>
    <x-icon :name="$icon" class="h-3.5 w-3.5" />
    {{ $status }}
</span>
