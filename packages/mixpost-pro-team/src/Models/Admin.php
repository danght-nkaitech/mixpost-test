<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inovector\Mixpost\Concerns\UsesUserModel;

class Admin extends Model
{
    use HasFactory;
    use UsesUserModel;

    public $table = 'mixpost_admins';

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'user_id');
    }
}
