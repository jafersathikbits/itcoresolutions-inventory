{{-- Shared fields for create + edit. Expects $units and optional $material. --}}
@php $material = $material ?? null; @endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $material?->name) }}" class="form-input" placeholder="e.g. Aluminium L angle 3/4" required maxlength="150" autofocus>
    </div>

    <div class="sm:col-span-2">
        <label for="unit_id" class="form-label">Unit <span class="text-red-500">*</span></label>
        <select id="unit_id" name="unit_id" class="form-input" required>
            <option value="">Select unit</option>
            @foreach($units as $u)
                <option value="{{ $u->id }}" @selected(old('unit_id', $material?->unit_id) == $u->id)>
                    {{ $u->name }} ({{ $u->symbol }})
                </option>
            @endforeach
        </select>
        @if($units->isEmpty())
            <p class="form-help text-amber-700">No units yet. @can('units.create')<a href="{{ route('units.create') }}" class="link">Create a unit</a> first.@endcan</p>
        @endif
    </div>

    <div>
        <label for="stock" class="form-label">Current stock <span class="text-red-500">*</span></label>
        <input id="stock" name="stock" type="number" step="0.001" min="0" value="{{ old('stock', $material ? (float) $material->stock : 0) }}" class="form-input" required>
    </div>

    <div>
        <label for="threshold" class="form-label">Low-stock threshold <span class="text-red-500">*</span></label>
        <input id="threshold" name="threshold" type="number" step="0.001" min="0" value="{{ old('threshold', $material ? (float) $material->threshold : 0) }}" class="form-input" required>
        <p class="form-help">Marked LOW when stock is at or below this value.</p>
    </div>

    <div class="sm:col-span-2">
        <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">
            <input id="is_composite" name="is_composite" type="checkbox" value="1" class="form-checkbox mt-0.5" @checked(old('is_composite', $material?->is_composite))>
            <span>
                <span class="block text-sm font-medium text-slate-800">Composite material</span>
                <span class="block text-xs text-slate-500">Made from other raw materials (e.g. a tray made from aluminium angle and net). Configure its components after saving.</span>
            </span>
        </label>
    </div>

    <div class="sm:col-span-2" x-data="{ preview: null }">
        <label for="image" class="form-label">
            {{ $material ? 'Replace image (optional)' : 'Image' }}
            @unless($material)<span class="text-red-500">*</span>@endunless
        </label>
        <div class="mt-1 flex items-center gap-4">
            <template x-if="preview">
                <img :src="preview" class="thumb h-20 w-20" alt="Preview">
            </template>
            <template x-if="!preview">
                <x-image-thumb :path="$material?->image_path" size="h-20 w-20" />
            </template>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-file mt-0"
                   @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                   @unless($material) required @endunless>
        </div>
        <p class="form-help">JPG, PNG or WEBP, max 4 MB.</p>
    </div>
</div>
