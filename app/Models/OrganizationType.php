<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationType extends Model
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

    public function partnerOrganizations(): HasMany
    {
        return $this->hasMany(PartnerOrganization::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->firstAvailableLocalizedValue('name');
    }
}
