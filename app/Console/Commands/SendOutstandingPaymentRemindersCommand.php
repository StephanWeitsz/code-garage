<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use CodeGarage\Payments\Application\Services\PaymentService;

class SendOutstandingPaymentRemindersCommand extends Command
{
    protected $signature = 'payments:send-outstanding-reminders {--force : Ignore normal reminder throttling rules}';

    protected $description = 'Send outstanding payment reminder emails to students with unpaid payment records.';

    public function handle(PaymentService $payments): int
    {
        $sent = $payments->sendScheduledOutstandingReminders((bool) $this->option('force'));

        $this->info("Outstanding payment reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
