<x-app-layout>
    <x-slot name="title">Add Unit</x-slot>

    <div class="page-narrow">
        <a href="{{ route('units.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Units
        </a>
        <div class="card">
            <div class="card-header"><h1 class="card-title text-lg">Add Unit</h1></div>
            <div class="card-body">
                <x-form-errors />
                <form method="POST" action="{{ route('units.store') }}" class="space-y-6">
                    @csrf
                    @include('units.partials.form')
                    <div class="form-actions border-t border-slate-100 pt-5">
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save Unit</button>
                        <a href="{{ route('units.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
