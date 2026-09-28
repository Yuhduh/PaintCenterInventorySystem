<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="app-page">
        <div class="app-page-header">
            <div><h1 class="app-page-title">Profile settings</h1><p class="app-page-description">Manage your identity, credentials, and account lifecycle.</p></div>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="app-panel p-5 sm:p-7">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="app-panel p-5 sm:p-7">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="app-panel p-5 sm:p-7 lg:col-span-2">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
