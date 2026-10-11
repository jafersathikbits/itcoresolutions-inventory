{{-- Session flash messages, rendered once by the app layout for every page --}}
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition class="alert alert-success mb-4" role="status">
        <x-icon name="check-circle" class="h-5 w-5 shrink-0" />
        <div class="flex-1">{{ session('success') }}</div>
        <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss"><x-icon name="x" class="h-4 w-4" /></button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition class="alert alert-error mb-4" role="alert">
        <x-icon name="x-circle" class="h-5 w-5 shrink-0" />
        <div class="flex-1">{{ session('error') }}</div>
        <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss"><x-icon name="x" class="h-4 w-4" /></button>
    </div>
@endif
