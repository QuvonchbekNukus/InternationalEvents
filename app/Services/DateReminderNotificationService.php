<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PartnerContact;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DateReminderNotificationService
{
    public const PARTNER_CONTACT_BIRTHDAY_TYPE = 'birthday_reminder';

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
                if ($this->birthdayReminderAlreadySentToday($user->id, $contact->id, $today)) {
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

    private function birthdayReminderAlreadySentToday(int $userId, int $contactId, CarbonInterface $today): bool
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->where('related_type', PartnerContact::class)
            ->where('related_id', $contactId)
            ->where('type', self::PARTNER_CONTACT_BIRTHDAY_TYPE)
            ->whereDate('created_at', $today->toDateString())
            ->exists();
    }
}
