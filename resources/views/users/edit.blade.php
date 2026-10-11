<x-app-layout>
    <x-slot name="title">Edit {{ $user->name }}</x-slot>

    <div class="page-narrow">
        <a href="{{ route('users.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Users
        </a>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title text-lg">Edit User</h1>
                @if($isOwner)<span class="badge badge-indigo"><x-icon name="key" class="h-3 w-3" /> Owner</span>@endif
            </div>
            <div class="card-body">
                <x-form-errors />
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('users.partials.form', ['user' => $user, 'isOwner' => $isOwner])
                    <div class="form-actions border-t border-slate-100 pt-5">
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Update User</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
