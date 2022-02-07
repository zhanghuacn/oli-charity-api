<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use ModelFilter;
    use HasExtendsProperty;

    public const TYPE_DONOR = 'DONOR';
    public const TYPE_STAFF = 'STAFF';
    public const TYPE_HOST = 'HOST';
    public const TYPE_SPONSOR = 'SPONSOR';
    public const TYPE_CHARITY = 'CHARITY';

    public const DEFAULT_EXTENDS = [];

    protected $fillable = [
        'code',
        'lottery_code',
        'charity_id',
        'activity_id',
        'user_id',
        'group_id',
        'type',
        'price',
        'amount',
        'anonymous',
        'verified_at',
        'extends',
    ];

    protected $casts = [
        'anonymous' => 'bool',
        'price' => 'float',
        'amount' => 'float',
        'extends' => 'array',
        'verified_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'type' => self::TYPE_DONOR,
    ];

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_ticket');
    }

    public function groupInvite(): HasOne
    {
        return $this->hasOne(GroupInvite::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function attachGroup($group, $pivotData = []): static
    {
        $group = $this->retrieveGroupId($group);
        if (is_null($this->group_id)) {
            $this->group_id = $group;
            $this->save();

            if ($this->relationLoaded('group')) {
                $this->load('group');
            }
        }
        $this->load('groups');
        if (!$this->groups->contains($group)) {
            $this->groups()->attach($group, $pivotData);
            if ($this->relationLoaded('groups')) {
                $this->load('groups');
            }
        }
        return $this;
    }

    public function detachGroup($group): static
    {
        $group = $this->retrieveGroupId($group);
        $this->groups()->detach($group);

        if ($this->relationLoaded('groups')) {
            $this->load('groups');
        }

        if ($this->groups()->count() === 0 || $this->group_id === $group) {
            $this->group_id = null;
            $this->save();

            if ($this->relationLoaded('group')) {
                $this->load('group');
            }
        }
        return $this;
    }


    protected function retrieveGroupId($group)
    {
        if (is_object($group)) {
            $group = $group->getKey();
        }
        if (is_array($group) && isset($group['id'])) {
            $group = $group['id'];
        }

        return $group;
    }

    protected static function booted()
    {
        static::saving(
            function (Ticket $ticket) {
                $ticket->code = $ticket->code ?? Str::uuid();
            }
        );
    }
}
