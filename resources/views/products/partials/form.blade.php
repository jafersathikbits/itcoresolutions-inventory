{{-- Shared fields for product create + edit. Optional $product. --}}
@php $product = $product ?? null; @endphp

<div class="space-y-5">
    <div>
        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $product?->name) }}" class="form-input" maxlength="180" required autofocus>
    </div>

    <div x-data="{ preview: null }">
        <label for="image" class="form-label">
            {{ $product ? 'Replace image (optional)' : 'Image' }}
            @unless($product)<span class="text-red-500">*</span>@endunless
        </label>
        <div class="mt-1 flex items-center gap-4">
            <template x-if="preview">
                <img :src="preview" class="thumb h-24 w-24" alt="Preview">
            </template>
            <template x-if="!preview">
                <x-image-thumb :path="$product?->image_path" size="h-24 w-24" />
            </template>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-file mt-0"
                   @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                   @unless($product) required @endunless>
        </div>
        <p class="form-help">JPG, PNG or WEBP, max 4 MB.</p>
    </div>
</div>
