<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Notifications\LowBalanceNotification;
use Illuminate\Support\Facades\Cache;

readonly class PerformCheckLowBalance {
    private const LOW_BALANCE_THRESHOLD_IN_CENTS = 1000;
    private const NOTIFICATION_COOLDOWN_IN_HOURS = 24;

    public function execute(User $user): void
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            return;
        }

        if ($wallet->balance < self::LOW_BALANCE_THRESHOLD_IN_CENTS && !$this->wasAlreadyNotified($user)) {
            $user->notify(new LowBalanceNotification($wallet->balance));

            $this->markAsNotified($user);
        }
    }

    private function wasAlreadyNotified(User $user): bool
    {
        return Cache::has("user.{$user->id}.notifications.low_balance");
    }

    private function markAsNotified(User $user): void
    {
        Cache::put(
            "user.{$user->id}.notifications.low_balance",
            true,
            now()->addHours(self::NOTIFICATION_COOLDOWN_IN_HOURS)
        );
    }
}
