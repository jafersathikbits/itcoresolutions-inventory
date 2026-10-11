<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>

    <x-slot name="header">
        <div class="min-w-0">
            <a href="{{ route('products.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Products
            </a>
            <h1 class="page-title">{{ $product->name }}</h1>
            <p class="page-subtitle">{{ $product->materials->count() }} BOM {{ \Illuminate\Support\Str::plural('item', $product->materials->count()) }}</p>
        </div>
        <div class="page-actions">
            @can('orders.create')
                <a href="{{ route('orders.create', ['product_id' => $product->id]) }}" class="btn btn-secondary"><x-icon name="clipboard" class="h-4 w-4" /> Order</a>
            @endcan
            @can('products.bom.manage')
                <a href="{{ route('products.bom.index', $product) }}" class="btn btn-primary"><x-icon name="layers" class="h-4 w-4" /> Manage BOM</a>
            @endcan
            @can('products.update')
                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary"><x-icon name="pencil" class="h-4 w-4" /> Edit</a>
            @endcan
        </div>
    </x-slot>

    <div class="page">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="card self-start overflow-hidden">
                <x-image-thumb :path="$product->image_path" :alt="$product->name" size="h-64 w-full" class="rounded-none border-0" />
            </div>

            <div class="card overflow-hidden lg:col-span-2">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Bill of Materials</h3>
                        <p class="card-subtitle">Quantities per 1 product. Composite items are expanded into their components when an order is placed.</p>
                    </div>
                </div>

                @if($product->materials->count() === 0)
                    <x-empty-state icon="layers" title="No BOM configured yet">
                        Orders for this product are blocked until a BOM is set.
                        @can('products.bom.manage') <a href="{{ route('products.bom.index', $product) }}" class="link">Configure BOM</a>@endcan
                    </x-empty-state>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th class="text-right">Qty per product</th>
                                    <th>Type</th>
                                    <th class="text-right">In stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->materials as $m)
                                    <tr>
                                        <td>
                                            @can('materials.view')
                                                <a href="{{ route('materials.show', $m) }}" class="link">{{ $m->name }}</a>
                                            @else
                                                <span class="font-medium">{{ $m->name }}</span>
                                            @endcan
                                            @if($m->is_composite && $m->components->count())
                                                <div class="mt-1 text-xs text-slate-500">
                                                    @foreach($m->components as $c)
                                                        {{ $c->childMaterial?->name }} × <x-qty :value="(float) $c->qty_per_parent * (float) $m->pivot->qty_per_product" :unit="$c->childMaterial?->unit?->symbol" />@if(!$loop->last), @endif
                                                    @endforeach
                                                </div>
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
                                        <td class="num">
                                            @if($m->is_composite)
                                                <span class="text-slate-400">—</span>
                                            @else
                                                <x-qty :value="$m->stock" class="{{ $m->is_low ? 'text-red-600 font-medium' : 'text-slate-600' }}" />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
