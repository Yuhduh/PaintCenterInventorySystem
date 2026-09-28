<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\MixingTransaction;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompleteSale
{
    /**
     * @param  array<string, array<string, mixed>>  $cart
     */
    public function handle(array $cart, int $userId, float $paymentAmount): Sale
    {
        if ($cart === []) {
            throw ValidationException::withMessages(['cart' => 'Add at least one item before checkout.']);
        }

        return DB::transaction(function () use ($cart, $userId, $paymentAmount): Sale {
            $normalizedLines = $this->normalizeLines($cart);
            $total = round(collect($normalizedLines)->sum('subtotal'), 2);

            if ($paymentAmount < $total) {
                throw ValidationException::withMessages([
                    'tenderedAmount' => 'The tendered amount must be at least '.number_format($total, 2).'.',
                ]);
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'invoice_number' => 'SALE-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'sold_at' => now(),
                'type' => collect($normalizedLines)->contains('type', 'custom_mix') ? 'mixed' : 'normal',
                'subtotal' => $total,
                'total' => $total,
                'payment_method' => 'cash',
                'payment_amount' => $paymentAmount,
                'change_amount' => round($paymentAmount - $total, 2),
            ]);

            foreach ($normalizedLines as $line) {
                if ($line['type'] === 'custom_mix') {
                    $sale->items()->create([
                        'description' => $line['description'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'subtotal' => $line['subtotal'],
                    ]);
                    $mix = MixingTransaction::create([
                        'sale_id' => $sale->id,
                        'price_basis_product_id' => $line['basis_product_id'],
                        'resulting_quantity' => $line['resulting_quantity'],
                        'resulting_unit' => $line['resulting_unit'],
                        'notes' => $line['notes'],
                    ]);
                    foreach ($line['components'] as $component) {
                        $mix->components()->create($component);
                    }

                    continue;
                }

                $inventory = Inventory::query()
                    ->where('product_id', $line['product_id'])
                    ->lockForUpdate()
                    ->firstOrCreate(['product_id' => $line['product_id']], ['quantity' => 0]);
                $before = (float) $inventory->quantity;
                if ($before < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'tenderedAmount' => "Insufficient stock for {$line['product_name']}.",
                    ]);
                }

                $inventory->decrement('quantity', $line['quantity']);
                $sale->items()->create([
                    'product_id' => $line['product_id'],
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);
                InventoryMovement::create([
                    'product_id' => $line['product_id'],
                    'user_id' => $userId,
                    'type' => 'sale',
                    'quantity_change' => -$line['quantity'],
                    'quantity_before' => $before,
                    'quantity_after' => $before - $line['quantity'],
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                ]);
            }

            AuditLog::create([
                'user_id' => $userId,
                'event' => 'sale_completed',
                'auditable_type' => Sale::class,
                'auditable_id' => $sale->id,
                'context' => ['total' => $total, 'lines' => count($normalizedLines)],
            ]);

            return $sale;
        });
    }

    /**
     * Rebuild prices and product details from trusted database records.
     *
     * @param  array<string, array<string, mixed>>  $cart
     * @return array<int, array<string, mixed>>
     */
    private function normalizeLines(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $line) {
            $quantity = (float) ($line['quantity'] ?? 0);
            if ($quantity <= 0) {
                throw ValidationException::withMessages(['cart' => 'Every cart quantity must be greater than zero.']);
            }

            if (($line['type'] ?? null) === 'custom_mix') {
                $basis = Product::query()->where('active', true)->findOrFail((int) ($line['basis_product_id'] ?? 0));
                $rawComponents = $line['components'] ?? null;
                if (! is_array($rawComponents)) {
                    throw ValidationException::withMessages(['cart' => 'A custom mix contains invalid components.']);
                }
                $components = [];
                foreach ($rawComponents as $component) {
                    if (! is_array($component)) {
                        throw ValidationException::withMessages(['cart' => 'A custom mix contains an invalid component.']);
                    }

                    $productId = (int) ($component['product_id'] ?? 0);
                    $estimatedQuantity = (float) ($component['estimated_quantity'] ?? 0);
                    $estimatedUnit = trim((string) ($component['estimated_quantity_unit'] ?? ''));
                    if ($estimatedQuantity <= 0 || $estimatedUnit === '' || ! Product::query()->where('active', true)->whereKey($productId)->exists()) {
                        throw ValidationException::withMessages(['cart' => 'A custom mix contains an invalid component.']);
                    }

                    $components[] = [
                        'product_id' => $productId,
                        'estimated_quantity' => $estimatedQuantity,
                        'estimated_quantity_unit' => $estimatedUnit,
                    ];
                }
                if ($components === []) {
                    throw ValidationException::withMessages(['cart' => 'A custom mix must contain at least one component.']);
                }

                $unitPrice = (float) $basis->selling_price;
                $normalized[] = [
                    'type' => 'custom_mix',
                    'description' => trim((string) ($line['description'] ?? 'Custom paint mix')),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($quantity * $unitPrice, 2),
                    'basis_product_id' => $basis->id,
                    'resulting_quantity' => (float) ($line['resulting_quantity'] ?? $quantity),
                    'resulting_unit' => (string) ($line['resulting_unit'] ?? ''),
                    'notes' => filled($line['notes'] ?? null) ? (string) $line['notes'] : null,
                    'components' => $components,
                ];

                continue;
            }

            $product = Product::query()->with('packageUnit')->where('active', true)->findOrFail((int) ($line['product_id'] ?? 0));
            $unitPrice = (float) $product->selling_price;
            $package = trim($product->package_size.' '.$product->packageUnit?->abbreviation);
            $normalized[] = [
                'type' => 'normal',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'description' => trim($product->name.' '.$package),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
            ];
        }

        return $normalized;
    }
}
