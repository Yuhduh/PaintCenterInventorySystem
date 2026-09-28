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

<body class="font-sans text-gray-900 antialiased">
    @php
        $authCopy = match (true) {
            request()->routeIs('login') => ['Welcome back', 'Sign in with your assigned credentials to continue.'],
            request()->routeIs('register') => ['Create your account', 'Set up your Grade A Paint Center access.'],
            request()->routeIs('password.request') => ['Reset access', 'Request a secure password reset link.'],
            request()->routeIs('password.reset') => ['Choose a new password', 'Create a secure password for your account.'],
            request()->routeIs('password.confirm') => ['Confirm your identity', 'Re-enter your password to continue securely.'],
            request()->routeIs('verification.notice') => ['Verify your email', 'Confirm your email address to activate access.'],
            default => ['Secure access', 'Continue to the Grade A Paint Center workspace.'],
        };
    @endphp
    @if (request()->routeIs('login'))
        <main class="login-studio" aria-labelledby="login-title">
            <x-paint-studio-background />

            <section class="login-card">
                <div class="login-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8H36L33 29C32.4 33.6 28.5 37 23.9 37C19.4 37 15.5 33.6 14.9 29L12 8Z" fill="currentColor" />
                        <path d="M10 8H38" stroke="#0f172a" stroke-width="4" stroke-linecap="round" />
                        <path d="M19 16C21 14 24 14 26 16" stroke="white" stroke-width="3" stroke-linecap="round" />
                        <path d="M24 37V43" stroke="#0f172a" stroke-width="4" stroke-linecap="round" />
                    </svg>
                </div>

                <div class="text-center">
                    <h1 id="login-title" class="text-3xl font-extrabold tracking-[-0.03em] text-slate-950 sm:text-4xl">Grade A Paint Center</h1>
                    <p class="mt-2 text-sm font-extrabold uppercase tracking-[0.12em] text-cyan-700">POS &amp; Inventory Management System</p>
                    <p class="mx-auto mt-4 max-w-sm text-sm leading-6 text-slate-600">Manage your sales, inventory, and paint products in one place.</p>
                </div>

                <div class="login-swatches" aria-hidden="true">
                    <span class="bg-red-500"></span>
                    <span class="bg-orange-400"></span>
                    <span class="bg-yellow-400"></span>
                    <span class="bg-emerald-500"></span>
                    <span class="bg-cyan-500"></span>
                    <span class="bg-violet-500"></span>
                    <span class="bg-pink-500"></span>
                </div>

                {{ $slot }}
            </section>
        </main>
    @else
    <div class="min-h-screen bg-slate-950 lg:grid lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <section class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16"
            aria-label="Grade A Paint Center">
            <div class="relative z-10 flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-cyan-400 text-xl font-extrabold text-cyan-950">G</span>
                <div>
                    <p class="text-lg font-extrabold tracking-tight">Grade A Paint Center</p>
                    <p class="text-sm text-cyan-100">Inventory and sales workspace</p>
                </div>
            </div>
            <div class="my-10" aria-hidden="true">
                <x-paint-drips />
                <div class="mt-8 flex flex-wrap gap-x-5 gap-y-2 text-xs font-bold text-cyan-100">
                    <span>Products</span><span>Inventory</span><span>Custom mixing</span><span>Sales</span>
                </div>
            </div>
            <div class="relative z-10 max-w-lg pb-6">
                <p class="text-4xl font-extrabold leading-tight tracking-[-0.03em] xl:text-5xl">One workspace from shelf to sale.</p>
                <p class="mt-5 max-w-md text-base leading-7 text-slate-300">Move through products, inventory, custom mixes, and checkout with every adjustment kept in view.</p>
            </div>
        </section>
        <div class="flex min-h-screen w-full flex-col bg-slate-50">
            <livewire:current-date-time />
            <x-paint-drips class="paint-drips-mobile lg:hidden" />

            <div class="flex flex-1 items-center px-5 py-10 sm:px-10 lg:px-16">
                <div class="login-panel relative mx-auto w-full max-w-md overflow-hidden rounded-2xl bg-white p-7 shadow-[0_28px_70px_-38px_rgba(15,23,42,0.55)] sm:p-10">
                    <div class="flex flex-col gap-7">
                        <div>
                            <div class="mb-7 flex items-center gap-3 lg:hidden">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan-700 text-lg font-extrabold text-white">G</span>
                                <span class="font-extrabold text-slate-900">Grade A Paint Center</span>
                            </div>
                            <h1 class="text-3xl font-extrabold tracking-[-0.03em] text-slate-950">{{ $authCopy[0] }}</h1>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $authCopy[1] }}</p>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</body>

</html>
