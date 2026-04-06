<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use LogsModelActivity;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];
}
