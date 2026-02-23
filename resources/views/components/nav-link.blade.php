@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-200 text-sm font-medium leading-5 text-indigo-50 focus:outline-none focus:border-indigo-100 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-indigo-200 hover:text-indigo-50 hover:border-indigo-200/60 focus:outline-none focus:text-indigo-50 focus:border-indigo-200/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
