<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class Audience extends Model
{
    use HasFactory;
    use OwnedByWorkspace;

    public $table = 'mixpost_audience';

    protected $fillable = [
        'account_id',
        'total',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public $timestamps = false;

    public function scopeAccount($query, int $accountId)
    {
        $query->where('account_id', $accountId);
    }
}
