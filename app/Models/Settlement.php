<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    protected $fillable = [
        'colocation_id',
        'debtor_id',
        'creditor_id',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function colocation(): BelongsTo
    {
        return $this->belongsTo(Colocation::class);
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'debtor_id');
    }

    public function creditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creditor_id');
    }
}
