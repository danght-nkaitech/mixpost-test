<?php

namespace Inovector\MixpostEnterprise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Casts\EncryptArrayObject;

class PaymentPlatform extends Model
{
    use HasFactory;

    protected $table = 'mixpost_e_payment_platforms';

    protected $fillable = [
        'name',
        'credentials',
        'options',
        'enabled',
    ];

    protected $casts = [
        'credentials' => EncryptArrayObject::class,
        'options' => 'array',
        'enabled' => 'boolean',
    ];

    public $timestamps = false;
}
