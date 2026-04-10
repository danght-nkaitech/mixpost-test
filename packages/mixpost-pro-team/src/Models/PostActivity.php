<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Concerns\Model\PostActivity\Comment;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Enums\PostActivityType;

class PostActivity extends Model
{
    use Comment;
    use HasFactory;
    use HasUuid;
    use UsesUserModel;

    public $table = 'mixpost_post_activities';

    protected $fillable = [
        'uuid',
        'user_id',
        'parent_id',
        'type',
        'data',
        'text',
    ];

    protected $casts = [
        'type' => PostActivityType::class,
        'data' => 'array',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'user_id');
    }
}
