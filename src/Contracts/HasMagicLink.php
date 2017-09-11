<?php

namespace Cesargb\MagicLink\Contracts;

interface HasMagicLink
{
    /**
     * Create Auth Link.
     *
     * @param int $expired_in
     *
     * @return bool|string
     */
    public function create_magiclink($expired_in);

    /**
     * Auth links has user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function magiclinks();
}
