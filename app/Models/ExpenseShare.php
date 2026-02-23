<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ExpenseShare extends Model
{
    protected $fillable = [
        'expense_id',
        'user_id',
        'share_amount',
    ];

    protected function casts(): array
    {
        return [
            'share_amount' => 'decimal:2',
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
