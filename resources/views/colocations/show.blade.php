<x-app-layout>
    <x-slot name="header">
        <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
            {{ $colocation->name }}
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
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-indigo-100/85">Owner: {{ $colocation->owner->name }}</p>
                        <p class="text-sm text-indigo-100/85">Status: {{ strtoupper($colocation->status) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($colocation->owner_id === auth()->id())
                            <a href="{{ route('colocations.edit', $colocation) }}"
                               class="space-button inline-flex items-center px-3 py-2 text-sm">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('colocations.cancel', $colocation) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-500">
                                    Cancel
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('colocations.leave', $colocation) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-500">
                                    Leave
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-panel sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Active members</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-indigo-200/20">
                        <thead class="bg-indigo-400/15">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Reputation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-200/15">
                            @foreach ($colocation->activeMembers as $member)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-indigo-50">{{ $member->name }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $member->email }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ strtoupper($member->pivot->role) }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $member->reputation }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
