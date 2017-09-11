<?php

namespace Cesargb\MagicLink\Traits;

use Cesargb\MagicLink\MagicLink;

trait HasMagicLink
{
    public function create_magiclink($expires_in, $redirect_url = '')
    {
        $magiclink = new MagicLink();

        return $magiclink->add($this, $expires_in, $redirect_url);
    }

    public function magiclinks()
    {
        return $this->hasMany(\Cesargb\MagicLink\Models\MagicLink::class, 'user_id', config('magiclink.user_primarykey', 'id'));
    }
}
