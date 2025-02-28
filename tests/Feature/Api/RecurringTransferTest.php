<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Wallet;

use function Pest\Laravel\actingAs;

test('user can create a recurring transfer', function () {
    $sender = User::factory()->has(Wallet::factory()->richChillGuy())->create();
    $recipient = User::factory()->has(Wallet::factory()->richChillGuy())->create();

    actingAs($sender);

    $response = \Pest\Laravel\postJson('/api/v1/recurring-transfers', [
        'recipient_email' => $recipient->email,
        'amount' => 1000,
        'reason' => 'test',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonths(3)->format('Y-m-d'),
        'frequency_days' => 30
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('recurring_transfers', [
        'user_id' => $sender->id,
        'recipient_email' => $recipient->email,
        'amount' => 1000,
        'reason' => 'test',
        'start_date' => now()->format('Y-m-d 00:00:00'),
        'end_date' => now()->addMonths(3)->format('Y-m-d 00:00:00'),
        'frequency_days' => 30
    ]);

    $this->assertDatabaseHas('wallet_transfers', [
        'source_id' => $sender->id,
        'target_id' => $recipient->id,
        'amount' => 1000,
    ]);
});
