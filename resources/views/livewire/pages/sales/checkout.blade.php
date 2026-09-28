<?php

use App\Actions\CompleteSale;
use App\Support\Currency;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public array $cart = [];
    public string $tenderedAmount = '';

    public function mount(): void
    {
        $this->cart = session('pos.cart', []);
        if ($this->cart === []) {
            $this->redirectRoute('sales.index');
        }
    }

    public function total(): float
    {
        return round(collect($this->cart)->sum('subtotal'), 2);
    }
    public function change(): float
    {
        return max(0, (float) $this->tenderedAmount - $this->total());
    }

    public function completeSale(CompleteSale $completeSale): void
    {
        $total = $this->total();
        $this->validate(['tenderedAmount' => ['required', 'numeric', 'min:' . $total]]);

        try {
            $sale = $completeSale->handle($this->cart, (int) auth()->id(), (float) $this->tenderedAmount);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
            return;
        }
        session()->forget('pos.cart');
        $this->redirectRoute('sales.receipt', ['sale' => $sale]);
    }

    public function render(): mixed
    {
        return view('livewire.pages.sales.checkout', ['currency' => Currency::class]);
    }
}; ?>
<div class="app-page max-w-4xl">
    <div class="app-page-header">
        <div>
            <h1 class="app-page-title">Payment</h1>
            <p class="app-page-description">Cash-only checkout</p>
        </div><a href="{{ route('sales.index') }}" class="font-bold text-cyan-300 underline decoration-cyan-700 underline-offset-4 hover:text-white" wire:navigate>Back to POS</a>
    </div>
    <div class="app-panel">
        <div class="divide-y">
            @foreach ($cart as $line)
                <div class="flex justify-between px-4 py-3"><span>{{ $line['description'] }} × {{ $line['quantity'] }}
                        <small
                            class="text-slate-500">{{ $line['package'] ?: 'Custom Mix' }}</small></span><span>{{ $currency::format($line['subtotal']) }}</span>
                </div>
            @endforeach
        </div>
        <div class="app-total-block m-4 flex items-center justify-between">
            <span class="text-xs font-extrabold uppercase tracking-[0.1em] text-cyan-200">Total amount</span><span class="text-2xl font-extrabold tabular-nums">{{ $currency::format($this->total()) }}</span>
        </div>
    </div>
    <form
        x-on:submit.prevent="$dispatch('request-confirmation', {
            title: 'Complete sale?',
            message: @js('Complete this cash sale for ' . Currency::format($this->total()) . '? The sale and inventory movements will be recorded immediately.'),
            action: 'completeSale',
            arguments: [],
            confirmText: 'Complete sale',
            tone: 'warning',
        })"
        class="app-panel space-y-5 p-5 sm:p-6"><x-input-label
            for="tenderedAmount" value="Tendered Amount" /><x-text-input wire:model.live="tenderedAmount"
            id="tenderedAmount" type="number" step="0.01" class="block w-full" autofocus />
        @error('tenderedAmount')
            <p class="text-red-600">{{ $message }}</p>
        @enderror
        <div class="app-change-block flex items-center justify-between"><span class="text-xs font-extrabold uppercase tracking-[0.1em] text-emerald-50">Change due</span><strong class="text-2xl tabular-nums">{{ $currency::format($this->change()) }}</strong></div><button type="submit"
            class="app-button-primary w-full">Complete
            Sale</button>
    </form>
    <x-confirmation-modal />
</div>
