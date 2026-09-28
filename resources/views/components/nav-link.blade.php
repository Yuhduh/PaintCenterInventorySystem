@props(['active'])

@php
$classes = ($active ?? false)
            ? 'relative inline-flex min-h-11 items-center rounded-xl bg-violet-600 px-3 py-2 text-sm font-bold leading-5 text-white shadow-[0_12px_28px_-20px_rgba(124,58,237,0.95)] transition duration-200'
            : 'relative inline-flex min-h-11 items-center rounded-xl px-3 py-2 text-sm font-semibold leading-5 text-cyan-950 transition duration-200 hover:bg-cyan-50 sm:text-blue-100 sm:hover:bg-blue-900 sm:hover:text-white';
@endphp

<a @if($active ?? false) aria-current="page" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
