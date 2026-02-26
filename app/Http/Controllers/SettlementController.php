<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Settlement;
use App\Models\User;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function markPaid(
        Request $request,
        Colocation $colocation,
        Settlement $settlement,
        SettlementService $settlementService
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $this->abortUnlessActiveMember($user, $colocation);

        abort_unless($settlement->colocation_id === $colocation->id, 404);
        abort_unless($settlement->status === 'pending', 422);

        $canMarkPaid = $user->id === $settlement->debtor_id || $user->id === $colocation->owner_id;
        abort_unless($canMarkPaid, 403);

        $settlement->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $settlementService->refreshPendingSettlements($colocation);

        return back()->with('status', 'Settlement marked as paid.');
    }

    private function abortUnlessActiveMember(User $user, Colocation $colocation): void
    {
        $isActiveMember = $colocation->activeMembers()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isActiveMember, 403);
    }
}
