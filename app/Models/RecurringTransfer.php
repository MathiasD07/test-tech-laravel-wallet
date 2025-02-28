<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_email',
        'amount',
        'reason',
        'start_date',
        'end_date',
        'frequency_days',
        'is_active',
        'next_execution_date'
    ];

    protected $casts = [
        'amount' => 'integer',
        'frequency_days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
        'next_execution_date' => 'date'
    ];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
