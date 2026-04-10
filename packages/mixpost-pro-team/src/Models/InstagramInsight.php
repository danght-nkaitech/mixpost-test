<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;
use Inovector\Mixpost\Enums\InstagramInsightType;

class InstagramInsight extends Model
{
    use HasFactory;
    use OwnedByWorkspace;

    public $table = 'mixpost_instagram_insights';

    protected $fillable = [
        'account_id',
        'type',
        'value',
        'date',
    ];

    protected $casts = [
        'type' => InstagramInsightType::class,
        'date' => 'date',
    ];

    public function scopeAccount($query, int $accountId)
    {
        $query->where('account_id', $accountId);
    }
}
