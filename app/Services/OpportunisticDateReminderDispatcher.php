<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OpportunisticDateReminderDispatcher
{
    private const CACHE_KEY_NEXT_AT = 'notifications:opportunistic-scan-next-at';

    private const LOCK_KEY = 'notifications:opportunistic-scan';

    public function __construct(
        private DateReminderNotificationService $dateReminders
    ) {}

    /**
     * Run birthday / event-start / visit-start reminder creation when due.
     * Safe to call often: throttled by cache; duplicates prevented in DB per day.
     */
    public function dispatchIfDue(?Authenticatable $user): void
    {
        if ($user === null) {
            return;
        }

        if ($this->isBeforeNextScheduledScan()) {
            return;
        }

        $lock = Cache::lock(self::LOCK_KEY, 120);
        if (! $lock->get()) {
            return;
        }

        try {
            if ($this->isBeforeNextScheduledScan()) {
                return;
            }

            $this->dateReminders->dispatchPartnerContactBirthdayReminders();
            $this->dateReminders->dispatchEventStartReminders();
            $this->dateReminders->dispatchVisitStartReminders();

            $minutes = (int) config('notifications.opportunistic_interval_minutes', 15);
            Cache::put(
                self::CACHE_KEY_NEXT_AT,
                now()->addMinutes($minutes)->toIso8601String(),
                now()->addDay()
            );
        } finally {
            $lock->release();
        }
    }

    private function isBeforeNextScheduledScan(): bool
    {
        $raw = Cache::get(self::CACHE_KEY_NEXT_AT);
        if ($raw === null) {
            return false;
        }

        $next = $raw instanceof Carbon ? $raw : Carbon::parse((string) $raw);

        return now()->lt($next);
    }
}
