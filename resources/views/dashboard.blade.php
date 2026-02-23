<x-app-layout>
    <x-slot name="header">
        <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
            {{ __('EasyColoc Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="space-chip text-indigo-50 px-4 py-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <div class="space-panel overflow-hidden sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-2">Welcome, {{ auth()->user()->name }}</h3>
                <p class="text-indigo-100/90 mb-4">Start by creating your colocation or opening your active one.</p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('colocations.index') }}"
                       class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                        Open Colocations
                    </a>
                    <a href="{{ route('colocations.create') }}"
                       class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                        Create Colocation
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
