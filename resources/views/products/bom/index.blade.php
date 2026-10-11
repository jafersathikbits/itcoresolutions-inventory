<x-app-layout>
    <x-slot name="title">BOM · {{ $product->name }}</x-slot>

    <x-slot name="header">
        <div class="min-w-0">
            <a href="{{ route('products.show', $product) }}" class="mb-1 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> {{ $product->name }}
            </a>
            <h1 class="page-title">Bill of Materials: {{ $product->name }}</h1>
            <p class="page-subtitle">Materials required to produce <b>1 unit</b> of this product.</p>
        </div>
    </x-slot>

    <div class="page">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Add / update -->
            <div class="card self-start">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Add / Update Material</h3>
                        <p class="card-subtitle">Choosing a material already in the BOM updates its quantity.</p>
                    </div>
                </div>
                <div class="card-body">
                    <x-form-errors />
                    <form method="POST" action="{{ route('products.bom.store', $product) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="material_id" class="form-label">Material</label>
                            <select id="material_id" name="material_id" class="form-input" required>
                                <option value="">Select material</option>
                                @foreach($allMaterials as $m)
                                    <option value="{{ $m->id }}" @selected(old('material_id') == $m->id)>
                                        {{ $m->name }} ({{ $m->unit?->symbol }}){{ $m->is_composite ? ' · Composite' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="qty_per_product" class="form-label">Qty per 1 product</label>
                            <input id="qty_per_product" name="qty_per_product" type="number" step="0.001" min="0.001" value="{{ old('qty_per_product') }}" class="form-input" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-full"><x-icon name="check" class="h-4 w-4" /> Save BOM Item</button>
                    </form>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <!-- Current BOM -->
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h3 class="card-title">Current BOM</h3>
                        <span class="badge badge-gray">{{ $product->materials->count() }}</span>
                    </div>

                    @if($product->materials->count() === 0)
                        <x-empty-state icon="layers" title="No BOM items yet">Add the materials needed to make one unit.</x-empty-state>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th class="text-right">Qty per 1</th>
                                        <th>Type</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->materials as $m)
                                        <tr>
                                            <td>
                                                <div class="font-medium text-slate-800">{{ $m->name }}</div>
                                                @if($m->is_composite)
                                                    @if($m->components->count() === 0)
                                                        <div class="mt-1 text-xs font-medium text-red-600">No components set: nothing will be deducted for this item.</div>
                                                    @else
                                                        <ul class="mt-1 space-y-0.5 text-xs text-slate-500">
                                                            @foreach($m->components as $c)
                                                                <li>
                                                                    ↳ {{ $c->childMaterial?->name }}:
                                                                    <x-qty :value="$c->qty_per_parent" :unit="$c->childMaterial?->unit?->symbol" /> per {{ $m->unit?->symbol }}
                                                                    → <b class="text-slate-700"><x-qty :value="(float) $m->pivot->qty_per_product * (float) $c->qty_per_parent" :unit="$c->childMaterial?->unit?->symbol" /></b> per product
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="num font-medium"><x-qty :value="$m->pivot->qty_per_product" :unit="$m->unit?->symbol" /></td>
                                            <td>
                                                @if($m->is_composite)
                                                    <span class="badge badge-amber">Composite</span>
                                                @else
                                                    <span class="badge badge-gray">Raw</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <x-confirm-delete :action="route('products.bom.destroy', [$product, $m->id])" message="Remove this BOM item?" label="Remove" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Expanded requirements -->
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Raw Material Requirements</h3>
                            <p class="card-subtitle">Composites flattened into raw materials. This is exactly what is checked and deducted per product ordered.</p>
                        </div>
                    </div>
                    @if(count($expanded) === 0)
                        <x-empty-state icon="cube" title="No requirements yet" />
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Raw material</th>
                                        <th class="text-right">Total per 1 product</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expanded as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="num font-semibold"><x-qty :value="$row['qty']" :unit="$row['unit']" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
