<?php

namespace App\Support;

final class LocaleLabels
{
    /**
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    public static function map(string $translationPrefix, array $keys): array
    {
        $out = [];

        foreach ($keys as $key) {
            $out[$key] = __($translationPrefix.'.'.$key);
        }

        return $out;
    }
}
