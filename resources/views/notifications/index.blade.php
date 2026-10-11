<x-app-layout>
    <x-slot name="title">Notifications</x-slot>

    <x-slot name="header">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">{{ $unreadCount > 0 ? $unreadCount . ' unread' : 'You are all caught up' }}</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.markAllRead') }}" class="page-actions">
                @csrf
                <button type="submit" class="btn btn-secondary"><x-icon name="check" class="h-4 w-4" /> Mark all as read</button>
            </form>
        @endif
    </x-slot>

    <div class="page space-y-4">
        <div class="segmented">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" class="{{ $filter !== 'unread' ? 'active' : '' }}">All</a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="{{ $filter === 'unread' ? 'active' : '' }}">
                Unread @if($unreadCount > 0)<span class="ml-1 rounded-full bg-accent-500 px-1.5 text-xs text-white">{{ $unreadCount }}</span>@endif
            </a>
        </div>

        <div class="card overflow-hidden">
            @if($notifications->count() === 0)
                <x-empty-state icon="bell" :title="$filter === 'unread' ? 'No unread notifications' : 'No notifications yet'" />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($notifications as $n)
                        @php
                            [$tone, $icon] = match ($n->type) {
                                'order_blocked' => ['bg-red-50 text-red-600', 'x-circle'],
                                'order_warning' => ['bg-amber-50 text-amber-600', 'warning'],
                                default => ['bg-brand-50 text-brand-600', 'info'],
                            };
                        @endphp
                        <li class="flex gap-4 p-4 sm:p-5 {{ $n->read_at ? '' : 'bg-brand-50/40' }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $tone }}">
                                <x-icon :name="$icon" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-900">{{ $n->title }}</span>
                                    @unless($n->read_at)
                                        <span class="h-2 w-2 rounded-full bg-accent-500" title="Unread"></span>
                                    @endunless
                                </div>
                                <div class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $n->message }}</div>
                                <div class="mt-2 text-xs text-slate-500">
                                    <span title="{{ $n->created_at?->format('Y-m-d H:i') }}">{{ $n->created_at?->diffForHumans() }}</span>
                                    @if($n->read_at) · read {{ $n->read_at->diffForHumans() }} @endif
                                </div>
                            </div>
                            @unless($n->read_at)
                                <form method="POST" action="{{ route('notifications.markRead', $n) }}" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="filter" value="{{ $filter }}">
                                    <button type="submit" class="btn btn-ghost btn-sm" title="Mark as read"><x-icon name="check" class="h-4 w-4" /><span class="hidden sm:inline">Mark read</span></button>
                                </form>
                            @endunless
                        </li>
                    @endforeach
                </ul>

                @if($notifications->hasPages())
                    <div class="border-t border-slate-100 px-4 py-3">{{ $notifications->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
