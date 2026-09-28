<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public bool $open = false;
    public bool $collapsed = false;

    /**
     * Toggle the mobile navigation menu.
     */
    public function toggleNavigation(): void
    {
        $this->open = !$this->open;
    }

    /**
     * Toggle the desktop sidebar width.
     */
    public function toggleSidebar(): void
    {
        $this->collapsed = !$this->collapsed;
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav
    aria-label="Primary navigation"
    class="border-b border-cyan-100 bg-white shadow-[12px_0_40px_-32px_rgba(2,8,23,0.9)] transition-[width] duration-200 print:hidden sm:sticky sm:top-0 sm:flex sm:h-screen sm:shrink-0 sm:flex-col sm:overflow-y-auto sm:border-b-0 sm:border-r-0 sm:bg-blue-950 {{ $collapsed ? 'sm:w-20' : 'sm:w-64' }}">
    <!-- Primary Navigation Menu -->
    <div class="px-4 sm:px-4">
        <div class="flex h-16 items-center justify-between sm:h-auto sm:flex-col sm:items-stretch sm:gap-5 sm:py-5">
            <div class="flex sm:flex-col">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 sm:hidden" aria-label="Grade A Paint Center dashboard">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-yellow-300 text-cyan-700">
                        <svg class="h-7 w-7" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M12 8H36L33 29C32.4 33.6 28.5 37 23.9 37C19.4 37 15.5 33.6 14.9 29L12 8Z" fill="currentColor"/><path d="M10 8H38" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/><path d="M24 37V43" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/></svg>
                    </span>
                    <span class="font-extrabold tracking-tight text-slate-950">Grade A Paint</span>
                </a>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="mb-1 hidden items-center gap-3 rounded-xl px-2 py-2 sm:flex {{ $collapsed ? 'justify-center' : '' }}"
                    aria-label="Grade A Paint Center dashboard">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-yellow-300 text-cyan-700 shadow-[0_12px_24px_-14px_rgba(250,204,21,0.8)]">
                        <svg class="h-8 w-8" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M12 8H36L33 29C32.4 33.6 28.5 37 23.9 37C19.4 37 15.5 33.6 14.9 29L12 8Z" fill="currentColor"/><path d="M10 8H38" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/><path d="M24 37V43" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/></svg>
                    </span>
                    @if (!$collapsed)
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-extrabold tracking-tight text-white">Grade A Paint</span>
                            <span class="block text-xs font-medium text-blue-200">POS &amp; Inventory</span>
                        </span>
                    @endif
                </a>

                <!-- Sidebar Toggle -->
                <button wire:click="toggleSidebar" type="button"
                    aria-label="{{ $collapsed ? __('Expand sidebar') : __('Collapse sidebar') }}"
                    class="hidden min-h-10 min-w-10 items-center justify-center rounded-xl p-2 text-blue-200 transition hover:bg-blue-900 hover:text-white sm:flex {{ $collapsed ? 'self-center' : 'self-end' }}">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        @if ($collapsed)
                            <path d="m9 18 6-6-6-6" />
                        @else
                            <path d="m15 18-6-6 6-6" />
                        @endif
                    </svg>
                </button>

                <!-- Navigation Links -->
                <div class="mt-3 hidden space-y-1 sm:block">
                    @if (!$collapsed)
                        <p
                            class="px-3 pb-2 pt-3 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">
                            Operations</p>
                    @endif
                    <x-nav-link
                        class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                        :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <rect width="7" height="9" x="3" y="3" rx="1" />
                            <rect width="7" height="5" x="14" y="3" rx="1" />
                            <rect width="7" height="9" x="14" y="12" rx="1" />
                            <rect width="7" height="5" x="3" y="16" rx="1" />
                        </svg>

                        @if (!$collapsed)
                            <span>{{ __('Dashboard') }}</span>
                        @endif
                    </x-nav-link>
                    @if (auth()->user()->canManageOperations())
                        @if (!$collapsed)
                            <p
                                class="px-3 pb-2 pt-5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">
                                Products</p>
                        @endif
                        <x-nav-link
                        class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                        :href="route('products.index')" :active="request()->routeIs('products.*')" wire:navigate>
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 3h18v18H3z" />
                            <path d="M3 9h18M9 21V9" />
                        </svg>
                        @if (!$collapsed)
                            <span>{{ __('Products') }}</span>
                        @endif
                        </x-nav-link>
                        @if (auth()->user()->canAccessAdministration())
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('references.brands')" :active="request()->routeIs('references.brands')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 13 13 20l-9-9V4h7l9 9Z" stroke-linejoin="round"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Brands') }}</span>
                            @endif
                        </x-nav-link>
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('references.categories')" :active="request()->routeIs('references.categories')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Categories') }}</span>
                            @endif
                        </x-nav-link>
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('references.package-units')" :active="request()->routeIs('references.package-units')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke-linejoin="round"/><path d="m4.5 7.8 7.5 4.4 7.5-4.4M12 12.2V21"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Package Units') }}</span>
                            @endif
                        </x-nav-link>
                        @endif
                        @if (!$collapsed)
                        <p
                            class="px-3 pb-2 pt-5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">
                            Inventory</p>
                        @endif
                        <x-nav-link
                        class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                        :href="route('inventory.stock-in')" :active="request()->routeIs('inventory.stock-in', 'inventory.physical-count')" wire:navigate>
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12M7 8h10" stroke-linecap="round"/><path d="M5 15v5h14v-5" stroke-linejoin="round"/></svg>
                        @if (!$collapsed)
                            <span>{{ __('Inventory') }}</span>
                        @endif
                        </x-nav-link>
                        <x-nav-link
                        class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                        :href="route('inventory.movements')" :active="request()->routeIs('inventory.movements')" wire:navigate>
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 4-3 3 3 3M5 7h14M16 20l3-3-3-3M19 17H5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @if (!$collapsed)
                            <span>{{ __('Movements') }}</span>
                        @endif
                        </x-nav-link>
                    @endif
                    @if (!$collapsed)
                        <p
                            class="px-3 pb-2 pt-5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">
                            Sales &amp; Records</p>
                    @endif
                    <x-nav-link
                        class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                        :href="route('sales.index')" :active="request()->routeIs('sales.index', 'sales.checkout', 'sales.receipt')" wire:navigate>
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"/></svg>
                        @if (!$collapsed)
                            <span>{{ __('Sales') }}</span>
                        @endif
                    </x-nav-link>
                    <x-nav-link
                        class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                        :href="route('sales.history')" :active="request()->routeIs('sales.history')" wire:navigate>
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 3v5h5M12 7v5l3 2" stroke-linecap="round"/></svg>
                        @if (!$collapsed)
                            <span>{{ __('Sales History') }}</span>
                        @endif
                    </x-nav-link>
                    @if (auth()->user()->canManageOperations())
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('reports.inventory')" :active="request()->routeIs('reports.inventory')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Inventory Report') }}</span>
                            @endif
                        </x-nav-link>
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('reports.sales')" :active="request()->routeIs('reports.sales')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-7m5 7V3" stroke-linecap="round"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Sales Report') }}</span>
                            @endif
                        </x-nav-link>
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('reports.mixing')" :active="request()->routeIs('reports.mixing')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 3h6M10 3v5l-4.5 8.2A3.2 3.2 0 0 0 8.3 21h7.4a3.2 3.2 0 0 0 2.8-4.8L14 8V3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Mixing Report') }}</span>
                            @endif
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->canAccessAdministration())
                        @if (!$collapsed)
                            <p
                                class="px-3 pb-2 pt-5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">
                                Administration</p>
                        @endif
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('audit.index')" :active="request()->routeIs('audit.*')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 11h6M9 15h4" stroke-linecap="round"/><path d="M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Z" stroke-linejoin="round"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('Audit Log') }}</span>
                            @endif
                        </x-nav-link>
                        <x-nav-link
                            class="flex w-full items-center gap-2 px-3 py-2 {{ $collapsed ? 'justify-center' : 'justify-start text-start' }}"
                            :href="route('user-access.index')" :active="request()->routeIs('user-access.*')" wire:navigate>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M22 11h-6" stroke-linecap="round"/></svg>
                            @if (!$collapsed)
                                <span>{{ __('User Access') }}</span>
                            @endif
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button wire:click="toggleNavigation" type="button" aria-controls="mobile-navigation"
                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                    aria-label="{{ $open ? __('Close navigation menu') : __('Open navigation menu') }}"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl p-2 text-cyan-800 transition duration-150 hover:bg-cyan-50 hover:text-cyan-950 focus:bg-cyan-50 focus:text-cyan-950 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path class="{{ $open ? 'hidden' : 'inline-flex' }}" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path class="{{ $open ? 'inline-flex' : 'hidden' }}" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="mobile-navigation" class="{{ $open ? 'block' : 'hidden' }} border-t border-cyan-100 bg-white sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <p class="border-b border-cyan-100 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-cyan-700">
                Operations</p>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if (auth()->user()->canManageOperations())
                <p
                    class="border-b border-cyan-100 px-3 pb-2 pt-3 text-[10px] font-bold uppercase tracking-wider text-cyan-700">
                    Products</p>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" wire:navigate>
                    {{ __('Products') }}
                </x-responsive-nav-link>
                <p
                    class="border-b border-cyan-100 px-3 pb-2 pt-3 text-[10px] font-bold uppercase tracking-wider text-cyan-700">
                    Inventory</p>
            <x-responsive-nav-link :href="route('inventory.stock-in')" :active="request()->routeIs('inventory.stock-in', 'inventory.physical-count')" wire:navigate>
                    {{ __('Inventory') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('inventory.movements')" :active="request()->routeIs('inventory.movements')" wire:navigate>
                    {{ __('Movements') }}
                </x-responsive-nav-link>
            @endif
            <p
                class="border-b border-cyan-100 px-3 pb-2 pt-3 text-[10px] font-bold uppercase tracking-wider text-cyan-700">
                Sales &amp; Records</p>
            <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.index', 'sales.checkout', 'sales.receipt')" wire:navigate>
                {{ __('Sales') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sales.history')" :active="request()->routeIs('sales.history')" wire:navigate>
                {{ __('Sales History') }}
            </x-responsive-nav-link>
            @if (auth()->user()->canManageOperations())
                <p class="border-b border-slate-200 px-3 pb-2 pt-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Reports</p>
                <x-responsive-nav-link :href="route('reports.inventory')" :active="request()->routeIs('reports.inventory')" wire:navigate>
                    {{ __('Inventory Report') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.sales')" :active="request()->routeIs('reports.sales')" wire:navigate>
                    {{ __('Sales Report') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.mixing')" :active="request()->routeIs('reports.mixing')" wire:navigate>
                    {{ __('Mixing Report') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->canAccessAdministration())
                <p
                    class="border-b border-cyan-100 px-3 pb-2 pt-3 text-[10px] font-bold uppercase tracking-wider text-cyan-700">
                    Administration</p>
                <x-responsive-nav-link :href="route('references.brands')" :active="request()->routeIs('references.brands')" wire:navigate>
                    {{ __('Brands') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('references.categories')" :active="request()->routeIs('references.categories')" wire:navigate>
                    {{ __('Categories') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('references.package-units')" :active="request()->routeIs('references.package-units')" wire:navigate>
                    {{ __('Package Units') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('audit.index')" :active="request()->routeIs('audit.*')" wire:navigate>
                    {{ __('Audit Log') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user-access.index')" :active="request()->routeIs('user-access.*')" wire:navigate>
                    {{ __('User Access') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-cyan-100 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-bold text-slate-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-slate-500">{{ auth()->user()->username }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
