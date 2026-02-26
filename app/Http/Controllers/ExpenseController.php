<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Models\User;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function store(
        Request $request,
        Colocation $colocation,
        SettlementService $settlementService
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $this->abortUnlessActiveMember($user, $colocation);
        abort_unless($colocation->status === 'active', 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'payer_id' => ['required', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $isPayerActive = $colocation->activeMembers()
            ->where('users.id', (int) $validated['payer_id'])
            ->exists();

        if (! $isPayerActive) {
            return back()->withErrors([
                'expense' => 'Payer must be an active member.',
            ]);
        }

        if ($validated['category_id'] !== null) {
            $categoryBelongsToColocation = Category::query()
                ->where('id', (int) $validated['category_id'])
                ->where('colocation_id', $colocation->id)
                ->exists();

            if (! $categoryBelongsToColocation) {
                return back()->withErrors([
                    'expense' => 'Selected category does not belong to this colocation.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $colocation): void {
            $expense = Expense::create([
                'colocation_id' => $colocation->id,
                'payer_id' => (int) $validated['payer_id'],
                'category_id' => $validated['category_id'] !== null
                    ? (int) $validated['category_id']
                    : null,
                'title' => trim($validated['title']),
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
            ]);

            $this->createEqualShares($colocation, $expense);
        });

        $settlementService->refreshPendingSettlements($colocation);

        return back()->with('status', 'Expense added.');
    }

    public function destroy(
        Request $request,
        Colocation $colocation,
        Expense $expense,
        SettlementService $settlementService
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $this->abortUnlessActiveMember($user, $colocation);
        abort_unless($expense->colocation_id === $colocation->id, 404);

        $canDelete = $colocation->owner_id === $user->id || $expense->payer_id === $user->id;
        abort_unless($canDelete, 403);

        $expense->delete();

        $settlementService->refreshPendingSettlements($colocation);

        return back()->with('status', 'Expense deleted.');
    }

    private function abortUnlessActiveMember(User $user, Colocation $colocation): void
    {
        $isActiveMember = $colocation->activeMembers()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isActiveMember, 403);
    }

    private function createEqualShares(Colocation $colocation, Expense $expense): void
    {
        $memberIds = $colocation->activeMembers()
            ->orderBy('users.id')
            ->pluck('users.id')
            ->all();

        $memberCount = count($memberIds);
        if ($memberCount === 0) {
            return;
        }

        $totalCents = (int) round(((float) $expense->amount) * 100);
        $baseShare = intdiv($totalCents, $memberCount);
        $remainder = $totalCents % $memberCount;

        $rows = [];

        foreach ($memberIds as $index => $memberId) {
            $shareCents = $baseShare + ($index < $remainder ? 1 : 0);

            $rows[] = [
                'expense_id' => $expense->id,
                'user_id' => $memberId,
                'share_amount' => number_format($shareCents / 100, 2, '.', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ExpenseShare::query()->insert($rows);
    }
}
