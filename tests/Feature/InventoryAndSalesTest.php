<?php

use App\Actions\CompleteSale;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PhysicalInventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Volt;

function inventoryProduct(array $attributes = []): Product
{
    $category = Category::firstOrCreate(['name' => 'Paint']);

    $product = Product::create(array_merge([
        'category_id' => $category->id,
        'sku' => 'PAINT-'.uniqid(),
        'name' => 'Interior Paint',
        'selling_price' => 100,
        'low_stock_threshold' => 1,
        'active' => true,
    ], $attributes));

    Inventory::create(['product_id' => $product->id, 'quantity' => 0]);

    return $product;
}

test('stock in increases inventory and records a movement', function () {
    $user = User::factory()->create();
    $product = inventoryProduct();

    Volt::actingAs($user)
        ->test('pages.inventory.stock-in')
        ->set('productId', $product->id)
        ->set('quantity', '5')
        ->call('addItem')
        ->call('saveStockIn')
        ->assertHasNoErrors();

    expect((float) $product->fresh()->inventory->quantity)->toBe(5.0)
        ->and(InventoryMovement::where('product_id', $product->id)->where('type', 'stock_in')->count())->toBe(1);
});

test('a physical count requires at least one entered quantity', function () {
    $user = User::factory()->create();
    inventoryProduct();

    Volt::actingAs($user)
        ->test('pages.inventory.physical-count')
        ->call('confirmCount')
        ->assertHasErrors('counts');

    expect(PhysicalInventory::query()->count())->toBe(0);
});

test('a sale cannot exceed available inventory and reduces stock when valid', function () {
    $user = User::factory()->create();
    $product = inventoryProduct();
    $product->inventory->update(['quantity' => 2]);

    $cart = Volt::actingAs($user)
        ->test('pages.sales.index')
        ->call('addProduct', $product->id)
        ->get('cart');

    $tamperedCart = $cart;
    $tamperedCart['product:'.$product->id]['quantity'] = 3;
    $tamperedCart['product:'.$product->id]['unit_price'] = 1;
    $tamperedCart['product:'.$product->id]['subtotal'] = 3;

    expect(fn () => app(CompleteSale::class)->handle($tamperedCart, $user->id, 1000))
        ->toThrow(ValidationException::class);

    expect((float) $product->fresh()->inventory->quantity)->toBe(2.0);

    $sale = app(CompleteSale::class)->handle($cart, $user->id, 100);

    expect((float) $product->fresh()->inventory->quantity)->toBe(1.0)
        ->and(InventoryMovement::where('product_id', $product->id)->where('type', 'sale')->count())->toBe(1)
        ->and((float) $sale->total)->toBe(100.0);
});
