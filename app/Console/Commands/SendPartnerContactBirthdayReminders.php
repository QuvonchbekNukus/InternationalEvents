<?php

namespace App\Console\Commands;

use App\Services\DateReminderNotificationService;
use Illuminate\Console\Command;

class SendPartnerContactBirthdayReminders extends Command
{
    protected $signature = 'notifications:partner-contact-birthdays';

    protected $description = 'Create in-app notifications one day before partner contact birthdays';

    public function handle(DateReminderNotificationService $service): int
    {
        $count = $service->dispatchPartnerContactBirthdayReminders();

        $this->info("Created {$count} birthday reminder notification(s).");

        return self::SUCCESS;
    }
}
