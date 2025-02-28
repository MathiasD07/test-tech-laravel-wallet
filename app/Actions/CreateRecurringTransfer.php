<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RecurringTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

readonly class CreateRecurringTransfer
{
    public function __construct(
        protected PerformWalletTransfer $performWalletTransfer,
        protected ExecuteRecurringTransfer $executeRecurringTransfer
    )
    {
    }

    public function execute(
        User $user,
        string $recipientEmail,
        int $amount,
        string $reason,
        Carbon $startDate,
        Carbon $endDate,
        int $frequencyDays
    ): RecurringTransfer {
        return DB::transaction(function () use ($user, $recipientEmail, $amount, $reason, $startDate, $endDate, $frequencyDays) {
            $recurringTransfer = $user->recurringTransfers()->create([
                'recipient_email' => $recipientEmail,
                'amount' => $amount,
                'reason' => $reason,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'frequency_days' => $frequencyDays,
                'is_active' => true,
                'next_execution_date' => $startDate
            ]);

            $this->executeRecurringTransfer->execute($recurringTransfer);

            // TODO/IDEA : NOTIFY THE USER

            return $recurringTransfer;
        });
    }
}
