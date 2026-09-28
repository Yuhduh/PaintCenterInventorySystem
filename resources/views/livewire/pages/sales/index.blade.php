<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $brandId = '';
    public string $categoryId = '';
    public string $packageUnit = '';
    public bool $availableOnly = true;
    public array $cart = [];
    public string $productId = '';
    public string $quantity = '1';
    public bool $showMixPanel = false;
    public string $mixDescription = 'Custom paint mix';
    public string $mixQuantity = '1';
    public string $mixResultUnit = '';
    public string $mixProductId = '';
    public string $mixEstimatedQuantity = '';
    public string $mixEstimatedQuantityUnit = '';
    public string $mixNotes = '';
    public array $mixComponents = [];

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
    public function updatedPackageUnit(): void
    {
        $this->resetPage();
    }
    public function updatedAvailableOnly(): void
    {
        $this->resetPage();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::with(['inventory', 'packageUnit'])
            ->where('active', true)
            ->findOrFail($productId);
        $key = 'product:' . $product->id;
        $quantity = (float) ($this->cart[$key]['quantity'] ?? 0) + 1;
        $available = (float) ($product->inventory?->quantity ?? 0);
        if ($quantity > $available) {
            $this->addError('cart', "Only {$available} package-equivalents of {$product->name} are available.");
            return;
        }
        $this->cart[$key] = ['type' => 'normal', 'product_id' => $product->id, 'description' => $product->name, 'package' => trim($product->package_size . ' ' . $product->packageUnit?->abbreviation), 'sku' => $product->sku, 'quantity' => $quantity, 'unit_price' => (float) $product->selling_price, 'subtotal' => $quantity * (float) $product->selling_price];
    }

    public function updateCartQuantity(string $key, string $value): bool
    {
        if (!isset($this->cart[$key])) {
            return false;
        }
        $quantity = (float) $value;
        if ($quantity <= 0) {
            unset($this->cart[$key]);
            return true;
        }
        if ($this->cart[$key]['type'] === 'normal' && $quantity > (float) (Product::with('inventory')->find($this->cart[$key]['product_id'])?->inventory?->quantity ?? 0)) {
            $this->addError('cart', 'Requested quantity exceeds available stock.');
            $this->addError('productId', 'Requested quantity exceeds available stock.');
            return false;
        }
        $this->cart[$key]['quantity'] = $quantity;
        $this->cart[$key]['subtotal'] = $quantity * (float) $this->cart[$key]['unit_price'];
        return true;
    }

    public function incrementCart(string $key): void
    {
        $this->updateCartQuantity($key, (string) ((float) $this->cart[$key]['quantity'] + 1));
    }
    public function decrementCart(string $key): void
    {
        $this->updateCartQuantity($key, (string) ((float) $this->cart[$key]['quantity'] - 1));
    }
    public function removeFromCart(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function openMixPanel(): void
    {
        $this->showMixPanel = true;
    }

    public function addMixComponent(): void
    {
        if ($this->mixEstimatedQuantityUnit === '' && $this->mixProductId !== '') {
            $this->mixEstimatedQuantityUnit = Product::with('packageUnit')->find($this->mixProductId)?->packageUnit?->abbreviation ?: 'package-equivalent';
        }
        $validated = $this->validate(['mixProductId' => ['required', 'exists:products,id'], 'mixEstimatedQuantity' => ['required', 'numeric', 'gt:0'], 'mixEstimatedQuantityUnit' => ['required', 'string', 'max:30']]);
        $this->mixComponents[] = ['product_id' => (int) $validated['mixProductId'], 'estimated_quantity' => (float) $validated['mixEstimatedQuantity'], 'estimated_quantity_unit' => $validated['mixEstimatedQuantityUnit']];
        $this->reset(['mixProductId', 'mixEstimatedQuantity', 'mixEstimatedQuantityUnit']);
    }

    public function removeMixComponent(int $index): void
    {
        unset($this->mixComponents[$index]);
        $this->mixComponents = array_values($this->mixComponents);
    }

    public function addMixToCart(): void
    {
        if ($this->mixResultUnit === '') {
            $this->mixResultUnit = 'L';
        }
        $this->validate(['mixDescription' => ['required', 'string', 'max:255'], 'mixQuantity' => ['required', 'numeric', 'gt:0'], 'mixResultUnit' => ['required', 'in:ml,L,gal'], 'mixComponents' => ['required', 'array', 'min:1'], 'mixComponents.*.product_id' => ['required', 'exists:products,id'], 'mixComponents.*.estimated_quantity' => ['required', 'numeric', 'gt:0'], 'mixComponents.*.estimated_quantity_unit' => ['required', 'string', 'max:30']]);
        $products = Product::whereIn('id', collect($this->mixComponents)->pluck('product_id'))
            ->get()
            ->keyBy('id');
        $basis = $products->sortByDesc(fn(Product $product): float => (float) $product->selling_price)->first();
        $key = 'mix:' . Str::uuid();
        $quantity = (float) $this->mixQuantity;
        $this->cart[$key] = ['type' => 'custom_mix', 'description' => $this->mixDescription, 'package' => $quantity . ' ' . $this->mixResultUnit, 'resulting_quantity' => $quantity, 'resulting_unit' => $this->mixResultUnit, 'sku' => 'CUSTOM MIX', 'quantity' => $quantity, 'unit_price' => (float) $basis->selling_price, 'subtotal' => $quantity * (float) $basis->selling_price, 'basis_product_id' => $basis->id, 'basis_product_name' => $basis->name, 'components' => $this->mixComponents, 'notes' => $this->mixNotes];
        $this->resetMixForm();
    }

    public function editMix(string $key): void
    {
        $mix = $this->cart[$key] ?? null;
        if (!$mix || $mix['type'] !== 'custom_mix') {
            return;
        }
        $this->mixDescription = $mix['description'];
        $this->mixQuantity = (string) $mix['quantity'];
        $this->mixResultUnit = $mix['resulting_unit'] ?? '';
        $this->mixComponents = $mix['components'];
        $this->mixNotes = $mix['notes'] ?? '';
        unset($this->cart[$key]);
        $this->showMixPanel = true;
    }
    public function resetMixForm(): void
    {
        $this->reset(['showMixPanel', 'mixDescription', 'mixQuantity', 'mixResultUnit', 'mixProductId', 'mixEstimatedQuantity', 'mixEstimatedQuantityUnit', 'mixNotes', 'mixComponents']);
        $this->mixDescription = 'Custom paint mix';
        $this->mixQuantity = '1';
    }
    public function cartTotal(): float
    {
        return round(collect($this->cart)->sum('subtotal'), 2);
    }

    public function proceedToPayment(): void
    {
        if ($this->cart === []) {
            $this->addError('cart', 'Add at least one item before payment.');
            return;
        }
        session(['pos.cart' => $this->cart]);
        $this->redirectRoute('sales.checkout');
    }

    public function render(): mixed
    {
        return view('livewire.pages.sales.index', [
            'products' => Product::with(['brand', 'category', 'packageUnit', 'inventory'])
                ->where('active', true)
                ->when($this->search, fn($query) => $query->where(fn($query) => $query->where('name', 'like', '%' . $this->search . '%')->orWhere('sku', 'like', '%' . $this->search . '%')))
                ->when($this->brandId, fn($query) => $query->where('brand_id', $this->brandId))
                ->when($this->categoryId, fn($query) => $query->where('category_id', $this->categoryId))
                ->when($this->packageUnit, fn($query) => $query->where('package_unit_id', $this->packageUnit))
                ->when($this->availableOnly, fn($query) => $query->whereHas('inventory', fn($query) => $query->where('quantity', '>', 0)))
                ->orderBy('name')
                ->paginate(12),
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'packageUnits' => \App\Models\PackageUnit::where('active', true)->orderBy('name')->get(),
            'mixProducts' => Product::where('active', true)->orderBy('name')->get(),
        ]);
    }
}; ?>
<div class="app-page-wide">
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif
    <div class="app-page-header">
        <div>
            <h1 class="app-page-title">Point of sale</h1>
            <p class="app-page-description">Build the cart, then proceed to cash payment.</p>
        </div><button wire:click="openMixPanel" type="button"
            class="app-button-primary"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v18M3 12h18" stroke-linecap="round"/></svg>Custom mix</button>
    </div>
    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="space-y-3">
            <div class="app-toolbar grid sm:grid-cols-2 xl:grid-cols-5"><input
                    wire:model.live.debounce.300ms="search" type="search" placeholder="Search product or SKU"
                    aria-label="Search point of sale products" class="app-input xl:col-span-2"><select wire:model.live="brandId"
                    aria-label="Filter products by brand" class="app-select">
                    <option value="">All brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="categoryId" aria-label="Filter products by category" class="app-select">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="packageUnit" aria-label="Filter products by package unit" class="app-select">
                    <option value="">All package units</option>
                    @foreach ($packageUnits as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
                <label class="flex min-h-10 items-center gap-2 font-semibold text-slate-600 xl:col-span-5"><input
                        wire:model.live="availableOnly" type="checkbox" class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600"> Available
                    only</label>
            </div>
            @error('cart')
                <div class="rounded-md bg-red-50 p-2 text-red-700">{{ $message }}</div>
            @enderror
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    <button wire:click="addProduct({{ $product->id }})" type="button"
                        wire:key="pos-product-{{ $product->id }}"
                        class="group rounded-2xl bg-white p-4 text-left shadow-[0_16px_36px_-32px_rgba(15,23,42,0.75)] ring-1 ring-inset ring-slate-200 transition duration-200 hover:-translate-y-0.5 hover:ring-cyan-400 focus-visible:ring-2 focus-visible:ring-cyan-500">
                        <div class="flex justify-between gap-2">
                            <div>
                                <p class="font-extrabold text-slate-950">{{ $product->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $product->brand?->name ?? 'Unbranded' }} ·
                                    {{ $product->package_size }} {{ $product->packageUnit?->abbreviation }}</p>
                            </div><span class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-[10px] font-bold text-slate-600">{{ $product->sku }}</span>
                        </div>
                        <div class="mt-3 flex justify-between"><span
                                class="font-extrabold text-slate-950">{{ \App\Support\Currency::format($product->selling_price) }}</span><span
                                class="text-xs font-semibold {{ ($product->inventory?->quantity ?? 0) <= $product->low_stock_threshold ? 'text-red-700' : 'text-slate-500' }}">{{ $product->inventory?->quantity ?? 0 }} on hand</span>
                        </div>
                </button>@empty<div
                        class="app-panel app-empty sm:col-span-2 xl:col-span-3">No products
                        match these filters.</div>
                @endforelse
            </div>
            {{ $products->links() }}
        </section>
        <aside class="app-panel sticky top-24 p-5">
            <div class="flex justify-between border-b pb-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-950">Current cart</h2>
                    <p class="text-xs text-slate-500">{{ count($cart) }} line(s)</p>
                </div><button
                    x-on:click="$dispatch('request-confirmation', {
                        title: 'Clear cart?',
                        message: @js('Remove all ' . count($cart) . ' line(s) from the current cart?'),
                        action: 'clearCart',
                        arguments: [],
                        confirmText: 'Clear cart',
                        tone: 'danger',
                    })"
                    type="button" class="app-action-danger" @disabled($cart === [])>Clear</button>
            </div>
            <div class="max-h-[55vh] space-y-3 overflow-y-auto py-3">
                @forelse ($cart as $key => $line)
                    <div wire:key="cart-{{ $key }}" class="border-b pb-3">
                        <div class="flex justify-between gap-2">
                            <div>
                                <p class="font-medium">{{ $line['description'] }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $line['type'] === 'custom_mix' ? 'Custom Mix · ' . $line['basis_product_name'] : $line['package'] . ' · ' . $line['sku'] }}
                                </p>
                            </div><button wire:click="removeFromCart('{{ $key }}')" type="button"
                                class="app-action-danger">Remove</button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex overflow-hidden rounded-xl ring-1 ring-inset ring-slate-200"><button wire:click="decrementCart('{{ $key }}')"
                                    type="button" class="grid h-11 w-11 place-items-center font-bold text-slate-600 hover:bg-slate-100" aria-label="Decrease quantity for {{ $line['description'] }}">−</button><input
                                    wire:change="updateCartQuantity('{{ $key }}', $event.target.value)"
                                    value="{{ $line['quantity'] }}"
                                    aria-label="Quantity for {{ $line['description'] }}"
                                    class="w-12 border-0 p-1 text-center text-xs font-bold focus:ring-cyan-600"><button
                                    wire:click="incrementCart('{{ $key }}')" type="button"
                                    class="grid h-11 w-11 place-items-center font-bold text-slate-600 hover:bg-slate-100" aria-label="Increase quantity for {{ $line['description'] }}">+</button></div>
                            <strong>{{ \App\Support\Currency::format($line['subtotal']) }}</strong>
                        </div>
                        @if ($line['type'] === 'custom_mix')
                            <button wire:click="editMix('{{ $key }}')" type="button"
                                class="app-action mt-1">Edit mix</button>
                        @endif
                    </div>
                @empty<div class="py-8 text-center text-slate-500">Cart is empty.</div>
                @endforelse
            </div>
            <div class="border-t pt-3">
                <div class="app-total-block mb-3 flex items-center justify-between">
                    <span class="text-xs font-extrabold uppercase tracking-[0.1em] text-cyan-200">Total</span><span class="text-2xl font-extrabold tabular-nums">{{ \App\Support\Currency::format($this->cartTotal()) }}</span>
                </div>
                <button wire:click="proceedToPayment" type="button"
                    class="app-button-primary w-full">Proceed
                    to Payment</button>
            </div>
        </aside>
    </div>
    @if ($showMixPanel)
        <x-modal name="mix-panel" :show="$showMixPanel" focusable>
            <div x-on:keydown.escape.window="$wire.resetMixForm()"
                class="max-h-[90vh] w-full overflow-y-auto bg-white p-6 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="mix-panel-title">
                <div class="flex justify-between">
                    <h2 id="mix-panel-title" class="text-xl font-extrabold tracking-tight text-slate-950">Add custom mix to cart</h2><button wire:click="resetMixForm"
                        type="button" class="grid h-11 w-11 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-950" aria-label="Close custom mix form"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/></svg></button>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div><x-input-label for="mixDescription" value="Result description" /><x-text-input
                            wire:model="mixDescription" id="mixDescription" class="mt-1 block w-full" /></div>
                    <div><x-input-label for="mixQuantity" value="Resulting quantity" /><x-text-input
                            wire:model="mixQuantity" id="mixQuantity" type="number" step="0.001"
                            class="mt-1 block w-full" /></div>
                    <div><x-input-label for="mixResultUnit" value="Resulting unit" /><select
                            wire:model="mixResultUnit" id="mixResultUnit"
                            class="app-select mt-1 block w-full">
                            <option value="">Choose unit</option>
                            <option value="ml">ml</option>
                            <option value="L">L</option>
                            <option value="gal">gal</option>
                        </select></div>
                </div>
                <div class="mt-4 grid items-end gap-3 sm:grid-cols-[1fr_150px_130px_auto]"><select
                        wire:model="mixProductId" aria-label="Custom mix material" class="app-select">
                        <option value="">Choose material</option>
                        @foreach ($mixProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} · {{ $product->name }}</option>
                        @endforeach
                    </select>
                    <x-text-input wire:model="mixEstimatedQuantity" type="number" step="0.001"
                        placeholder="Estimated use" aria-label="Estimated material quantity" /><select wire:model="mixEstimatedQuantityUnit"
                        aria-label="Estimated material unit" class="app-select">
                        <option value="">Unit</option>
                        <option value="ml">ml</option>
                        <option value="L">L</option>
                        <option value="gal">gal</option>
                    </select><button wire:click="addMixComponent" type="button"
                        class="app-button-primary">Add material</button>
                </div>
                <div class="mt-3 space-y-2">
                    @forelse ($mixComponents as $index => $component)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-xs">
                            <span>{{ $mixProducts->firstWhere('id', $component['product_id'])?->name }} ·
                                {{ $component['estimated_quantity'] }}
                                {{ $component['estimated_quantity_unit'] }}</span><button
                                wire:click="removeMixComponent({{ $index }})" type="button"
                                class="app-action-danger">Remove</button>
                    </div>@empty<p class="text-slate-500">Add base and
                            tint materials.</p>
                    @endforelse
                </div>
                <textarea wire:model="mixNotes" rows="2" placeholder="Mix notes" aria-label="Custom mix notes"
                    class="app-textarea mt-3 block w-full"></textarea>
                <div class="mt-4 flex justify-end gap-2"><button wire:click="resetMixForm" type="button"
                        class="app-button-secondary">Cancel</button><button wire:click="addMixToCart"
                        type="button" class="app-button-primary">Add mix to
                        cart</button></div>
            </div>
        </x-modal>
    @endif
    <x-confirmation-modal />
</div>
