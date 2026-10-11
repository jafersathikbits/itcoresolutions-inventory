{{-- Recursive breakdown of a composite material. Expects $material and $level. --}}
<ul class="{{ $level > 0 ? 'ml-5 mt-2 border-l border-slate-200 pl-4' : '' }} space-y-2">
    @foreach($material->components as $comp)
        <li>
            <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md {{ $comp->childMaterial?->is_composite ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-500' }}">
                        <x-icon :name="$comp->childMaterial?->is_composite ? 'layers' : 'cube'" class="h-4 w-4" />
                    </span>
                    @if($comp->childMaterial)
                        <a href="{{ route('materials.show', $comp->childMaterial) }}" class="truncate font-medium text-slate-800 hover:text-brand-700">{{ $comp->childMaterial->name }}</a>
                    @else
                        <span class="text-slate-400">(deleted material)</span>
                    @endif
                </div>
                <div class="shrink-0 text-sm text-slate-700">
                    <x-qty :value="$comp->qty_per_parent" :unit="$comp->childMaterial?->unit?->symbol" class="font-semibold" />
                    <span class="text-xs text-slate-400">per 1 {{ $material->unit?->symbol }}</span>
                </div>
            </div>

            @if($comp->childMaterial?->is_composite && $level < 5)
                @include('materials.partials.composite_tree', ['material' => $comp->childMaterial, 'level' => $level + 1])
            @endif
        </li>
    @endforeach
</ul>
