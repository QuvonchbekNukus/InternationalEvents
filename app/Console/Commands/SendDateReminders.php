<?php

namespace App\Console\Commands;

use App\Services\DateReminderNotificationService;
use Illuminate\Console\Command;

class SendDateReminders extends Command
{
    protected $signature = 'notifications:date-reminders';

    protected $description = 'Create in-app reminders: partner birthdays (day before), events and visits starting today';

    public function handle(DateReminderNotificationService $service): int
    {
        $birthdays = $service->dispatchPartnerContactBirthdayReminders();
        $events = $service->dispatchEventStartReminders();
        $visits = $service->dispatchVisitStartReminders();

        $this->info("Birthday reminders: {$birthdays}, event start: {$events}, visit start: {$visits}.");

        return self::SUCCESS;
    }
}
