<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ReputationLog extends Model
{
    protected $fillable = [
        'user_id',
        'colocation_id',
        'delta',
        'reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function colocation(): BelongsTo
    {
        return $this->belongsTo(Colocation::class);
    }
}
