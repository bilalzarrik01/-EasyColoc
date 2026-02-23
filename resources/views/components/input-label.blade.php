@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-indigo-100']) }}>
    {{ $value ?? $slot }}
</label>
