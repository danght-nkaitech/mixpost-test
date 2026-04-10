<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inovector\Mixpost\Concerns\UsesUserModel;

class PostActivitiesNotificationSubscription extends Model
{
    use HasFactory;
    use UsesUserModel;

    public $table = 'mixpost_post_activities_ns';

    protected $fillable = [
        'user_id',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'user_id');
    }
}
