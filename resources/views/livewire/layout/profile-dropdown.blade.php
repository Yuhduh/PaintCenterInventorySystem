<div>
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button
                class="inline-flex min-h-11 items-center rounded-xl bg-white px-3 py-2 text-sm font-semibold leading-4 text-slate-700 ring-1 ring-inset ring-slate-200 transition duration-200 hover:bg-slate-50 hover:text-slate-950">
                <span class="me-2 grid h-7 w-7 place-items-center rounded-lg bg-yellow-300 text-xs font-extrabold uppercase text-yellow-950">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span class="text-start">
                    <span class="block" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                    <span class="mt-0.5 block text-[10px] font-bold uppercase tracking-wider text-violet-600">{{ auth()->user()->role }}</span>
                </span>

                <div class="ms-1">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile')" wire:navigate>
                {{ __('Profile') }}
            </x-dropdown-link>

            <button wire:click="logout" class="w-full text-start">
                <x-dropdown-link>
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </button>
        </x-slot>
    </x-dropdown>
</div>
