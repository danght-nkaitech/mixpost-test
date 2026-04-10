<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Casts\PostingScheduleTimesCast;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class PostingSchedule extends Model
{
    use HasFactory;
    use OwnedByWorkspace;

    public $table = 'mixpost_posting_schedule';

    protected $fillable = [
        'uuid',
        'times',
    ];

    protected $casts = [
        'times' => PostingScheduleTimesCast::class,
    ];
}
