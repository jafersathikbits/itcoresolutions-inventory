<x-app-layout>
    <x-slot name="title">Units</x-slot>

    <x-slot name="header">
        <div>
            <h1 class="page-title">Units</h1>
            <p class="page-subtitle">Measurement units used by materials (ft, sqft, nos, kg, ml...).</p>
        </div>
        @can('units.create')
            <div class="page-actions">
                <a href="{{ route('units.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Add Unit</a>
            </div>
        @endcan
    </x-slot>

    <div class="page">
        <div class="card overflow-hidden">
            @if($units->isEmpty())
                <x-empty-state icon="scale" title="No units yet">Create units like feet, sqft, nos, ml or kg.</x-empty-state>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th>Decimals</th>
                                <th class="text-right">Materials</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $unit->name }}</td>
                                    <td><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">{{ $unit->symbol }}</code></td>
                                    <td>
                                        @if($unit->allow_decimal)
                                            <span class="badge badge-green">Allowed</span>
                                        @else
                                            <span class="badge badge-gray">Whole numbers</span>
                                        @endif
                                    </td>
                                    <td class="num text-slate-600">{{ $unit->materials_count }}</td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            @can('units.update')
                                                <a href="{{ route('units.edit', $unit) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /> Edit</a>
                                            @endcan
                                            @can('units.delete')
                                                @if($unit->materials_count === 0)
                                                    <x-confirm-delete :action="route('units.destroy', $unit)" :message="'Delete unit “' . $unit->name . '”?'" />
                                                @else
                                                    <span class="btn btn-sm cursor-not-allowed text-slate-300" title="In use by {{ $unit->materials_count }} material(s)"><x-icon name="trash" class="h-4 w-4" /> Delete</span>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
