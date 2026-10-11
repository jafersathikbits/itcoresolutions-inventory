<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>IT Core Solutions Inventory</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="h-full font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen">
            <!-- Brand panel -->
            <div class="relative hidden w-1/2 overflow-hidden bg-slate-900 lg:flex lg:flex-col lg:justify-between p-12">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>

                <div class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white">
                        <img src="{{ asset('images/logo.png') }}" alt="IT Core Solutions" class="h-8 w-8 object-contain">
                    </span>
                    <span class="text-lg font-semibold text-white">IT Core Solutions</span>
                </div>

                <div class="relative max-w-md">
                    <h1 class="text-3xl font-bold leading-tight text-white">Inventory, orders and stock in one place.</h1>
                    <p class="mt-4 text-slate-300">
                        Track materials and composites, manage product BOMs, place orders with automatic stock checks
                        and get low-stock alerts and daily reports.
                    </p>
                    <ul class="mt-8 space-y-3 text-sm text-slate-300">
                        <li class="flex items-center gap-3"><x-icon name="check-circle" class="h-5 w-5 text-accent-400" /> Automatic stock deduction from BOM</li>
                        <li class="flex items-center gap-3"><x-icon name="check-circle" class="h-5 w-5 text-accent-400" /> Low-stock warnings via Telegram</li>
                        <li class="flex items-center gap-3"><x-icon name="check-circle" class="h-5 w-5 text-accent-400" /> Daily PDF reports</li>
                    </ul>
                </div>

                <div class="relative text-xs text-slate-500">&copy; {{ date('Y') }} IT Core Solutions</div>
            </div>

            <!-- Form panel -->
            <div class="flex w-full flex-col items-center justify-center bg-slate-50 px-6 py-12 lg:w-1/2">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="IT Core Solutions" class="h-10 w-10 object-contain">
                    <span class="text-lg font-semibold text-slate-800">IT Core Solutions Inventory</span>
                </div>

                <div class="w-full max-w-md card card-body sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
