<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\App;

trait ResolvesLocalizedAttributes
{
    /**
     * @return list<string>
     */
    protected function localizedAttributeCandidates(string $baseAttribute): array
    {
        $localePriority = match (App::currentLocale()) {
            'ru' => ['ru', 'uz', 'cryl'],
            'cryl' => ['cryl', 'uz', 'ru'],
            default => ['uz', 'ru', 'cryl'],
        };

        $candidates = [];

        foreach ($localePriority as $suffix) {
            $candidates[] = $baseAttribute.'_'.$suffix;
        }

        $candidates[] = $baseAttribute;

        return $candidates;
    }

    protected function firstAvailableLocalizedValue(string ...$baseAttributes): string
    {
        foreach ($baseAttributes as $baseAttribute) {
            foreach ($this->localizedAttributeCandidates($baseAttribute) as $attribute) {
                $value = trim((string) $this->getAttribute($attribute));

                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }
}
