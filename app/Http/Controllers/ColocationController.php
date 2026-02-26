<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColocationRequest;
use App\Http\Requests\UpdateColocationRequest;
use App\Models\Colocation;
use App\Models\Settlement;
use App\Models\User;
use App\Services\ReputationService;
use App\Services\SettlementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ColocationController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $activeColocations = $user->colocations()
            ->wherePivotNull('left_at')
            ->where('colocations.status', 'active')
            ->withPivot(['role', 'joined_at'])
            ->orderByDesc('colocations.created_at')
            ->get();

        return view('colocations.index', [
            'activeColocations' => $activeColocations,
        ]);
    }

    public function create(): View
    {
        return view('colocations.create');
    }

    public function store(StoreColocationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasActiveColocation()) {
            return back()
                ->withErrors([
                    'name' => 'You already belong to an active colocation.',
                ])
                ->withInput();
        }

        $colocation = DB::transaction(function () use ($request, $user): Colocation {
            $colocation = Colocation::create([
                'name' => $request->validated('name'),
                'owner_id' => $user->id,
                'status' => 'active',
            ]);

            $colocation->members()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
                'left_at' => null,
            ]);

            return $colocation;
        });

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('status', 'Colocation created successfully.');
    }

    public function show(Request $request, Colocation $colocation, SettlementService $settlementService): View
    {
        $this->abortUnlessActiveMember(auth()->user(), $colocation);

        $colocation->load([
            'activeMembers:id,name,email,reputation',
            'owner:id,name,email',
            'invitations' => function ($query): void {
                $query->with('inviter:id,name,email')
                    ->latest();
            },
            'categories' => function ($query): void {
                $query->orderBy('name');
            },
        ]);

        $selectedMonth = (string) $request->query('month', '');
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = '';
        }

        $expensesQuery = $colocation->expenses()
            ->with([
                'payer:id,name',
                'category:id,name',
            ])
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if ($selectedMonth !== '') {
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $expensesQuery->whereBetween('expense_date', [
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
            ]);
        }

        $expenses = $expensesQuery->get();

        $availableMonths = $colocation->expenses()
            ->orderByDesc('expense_date')
            ->pluck('expense_date')
            ->map(function ($date): string {
                return Carbon::parse($date)->format('Y-m');
            })
            ->unique()
            ->values();

        $pendingSettlements = $colocation->settlements()
            ->where('status', 'pending')
            ->with([
                'debtor:id,name',
                'creditor:id,name',
            ])
            ->orderByDesc('amount')
            ->get();

        $paidSettlements = $colocation->settlements()
            ->where('status', 'paid')
            ->with([
                'debtor:id,name',
                'creditor:id,name',
            ])
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        $memberSummaries = $settlementService->getMemberSummaries($colocation);

        $balanceRows = $colocation->activeMembers
            ->map(function (User $member) use ($memberSummaries): array {
                $summary = $memberSummaries[$member->id] ?? [
                    'paid' => '0.00',
                    'share' => '0.00',
                    'balance' => '0.00',
                ];

                return [
                    'member' => $member,
                    'paid' => $summary['paid'],
                    'share' => $summary['share'],
                    'balance' => $summary['balance'],
                ];
            });

        return view('colocations.show', [
            'colocation' => $colocation,
            'expenses' => $expenses,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
            'pendingSettlements' => $pendingSettlements,
            'paidSettlements' => $paidSettlements,
            'balanceRows' => $balanceRows,
        ]);
    }

    public function edit(Colocation $colocation): View
    {
        $this->abortUnlessOwner(auth()->user(), $colocation);

        return view('colocations.edit', [
            'colocation' => $colocation,
        ]);
    }

    public function update(UpdateColocationRequest $request, Colocation $colocation): RedirectResponse
    {
        $this->abortUnlessOwner(auth()->user(), $colocation);

        $colocation->update([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('status', 'Colocation updated successfully.');
    }

    public function cancel(
        Colocation $colocation,
        SettlementService $settlementService,
        ReputationService $reputationService
    ): RedirectResponse
    {
        $this->abortUnlessOwner(auth()->user(), $colocation);

        if ($colocation->status === 'cancelled') {
            return back()->withErrors([
                'colocation' => 'Colocation is already cancelled.',
            ]);
        }

        $settlementService->refreshPendingSettlements($colocation);
        $memberSummaries = $settlementService->getMemberSummaries($colocation);

        DB::transaction(function () use ($colocation, $memberSummaries, $reputationService): void {
            $activeMembers = $colocation->activeMembers()->get(['users.id', 'users.reputation']);

            foreach ($activeMembers as $activeMember) {
                $balance = (float) ($memberSummaries[$activeMember->id]['balance'] ?? 0);
                $hasDebt = $balance < 0;

                $reputationService->applyDelta(
                    $activeMember,
                    $hasDebt ? -1 : 1,
                    $hasDebt ? 'cancelled_with_debt' : 'cancelled_without_debt',
                    $colocation->id
                );
            }

            $colocation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        });

        $settlementService->refreshPendingSettlements($colocation);

        $colocation->refresh();

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('status', 'Colocation cancelled.');
    }

    public function leave(
        Colocation $colocation,
        SettlementService $settlementService,
        ReputationService $reputationService
    ): RedirectResponse {
        $user = auth()->user();
        $this->abortUnlessActiveMember($user, $colocation);

        if ($colocation->owner_id === $user->id) {
            return back()->withErrors([
                'membership' => 'Owner cannot leave the colocation. Cancel it instead.',
            ]);
        }

        $settlementService->refreshPendingSettlements($colocation);
        $memberSummaries = $settlementService->getMemberSummaries($colocation);
        $userBalance = (float) ($memberSummaries[$user->id]['balance'] ?? 0);
        $hasDebt = $userBalance < 0;

        DB::transaction(function () use ($colocation, $user, $hasDebt, $reputationService): void {
            $colocation->members()
                ->updateExistingPivot($user->id, [
                    'left_at' => now(),
                ]);

            $reputationService->applyDelta(
                $user,
                $hasDebt ? -1 : 1,
                $hasDebt ? 'left_with_debt' : 'left_without_debt',
                $colocation->id
            );
        });

        $settlementService->refreshPendingSettlements($colocation);

        return redirect()
            ->route('colocations.index')
            ->with('status', 'You left the colocation.');
    }

    public function removeMember(
        Request $request,
        Colocation $colocation,
        User $member,
        SettlementService $settlementService,
        ReputationService $reputationService
    ): RedirectResponse {
        $owner = $request->user();
        $this->abortUnlessOwner($owner, $colocation);
        abort_unless($colocation->status === 'active', 403);

        if ($member->id === $colocation->owner_id) {
            return back()->withErrors([
                'membership' => 'Owner cannot be removed.',
            ]);
        }

        $isActiveMember = $colocation->activeMembers()
            ->where('users.id', $member->id)
            ->exists();

        if (! $isActiveMember) {
            return back()->withErrors([
                'membership' => 'Selected user is not an active member.',
            ]);
        }

        $settlementService->refreshPendingSettlements($colocation);
        $memberSummaries = $settlementService->getMemberSummaries($colocation);
        $memberBalance = (float) ($memberSummaries[$member->id]['balance'] ?? 0);
        $hasDebt = $memberBalance < 0;

        DB::transaction(function () use (
            $colocation,
            $member,
            $owner,
            $hasDebt,
            $reputationService
        ): void {
            if ($hasDebt) {
                $pendingMemberDebts = Settlement::query()
                    ->where('colocation_id', $colocation->id)
                    ->where('status', 'pending')
                    ->where('debtor_id', $member->id)
                    ->get();

                foreach ($pendingMemberDebts as $pendingMemberDebt) {
                    if ((int) $pendingMemberDebt->creditor_id === (int) $owner->id) {
                        continue;
                    }

                    Settlement::create([
                        'colocation_id' => $colocation->id,
                        'debtor_id' => $pendingMemberDebt->creditor_id,
                        'creditor_id' => $owner->id,
                        'amount' => $pendingMemberDebt->amount,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }
            }

            $colocation->members()
                ->updateExistingPivot($member->id, [
                    'left_at' => now(),
                ]);

            $reputationService->applyDelta(
                $member,
                $hasDebt ? -1 : 1,
                $hasDebt ? 'removed_with_debt' : 'removed_without_debt',
                $colocation->id
            );
        });

        $settlementService->refreshPendingSettlements($colocation);

        return back()->with('status', 'Member removed.');
    }

    private function abortUnlessActiveMember(User $user, Colocation $colocation): void
    {
        $isMember = $colocation->members()
            ->where('users.id', $user->id)
            ->wherePivotNull('left_at')
            ->exists();

        abort_unless($isMember, 403);
    }

    private function abortUnlessOwner(User $user, Colocation $colocation): void
    {
        abort_unless($colocation->owner_id === $user->id, 403);
    }
}
