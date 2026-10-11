<x-app-layout>
    <x-slot name="title">Components · {{ $material->name }}</x-slot>

    <x-slot name="header">
        <div class="min-w-0">
            <a href="{{ route('materials.show', $material) }}" class="mb-1 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> {{ $material->name }}
            </a>
            <h1 class="page-title">Components of {{ $material->name }}</h1>
            <p class="page-subtitle">Raw materials needed to produce <b>1 {{ $material->unit?->symbol }}</b> of this composite.</p>
        </div>
    </x-slot>

    <div class="page">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Add / update -->
            <div class="card self-start">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Add / Update Component</h3>
                        <p class="card-subtitle">Selecting an existing component updates its quantity.</p>
                    </div>
                </div>
                <div class="card-body">
                    <x-form-errors />
                    <form method="POST" action="{{ route('materials.components.store', $material) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="child_material_id" class="form-label">Child material</label>
                            <select id="child_material_id" name="child_material_id" class="form-input" required>
                                <option value="">Select raw material</option>
                                @foreach($childOptions as $opt)
                                    <option value="{{ $opt->id }}" @selected(old('child_material_id') == $opt->id)>{{ $opt->name }} ({{ $opt->unit?->symbol }})</option>
                                @endforeach
                            </select>
                            <p class="form-help">Only raw materials can be components (prevents cycles).</p>
                        </div>
                        <div>
                            <label for="qty_per_parent" class="form-label">Qty for 1 {{ $material->unit?->symbol }}</label>
                            <input id="qty_per_parent" name="qty_per_parent" type="number" step="0.001" min="0.001" value="{{ old('qty_per_parent') }}" class="form-input" required>
                            <p class="form-help">e.g. 1 tray needs 4 sqft aluminium angle and 1 sqft stainless net.</p>
                        </div>
                        <button type="submit" class="btn btn-primary w-full"><x-icon name="check" class="h-4 w-4" /> Save Component</button>
                    </form>
                </div>
            </div>

            <!-- Current -->
            <div class="space-y-6 lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h3 class="card-title">Current Components</h3>
                        <span class="badge badge-gray">{{ $material->components->count() }}</span>
                    </div>
                    @if($material->components->count() === 0)
                        <x-empty-state icon="layers" title="No components yet">Add the raw materials this composite is made of.</x-empty-state>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Child material</th>
                                        <th class="text-right">Qty per 1 {{ $material->unit?->symbol }}</th>
                                        <th class="text-right">In stock</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($material->components as $c)
                                        <tr>
                                            <td class="font-medium text-slate-800">{{ $c->childMaterial?->name }}</td>
                                            <td class="num"><x-qty :value="$c->qty_per_parent" :unit="$c->childMaterial?->unit?->symbol" /></td>
                                            <td class="num {{ $c->childMaterial?->is_low ? 'text-red-600 font-medium' : 'text-slate-600' }}"><x-qty :value="$c->childMaterial?->stock" /></td>
                                            <td class="text-right">
                                                <x-confirm-delete :action="route('materials.components.destroy', [$material, $c])" message="Remove this component?" label="Remove" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if($material->components->count() > 0)
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Breakdown</h3></div>
                        <div class="card-body">
                            @include('materials.partials.composite_tree', ['material' => $material, 'level' => 0])
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
