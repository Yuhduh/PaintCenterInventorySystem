<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\PackageUnit;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?int $editingProductId = null;
    public string $sku = '';
    public string $name = '';
    public string $brandId = '';
    public string $categoryId = '';
    public string $newCategory = '';
    public string $packageUnitId = '';
    public string $packageSize = '';
    public string $packageUnit = '';
    public string $sellingPrice = '';
    public string $lowStockThreshold = '0';
    public string $manufacturerCode = '';

    public function saveProduct(): void
    {
        if ($this->categoryId === '' && $this->newCategory !== '') {
            $this->categoryId = (string) Category::firstOrCreate(['name' => trim($this->newCategory)])->id;
        }
        if ($this->packageUnitId === '' && $this->packageUnit !== '') {
            $this->packageUnitId = (string) PackageUnit::where('name', $this->packageUnit)->orWhere('abbreviation', $this->packageUnit)->value('id');
        }
        if ($this->packageUnitId === '') {
            $this->packageUnitId = (string) PackageUnit::where('active', true)->value('id');
        }

        $validated = $this->validate([
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->editingProductId)],
            'name' => ['required', 'string', 'max:255'],
            'brandId' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'categoryId' => ['required', 'integer', Rule::exists('categories', 'id')],
            'packageUnitId' => ['required', 'integer', Rule::exists('package_units', 'id')],
            'packageSize' => ['nullable', 'numeric', 'min:0'],
            'sellingPrice' => ['required', 'numeric', 'min:0'],
            'lowStockThreshold' => ['required', 'numeric', 'min:0'],
            'manufacturerCode' => ['nullable', 'string', 'max:100'],
        ]);

        $attributes = [
            'category_id' => $validated['categoryId'],
            'brand_id' => $validated['brandId'] ?: null,
            'package_unit_id' => $validated['packageUnitId'],
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'package_size' => $validated['packageSize'] ?: null,
            'selling_price' => $validated['sellingPrice'],
            'low_stock_threshold' => $validated['lowStockThreshold'],
            'manufacturer_code' => $validated['manufacturerCode'] ?: null,
        ];

        $product = $this->editingProductId ? tap(Product::findOrFail($this->editingProductId))->update($attributes) : Product::create($attributes + ['active' => true]);

        if (!$this->editingProductId) {
            Inventory::create(['product_id' => $product->id, 'quantity' => 0]);
        }

        AuditLog::create(['user_id' => auth()->id(), 'event' => $this->editingProductId ? 'product_updated' : 'product_created', 'auditable_type' => Product::class, 'auditable_id' => $product->id, 'context' => ['sku' => $product->sku, 'name' => $product->name]]);

        $this->resetForm();
        session()->flash('status', 'Product saved successfully.');
    }

    public function createProduct(): void
    {
        $this->saveProduct();
    }

    public function editProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->showForm = true;
        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->brandId = (string) ($product->brand_id ?? '');
        $this->categoryId = (string) $product->category_id;
        $this->packageUnitId = (string) ($product->package_unit_id ?? '');
        $this->packageSize = (string) ($product->package_size ?? '');
        $this->sellingPrice = (string) $product->selling_price;
        $this->lowStockThreshold = (string) $product->low_stock_threshold;
        $this->manufacturerCode = (string) ($product->manufacturer_code ?? '');
    }

    public function toggleProductStatus(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['active' => !$product->active]);
        AuditLog::create(['user_id' => auth()->id(), 'event' => $product->active ? 'product_reactivated' : 'product_deactivated', 'auditable_type' => Product::class, 'auditable_id' => $product->id]);
        session()->flash('status', $product->active ? 'Product reactivated.' : 'Product deactivated.');
    }

    public function resetForm(): void
    {
        $this->reset(['showForm', 'editingProductId', 'sku', 'name', 'brandId', 'categoryId', 'newCategory', 'packageUnitId', 'packageSize', 'packageUnit', 'sellingPrice', 'manufacturerCode']);
        $this->lowStockThreshold = '0';
    }

    public function render(): mixed
    {
        $products = Product::query()
            ->with(['brand', 'category', 'packageUnit', 'inventory'])
            ->when($this->search, fn($query) => $query->where(fn($query) => $query->where('name', 'like', '%' . $this->search . '%')->orWhere('sku', 'like', '%' . $this->search . '%')))
            ->when($this->categoryId, fn($query) => $query->where('category_id', $this->categoryId))
            ->latest()
            ->paginate(15);

        return view('livewire.pages.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'packageUnits' => PackageUnit::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }
}; ?>

<div class="app-page">
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif

    <div class="app-page-header">
        <div>
            <h1 class="app-page-title">Product catalog</h1>
            <p class="app-page-description">Manage every sellable package as its own SKU.</p>
        </div>
        <button wire:click="$set('showForm', true)" type="button"
            class="app-button-primary">Add
            product</button>
    </div>

    <div class="app-toolbar">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search name or SKU" aria-label="Search products"
            class="app-input w-full sm:max-w-sm">
        <select wire:model.live="categoryId" aria-label="Filter products by category" class="app-select sm:max-w-xs">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="app-panel">
        <div class="overflow-x-auto">
            <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Product</th>
                        <th class="px-3 py-2">SKU</th>
                        <th class="px-3 py-2">Brand</th>
                        <th class="px-3 py-2">Category</th>
                        <th class="px-3 py-2">Package Unit</th>
                        <th class="px-3 py-2">Price</th>
                        <th class="px-3 py-2">Stock</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td class="px-3 py-2 font-bold text-slate-900">{{ $product->name }}</td>
                            <td class="px-3 py-2 font-mono text-slate-600">{{ $product->sku }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $product->brand?->name ?? 'No brand' }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $product->category->name }}</td>
                            <td class="px-3 py-2 text-slate-600">
                                {{ $product->package_size ? rtrim(rtrim((string) $product->package_size, '0'), '.') : '-' }}
                                {{ $product->packageUnit?->abbreviation }}</td>
                            <td class="px-3 py-2">{{ \App\Support\Currency::format($product->selling_price) }}</td>
                            <td
                                class="px-3 py-2 {{ $product->inventory && $product->inventory->quantity <= $product->low_stock_threshold ? 'font-semibold text-red-700' : 'text-slate-700' }}">
                                {{ $product->inventory?->quantity ?? 0 }}</td>
                            <td class="px-3 py-2"><span
                                    class="{{ $product->active ? 'app-status-success' : 'app-status-neutral' }}">{{ $product->active ? 'Active' : 'Deactivated' }}</span>
                            </td>
                            <td class="space-x-3 whitespace-nowrap px-3 py-2"><button
                                    wire:click="editProduct({{ $product->id }})" type="button"
                                    class="app-action">Edit</button><button
                                    x-on:click="$dispatch('request-confirmation', {
                                        title: @js($product->active ? 'Deactivate product?' : 'Reactivate product?'),
                                        message: @js(($product->active ? 'Deactivate ' : 'Reactivate ') . $product->name . '? This changes whether staff can use it in new transactions.'),
                                        action: 'toggleProductStatus',
                                        arguments: [{{ $product->id }}],
                                        confirmText: @js($product->active ? 'Deactivate' : 'Reactivate'),
                                        tone: @js($product->active ? 'danger' : 'warning'),
                                    })"
                                    type="button"
                                    class="{{ $product->active ? 'app-action-danger' : 'app-action text-emerald-700' }}">{{ $product->active ? 'Deactivate' : 'Reactivate' }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="app-empty">No products match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>

    @if ($showForm)
        <x-modal name="product-form" :show="$showForm" focusable>
            <form wire:submit="saveProduct"
                x-on:keydown.escape.window="$wire.resetForm()"
                class="max-h-[90vh] w-full overflow-y-auto bg-white p-6 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="product-form-title">
                <div class="flex items-center justify-between">
                    <h2 id="product-form-title" class="text-xl font-extrabold tracking-tight text-slate-950">
                        {{ $editingProductId ? 'Edit product' : 'Add product' }}</h2><button wire:click="resetForm"
                        type="button" class="grid h-11 w-11 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-950" aria-label="Close product form"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/></svg></button>
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <x-input-label for="sku" value="SKU" /><x-text-input wire:model="sku" id="sku"
                        required />
                    <x-input-label for="name" value="Product name" /><x-text-input wire:model="name" id="name"
                        required />
                    <x-input-label for="categoryId" value="Category" /><select wire:model="categoryId" id="categoryId"
                        class="app-select" required>
                        <option value="">Choose category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-label for="brandId" value="Brand" /><select wire:model="brandId" id="brandId"
                        class="app-select">
                        <option value="">No brand</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <x-input-label for="packageSize" value="Package size" /><x-text-input wire:model="packageSize"
                        id="packageSize" type="number" step="0.001" />
                    <x-input-label for="packageUnitId" value="Package unit" /><select wire:model="packageUnitId"
                        id="packageUnitId" class="app-select" required>
                        <option value="">Choose package unit</option>
                        @foreach ($packageUnits as $packageUnit)
                            <option value="{{ $packageUnit->id }}">{{ $packageUnit->name }}
                                ({{ $packageUnit->abbreviation }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-label for="sellingPrice" value="Selling price" /><x-text-input wire:model="sellingPrice"
                        id="sellingPrice" type="number" step="0.01" required />
                    <x-input-label for="lowStockThreshold" value="Low-stock threshold" /><x-text-input
                        wire:model="lowStockThreshold" id="lowStockThreshold" type="number" step="0.001"
                        required />
                </div>
                <div class="mt-6 flex justify-end gap-3"><button wire:click="resetForm" type="button"
                        class="app-button-secondary">Cancel</button><x-primary-button>{{ $editingProductId ? 'Save changes' : 'Create product' }}</x-primary-button>
                </div>
            </form>
        </x-modal>
    @endif

    <x-confirmation-modal />
</div>
