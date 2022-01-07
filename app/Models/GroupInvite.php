<?php

namespace App\Models;

use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GroupInvite extends Model
{
    use HasFactory;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    protected $table = 'group_invite';

    public const TYPE_INVITE = 'INVITE';
    public const TYPE_REQUEST = 'REQUEST';

    protected $fillable = [
        'ticket_id',
        'inviter_id',
        'team_id',
        'type',
        'accept_token',
        'deny_token',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id', 'id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    protected static function booted()
    {
        static::saving(
            function (GroupInvite $teamInvite) {
                $teamInvite->accept_token = $teamInvite->accept_token ?? md5(Str::uuid());
                $teamInvite->deny_token = $teamInvite->deny_token ?? md5(Str::uuid());
            }
        );
    }
}
