<button {{ $attributes->merge(['type' => 'submit', 'class' => 'space-button inline-flex items-center px-4 py-2 border font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:ring-offset-0 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
