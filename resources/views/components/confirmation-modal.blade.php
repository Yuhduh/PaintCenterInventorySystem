@props(['name' => 'action-confirmation'])

<div
    x-data="{
        confirmation: {
            title: 'Confirm action',
            message: 'Are you sure you want to continue?',
            action: '',
            arguments: [],
            confirmText: 'Confirm',
            tone: 'danger',
        },
        processing: false,
        openConfirmation(details) {
            this.confirmation = {
                title: details.title ?? 'Confirm action',
                message: details.message ?? 'Are you sure you want to continue?',
                action: details.action ?? '',
                arguments: details.arguments ?? [],
                confirmText: details.confirmText ?? 'Confirm',
                tone: details.tone ?? 'danger',
            };
            this.$dispatch('open-modal', '{{ $name }}');
        },
        async confirmAction() {
            if (!this.confirmation.action || this.processing) {
                return;
            }

            this.processing = true;

            try {
                await this.$wire[this.confirmation.action](...this.confirmation.arguments);
                this.$dispatch('close-modal', '{{ $name }}');
            } finally {
                this.processing = false;
            }
        },
    }"
    x-on:request-confirmation.window="openConfirmation($event.detail)"
>
    <x-modal :name="$name" max-width="md" focusable>
        <div class="p-6" role="alertdialog" aria-modal="true" aria-labelledby="confirmation-title"
            aria-describedby="confirmation-message">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full"
                    x-bind:class="confirmation.tone === 'danger' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.166-.17 2.625-1.515 2.625H3.72c-1.347 0-2.189-1.459-1.516-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 7a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 id="confirmation-title" class="text-xl font-extrabold tracking-tight text-slate-950" x-text="confirmation.title">
                    </h2>
                    <p id="confirmation-message" class="mt-2 text-sm leading-6 text-slate-600"
                        x-text="confirmation.message"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')" x-bind:disabled="processing"
                    class="app-button-secondary">
                    Cancel
                </button>
                <button type="button" x-on:click="confirmAction" x-bind:disabled="processing"
                    x-bind:class="confirmation.tone === 'danger' ? 'bg-red-600 hover:bg-red-500' : 'bg-amber-600 hover:bg-amber-500'"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-[0_10px_22px_-14px_rgba(15,23,42,0.7)] transition disabled:cursor-not-allowed disabled:opacity-60">
                    <span x-show="!processing" x-text="confirmation.confirmText"></span>
                    <span x-show="processing">Processing…</span>
                </button>
            </div>
        </div>
    </x-modal>
</div>
