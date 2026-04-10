<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;

class Metric extends Model
{
    use HasFactory;
    use OwnedByWorkspace;

    public $table = 'mixpost_metrics';

    protected $fillable = [
        'account_id',
        'data',
        'date',
    ];

    protected $casts = [
        'data' => 'array',
        'date' => 'date',
    ];

    public $timestamps = false;

    public function scopeAccount($query, int $accountId)
    {
        $query->where('account_id', $accountId);
    }
}
