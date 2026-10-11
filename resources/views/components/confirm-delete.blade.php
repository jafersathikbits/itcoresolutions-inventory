{{-- Small inline DELETE form with a confirmation prompt --}}
@props(['action', 'message' => 'Are you sure?', 'label' => 'Delete', 'size' => 'btn-sm'])
<form method="POST" action="{{ $action }}" class="inline" onsubmit="return confirm(@js($message));">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => "btn btn-danger-soft $size"]) }}>
        <x-icon name="trash" class="h-4 w-4" />
        {{ $label }}
    </button>
</form>
