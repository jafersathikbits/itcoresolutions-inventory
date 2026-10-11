<x-app-layout>
    <x-slot name="title">Products</x-slot>

    <x-slot name="header">
        <div>
            <h1 class="page-title">Products</h1>
            <p class="page-subtitle">Finished products and their bill of materials (BOM).</p>
        </div>
        @can('products.create')
            <div class="page-actions">
                <a href="{{ route('products.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Add Product</a>
            </div>
        @endcan
    </x-slot>

    <div class="page space-y-5">
        <form method="GET" action="{{ route('products.index') }}" class="relative w-full sm:w-80">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Search products..." class="form-input mt-0 pl-9 bg-white">
        </form>

        @if($products->isEmpty())
            <div class="card">
                <x-empty-state icon="box" :title="$search !== '' ? 'No products match “' . $search . '”' : 'No products yet'">
                    @if($search === '') Add products, upload an image and configure their BOM. @endif
                </x-empty-state>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $p)
                    <div class="card group flex flex-col overflow-hidden transition hover:shadow-md">
                        <a href="{{ route('products.show', $p) }}" class="block overflow-hidden">
                            <x-image-thumb :path="$p->image_path" :alt="$p->name" size="h-44 w-full" class="rounded-none border-0 transition duration-300 group-hover:scale-105" />
                        </a>
                        <div class="flex flex-1 flex-col p-4">
                            <a href="{{ route('products.show', $p) }}" class="font-semibold text-slate-900 hover:text-brand-700">{{ $p->name }}</a>
                            <div class="mt-1 mb-4">
                                @if($p->materials_count > 0)
                                    <span class="badge badge-blue">{{ $p->materials_count }} BOM {{ \Illuminate\Support\Str::plural('item', $p->materials_count) }}</span>
                                @else
                                    <span class="badge badge-amber"><x-icon name="warning" class="h-3 w-3" /> No BOM</span>
                                @endif
                            </div>

                            <div class="mt-auto flex items-center gap-1 border-t border-slate-100 pt-3">
                                @can('products.bom.manage')
                                    <a href="{{ route('products.bom.index', $p) }}" class="btn btn-ghost btn-sm"><x-icon name="layers" class="h-4 w-4" /> BOM</a>
                                @endcan
                                <div class="ml-auto flex items-center gap-1">
                                    @can('products.update')
                                        <a href="{{ route('products.edit', $p) }}" class="btn btn-ghost btn-sm" title="Edit"><x-icon name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></a>
                                    @endcan
                                    @can('products.delete')
                                        <x-confirm-delete :action="route('products.destroy', $p)" :message="'Delete product “' . $p->name . '”?'" label="" title="Delete" />
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($products->hasPages())
                <div>{{ $products->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
