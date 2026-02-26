<?php

namespace App\Services;

use App\Models\ReputationLog;
use App\Models\User;

class ReputationService
{
    public function applyDelta(User $user, int $delta, string $reason, ?int $colocationId = null): void
    {
        $user->increment('reputation', $delta);

        ReputationLog::create([
            'user_id' => $user->id,
            'colocation_id' => $colocationId,
            'delta' => $delta,
            'reason' => $reason,
        ]);
    }
}
