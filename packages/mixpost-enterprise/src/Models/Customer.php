<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inovector\Mixpost\Concerns\UsesUserModel;

class Customer extends Model
{
    use HasFactory;
    use UsesUserModel;

    public $table = 'mixpost_e_customers';

    protected $fillable = [
        'user_id',
        'platform_customer_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::getUserClass(), 'user_id', 'id');
    }
}
