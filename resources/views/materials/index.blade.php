<x-app-layout>
    <x-slot name="title">Materials</x-slot>

    <x-slot name="header">
        <div>
            <h1 class="page-title">Materials</h1>
            <p class="page-subtitle">
                Raw and composite materials.
                @if($lowCount > 0)
                    <a href="{{ route('materials.index', ['filter' => 'low']) }}" class="font-medium text-red-600 hover:underline">{{ $lowCount }} low on stock</a>
                @else
                    <span class="text-emerald-600">All stock levels OK</span>
                @endif
            </p>
        </div>

        @can('materials.create')
            <div class="page-actions">
                <a href="{{ route('materials.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Add Material
                </a>
            </div>
        @endcan
    </x-slot>

    <div class="page">
        <div class="card overflow-hidden">
            <!-- Toolbar -->
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="segmented">
                    <a href="{{ route('materials.index', array_filter(['q' => $search])) }}" class="{{ request('filter') !== 'low' ? 'active' : '' }}">All</a>
                    <a href="{{ route('materials.index', array_filter(['filter' => 'low', 'q' => $search])) }}" class="{{ request('filter') === 'low' ? 'active' : '' }}">
                        Low stock <span class="ml-1 rounded-full bg-red-100 px-1.5 text-xs text-red-700">{{ $lowCount }}</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('materials.index') }}" class="relative w-full sm:w-72">
                    @if(request('filter') === 'low')
                        <input type="hidden" name="filter" value="low">
                    @endif
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search materials..." class="form-input mt-0 pl-9">
                </form>
            </div>

            @if($materials->isEmpty())
                <x-empty-state icon="cube" :title="$search !== '' ? 'No materials match “' . $search . '”' : (request('filter') === 'low' ? 'No low-stock materials' : 'No materials yet')">
                    @if($search === '' && request('filter') !== 'low')
                        Add materials and upload their images to get started.
                    @endif
                </x-empty-state>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Unit</th>
                                <th class="text-right">Stock</th>
                                <th class="text-right">Threshold</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $m)
                                <tr class="{{ $m->is_low ? 'bg-red-50/40' : '' }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <x-image-thumb :path="$m->image_path" :alt="$m->name" size="h-11 w-11" />
                                            <div class="min-w-0">
                                                <a href="{{ route('materials.show', $m) }}" class="link">{{ $m->name }}</a>
                                                @if($m->is_composite)
                                                    <div class="mt-0.5"><span class="badge badge-amber"><x-icon name="layers" class="h-3 w-3" /> Composite</span></div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-slate-600">{{ $m->unit?->symbol }}</td>
                                    <td class="num font-semibold {{ $m->is_low ? 'text-red-600' : 'text-slate-900' }}"><x-qty :value="$m->stock" /></td>
                                    <td class="num text-slate-600"><x-qty :value="$m->threshold" /></td>
                                    <td><x-stock-badge :low="$m->is_low" /></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('materials.show', $m) }}" class="btn btn-ghost btn-sm" title="View"><x-icon name="eye" class="h-4 w-4" /><span class="sr-only">View</span></a>
                                            @can('materials.update')
                                                <a href="{{ route('materials.edit', $m) }}" class="btn btn-ghost btn-sm" title="Edit"><x-icon name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></a>
                                            @endcan
                                            @can('materials.delete')
                                                <x-confirm-delete :action="route('materials.destroy', $m)" :message="'Delete material “' . $m->name . '”?'" label="" title="Delete" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($materials->hasPages())
                    <div class="border-t border-slate-100 px-4 py-3">{{ $materials->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
