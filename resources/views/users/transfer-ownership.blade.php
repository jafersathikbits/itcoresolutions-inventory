<x-app-layout>
    <x-slot name="title">Transfer Ownership</x-slot>

    <div class="page-narrow">
        <a href="{{ route('users.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Users
        </a>
        <div class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title text-lg">Transfer Ownership</h1>
                    <p class="card-subtitle">The owner is the first Super Admin and cannot be demoted or deleted.</p>
                </div>
            </div>
            <div class="card-body space-y-5">
                <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700"><x-icon name="key" class="h-5 w-5" /></span>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">Current owner</div>
                        <div class="font-semibold text-slate-900">{{ $owner?->name ?? '-' }} <span class="font-normal text-slate-500">{{ $owner?->email }}</span></div>
                    </div>
                </div>

                @if(!$owner)
                    <div class="alert alert-error"><x-icon name="x-circle" class="h-5 w-5 shrink-0" /> Owner not found.</div>
                @elseif($targets->count() === 0)
                    <div class="alert alert-warning">
                        <x-icon name="warning" class="h-5 w-5 shrink-0" />
                        <div>No other Super Admin exists. <a href="{{ route('users.create') }}" class="link">Create another Super Admin</a> first.</div>
                    </div>
                @elseif((int) auth()->id() !== (int) $owner->id)
                    <div class="alert alert-info"><x-icon name="info" class="h-5 w-5 shrink-0" /> Only the current owner can transfer ownership.</div>
                @else
                    <form method="POST" action="{{ route('users.transferOwnership') }}" class="space-y-5"
                          onsubmit="return confirm('Transfer ownership? You will no longer be the owner.');">
                        @csrf
                        <div>
                            <label for="target_user_id" class="form-label">New owner (Super Admin)</label>
                            <select id="target_user_id" name="target_user_id" class="form-input" required>
                                <option value="">Select user</option>
                                @foreach($targets as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-actions border-t border-slate-100 pt-5">
                            <button type="submit" class="btn btn-primary"><x-icon name="key" class="h-4 w-4" /> Transfer Ownership</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
