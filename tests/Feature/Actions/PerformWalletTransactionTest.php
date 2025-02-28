<?php

declare(strict_types=1);

use App\Actions\PerformWalletTransaction;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalance;
use App\Models\Wallet;

use App\Notifications\LowBalanceNotification;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->action = app(PerformWalletTransaction::class);
});

test('perform a credit transaction', function () {
    $wallet = Wallet::factory()->forUser()->richChillGuy()->create();

    $this->action->execute($wallet, WalletTransactionType::CREDIT, 100, 'test');

    expect($wallet->balance)->toBe(1_000_100);

    assertDatabaseHas('wallets', [
        'id' => $wallet->id,
        'balance' => 1_000_100,
    ]);

    assertDatabaseHas('wallet_transactions', [
        'amount' => 100,
        'wallet_id' => $wallet->id,
        'type' => WalletTransactionType::CREDIT,
        'reason' => 'test',
    ]);
});

test('perform a debit transaction', function () {
    $wallet = Wallet::factory()->forUser()->richChillGuy()->create();

    $this->action->execute($wallet, WalletTransactionType::DEBIT, 100, 'test');

    expect($wallet->balance)->toBe(999_900);

    assertDatabaseHas('wallets', [
        'id' => $wallet->id,
        'balance' => 999_900,
    ]);

    assertDatabaseHas('wallet_transactions', [
        'amount' => 100,
        'wallet_id' => $wallet->id,
        'type' => WalletTransactionType::DEBIT,
        'reason' => 'test',
    ]);
});

test('cannot perform a debit transaction if balance is insufficient', function () {
    $wallet = Wallet::factory()->forUser()->create();

    expect(function () use ($wallet) {
        $this->action->execute($wallet, WalletTransactionType::DEBIT, 100, 'test');
    })->toThrow(InsufficientBalance::class);

    assertDatabaseHas('wallets', [
        'id' => $wallet->id,
        'balance' => 0,
    ]);

    assertDatabaseCount('wallet_transactions', 0);
});

test('force a debit transaction when balance is insufficient', function () {
    $wallet = Wallet::factory()->forUser()->create();

    $this->action->execute(wallet: $wallet, type: WalletTransactionType::DEBIT, amount: 100, reason: 'test', force: true);

    expect($wallet->balance)->toBe(-100);

    assertDatabaseHas('wallets', [
        'id' => $wallet->id,
        'balance' => -100,
    ]);

    assertDatabaseHas('wallet_transactions', [
        'amount' => 100,
        'wallet_id' => $wallet->id,
        'type' => WalletTransactionType::DEBIT,
        'reason' => 'test',
    ]);
});

test('user is notified when balance is low', function () {
    Notification::fake();

    $wallet = Wallet::factory()->forUser()->richChillGuy()->create();

    $this->action->execute(wallet: $wallet, type: WalletTransactionType::DEBIT, amount: 999_999, reason: 'test', force: true);

    Notification::assertSentTo(
        $wallet->user,
        LowBalanceNotification::class,
        static function (LowBalanceNotification $notification) {
            return $notification->balance === 1;
        }
    );
});

test('user is not notified when balance is sufficient', function () {
    Notification::fake();

    $wallet = Wallet::factory()->forUser()->richChillGuy()->create();

    $this->action->execute(wallet: $wallet, type: WalletTransactionType::DEBIT, amount: 999_000, reason: 'test', force: true);

    Notification::assertNothingSent();
});

test('user is not notified twice in 24 hours', function () {
   Notification::fake();

   $wallet = Wallet::factory()->forUser()->richChillGuy()->create();

   $this->action->execute(wallet: $wallet, type: WalletTransactionType::DEBIT, amount: 999_998, reason: 'test', force: true);

   Notification::assertSentTo(
        $wallet->user,
        LowBalanceNotification::class,
        static function (LowBalanceNotification $notification) {
            return $notification->balance === 2;
        }
    );

    Notification::fake();

    $this->action->execute(wallet: $wallet, type: WalletTransactionType::DEBIT, amount: 1, reason: 'test', force: true);

    Notification::assertNothingSent();
});
