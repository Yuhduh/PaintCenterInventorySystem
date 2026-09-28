<x-app-layout>
    <div class="app-page max-w-3xl">
        <section class="app-page-header print:hidden">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-cyan-300">Completed sale</p>
                <h1 class="app-page-title">Receipt {{ $sale->invoice_number }}</h1>
                <p class="app-page-description">Review the transaction details or print a customer copy.</p>
            </div>
            <button onclick="window.print()" type="button" class="app-button-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Print receipt
            </button>
        </section>

        <article class="app-panel p-5 sm:p-7">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-950">Grade A Paint Center</h2>
                    <p class="mt-1 font-mono text-xs font-semibold text-violet-700">{{ $sale->invoice_number }}</p>
                </div>
                <div class="text-sm text-slate-500 sm:text-right">
                    <p class="font-semibold text-slate-700">{{ $sale->sold_at->format('M j, Y · g:i A') }}</p>
                    <p>{{ $sale->user?->name }}</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto rounded-xl ring-1 ring-slate-200">
                <table class="app-table min-w-full">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Package</th>
                        <th>Qty</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $item->description }}</td>
                            <td>{{ $item->product?->packageUnit?->abbreviation ?? 'Custom Mix' }}</td>
                            <td class="tabular-nums">{{ $item->quantity }}</td>
                            <td class="text-right font-bold tabular-nums text-slate-950">{{ \App\Support\Currency::format($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <dl class="mt-5 ml-auto grid max-w-sm grid-cols-2 gap-x-5 gap-y-2 text-sm">
                <dt class="text-slate-500">Tendered</dt>
                <dd class="text-right font-semibold tabular-nums text-slate-800">{{ \App\Support\Currency::format($sale->payment_amount) }}</dd>
                <dt class="text-slate-500">Change</dt>
                <dd class="text-right font-semibold tabular-nums text-emerald-700">{{ \App\Support\Currency::format($sale->change_amount) }}</dd>
                <dt class="border-t border-slate-200 pt-3 font-extrabold text-slate-950">Total</dt>
                <dd class="border-t border-slate-200 pt-3 text-right text-xl font-extrabold tabular-nums text-violet-700">{{ \App\Support\Currency::format($sale->total) }}</dd>
            </dl>

            @if ($sale->mixingTransaction)
                <div class="mt-6 rounded-xl bg-cyan-50 p-4">
                    <h3 class="font-extrabold text-cyan-950">Custom mix · {{ $sale->mixingTransaction->resulting_quantity }}
                        {{ $sale->mixingTransaction->resulting_unit }}</h3>
                    <div class="mt-2 space-y-1 text-sm text-cyan-900">
                        @foreach ($sale->mixingTransaction->components as $component)
                            <p><span class="font-semibold">{{ $component->product->name }}</span> · {{ $component->estimated_quantity }}
                                {{ $component->estimated_quantity_unit }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
    </div>
</x-app-layout>
