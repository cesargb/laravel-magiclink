<?php

namespace Cesargb\MagicLink\Models;

use Illuminate\Database\Eloquent\Model;

class MagicLink extends Model
{
    public function user()
    {
        return $this->hasOne(config(config('magicklink.auth_provider').'.model'), config('magiclink.user_primarykey', 'id'), 'users_id');
    }
}
