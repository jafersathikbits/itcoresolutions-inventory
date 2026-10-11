{{-- Sidebar navigation (desktop: fixed; mobile: slide-over). $unreadNotificationsCount is shared by AppServiceProvider. --}}
@php
    $links = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard', 'can' => 'dashboard.view'],
        ['route' => 'orders.index', 'match' => 'orders.*', 'icon' => 'clipboard', 'label' => 'Orders', 'can' => 'orders.view'],
        ['section' => 'Inventory'],
        ['route' => 'materials.index', 'match' => 'materials.*', 'icon' => 'cube', 'label' => 'Materials', 'can' => 'materials.view'],
        ['route' => 'products.index', 'match' => 'products.*', 'icon' => 'box', 'label' => 'Products', 'can' => 'products.view'],
        ['route' => 'units.index', 'match' => 'units.*', 'icon' => 'scale', 'label' => 'Units', 'can' => 'units.view'],
        ['section' => 'Insights'],
        ['route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'document', 'label' => 'Reports', 'can' => 'reports.view'],
        ['route' => 'notifications.index', 'match' => 'notifications.*', 'icon' => 'bell', 'label' => 'Notifications', 'can' => null],
        ['section' => 'Administration'],
        ['route' => 'users.index', 'match' => 'users.*', 'icon' => 'users', 'label' => 'Users', 'can' => 'users.view'],
    ];

    // Drop links the user cannot open, then drop section titles that ended up empty
    $visible = [];
    foreach ($links as $link) {
        if (isset($link['section']) || $link['can'] === null || auth()->user()->can($link['can'])) {
            $visible[] = $link;
        }
    }
    $visible = array_values(array_filter($visible, function ($link, $i) use ($visible) {
        if (!isset($link['section'])) return true;
        $next = $visible[$i + 1] ?? null;
        return $next && !isset($next['section']);
    }, ARRAY_FILTER_USE_BOTH));
@endphp

<!-- Mobile backdrop -->
<div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-slate-900 transition-transform duration-200 ease-out lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    aria-label="Main navigation"
>
    <div class="flex h-16 shrink-0 items-center gap-3 px-5 border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white">
                <img src="{{ asset('images/logo.png') }}" alt="IT Core Solutions" class="h-7 w-7 object-contain">
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-white leading-tight truncate">IT Core Solutions</span>
                <span class="block text-xs text-slate-400 leading-tight">Inventory</span>
            </span>
        </a>
        <button type="button" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Close menu">
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @foreach($visible as $link)
            @if(isset($link['section']))
                <div class="nav-section">{{ $link['section'] }}</div>
            @else
                <x-nav-item :href="route($link['route'])" :active="request()->routeIs($link['match'])" :icon="$link['icon']">
                    {{ $link['label'] }}
                    @if($link['route'] === 'notifications.index' && $unreadNotificationsCount > 0)
                        <x-slot name="badge">
                            <span class="rounded-full bg-accent-500 px-2 py-0.5 text-[11px] font-semibold text-white">{{ $unreadNotificationsCount }}</span>
                        </x-slot>
                    @endif
                </x-nav-item>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3">
        <x-nav-item :href="route('profile.edit')" :active="request()->routeIs('profile.*')" icon="user">
            My Profile
        </x-nav-item>
    </div>
</aside>
