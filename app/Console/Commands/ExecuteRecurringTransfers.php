<?php

namespace App\Console\Commands;

use App\Actions\ExecuteRecurringTransfer;
use App\Models\RecurringTransfer;
use Illuminate\Console\Command;

class ExecuteRecurringTransfers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:execute-recurring-transfers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute all due recurring transfers';

    /**
     * Execute the console command.
     */
    public function handle(ExecuteRecurringTransfer $executeRecurringTransfer)
    {
        $this->info('Executing execute recurring transfers');

        $dueTransfers = RecurringTransfer::where('is_active', true)
            ->where('next_execution_date', '<=', now())
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($dueTransfers as $dueTransfer) {
            try {
                $result = $executeRecurringTransfer->execute($dueTransfer);

                if ($result) {
                    $this->info('Sucessfully executed recurring transfer ' . $dueTransfer->id);
                    $successCount++;
                } else {
                    $this->warn('Failed to execute recurring transfer ' . $dueTransfer->id);
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;

                $this->error('Error executing transfer ' . $dueTransfer->id . ': ' . $e->getMessage());
            }
        }

        $this->info('Execution completed. Success: ' . $successCount . ' Failed: ' . $failedCount);

        return Command::SUCCESS;
    }
}
