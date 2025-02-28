<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\InsufficientBalance;
use App\Models\RecurringTransfer;
use App\Models\User;

class ExecuteRecurringTransfer
{
    public function __construct(
        protected PerformWalletTransfer $performWalletTransfer,
    )
    {
    }

    public function execute(RecurringTransfer $recurringTransfer): bool
    {
        if (!$recurringTransfer->is_active || $recurringTransfer->next_execution_date->isAfter(now())) {
            return false;
        }

        if ($recurringTransfer->end_date->isBefore(now())) {
            $recurringTransfer->update(['is_active' => false]);
            return false;
        }

        $sender = $recurringTransfer->user;
        $recipient = User::whereEmail($recurringTransfer->recipient_email)->first();

        if (!$recipient) {
            $recurringTransfer->update(['is_active' => false]);
            return false;
        }

        try {
            $this->performWalletTransfer->execute(
                $sender,
                $recipient,
                $recurringTransfer->amount,
                $recurringTransfer->reason
            );

            $this->updateNextExecutionDate($recurringTransfer);

            return true;
        } catch (InsufficientBalance $exception) {
            $this->updateNextExecutionDate($recurringTransfer);

            \Log::warning(
                'Recurring transfer failed due to insufficient balance',
                [
                    'recurring_transfer_id' => $recurringTransfer->id,
                    'user_id' => $sender->id,
                    'amount' => $recurringTransfer->amount
                ]
            );

            return false;
        }
    }

    private function updateNextExecutionDate(RecurringTransfer $recurringTransfer): void
    {
        $nextDate = $recurringTransfer->next_execution_date->addDays($recurringTransfer->frequency_days);

        if ($nextDate->isAfter($recurringTransfer->end_date)) {
            $recurringTransfer->update(['is_active' => false]);
        } else {
            $recurringTransfer->update(['next_execution_date' => $nextDate]);
        }
    }
}
