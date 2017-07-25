<?php

namespace Cesargb\MagicLink\Models;

use Illuminate\Database\Eloquent\Model;

class MagicLink extends Model
{
    public function user()
    {
        return $this->hasOne(config('auth.providers.users.model'), config('magiclink.user_primarykey', 'id'), 'users_id');
    }
}
