<?php
use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component {
    use WithPagination;
    public string $search = '';
    public string $type = '';
    public function render(): mixed
    {
        return view('livewire.pages.sales.history', [
            'sales' => Sale::with(['user', 'items', 'mixingTransaction.components'])
                ->when($this->search, fn($query) => $query->where('invoice_number', 'like', '%' . $this->search . '%')->orWhereHas('items', fn($query) => $query->where('description', 'like', '%' . $this->search . '%')))
                ->when($this->type, fn($query) => $query->where('type', $this->type))
                ->latest('sold_at')
                ->paginate(20),
        ]);
    }
}; ?>
<div class="app-page">
    <div class="app-page-header">
        <div><h1 class="app-page-title">Sales history</h1>
        <p class="app-page-description">Review completed invoices, cashiers, totals, and mix details.</p></div>
    </div>
    <div class="app-toolbar"><input wire:model.live.debounce.300ms="search" type="search"
            placeholder="Search invoice or item" aria-label="Search sales history" class="app-input w-full max-w-sm"><select
            wire:model.live="type" aria-label="Filter sales by type" class="app-select">
            <option value="">All sale types</option>
            <option value="normal">Normal</option>
            <option value="mixed">Mixed</option>
            <option value="custom_mix">Custom mix</option>
        </select></div>
    <div class="app-panel">
        <div class="overflow-x-auto">
            <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Invoice</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Cashier</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Items</th>
                        <th class="px-3 py-2">Total</th>
                        <th class="px-3 py-2">Payment</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-history-{{ $sale->id }}">
                            <td class="px-3 py-2 font-mono">{{ $sale->invoice_number }}</td>
                            <td class="px-3 py-2">{{ $sale->sold_at->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-2">{{ $sale->user?->name ?? 'System' }}</td>
                            <td class="px-3 py-2 capitalize">{{ str_replace('_', ' ', $sale->type) }}</td>
                            <td class="px-3 py-2">{{ $sale->items->count() }}</td>
                            <td class="px-3 py-2 font-semibold">{{ \App\Support\Currency::format($sale->total) }}</td>
                            <td class="px-3 py-2 capitalize">{{ $sale->payment_method ?? '-' }}</td>
                            <td class="px-3 py-2"><a href="{{ route('sales.receipt', $sale) }}" wire:navigate
                                    class="app-link">View receipt</a></td>
                    </tr>@empty<tr>
                            <td colspan="8" class="app-empty">No sales match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $sales->links() }}</div>
    </div>
</div>
