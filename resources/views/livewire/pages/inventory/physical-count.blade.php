<?php

use App\Models\InventoryMovement;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\PhysicalInventory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public array $counts = [];
    public string $countedAt = '';
    public string $notes = '';
    public string $search = '';
    public string $brandId = '';
    public string $categoryId = '';
    public string $packageUnitId = '';

    public function mount(): void
    {
        $this->countedAt = now()->toDateString();
        $this->counts = Product::query()->with('inventory')->pluck('id')->mapWithKeys(fn(int $id): array => [$id => ''])->all();
    }

    public function confirmCount(): void
    {
        $this->validate(['countedAt' => ['required', 'date'], 'counts' => ['required', 'array'], 'counts.*' => ['nullable', 'numeric', 'min:0'], 'notes' => ['nullable', 'string', 'max:1000']]);
        $enteredCounts = collect($this->counts)->filter(fn(mixed $quantity): bool => $quantity !== '' && $quantity !== null);
        if ($enteredCounts->isEmpty()) {
            throw ValidationException::withMessages(['counts' => 'Enter at least one physical stock quantity.']);
        }

        DB::transaction(function () use ($enteredCounts): void {
            $count = PhysicalInventory::create(['user_id' => auth()->id(), 'counted_at' => $this->countedAt, 'notes' => $this->notes ?: null]);
            foreach ($enteredCounts as $productId => $physicalQuantity) {
                $product = Product::query()->findOrFail($productId);
                $inventory = Inventory::query()->where('product_id', $product->id)->lockForUpdate()->firstOrCreate(['product_id' => $product->id], ['quantity' => 0]);
                $systemQuantity = (float) $inventory->quantity;
                $physical = (float) $physicalQuantity;
                $count->items()->create(['product_id' => $product->id, 'system_quantity' => $systemQuantity, 'physical_quantity' => $physical, 'variance' => $physical - $systemQuantity]);
                $inventory->update(['quantity' => $physical]);
                InventoryMovement::create(['product_id' => $product->id, 'user_id' => auth()->id(), 'type' => 'physical_adjustment', 'quantity_change' => $physical - $systemQuantity, 'quantity_before' => $systemQuantity, 'quantity_after' => $physical, 'reference_type' => PhysicalInventory::class, 'reference_id' => $count->id, 'reason' => $this->notes]);
            }
            AuditLog::create(['user_id' => auth()->id(), 'event' => 'physical_inventory_confirmed', 'auditable_type' => PhysicalInventory::class, 'auditable_id' => $count->id, 'context' => ['items' => $enteredCounts->count()]]);
        });
        $this->reset('notes');
        $this->mount();
        session()->flash('status', 'Physical inventory count confirmed.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedBrandId(): void
    {
        $this->resetPage();
    }
    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }
    public function updatedPackageUnitId(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        return view('livewire.pages.inventory.physical-count', [
            'products' => Product::query()
                ->with(['inventory', 'category', 'brand', 'packageUnit'])
                ->where('active', true)
                ->when($this->search, fn($query) => $query->where(fn($query) => $query->where('name', 'like', '%' . $this->search . '%')->orWhere('sku', 'like', '%' . $this->search . '%')))
                ->when($this->brandId, fn($query) => $query->where('brand_id', $this->brandId))
                ->when($this->categoryId, fn($query) => $query->where('category_id', $this->categoryId))
                ->when($this->packageUnitId, fn($query) => $query->where('package_unit_id', $this->packageUnitId))
                ->orderBy('name')
                ->paginate(25),
            'brands' => \App\Models\Brand::where('active', true)->orderBy('name')->get(),
            'categories' => \App\Models\Category::where('active', true)->orderBy('name')->get(),
            'packageUnits' => \App\Models\PackageUnit::where('active', true)->orderBy('name')->get(),
        ]);
    }
}; ?>

<div class="app-page max-w-6xl">
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif
    <div class="app-page-header">
        <div><h1 class="app-page-title">Weekly physical inventory</h1>
        <p class="app-page-description">Enter what is physically measured; the confirmed count becomes official stock.</p></div>
    </div>
    <form
        x-on:submit.prevent="$dispatch('request-confirmation', {
            title: 'Apply physical count?',
            message: 'Apply the entered physical quantities as the new official stock balances? Inventory adjustments will be recorded and cannot be edited here afterward.',
            action: 'confirmCount',
            arguments: [],
            confirmText: 'Apply count',
            tone: 'danger',
        })"
        class="app-panel space-y-5 p-5 sm:p-7">
        <div class="max-w-xs"><x-input-label for="countedAt" value="Count date" /><x-text-input wire:model="countedAt"
                id="countedAt" type="date" class="mt-1 block w-full" /></div>
        <div class="flex flex-wrap gap-3 rounded-xl bg-slate-50 p-3"><input wire:model.live.debounce.300ms="search" type="search"
                placeholder="Search product or SKU" aria-label="Search products to count" class="app-input"><select wire:model.live="brandId"
                aria-label="Filter count by brand" class="app-select">
                <option value="">All brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="categoryId" aria-label="Filter count by category" class="app-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="packageUnitId" aria-label="Filter count by package unit" class="app-select">
                <option value="">All package units</option>
                @foreach ($packageUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="overflow-x-auto">
            @error('counts')
                <div class="mb-3 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
            @enderror
            <table class="app-table min-w-full">
                <thead>
                    <tr class="text-left text-xs uppercase">
                        <th class="px-2 py-2">Product</th>
                        <th class="px-2 py-2">SKU</th>
                        <th class="px-2 py-2">Brand</th>
                        <th class="px-2 py-2">Category</th>
                        <th class="px-2 py-2">Package Unit</th>
                        <th class="px-2 py-2">System Stock</th>
                        <th class="px-2 py-2">Physical Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        <tr wire:key="count-{{ $product->id }}">
                            <td class="px-2 py-2 font-medium">{{ $product->name }}</td>
                            <td class="px-2 py-2 font-mono">{{ $product->sku }}</td>
                            <td class="px-2 py-2">{{ $product->brand?->name ?? '-' }}</td>
                            <td class="px-2 py-2">{{ $product->category->name }}</td>
                            <td class="px-2 py-2">{{ $product->packageUnit?->abbreviation ?? '-' }}</td>
                            <td class="px-2 py-2">{{ $product->inventory?->quantity ?? 0 }}</td>
                            <td class="px-2 py-2"><input wire:model="counts.{{ $product->id }}" type="number"
                                    aria-label="Counted quantity for {{ $product->name }}"
                                    min="0" step="0.001" class="app-input w-40"
                                    placeholder="Skip"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $products->links() }}</div>
        <div><x-input-label for="notes" value="Reason / notes" />
            <textarea wire:model="notes" id="notes" rows="2" class="app-textarea mt-1 block w-full"></textarea>
        </div>
        <div class="flex justify-end"><x-primary-button>Confirm physical count</x-primary-button></div>
    </form>
    <x-confirmation-modal />
</div>
