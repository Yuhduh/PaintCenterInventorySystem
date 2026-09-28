<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>
<div x-data="{ showPassword: false }">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form wire:submit="login" novalidate>
        <!-- Username -->
        <div>
            <label for="username" class="app-label flex items-center gap-2">
                <svg class="h-4 w-4 text-cyan-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M20 21a8 8 0 0 0-16 0" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                {{ __('Username') }}
            </label>
            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-slate-400" aria-hidden="true">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21a8 8 0 0 0-16 0" /><circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
                <x-text-input wire:model="form.username" id="username" class="block w-full pl-11" type="text"
                    name="username" required autofocus autocomplete="username" placeholder="Enter your username" />
            </div>
            <x-input-error :messages="$errors->get('form.username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <label for="password" class="app-label flex items-center gap-2">
                <svg class="h-4 w-4 text-violet-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                {{ __('Password') }}
            </label>

            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-slate-400" aria-hidden="true">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </span>
                <x-text-input wire:model="form.password" id="password" class="block w-full px-11" type="password"
                    x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                    placeholder="Enter your password" />
                <button type="button" class="password-visibility" x-on:click="showPassword = ! showPassword"
                    x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                    x-bind:aria-pressed="showPassword" aria-label="Show password">
                    <svg x-show="! showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2.1 12a10 10 0 0 1 19.8 0 10 10 0 0 1-19.8 0Z" /><circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-cloak x-show="showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m3 3 18 18" /><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8" /><path d="M9.9 4.2A10.2 10.2 0 0 1 22 12a15.7 15.7 0 0 1-2.1 3.2" /><path d="M6.6 6.6A15.7 15.7 0 0 0 2 12a10.2 10.2 0 0 0 14.1 7.8" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="mt-7">
            <x-primary-button class="login-submit w-full" wire:loading.attr="disabled" wire:target="login">
                <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-90" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" />
                </svg>
                <span wire:loading.remove wire:target="login">{{ __('Login') }}</span>
                <span wire:loading wire:target="login">{{ __('Logging in…') }}</span>
            </x-primary-button>
        </div>
    </form>
</div>
