<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('system')
            ->logFillable()
            ->logExcept($this->activityLogExceptAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => $this->activityDescription($eventName));
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $properties = collect($activity->properties ?? []);

        $activity->properties = $properties->merge(array_filter([
            'subject_label' => $this->activitySubjectLabel(),
            'subject_type_label' => $this->activitySubjectTypeLabel(),
            'causer_name' => $this->resolveActivityModelLabel($activity->causer),
            'ip_address' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 255, ''),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    protected function activityDescription(string $eventName): string
    {
        return match ($eventName) {
            'created' => "{$this->activitySubjectTypeLabel()} yaratildi",
            'updated' => "{$this->activitySubjectTypeLabel()} tahrirlandi",
            'deleted' => "{$this->activitySubjectTypeLabel()} o'chirildi",
            default => "{$this->activitySubjectTypeLabel()} amali bajarildi",
        };
    }

    protected function activitySubjectLabel(): string
    {
        return $this->resolveActivityModelLabel($this) ?? class_basename($this).' #'.$this->getKey();
    }

    protected function activitySubjectTypeLabel(): string
    {
        return match (class_basename($this)) {
            'Agreement' => 'Kelishuv',
            'AgreementDirection' => "Kelishuv yo'nalishi",
            'AgreementType' => 'Kelishuv turi',
            'Country' => 'Davlat',
            'Department' => "Bo'lim",
            'Document' => 'Hujjat',
            'DocumentType' => 'Hujjat turi',
            'Event' => 'Tadbir',
            'EventType' => 'Tadbir turi',
            'OrganizationType' => 'Tashkilot turi',
            'PartnerContact' => 'Hamkor kontakt',
            'PartnerOrganization' => 'Hamkor tashkilot',
            'Rank' => 'Unvon',
            'User' => 'Foydalanuvchi',
            'Visit' => 'Tashrif',
            'VisitType' => 'Tashrif turi',
            default => Str::headline(class_basename($this)),
        };
    }

    protected function resolveActivityModelLabel(?Model $model): ?string
    {
        if (! $model) {
            return null;
        }

        foreach ([
            'display_title',
            'display_name',
            'full_name',
            'name_uz',
            'name_ru',
            'title_uz',
            'title_ru',
            'short_title_uz',
            'short_title_ru',
            'agreement_number',
            'document_number',
            'short_name',
            'code',
        ] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }

    /**
     * @return array<int, string>
     */
    protected function activityLogExceptAttributes(): array
    {
        return property_exists($this, 'activityLogExcept')
            ? $this->activityLogExcept
            : [];
    }
}
