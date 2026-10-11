<x-app-layout>
    <x-slot name="title">Users</x-slot>

    <x-slot name="header">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Owner: <b class="text-slate-700">{{ $owner?->name ?? '-' }}</b> (the first Super Admin)</p>
        </div>
        <div class="page-actions">
            @can('users.transfer_ownership')
                <a href="{{ route('users.transferOwnershipForm') }}" class="btn btn-secondary"><x-icon name="key" class="h-4 w-4" /> Transfer Ownership</a>
            @endcan
            @can('users.create')
                <a href="{{ route('users.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Create User</a>
            @endcan
        </div>
    </x-slot>

    <div class="page">
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Telegram</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            @php
                                $role = $u->roles->first()?->name;
                                $isOwner = $owner && ((int) $owner->id === (int) $u->id);
                                $roleBadge = match ($role) {
                                    'Super Admin' => 'badge-purple',
                                    'Admin' => 'badge-blue',
                                    'Operator' => 'badge-gray',
                                    default => 'badge-red',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->name, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 font-medium text-slate-900">
                                                {{ $u->name }}
                                                @if($isOwner)<span class="badge badge-indigo"><x-icon name="key" class="h-3 w-3" /> Owner</span>@endif
                                                @if((int) $u->id === (int) auth()->id())<span class="badge badge-gray">You</span>@endif
                                            </div>
                                            <div class="truncate text-xs text-slate-500">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge {{ $roleBadge }}">{{ $role ?? 'No role' }}</span></td>
                                <td class="text-slate-600">
                                    @if($u->telegram_chat_id)
                                        <span class="inline-flex items-center gap-1"><x-icon name="check-circle" class="h-4 w-4 text-emerald-500" /> {{ $u->telegram_chat_id }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        @can('users.update')
                                            <a href="{{ route('users.edit', $u) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /> Edit</a>
                                        @endcan
                                        @can('users.delete')
                                            @unless($isOwner || (int) $u->id === (int) auth()->id())
                                                <x-confirm-delete :action="route('users.destroy', $u)" :message="'Delete user “' . $u->name . '”?'" />
                                            @endunless
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
