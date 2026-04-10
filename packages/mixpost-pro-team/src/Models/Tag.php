<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class Tag extends Model
{
    use HasFactory;
    use HasUuid;
    use OwnedByWorkspace;

    public $table = 'mixpost_tags';

    protected $fillable = [
        'uuid',
        'name',
        'hex_color',
    ];
}
