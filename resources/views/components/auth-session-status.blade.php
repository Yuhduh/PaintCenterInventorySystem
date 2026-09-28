@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-800']) }}>
        {{ $status }}
    </div>
@endif
