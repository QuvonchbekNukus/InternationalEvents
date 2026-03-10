<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use LogsModelActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name_ru',
        'name_uz',
        'name_cryl',
        'code',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function responsibleVisits(): HasMany
    {
        return $this->hasMany(Visit::class, 'responsible_department_id');
    }

    public function responsibleEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'responsible_department_id');
    }
}
