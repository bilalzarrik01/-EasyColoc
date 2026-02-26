<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function getMemberSummaries(Colocation $colocation): array
    {
        $state = $this->buildStateInCents($colocation);
        $summaries = [];

        foreach ($state['member_ids'] as $memberId) {
            $summaries[$memberId] = [
                'paid' => $this->fromCents($state['paid'][$memberId]),
                'share' => $this->fromCents($state['share'][$memberId]),
                'balance' => $this->fromCents($state['balances'][$memberId]),
            ];
        }

        return $summaries;
    }

    public function refreshPendingSettlements(Colocation $colocation): void
    {
        $state = $this->buildStateInCents($colocation);
        $balances = $state['balances'];

        DB::transaction(function () use ($colocation, $balances): void {
            Settlement::query()
                ->where('colocation_id', $colocation->id)
                ->where('status', 'pending')
                ->delete();

            $creditors = [];
            $debtors = [];

            foreach ($balances as $userId => $balanceCents) {
                if ($balanceCents > 0) {
                    $creditors[] = [
                        'user_id' => $userId,
                        'amount' => $balanceCents,
                    ];
                }

                if ($balanceCents < 0) {
                    $debtors[] = [
                        'user_id' => $userId,
                        'amount' => abs($balanceCents),
                    ];
                }
            }

            usort($creditors, fn (array $a, array $b): int => $b['amount'] <=> $a['amount']);
            usort($debtors, fn (array $a, array $b): int => $b['amount'] <=> $a['amount']);

            $debtorIndex = 0;
            $creditorIndex = 0;

            while (isset($debtors[$debtorIndex], $creditors[$creditorIndex])) {
                $amountToSettle = min(
                    $debtors[$debtorIndex]['amount'],
                    $creditors[$creditorIndex]['amount']
                );

                if ($amountToSettle <= 0) {
                    break;
                }

                Settlement::create([
                    'colocation_id' => $colocation->id,
                    'debtor_id' => $debtors[$debtorIndex]['user_id'],
                    'creditor_id' => $creditors[$creditorIndex]['user_id'],
                    'amount' => $this->fromCents($amountToSettle),
                    'status' => 'pending',
                    'paid_at' => null,
                ]);

                $debtors[$debtorIndex]['amount'] -= $amountToSettle;
                $creditors[$creditorIndex]['amount'] -= $amountToSettle;

                if ($debtors[$debtorIndex]['amount'] === 0) {
                    $debtorIndex++;
                }

                if ($creditors[$creditorIndex]['amount'] === 0) {
                    $creditorIndex++;
                }
            }
        });
    }

    private function buildStateInCents(Colocation $colocation): array
    {
        $memberIds = $colocation->activeMembers()
            ->pluck('users.id')
            ->all();

        $paid = [];
        $share = [];
        $balances = [];

        foreach ($memberIds as $memberId) {
            $paid[$memberId] = 0;
            $share[$memberId] = 0;
            $balances[$memberId] = 0;
        }

        if ($memberIds === []) {
            return [
                'member_ids' => [],
                'paid' => [],
                'share' => [],
                'balances' => [],
            ];
        }

        $activeMemberLookup = array_flip($memberIds);

        $expenses = Expense::query()
            ->where('colocation_id', $colocation->id)
            ->with(['shares:id,expense_id,user_id,share_amount'])
            ->get(['id', 'payer_id', 'amount']);

        foreach ($expenses as $expense) {
            $expenseAmount = $this->toCents($expense->amount);

            if (isset($activeMemberLookup[$expense->payer_id])) {
                $paid[$expense->payer_id] += $expenseAmount;
                $balances[$expense->payer_id] += $expenseAmount;
            }

            foreach ($expense->shares as $expenseShare) {
                if (! isset($activeMemberLookup[$expenseShare->user_id])) {
                    continue;
                }

                $shareAmount = $this->toCents($expenseShare->share_amount);

                $share[$expenseShare->user_id] += $shareAmount;
                $balances[$expenseShare->user_id] -= $shareAmount;
            }
        }

        $paidSettlements = Settlement::query()
            ->where('colocation_id', $colocation->id)
            ->where('status', 'paid')
            ->get(['debtor_id', 'creditor_id', 'amount']);

        foreach ($paidSettlements as $paidSettlement) {
            if (
                ! isset($activeMemberLookup[$paidSettlement->debtor_id]) ||
                ! isset($activeMemberLookup[$paidSettlement->creditor_id])
            ) {
                continue;
            }

            $paidAmount = $this->toCents($paidSettlement->amount);

            $balances[$paidSettlement->debtor_id] += $paidAmount;
            $balances[$paidSettlement->creditor_id] -= $paidAmount;
        }

        return [
            'member_ids' => $memberIds,
            'paid' => $paid,
            'share' => $share,
            'balances' => $balances,
        ];
    }

    private function toCents(float|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
