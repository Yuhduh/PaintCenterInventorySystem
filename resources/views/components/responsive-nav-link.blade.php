@props(['active'])

@php
$classes = ($active ?? false)
            ? 'mx-2 block min-h-11 w-[calc(100%-1rem)] rounded-xl bg-violet-600 px-3 py-2.5 text-start text-sm font-bold text-white transition duration-200'
            : 'mx-2 block min-h-11 w-[calc(100%-1rem)] rounded-xl px-3 py-2.5 text-start text-sm font-semibold text-cyan-950 transition duration-200 hover:bg-cyan-50';
@endphp

<a @if($active ?? false) aria-current="page" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
