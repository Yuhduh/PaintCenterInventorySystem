<?php

use App\Models\Category;
use App\Models\Brand;
use App\Models\InventoryMovement;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $search = '';
    public string $categoryId = '';
    public string $brandId = '';
    public string $from = '';
    public string $to = '';

    public function setPreset(string $preset): void
    {
        $this->from = $preset === 'week' ? now()->startOfWeek()->toDateString() : now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryId', 'brandId', 'from', 'to']);
    }

    public function render(): mixed
    {
        return view('livewire.pages.reports.inventory', [
            'products' => Product::query()
                ->with(['inventory', 'category', 'brand', 'packageUnit'])
                ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%')->orWhere('sku', 'like', '%' . $this->search . '%'))
                ->when($this->categoryId, fn($query) => $query->where('category_id', $this->categoryId))
                ->when($this->brandId, fn($query) => $query->where('brand_id', $this->brandId))
                ->where('active', true)
                ->orderBy('name')
                ->paginate(25),
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('active', true)->orderBy('name')->get(),
            'periodMovements' => InventoryMovement::query()->when($this->from, fn($query) => $query->whereDate('created_at', '>=', $this->from))->when($this->to, fn($query) => $query->whereDate('created_at', '<=', $this->to))->count(),
        ]);
    }
}; ?>

<div class="app-page">
    <div class="app-page-header">
        <div>
            <h1 class="app-page-title">Inventory report</h1>
            <p class="app-page-description">Current stock is live; period activity covers {{ $periodMovements }} movements.
            </p>
        </div><a href="{{ route('reports.inventory.pdf') }}"
            class="app-button-primary">Export PDF</a>
    </div>
    <div class="app-toolbar"><input wire:model.live.debounce.300ms="search"
            type="search" placeholder="Search products" aria-label="Search inventory report" class="app-input"><select
            wire:model.live="brandId" aria-label="Filter report by brand" class="app-select">
            <option value="">All brands</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="categoryId" aria-label="Filter report by category" class="app-select">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <button wire:click="setPreset('week')" type="button" class="app-button-secondary">This week</button><button
            wire:click="setPreset('year')" type="button" class="app-button-secondary">This year</button><input
            wire:model.live="from" type="date" aria-label="Inventory report start date" class="app-input"><input wire:model.live="to"
            type="date" aria-label="Inventory report end date" class="app-input"><button wire:click="resetFilters" type="button"
            class="app-button-secondary">Reset filters</button>
    </div>
    <div class="app-panel">
        <div class="overflow-x-auto">
            <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Product</th>
                        <th class="px-4 py-2">SKU</th>
                        <th class="px-4 py-2">Brand</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2">Package Unit</th>
                        <th class="px-4 py-2">Current Stock</th>
                        <th class="px-4 py-2">Threshold</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($products as $product)
                        <tr wire:key="report-product-{{ $product->id }}">
                            <td class="px-4 py-2 font-medium">{{ $product->name }}</td>
                            <td class="px-4 py-2 font-mono">{{ $product->sku }}</td>
                            <td class="px-4 py-2">{{ $product->brand?->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $product->category->name }}</td>
                            <td class="px-4 py-2">{{ $product->packageUnit?->abbreviation ?? '-' }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $product->inventory?->quantity ?? 0 }}</td>
                            <td class="px-4 py-2">{{ $product->low_stock_threshold }}</td>
                            <td class="px-4 py-2">
                                @php($stock = $product->inventory?->quantity ?? 0)
                                <span class="{{ $stock == 0 ? 'app-status-danger' : ($stock <= $product->low_stock_threshold ? 'app-status-warning' : 'app-status-success') }}">
                                    {{ $stock == 0 ? 'Out of stock' : ($stock <= $product->low_stock_threshold ? 'Low stock' : 'Healthy') }}
                                </span>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="8" class="app-empty">No products match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $products->links() }}</div>
    </div>
</div>
