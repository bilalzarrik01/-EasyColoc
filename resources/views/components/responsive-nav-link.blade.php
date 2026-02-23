@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-200 text-start text-base font-medium text-indigo-50 bg-indigo-500/20 focus:outline-none focus:text-indigo-50 focus:bg-indigo-400/30 focus:border-indigo-100 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-indigo-200 hover:text-indigo-50 hover:bg-indigo-500/10 hover:border-indigo-200/60 focus:outline-none focus:text-indigo-50 focus:bg-indigo-500/10 focus:border-indigo-200/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
