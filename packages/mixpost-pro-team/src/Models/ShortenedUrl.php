<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class ShortenedUrl extends Model
{
    use HasFactory;
    use OwnedByWorkspace;

    public $table = 'mixpost_shortened_urls';

    protected $fillable = [
        'provider',
        'original_url',
        'short_url',
    ];

    const UPDATED_AT = null;
}
