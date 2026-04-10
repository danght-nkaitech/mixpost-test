<?php

namespace Inovector\Mixpost\Concerns\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Inovector\Mixpost\Models\Setting;

trait UserHasSettings
{
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'user_id');
    }
}
