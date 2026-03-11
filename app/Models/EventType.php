<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedAttributes;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    use LogsModelActivity;
    use ResolvesLocalizedAttributes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name_ru',
        'name_uz',
        'name_cryl',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('name');
    }
}
