<x-app-layout>
    <x-slot name="title">Add Product</x-slot>

    <div class="page-narrow">
        <a href="{{ route('products.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Products
        </a>

        <div class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title text-lg">Add Product</h1>
                    <p class="card-subtitle">A product image is required. You can configure the BOM after saving.</p>
                </div>
            </div>
            <div class="card-body">
                <x-form-errors />
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @include('products.partials.form')
                    <div class="form-actions border-t border-slate-100 pt-5">
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save Product</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
