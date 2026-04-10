<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class Template extends Model
{
    use HasFactory;
    use HasUuid;
    use OwnedByWorkspace;

    public $table = 'mixpost_templates';

    protected $fillable = [
        'uuid',
        'name',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'data' => 'array',
    ];
}
