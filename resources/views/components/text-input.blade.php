@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-indigo-200/40 bg-indigo-50/95 text-indigo-900 placeholder-indigo-400 focus:border-indigo-300 focus:ring-indigo-200 rounded-md shadow-sm']) }}>
