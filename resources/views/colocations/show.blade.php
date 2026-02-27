<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
                {{ $colocation->name }}
            </h2>
            <a href="{{ route('colocations.index') }}"
               class="text-sm text-indigo-100/85 hover:text-indigo-50 underline underline-offset-4">
                Back to list
            </a>
        </div>
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

            @php
                $memberCount = $colocation->activeMembers->count();
                $categoryCount = $colocation->categories->count();
                $filteredExpenseCount = $expenses->count();
                $pendingCount = $pendingSettlements->count();
                $pendingTotal = (float) $pendingSettlements->sum('amount');
            @endphp

            <div class="space-panel sm:rounded-2xl p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-indigo-100/85">Owner: {{ $colocation->owner->name }}</p>
                        <p class="text-sm text-indigo-100/85">Status: {{ strtoupper($colocation->status) }}</p>
                        <p class="mt-2 text-xs text-indigo-100/80">
                            {{ $selectedMonth !== '' ? "Viewing month: $selectedMonth" : 'Viewing: all months' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($colocation->owner_id === auth()->id())
                            <a href="{{ route('colocations.edit', $colocation) }}"
                               class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('colocations.cancel', $colocation) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-full bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-500">
                                    Cancel
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('colocations.leave', $colocation) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-full bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-500">
                                    Leave
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                    <div class="space-card p-4">
                        <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Members</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $memberCount }}</p>
                    </div>
                    <div class="space-card p-4">
                        <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Categories</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $categoryCount }}</p>
                    </div>
                    <div class="space-card p-4">
                        <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Expenses</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $filteredExpenseCount }}</p>
                    </div>
                    <div class="space-card p-4">
                        <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Pending</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ $pendingCount }}</p>
                    </div>
                    <div class="space-card p-4">
                        <p class="text-xs text-indigo-100/85 uppercase tracking-wider">Pending total</p>
                        <p class="mt-2 text-2xl font-semibold text-indigo-50">{{ number_format($pendingTotal, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:columns-2 lg:gap-6">
            @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
                <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                    <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Categories</h3>

                    <form method="POST" action="{{ route('categories.store', $colocation) }}" class="flex flex-wrap items-end gap-3 mb-4">
                        @csrf
                        <div class="w-full sm:w-auto sm:min-w-72">
                            <label for="category-name" class="block text-sm text-indigo-100/90 mb-1">New category</label>
                            <input id="category-name"
                                   name="name"
                                   type="text"
                                   required
                                   class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50"
                                   placeholder="Groceries">
                        </div>
                        <button type="submit"
                                class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                            Add category
                        </button>
                    </form>

                    @if ($colocation->categories->isEmpty())
                        <p class="text-indigo-100/85">No categories yet.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($colocation->categories as $category)
                                <div class="inline-flex items-center gap-2 rounded-md bg-indigo-400/15 px-3 py-1.5">
                                    <span class="text-sm text-indigo-50">{{ $category->name }}</span>
                                    <form method="POST" action="{{ route('categories.destroy', [$colocation, $category]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-200 hover:text-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
                <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                    <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Invite a member</h3>
                    <form method="POST" action="{{ route('invitations.store', $colocation) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="w-full sm:w-auto sm:min-w-80">
                            <label for="invite-email" class="block text-sm text-indigo-100/90 mb-1">Email</label>
                            <input id="invite-email"
                                   name="email"
                                   type="email"
                                   required
                                   class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50"
                                   placeholder="friend@example.com">
                        </div>
                        <button type="submit"
                                class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                            Send invitation
                        </button>
                    </form>
                </div>
            @endif

            @if ($colocation->status === 'active')
                <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                    <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Add expense</h3>
                    <form method="POST" action="{{ route('expenses.store', $colocation) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label for="expense-title" class="block text-sm text-indigo-100/90 mb-1">Title</label>
                            <input id="expense-title"
                                   name="title"
                                   type="text"
                                   value="{{ old('title') }}"
                                   required
                                   class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50">
                        </div>

                        <div>
                            <label for="expense-amount" class="block text-sm text-indigo-100/90 mb-1">Amount</label>
                            <input id="expense-amount"
                                   name="amount"
                                   type="number"
                                   step="0.01"
                                   min="0.01"
                                   value="{{ old('amount') }}"
                                   required
                                   class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50">
                        </div>

                        <div>
                            <label for="expense-date" class="block text-sm text-indigo-100/90 mb-1">Date</label>
                            <input id="expense-date"
                                   name="expense_date"
                                   type="date"
                                   value="{{ old('expense_date', now()->toDateString()) }}"
                                   required
                                   class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50">
                        </div>

                        <div>
                            <label for="expense-payer" class="block text-sm text-indigo-100/90 mb-1">Payer</label>
                            <select id="expense-payer"
                                    name="payer_id"
                                    required
                                    class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50">
                                @foreach ($colocation->activeMembers as $member)
                                    <option value="{{ $member->id }}" @selected(old('payer_id', auth()->id()) == $member->id)>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="expense-category" class="block text-sm text-indigo-100/90 mb-1">Category</label>
                            <select id="expense-category"
                                    name="category_id"
                                    class="w-full rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50">
                                <option value="">No category</option>
                                @foreach ($colocation->categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit"
                                    class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                                Add expense
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="space-title text-lg font-medium text-indigo-50">Expenses</h3>

                    <form method="GET" action="{{ route('colocations.show', $colocation) }}" class="flex items-center gap-2">
                        <label for="month-filter" class="text-sm text-indigo-100/90">Month</label>
                        <select id="month-filter"
                                name="month"
                                class="rounded-md border-indigo-300/40 bg-indigo-950/30 text-indigo-50 text-sm">
                            <option value="">All months</option>
                            @foreach ($availableMonths as $month)
                                <option value="{{ $month }}" @selected($selectedMonth === $month)>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="space-button inline-flex items-center px-3 py-2 text-xs font-medium">
                            Apply
                        </button>
                    </form>
                </div>

                @if ($expenses->isEmpty())
                    <p class="text-indigo-100/85">No expenses found for this filter.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-indigo-200/20">
                            <thead class="bg-indigo-400/15">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Title</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Payer</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Category</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-indigo-200/15">
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $expense->expense_date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-50">{{ $expense->title }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $expense->payer->name }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $expense->category?->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ number_format((float) $expense->amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">
                                            @if ($colocation->status === 'active' && ($colocation->owner_id === auth()->id() || $expense->payer_id === auth()->id()))
                                                <form method="POST" action="{{ route('expenses.destroy', [$colocation, $expense]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-red-200 hover:text-red-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Balances</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-indigo-200/20">
                        <thead class="bg-indigo-400/15">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Member</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Paid</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Share</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-200/15">
                            @foreach ($balanceRows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-indigo-50">{{ $row['member']->name }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ number_format((float) $row['paid'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ number_format((float) $row['share'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm {{ (float) $row['balance'] >= 0 ? 'text-emerald-300' : 'text-red-300' }}">
                                        {{ number_format((float) $row['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Who owes who</h3>

                @if ($pendingSettlements->isEmpty())
                    <p class="text-indigo-100/85">No pending settlements.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-indigo-200/20">
                            <thead class="bg-indigo-400/15">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Debtor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Creditor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-indigo-200/15">
                                @foreach ($pendingSettlements as $settlement)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-indigo-50">{{ $settlement->debtor->name }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $settlement->creditor->name }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ number_format((float) $settlement->amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">
                                            @if ($colocation->status === 'active' && (auth()->id() === $settlement->debtor_id || auth()->id() === $colocation->owner_id))
                                                <form method="POST" action="{{ route('settlements.markPaid', [$colocation, $settlement]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="space-button inline-flex items-center px-3 py-2 text-xs font-medium">
                                                        Mark paid
                                                    </button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Paid settlements (last 10)</h3>

                @if ($paidSettlements->isEmpty())
                    <p class="text-indigo-100/85">No paid settlements yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-indigo-200/20">
                            <thead class="bg-indigo-400/15">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Debtor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Creditor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Paid at</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-indigo-200/15">
                                @foreach ($paidSettlements as $settlement)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-indigo-50">{{ $settlement->debtor->name }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $settlement->creditor->name }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ number_format((float) $settlement->amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $settlement->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Active members</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-indigo-200/20">
                        <thead class="bg-indigo-400/15">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Reputation</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-200/15">
                            @foreach ($colocation->activeMembers as $member)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-indigo-50">{{ $member->name }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $member->email }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ strtoupper($member->pivot->role) }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $member->reputation }}</td>
                                    <td class="px-4 py-2 text-sm text-indigo-100/90">
                                        @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active' && $member->id !== $colocation->owner_id)
                                            <form method="POST" action="{{ route('colocations.removeMember', [$colocation, $member]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-red-200 hover:text-red-100">
                                                    Remove
                                                </button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-panel break-inside-avoid mb-6 sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">Invitations</h3>

                @if ($colocation->invitations->isEmpty())
                    <p class="text-indigo-100/85">No invitations yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-indigo-200/20">
                            <thead class="bg-indigo-400/15">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Invited by</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Expires</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-indigo-100 uppercase tracking-wider">Link</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-indigo-200/15">
                                @foreach ($colocation->invitations as $invitation)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-indigo-50">{{ $invitation->email }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ strtoupper($invitation->status) }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">{{ $invitation->inviter?->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">
                                            {{ $invitation->expires_at?->format('Y-m-d H:i') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-indigo-100/90">
                                            @if ($colocation->owner_id === auth()->id())
                                                <a href="{{ route('invitations.show', $invitation->token) }}"
                                                   class="underline hover:text-indigo-50">
                                                    Open
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
