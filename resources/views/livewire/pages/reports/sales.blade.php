<?php

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;
    public string $from = '';
    public string $to = '';

    public function render(): mixed
    {
        return view('livewire.pages.reports.sales', ['sales' => Sale::query()->with('items')->when($this->from, fn($query) => $query->whereDate('sold_at', '>=', $this->from))->when($this->to, fn($query) => $query->whereDate('sold_at', '<=', $this->to))->latest('sold_at')->paginate(25)]);
    }
}; ?>

<div class="app-page">
    <div class="app-page-header">
        <div><h1 class="app-page-title">Sales report</h1>
        <p class="app-page-description">Review normal and custom-mix sales by date.</p></div>
    </div>
    <div class="app-toolbar">
        <div><x-input-label for="from" value="From" /><x-text-input wire:model.live="from" id="from"
                type="date" class="mt-1" /></div>
        <div><x-input-label for="to" value="To" /><x-text-input wire:model.live="to" id="to"
                type="date" class="mt-1" /></div>
    </div>
    <div class="app-panel">
        <div class="overflow-x-auto">
            <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Invoice</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Items</th>
                        <th class="px-3 py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-report-{{ $sale->id }}">
                            <td class="px-3 py-2 font-mono">{{ $sale->invoice_number }}</td>
                            <td class="px-3 py-2">{{ $sale->sold_at->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $sale->type) }}</td>
                            <td class="px-3 py-2">{{ $sale->items->count() }}</td>
                            <td class="px-3 py-2 font-semibold">{{ \App\Support\Currency::format($sale->total) }}</td>
                    </tr>@empty<tr>
                            <td colspan="5" class="app-empty">No sales found for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $sales->links() }}</div>
    </div>
</div>
