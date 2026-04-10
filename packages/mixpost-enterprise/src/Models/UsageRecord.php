<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\OwnedByWorkspace;
use Inovector\MixpostEnterprise\Enums\UsageType;

class UsageRecord extends Model
{
    use HasFactory;
    use MassPrunable;
    use OwnedByWorkspace;

    protected $table = 'mixpost_e_usage_records';

    protected $fillable = [
        'type',
        'created_at',
    ];

    protected $casts = [
        'type' => UsageType::class,
        'created_at' => 'timestamp',
    ];

    public $timestamps = false;

    public function prunable(): Builder
    {
        return static::withoutWorkspace()->where('created_at', '<=', Carbon::now()->subMonth());
    }
}
