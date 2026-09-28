<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockIn;
use App\Models\InventoryMovement;
use App\Support\Currency;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function render(): mixed
    {
        $products = Product::query()
            ->with(['inventory', 'brand', 'category', 'packageUnit'])
            ->where('active', true)
            ->get();
        $today = Carbon::today();
        $lowStockProducts = $products->filter(fn(Product $product): bool => (float) ($product->inventory?->quantity ?? 0) <= (float) $product->low_stock_threshold);

        return view('livewire.pages.dashboard', [
            'productCount' => $products->count(),
            'lowStockCount' => $lowStockProducts->count(),
            'lowStockProducts' => $lowStockProducts->take(10),
            'todaySales' => Sale::query()->whereDate('sold_at', $today)->sum('total'),
            'recentSales' => Sale::query()->latest('sold_at')->limit(5)->get(),
            'recentStockIns' => StockIn::query()->with('items.product')->latest('received_at')->limit(5)->get(),
            'recentMovements' => InventoryMovement::query()->with('product')->latest()->limit(5)->get(),
            'currency' => Currency::class,
        ]);
    }
}; ?>

<div class="app-page space-y-7">
    <section class="app-page-header">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <h1 class="app-page-title">Paint center overview</h1>
                <p class="app-page-description">Stock health, daily sales, and the next actions that need your attention.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('mixing.index') }}" wire:navigate class="app-button-primary bg-cyan-400 text-cyan-950 hover:bg-cyan-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v18M3 12h18" stroke-linecap="round" /></svg>
                    Custom mix
                </a>
                @if (auth()->user()->canManageOperations())
                    <a href="{{ route('inventory.stock-in') }}" wire:navigate class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-900 px-4 py-2.5 font-bold text-white transition hover:bg-blue-800">
                        Receive stock
                    </a>
                @endif
            </div>
        </div>
    </section>

        <dl class="app-metric-grid">
            <div class="app-metric app-metric-blue">
                <span class="app-metric-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4zM4 9h16M9 20V9" stroke-linejoin="round"/></svg></span>
                <dt class="text-xs font-bold uppercase tracking-[0.08em] text-blue-700">Active SKUs</dt>
                <dd class="mt-1 text-3xl font-extrabold tabular-nums text-slate-950">{{ $productCount }}</dd>
            </div>
            <div class="app-metric app-metric-orange">
                <span class="app-metric-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 4.7 2.9 18a2 2 0 0 0 1.8 3h14.6a2 2 0 0 0 1.8-3L13.7 4.7a2 2 0 0 0-3.4 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <dt class="text-xs font-bold uppercase tracking-[0.08em] text-orange-700">Low-stock items</dt>
                <dd class="mt-1 flex items-center gap-2 text-3xl font-extrabold tabular-nums text-slate-950">
                    {{ $lowStockCount }}
                    @if ($lowStockCount > 0)
                        <span class="rounded-full bg-orange-300 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-orange-950">Review</span>
                    @endif
                </dd>
            </div>
            <div class="app-metric app-metric-green">
                <span class="app-metric-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"/></svg></span>
                <dt class="text-xs font-bold uppercase tracking-[0.08em] text-emerald-700">Sales today</dt>
                <dd class="mt-1 text-3xl font-extrabold tabular-nums text-slate-950">{{ $currency::format($todaySales) }}</dd>
            </div>
        </dl>

    <section class="app-surface overflow-hidden" aria-labelledby="low-stock-title">
        <div class="flex flex-col gap-3 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 id="low-stock-title" class="text-lg font-extrabold tracking-tight text-slate-950">Low-stock items</h2>
                <p class="mt-1 text-sm text-slate-500">Products at or below their reorder threshold.</p>
            </div>
            @if (auth()->user()->canManageOperations())
                <a class="app-link"
                    href="{{ route('reports.inventory') }}" wire:navigate>View inventory report</a>
            @endif
        </div>
        <div class="overflow-x-auto border-t border-slate-100">
            <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Package unit</th>
                        <th>Current stock</th>
                        <th>Threshold</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lowStockProducts as $product)
                        <tr>
                            <td class="font-bold text-slate-900">{{ $product->name }}</td>
                            <td class="font-mono text-xs">{{ $product->sku }}</td>
                            <td>{{ $product->brand?->name ?? '—' }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td>{{ $product->packageUnit?->abbreviation ?? '—' }}</td>
                            <td class="font-bold tabular-nums text-slate-950">{{ $product->inventory?->quantity ?? 0 }}</td>
                            <td class="tabular-nums">{{ $product->low_stock_threshold }}</td>
                            <td>
                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">
                                    {{ ($product->inventory?->quantity ?? 0) == 0 ? 'Out of stock' : 'Low stock' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <span class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-emerald-50 text-emerald-700">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </span>
                                <span class="mt-3 block font-bold text-slate-900">Stock levels look healthy</span>
                                <span class="mt-1 block text-slate-500">No products need reordering right now.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="app-surface p-5 sm:p-6">
            <h2 class="text-base font-extrabold text-slate-950">Recent sales</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($recentSales as $sale)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="font-semibold text-slate-700">{{ $sale->invoice_number }}</span>
                        <span class="font-extrabold tabular-nums text-slate-950">{{ $currency::format($sale->total) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-slate-500">No sales recorded yet.</p>
                @endforelse
            </div>
        </section>
        <section class="app-surface p-5 sm:p-6">
            <h2 class="text-base font-extrabold text-slate-950">Recent stock-ins</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($recentStockIns as $stockIn)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="font-semibold text-slate-700">Stock-in #{{ $stockIn->id }}</span>
                        <span class="text-slate-500">{{ $stockIn->items->sum('quantity') }} packages</span>
                    </div>
                @empty
                    <p class="py-6 text-slate-500">No stock-ins recorded yet.</p>
                @endforelse
            </div>
        </section>
        <section class="app-surface p-5 sm:p-6">
            <h2 class="text-base font-extrabold text-slate-950">Recent movements</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($recentMovements as $movement)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="font-semibold text-slate-700">{{ $movement->product->sku }}</span>
                        <span class="font-extrabold tabular-nums {{ $movement->quantity_change < 0 ? 'text-red-700' : 'text-emerald-700' }}">
                            {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-slate-500">No inventory movements yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    @if (auth()->user()->canManageOperations())
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('inventory.physical-count') }}" wire:navigate class="app-button-secondary">Physical inventory</a>
            <a href="{{ route('reports.inventory') }}" wire:navigate class="app-button-secondary">Inventory report</a>
        </div>
    @endif
</div>
