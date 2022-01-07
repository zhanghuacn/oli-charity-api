<?php

namespace App\Models;

use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory;
    use Filterable;
    use ModelFilter;

    public const ROLE_ADMIN_SUPER_ADMIN = 'SUPER-ADMIN';

    public const ROLE_CHARITY_SUPER_ADMIN = 'SUPER-ADMIN';
    public const ROLE_CHARITY_STAFF = 'STAFF';

    public const ROLE_SPONSOR_SUPER_ADMIN = 'SUPER-ADMIN';
    public const ROLE_SPONSOR_STAFF = 'STAFF';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = [
        'guard_name',
        'team_id',
        'pivot',
    ];
}
