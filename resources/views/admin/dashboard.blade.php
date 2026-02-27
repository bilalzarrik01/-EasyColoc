<x-app-layout>
    <x-slot name="header">
        <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
            {{ __('Admin Dashboard') }}
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

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-panel p-5">
                    <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Users</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $stats['users_total'] }}</p>
                    <p class="text-xs text-indigo-100/80 mt-1">
                        Active: {{ $stats['users_active'] }} · Banned: {{ $stats['users_banned'] }}
                    </p>
                </div>

                <div class="space-panel p-5">
                    <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Colocations</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $stats['colocations_total'] }}</p>
                    <p class="text-xs text-indigo-100/80 mt-1">
                        Active: {{ $stats['colocations_active'] }} · Cancelled: {{ $stats['colocations_cancelled'] }}
                    </p>
                </div>

                <div class="space-panel p-5">
                    <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Expenses</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $stats['expenses_total'] }}</p>
                    <p class="text-xs text-indigo-100/80 mt-1">All recorded expenses</p>
                </div>

                <div class="space-panel p-5">
                    <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Total Amount</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-50">
                        {{ number_format($stats['expenses_amount_total'], 2) }}
                    </p>
                    <p class="text-xs text-indigo-100/80 mt-1">Sum of all expenses</p>
                </div>
            </div>

            <div class="space-panel sm:rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="space-title text-lg font-medium text-indigo-50">Users Management</h3>
                    <span class="text-sm text-indigo-100/85">{{ $users->total() }} users</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-indigo-200/20">
                        <thead class="bg-indigo-400/15">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-200/15">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-indigo-50">{{ $user->name }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $user->email }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">
                                        {{ $user->is_global_admin ? 'Global Admin' : 'User' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">
                                        @if ($user->is_banned)
                                            <span class="text-red-300">Banned</span>
                                        @elseif (! $user->is_active)
                                            <span class="text-amber-300">Inactive</span>
                                        @else
                                            <span class="text-emerald-300">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">
                                        @if (auth()->id() === $user->id)
                                            <span class="text-indigo-200/70">Current user</span>
                                        @else
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if ($user->is_banned)
                                                    <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="space-button px-3 py-1 text-xs font-medium">
                                                            Unban
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="rounded-full bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-500">
                                                            Ban
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($user->is_active)
                                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="rounded-full bg-amber-600 px-3 py-1 text-xs font-medium text-white hover:bg-amber-500">
                                                            Deactivate
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="space-button px-3 py-1 text-xs font-medium">
                                                            Activate
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

