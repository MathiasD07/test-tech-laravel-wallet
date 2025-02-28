<?php

namespace Database\Factories;

use App\Models\RecurringTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringTransferFactory extends Factory
{
    protected $model = RecurringTransfer::class;

    public function definition(): array
    {
        $startDate = now()->addDays($this->faker->numberBetween(1, 5));
        $endDate = $startDate->copy()->addMonths($this->faker->numberBetween(1, 12));

        return [
            'recipient_email' => $this->faker->unique()->safeEmail(),
            'amount' => $this->faker->numberBetween(500, 10000),
            'reason'=> $this->faker->sentence(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'frequency_days' => $this->faker->numberBetween(7, 30),
            'next_execution_date' => $startDate,
            'is_active' => true,
        ];
    }
}
