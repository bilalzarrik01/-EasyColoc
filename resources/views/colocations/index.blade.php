<x-app-layout>
    <x-slot name="header">
        <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
            {{ __('My Colocations') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="space-chip text-indigo-50 px-4 py-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="space-card text-red-100 px-4 py-3 rounded-xl">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-panel sm:rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="space-title text-lg font-medium text-indigo-50">Active memberships</h3>
                    <a href="{{ route('colocations.create') }}"
                       class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                        Create Colocation
                    </a>
                </div>

                @if ($activeColocations->isEmpty())
                    <p class="text-indigo-100/85">You are not in any active colocation yet.</p>
                @else
                    <div class="divide-y divide-indigo-200/20">
                        @foreach ($activeColocations as $colocation)
                            <div class="py-4 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-indigo-50">{{ $colocation->name }}</p>
                                    <p class="text-sm text-indigo-100/85">
                                        Role: {{ strtoupper($colocation->pivot->role) }}
                                    </p>
                                </div>
                                <a href="{{ route('colocations.show', $colocation) }}"
                                   class="space-button px-4 py-2 text-sm font-medium">
                                    Open
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
