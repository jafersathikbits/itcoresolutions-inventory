<x-app-layout>
    <x-slot name="title">Edit {{ $material->name }}</x-slot>

    <div class="page-narrow">
        <a href="{{ route('materials.show', $material) }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> {{ $material->name }}
        </a>

        <div class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title text-lg">Edit Material</h1>
                    <p class="card-subtitle">Use “Add Stock” on the material page to record purchases with a history entry.</p>
                </div>
            </div>
            <div class="card-body">
                <x-form-errors />

                <form method="POST" action="{{ route('materials.update', $material) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @include('materials.partials.form', ['material' => $material])

                    <div class="form-actions border-t border-slate-100 pt-5">
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Update Material</button>
                        <a href="{{ route('materials.show', $material) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
