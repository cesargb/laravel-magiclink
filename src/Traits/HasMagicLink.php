<?php

namespace Cesargb\MagicLink\Traits;

use Cesargb\MagicLink\MagicLink;
use Cesargb\MagicLink\Models\MagicLink as MagicLinkModel;

trait HasMagicLink
{
    public function create_magiclink($redirect_url = '', $expires_in = 0)
    {
        $magiclink = new MagicLink;

        return $magiclink->add($this, $redirect_url, $expires_in);
    }

    public function magiclinks()
    {
        return $this->hasMany(MagicLinkModel::class, 'user_id', config('magiclink.user_primarykey', 'id'));
    }
}
