<?php

namespace App\Console\Commands;

use App\Services\FinancialReminderService;
use Illuminate\Console\Command;

class SendFinancialReminders extends Command
{
    protected $signature = 'reminders:send-financial';

    protected $description = 'Kirim reminder finansial otomatis ke user yang memenuhi syarat';

    public function handle(FinancialReminderService $financialReminderService): int
    {
        $summary = $financialReminderService->dispatchForCurrentHour();

        $this->info(sprintf(
            'Processed: %d | Sent: %d | Daily: %d | Budget: %d | Dream: %d',
            $summary['processed'],
            $summary['sent'],
            $summary['daily'],
            $summary['budget'],
            $summary['dream']
        ));

        return self::SUCCESS;
    }
}
