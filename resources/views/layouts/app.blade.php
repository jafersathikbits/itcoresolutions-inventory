<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' · ' : '' }}IT Core Solutions Inventory</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="h-full font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-full">

            @include('layouts.navigation')

            <div class="lg:pl-64 flex min-h-screen flex-col">
                <!-- Top bar -->
                <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <button type="button" class="btn-ghost -ml-2 rounded-lg p-2 lg:hidden" @click="sidebarOpen = true" aria-label="Open menu">
                        <x-icon name="menu" class="h-6 w-6" />
                    </button>

                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-slate-500 truncate">
                            {{ now()->format('l, d M Y') }}
                        </div>
                    </div>

                    <a href="{{ route('notifications.index') }}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="Notifications">
                        <x-icon name="bell" class="h-6 w-6" />
                        @if($unreadNotificationsCount > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-accent-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                            </span>
                        @endif
                    </a>

                    <x-dropdown align="right" width="w-56">
                        <x-slot name="trigger">
                            <button type="button" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden text-left sm:block">
                                    <span class="block text-sm font-medium text-slate-800 leading-tight">{{ Auth::user()->name }}</span>
                                    <span class="block text-xs text-slate-500 leading-tight">{{ $currentRoleName }}</span>
                                </span>
                                <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="border-b border-slate-100 px-4 py-2">
                                <div class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</div>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')">
                                <x-icon name="user" class="h-4 w-4" /> Profile
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm text-red-600 hover:bg-red-50">
                                    <x-icon name="logout" class="h-4 w-4" /> Log out
                                </button>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </header>

                <main class="flex-1">
                    <div class="page pb-0">
                        <x-flash />
                    </div>

                    @isset($header)
                        <div class="page pt-0 pb-0">
                            <div class="page-header">
                                {{ $header }}
                            </div>
                        </div>
                    @endisset

                    {{ $slot }}
                </main>

                <footer class="px-4 py-4 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
                    &copy; {{ date('Y') }} IT Core Solutions · Inventory Management
                </footer>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
