<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class ImportedPost extends Model
{
    use HasFactory;
    use OwnedByWorkspace;

    public $table = 'mixpost_imported_posts';

    protected $fillable = [
        'account_id',
        'provider_post_id',
        'content',
        'metrics',
        'created_at',
    ];

    protected $casts = [
        'content' => 'array',
        'metrics' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;
}
