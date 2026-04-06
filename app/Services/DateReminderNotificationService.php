<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Notification;
use App\Models\PartnerContact;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DateReminderNotificationService
{
    public const PARTNER_CONTACT_BIRTHDAY_TYPE = 'birthday_reminder';

    public const EVENT_START_TYPE = 'event_start_reminder';

    public const VISIT_START_TYPE = 'visit_start_reminder';

    /**
     * Notify users (with "view partner contacts") one day before each matching birthday (month/day only).
     *
     * @param  CarbonInterface|null  $today  Anchor "today" for matching; defaults to now (app timezone).
     */
    public function dispatchPartnerContactBirthdayReminders(?CarbonInterface $today = null): int
    {
        $today = $today ? Carbon::instance($today)->startOfDay() : Carbon::today();
        $tomorrow = $today->copy()->addDay();

        $contacts = PartnerContact::query()
            ->whereNotNull('birthday')
            ->whereMonth('birthday', $tomorrow->month)
            ->whereDay('birthday', $tomorrow->day)
            ->get();

        if ($contacts->isEmpty()) {
            return 0;
        }

        $users = User::query()
            ->where('is_active', true)
            ->permission('view partner contacts')
            ->get();

        if ($users->isEmpty()) {
            return 0;
        }

        $created = 0;

        foreach ($contacts as $contact) {
            foreach ($users as $user) {
                if ($this->reminderAlreadySentToday(
                    $user->id,
                    PartnerContact::class,
                    $contact->id,
                    self::PARTNER_CONTACT_BIRTHDAY_TYPE,
                    $today
                )) {
                    continue;
                }

                $name = $contact->display_name;
                $dateLabel = $tomorrow->locale(app()->getLocale())->translatedFormat('d F');

                Notification::create([
                    'user_id' => $user->id,
                    'title' => "Ertaga hamkor kontaktning tug'ilgan kuni",
                    'message' => "\"{$name}\" ning tug'ilgan kuni erta ({$dateLabel}).",
                    'type' => self::PARTNER_CONTACT_BIRTHDAY_TYPE,
                    'related_type' => PartnerContact::class,
                    'related_id' => $contact->id,
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Notify each event's responsible user on the calendar start day of the event.
     *
     * @param  CarbonInterface|null  $today  Anchor "today"; defaults to app timezone today.
     */
    public function dispatchEventStartReminders(?CarbonInterface $today = null): int
    {
        $today = $today ? Carbon::instance($today)->startOfDay() : Carbon::today();

        $events = Event::query()
            ->where('status', '!=', 'bekorlangan')
            ->where(function ($query): void {
                $query
                    ->whereNotNull('responsible_user_id')
                    ->orWhereNotNull('created_by');
            })
            ->whereDate('start_datetime', $today->toDateString())
            ->get();

        if ($events->isEmpty()) {
            return 0;
        }

        $created = 0;

        foreach ($events as $event) {
            $created += $this->ensureEventStartReminderFor($event, $today);
        }

        return $created;
    }

    /**
     * Create the "event starts today" notification for the responsible user and/or creator when due.
     * Used by the daily job and immediately after save when the start day is today.
     *
     * @return int Number of new notifications created (0 if none).
     */
    public function ensureEventStartReminderFor(Event $event, ?CarbonInterface $today = null): int
    {
        $today = $today ? Carbon::instance($today)->startOfDay() : Carbon::today();

        if ($event->status === 'bekorlangan' || ! $event->start_datetime) {
            return 0;
        }

        if (! Carbon::instance($event->start_datetime)->isSameDay($today)) {
            return 0;
        }

        $recipientIds = array_values(array_unique(array_filter([
            $event->responsible_user_id ? (int) $event->responsible_user_id : null,
            $event->created_by ? (int) $event->created_by : null,
        ], fn (?int $id): bool => $id !== null && $id > 0)));

        if ($recipientIds === []) {
            return 0;
        }

        $title = "Tadbir boshlanish kuni";
        $message = "\"{$event->display_title}\" tadbri bugun boshlanadi.";

        $created = 0;

        foreach ($recipientIds as $userId) {
            $user = User::query()
                ->whereKey($userId)
                ->where('is_active', true)
                ->first();

            if (! $user) {
                continue;
            }

            if ($this->reminderAlreadySentToday(
                $user->id,
                Event::class,
                (int) $event->getKey(),
                self::EVENT_START_TYPE,
                $today
            )) {
                continue;
            }

            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => self::EVENT_START_TYPE,
                'related_type' => Event::class,
                'related_id' => $event->getKey(),
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Notify each visit's responsible user on the calendar start day of the visit.
     *
     * @param  CarbonInterface|null  $today  Anchor "today"; defaults to app timezone today.
     */
    public function dispatchVisitStartReminders(?CarbonInterface $today = null): int
    {
        $today = $today ? Carbon::instance($today)->startOfDay() : Carbon::today();

        $visits = Visit::query()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query): void {
                $query
                    ->whereNotNull('responsible_user_id')
                    ->orWhereNotNull('created_by');
            })
            ->whereDate('start_date', $today->toDateString())
            ->get();

        if ($visits->isEmpty()) {
            return 0;
        }

        $created = 0;

        foreach ($visits as $visit) {
            $created += $this->ensureVisitStartReminderFor($visit, $today);
        }

        return $created;
    }

    /**
     * Create the "visit starts today" notification for the responsible user and/or creator when due.
     *
     * @return int Number of new notifications created (0 if none).
     */
    public function ensureVisitStartReminderFor(Visit $visit, ?CarbonInterface $today = null): int
    {
        $today = $today ? Carbon::instance($today)->startOfDay() : Carbon::today();

        if ($visit->status === 'cancelled' || ! $visit->start_date) {
            return 0;
        }

        $start = Carbon::parse($visit->start_date)->startOfDay();
        if (! $start->isSameDay($today)) {
            return 0;
        }

        $recipientIds = array_values(array_unique(array_filter([
            $visit->responsible_user_id ? (int) $visit->responsible_user_id : null,
            $visit->created_by ? (int) $visit->created_by : null,
        ], fn (?int $id): bool => $id !== null && $id > 0)));

        if ($recipientIds === []) {
            return 0;
        }

        $title = "Tashrif boshlanish kuni";
        $message = "\"{$visit->display_title}\" tashrifi bugun boshlanadi.";

        $created = 0;

        foreach ($recipientIds as $userId) {
            $user = User::query()
                ->whereKey($userId)
                ->where('is_active', true)
                ->first();

            if (! $user) {
                continue;
            }

            if ($this->reminderAlreadySentToday(
                $user->id,
                Visit::class,
                (int) $visit->getKey(),
                self::VISIT_START_TYPE,
                $today
            )) {
                continue;
            }

            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => self::VISIT_START_TYPE,
                'related_type' => Visit::class,
                'related_id' => $visit->getKey(),
            ]);

            $created++;
        }

        return $created;
    }

    private function reminderAlreadySentToday(
        int $userId,
        string $relatedType,
        int $relatedId,
        string $type,
        CarbonInterface $today
    ): bool {
        return Notification::query()
            ->where('user_id', $userId)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->where('type', $type)
            ->whereDate('created_at', $today->toDateString())
            ->exists();
    }
}
