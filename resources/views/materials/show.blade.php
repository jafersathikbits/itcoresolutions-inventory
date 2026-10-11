<x-app-layout>
    <x-slot name="title">{{ $material->name }}</x-slot>

    <x-slot name="header">
        <div class="min-w-0">
            <a href="{{ route('materials.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
                <x-icon name="arrow-left" class="h-4 w-4" /> Materials
            </a>
            <h1 class="page-title flex flex-wrap items-center gap-2">
                {{ $material->name }}
                <x-stock-badge :low="$material->is_low" />
                @if($material->is_composite)
                    <span class="badge badge-amber"><x-icon name="layers" class="h-3 w-3" /> Composite</span>
                @endif
            </h1>
        </div>

        <div class="page-actions">
            @if($material->is_composite)
                @can('materials.composite.manage')
                    <a href="{{ route('materials.components.index', $material) }}" class="btn btn-primary">
                        <x-icon name="layers" class="h-4 w-4" /> Manage Components
                    </a>
                @endcan
            @endif
            @can('materials.update')
                <a href="{{ route('materials.edit', $material) }}" class="btn btn-secondary">
                    <x-icon name="pencil" class="h-4 w-4" /> Edit
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="page">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: image + key figures -->
            <div class="space-y-6">
                <div class="card overflow-hidden">
                    <x-image-thumb :path="$material->image_path" :alt="$material->name" size="h-60 w-full" class="rounded-none border-0 border-b" />
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-slate-500">Current stock</dt>
                            <dd class="text-lg font-bold {{ $material->is_low ? 'text-red-600' : 'text-slate-900' }}"><x-qty :value="$material->stock" :unit="$material->unit?->symbol" /></dd>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-slate-500">Threshold</dt>
                            <dd class="font-semibold"><x-qty :value="$material->threshold" :unit="$material->unit?->symbol" /></dd>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-slate-500">Unit</dt>
                            <dd class="font-medium">{{ $material->unit?->name }} ({{ $material->unit?->symbol }})</dd>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-slate-500">Type</dt>
                            <dd class="font-medium">{{ $material->is_composite ? 'Composite' : 'Raw material' }}</dd>
                        </div>
                    </dl>
                </div>

                @if($material->is_composite && $material->components->count() === 0)
                    <div class="alert alert-warning">
                        <x-icon name="warning" class="h-5 w-5 shrink-0" />
                        <div>This composite has <b>no components</b> yet, so orders using it won't deduct any stock. Use <b>Manage Components</b>.</div>
                    </div>
                @endif
            </div>

            <!-- Right -->
            <div class="space-y-6 lg:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Add Stock</h3>
                            <p class="card-subtitle">Every addition is recorded in the stock history below.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @can('materials.stock.add')
                            <x-form-errors />
                            <form method="POST" action="{{ route('materials.stock.add', $material) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-6 sm:items-end">
                                @csrf
                                <div class="sm:col-span-2">
                                    <label for="qty" class="form-label">Quantity to add</label>
                                    <div class="relative">
                                        <input id="qty" name="qty" type="number" step="0.001" min="0.001" value="{{ old('qty') }}" class="form-input pr-12" required>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 mt-1 flex items-center pr-3 text-sm text-slate-400">{{ $material->unit?->symbol }}</span>
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="reason" class="form-label">Reason <span class="font-normal text-slate-400">(optional)</span></label>
                                    <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" class="form-input" placeholder="Purchase / Adjustment / etc">
                                </div>
                                <div class="sm:col-span-1">
                                    <button type="submit" class="btn btn-primary w-full"><x-icon name="plus" class="h-4 w-4" /> Add</button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-slate-500">You do not have permission to add stock.</p>
                        @endcan
                    </div>
                </div>

                @if($material->is_composite)
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Composite Breakdown</h3>
                                <p class="card-subtitle">Raw materials needed for 1 {{ $material->unit?->symbol }}.</p>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($material->components->count() === 0)
                                <p class="text-sm text-slate-500">No components yet.</p>
                            @else
                                @include('materials.partials.composite_tree', ['material' => $material, 'level' => 0])
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card overflow-hidden">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Stock History</h3>
                            <p class="card-subtitle">Latest {{ $stockLogs->count() }} movements.</p>
                        </div>
                    </div>
                    @if($stockLogs->isEmpty())
                        <x-empty-state icon="clipboard" title="No stock movements yet" />
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>When</th>
                                        <th>Type</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Before → After</th>
                                        <th>By / Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockLogs as $log)
                                        <tr>
                                            <td class="whitespace-nowrap text-slate-600" title="{{ $log->created_at?->format('Y-m-d H:i') }}">{{ $log->created_at?->format('d M, H:i') }}</td>
                                            <td>
                                                @if($log->type === 'add')
                                                    <span class="badge badge-green">+ Added</span>
                                                @elseif($log->type === 'deduct')
                                                    <span class="badge badge-red">− Deducted</span>
                                                @else
                                                    <span class="badge badge-gray">Adjusted</span>
                                                @endif
                                            </td>
                                            <td class="num font-medium"><x-qty :value="$log->qty" /></td>
                                            <td class="num whitespace-nowrap text-slate-600"><x-qty :value="$log->before_stock" /> → <x-qty :value="$log->after_stock" class="font-medium text-slate-900" /></td>
                                            <td>
                                                <div class="text-slate-800">{{ $log->user?->name ?? '-' }}</div>
                                                <div class="text-xs text-slate-500">{{ $log->reason }}</div>
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
    </div>
</x-app-layout>
