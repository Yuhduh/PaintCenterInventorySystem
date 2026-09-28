<?php

use App\Models\MixingComponent;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;
    public function render(): mixed
    {
        return view('livewire.pages.reports.mixing', [
            'components' => MixingComponent::query()
                ->with(['product', 'mixingTransaction.sale'])
                ->latest()
                ->paginate(25),
        ]);
    }
}; ?>

<div class="app-page">
    <div class="app-page-header">
        <div><h1 class="app-page-title">Mixing usage report</h1>
        <p class="app-page-description">Estimated component usage retained for weekly physical reconciliation.</p></div>
    </div>
    <div class="app-panel">
        <div class="overflow-x-auto">
            <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Sale</th>
                        <th class="px-3 py-2">Material</th>
                        <th class="px-3 py-2">SKU</th>
                        <th class="px-3 py-2">Package Unit</th>
                        <th class="px-3 py-2">Estimated Qty</th>
                        <th class="px-3 py-2">Estimated Unit</th>
                        <th class="px-3 py-2">Recorded</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($components as $component)
                        <tr wire:key="mix-report-{{ $component->id }}">
                            <td class="px-3 py-2 font-mono">{{ $component->mixingTransaction->sale->invoice_number }}
                            </td>
                            <td class="px-3 py-2">{{ $component->product->name }}</td>
                            <td class="px-3 py-2 font-mono">{{ $component->product->sku }}</td>
                            <td class="px-3 py-2">{{ $component->product->packageUnit?->abbreviation ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $component->estimated_quantity }}</td>
                            <td class="px-3 py-2">{{ $component->estimated_quantity_unit }}</td>
                            <td class="px-3 py-2">{{ $component->created_at->format('Y-m-d') }}</td>
                    </tr>@empty<tr>
                            <td colspan="7" class="app-empty">No mixing usage recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $components->links() }}</div>
    </div>
</div>
