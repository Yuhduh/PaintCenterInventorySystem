<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    @php
        $workspaceTitle = match (true) {
            request()->routeIs('dashboard') => 'Dashboard',
            request()->routeIs('products.*') => 'Products',
            request()->routeIs('references.*') => 'Product references',
            request()->routeIs('inventory.*') => 'Inventory',
            request()->routeIs('sales.*') => 'Sales & POS',
            request()->routeIs('reports.*') => 'Reports',
            request()->routeIs('audit.*') => 'Audit log',
            request()->routeIs('user-access.*') => 'User management',
            request()->routeIs('profile') => 'Profile',
            default => 'Paint center workspace',
        };
    @endphp
    <a href="#main-content"
        class="fixed left-4 top-4 z-50 -translate-y-24 rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition focus:translate-y-0">
        Skip to content
    </a>
    <div class="app-shell">
        <livewire:layout.navigation />

        <div class="min-w-0 flex-1 sm:overflow-y-auto">
            <!-- Page Heading -->
            <header class="app-topbar print:hidden">
                <div class="mx-auto flex min-h-20 max-w-[1600px] items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    @if (isset($header))
                        {{ $header }}
                    @else
                        <div class="flex items-center gap-3">
                            <div class="app-topbar-mark" aria-hidden="true">
                                <span class="bg-blue-600"></span><span class="bg-yellow-400"></span><span class="bg-pink-500"></span>
                            </div>
                            <div>
                                <p class="app-topbar-title">{{ $workspaceTitle }}</p>
                                <p class="app-topbar-date">{{ now()->format('l, F j, Y') }}</p>
                            </div>
                        </div>
                    @endif

                    <livewire:layout.profile-dropdown />
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" class="app-main-stage" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
